<?php

/**
 * Estimates Controller
 *
 *
 * @package Sprout_Invoice
 * @subpackage Estimates
 */
class SI_Estimates extends SI_Controller {
	const HISTORY_UPDATE = 'si_history_update';
	const HISTORY_STATUS_UPDATE = 'si_history_status_update';
	const HISTORY_INVOICE_CREATED = 'si_invoice_created';
	const VIEWED_STATUS_UPDATE = 'si_viewed_status_update';

	public static function init() {

		// Unique urls
		add_filter( 'wp_unique_post_slug', array( __CLASS__, 'post_slug' ), 10, 4 );

		// Adjust estimate id and status after clone
		add_action( 'si_cloned_post',  array( __CLASS__, 'adjust_cloned_estimate' ), 10, 3 );

		// reset cached totals
		add_action( 'save_post', array( __CLASS__, 'reset_totals_cache' ) );

		// Send Estimate Avaiable notification on estimate if the status is STATUS_PENDING.
		add_action( 'si_estimate_status_updated', array( __CLASS__, 'maybe_send_estimate_ready' ), 10, 3 );

		// Notifications
		add_filter( 'wp_ajax_sa_send_est_notification', array( __CLASS__, 'maybe_send_notification' ) );

	}


	/**
	 * Send Estimate Avaiable notification on estimate if the status is STATUS_PENDING.
	 *
	 * @since 20.5.2
	 *
	 * @param  SI_Estimate $estimate estimate object.
	 * @param  string      $status  new status.
	 * @param  string      $current_status  current status.
	 *
	 * @return void
	 */
	public static function maybe_send_estimate_ready( $estimate, $status, $current_status ) {
		if ( ! is_a( $estimate, 'SI_Estimate' ) ) {
			return;
		}

		// don't send pending notifications
		$suppress_notification = apply_filters( 'suppress_notifications_pending', false );
		if ( $suppress_notification ) {
			return;
		}

		if ( $estimate->get_status() === SI_Estimate::STATUS_PENDING ) {

			$client = $estimate->get_client();
			if ( ! is_a( $client, 'SI_Client' ) ) {
				return;
			}

			$recipients = $client->get_associated_users();
			if ( empty( $recipients ) ) {
				return;
			}
			do_action( 'send_estimate', $estimate, $recipients );
		}
	}

	///////////////
	// Re-writes //
	///////////////

	/**
	 * Filter the unique post slug.
	 *
	 * This function generates a unique slug for each estimate, allowing users to access the estimate directly
	 * while keeping the post ID hidden for security purposes.
	 *
	 * @todo Clean up the if statements. Check if all are needed or can be combinded.
	 *
	 * @param string $slug          The post slug.
	 * @param int    $post_ID       Post ID.
	 * @param string $post_status   The post status.
	 * @param string $post_type     Post type.
	 *
	 * @return string $slug         The post slug.
	 */
	public static function post_slug( $slug, $post_ID, $post_status, $post_type ) {
		$hashed_post_slug = wp_hash( $slug . microtime() );

		// (Legacy Code) Possibly cloned.
		if ( SI_Estimate::POST_TYPE === $post_type && false !== strpos( $slug, '-2' ) ) {
			return $hashed_post_slug; // add microtime to be unique
		}

		// (Legacy Code) Change every post that has auto-draft or temp status.
		if ( false !== strpos( $slug, __( 'auto-draft' ) ) || SI_Estimate::STATUS_TEMP === $post_status ) {
			return $hashed_post_slug; // add microtime to be unique
		}

		// (Legacy Code) Don't change on front-end edits.
		if ( in_array( $post_status, array( SI_Estimate::STATUS_PENDING, SI_Estimate::STATUS_APPROVED, SI_Estimate::STATUS_DECLINED ), true ) || apply_filters( 'si_is_estimate_currently_custom_status', $post_ID ) ) {
			return $slug;
		}

		// (Legacy Code) Make sure it's a new post.
		if ( ( ! isset( $_POST['post_name'] ) || $_POST['post_name'] == '' ) && SI_Estimate::POST_TYPE === $post_type ) {
			return $hashed_post_slug; // add microtime to be unique
		}
		return $slug;
	}


	/////////////////////
	// AJAX Callbacks //
	/////////////////////

	public static function maybe_send_notification() {
		// form maybe be serialized
		if ( isset( $_REQUEST['serialized_fields'] ) ) {
			$serialized_data = array_map( array( __CLASS__, 'sanitize_serialized_field' ), wp_unslash( $_REQUEST['serialized_fields'] ) ); // phpcs:ignore
			foreach ( $serialized_data as $key => $data ) {
				if ( strpos( $data['name'], '[]' ) !== false ) {
					$_REQUEST[ str_replace( '[]', '', $data['name'] ) ][] = $data['value'];
				} else {
					$_REQUEST[ $data['name'] ] = $data['value'];
				}
			}
		}
		if ( ! isset( $_REQUEST['sa_send_metabox_notification_nonce'] ) ) {
			self::ajax_fail( 'Forget something (nonce)?' ); }

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['sa_send_metabox_notification_nonce'] ) ), SI_Controller::NONCE ) ) {
			self::ajax_fail( 'Not going to fall for it!' ); }

		if ( ! isset( $_REQUEST['sa_send_metabox_doc_id'] ) ) {
			self::ajax_fail( 'Forget something (id)?' ); }

		if ( get_post_type( sanitize_text_field( wp_unslash( $_REQUEST['sa_send_metabox_doc_id'] ) ) ) !== SI_Estimate::POST_TYPE ) {
			return;
		}

		$recipients = ( isset( $_REQUEST['sa_metabox_recipients'] ) )
			? array_map( 'sanitize_text_field', ( wp_unslash( $_REQUEST['sa_metabox_recipients'] ) ) )
			: array();

		if ( isset( $_REQUEST['sa_metabox_custom_recipient'] ) && '' !== trim( sanitize_text_field( wp_unslash( $_REQUEST['sa_metabox_custom_recipient'] ) ) ) ) {
			$submitted_recipients = explode( ',', trim( sanitize_text_field( wp_unslash( $_REQUEST['sa_metabox_custom_recipient'] ) ) ) );
			foreach ( $submitted_recipients as $key => $email ) {
				$email = trim( $email );
				if ( is_email( $email ) ) {
					$recipients[] = $email;
				}
			}
		}

		if ( empty( $recipients ) ) {
			self::ajax_fail( 'A recipient is required.' );
		}

		$estimate = SI_Estimate::get_instance( sanitize_text_field( wp_unslash( $_REQUEST['sa_send_metabox_doc_id'] ) ) );
		$sender_note = isset( $_REQUEST['sa_send_metabox_sender_note'] )
			? sanitize_text_field( wp_unslash( $_REQUEST['sa_send_metabox_sender_note'] ) )
			: '';
		$estimate->set_sender_note( $sender_note );
		$from_email = null;
		$from_name = null;
		if ( isset( $_REQUEST['sa_send_metabox_send_as'] ) ) {
			$name_and_email = SI_Notifications_Control::email_split( sanitize_text_field( wp_unslash( $_REQUEST['sa_send_metabox_send_as'] ) ) );
			if ( is_email( $name_and_email['email'] ) ) {
				$from_name = $name_and_email['name'];
				$from_email = $name_and_email['email'];
			}
		}

		$types = apply_filters( 'si_estimate_notifications_manually_send', array( 'send_estimate' => __( 'Estimate Available', 'sprout-invoices' ) ) );

		$type = ( isset( $_REQUEST['sa_send_metabox_type'] ) //phpcs:ignore
			&& array_key_exists( $_REQUEST['sa_send_metabox_type'], $types ) ) //phpcs:ignore
			? sanitize_text_field( wp_unslash( $_REQUEST['sa_send_metabox_type'] ) ) : 'send_invoice' ; //phpcs:ignore

		$data = array(
			'estimate' => $estimate,
			'client' => $estimate->get_client(),
		);

		// Set Invoice
		$invoice = '';
		if ( $invoice_id = $estimate->get_invoice_id() ) {
			$invoice = SI_Invoice::get_instance( $invoice_id );
			$data['invoice'] = $invoice;
		}

		// send to user
		foreach ( array_unique( $recipients ) as $user_id ) {
			/**
			 * sometimes the recipients list will pass an email instead of an id
			 * attempt to find a user first.
			 */
			if ( is_email( $user_id ) ) {
				if ( $user = get_user_by( 'email', $user_id ) ) {
					$user_id = $user->ID;
					$to = SI_Notifications::get_user_email( $user_id );
				} else { // no user found
					$to = $user_id;
				}
			} else {
				$to = SI_Notifications::get_user_email( $user_id );
			}

			$data['manual_send'] = true;
			$data['to'] = $to;
			$data['user_id'] = $user_id;
			SI_Notifications::send_notification( $type, $data, $to );
		}
		//adds the ability to add a custom notification
		do_action( 'SI_custom_estimate_notification' );

		// If status is temp than change to pending.
		if ( ! in_array( $estimate->get_status(), array( SI_Estimate::STATUS_APPROVED, SI_Estimate::STATUS_PENDING ) ) ) {
			$estimate->set_pending();
		}

		header( 'Content-type: application/json' );
		if ( self::DEBUG ) { header( 'Access-Control-Allow-Origin: *' ); }
		echo wp_json_encode( array( 'response' => __( 'Notification Queued', 'sprout-invoices' ) ) );
		exit();
	}

	////////////
	// Misc. //
	////////////

	public static function reset_totals_cache( $estimate_id = 0 ) {
		$estimate = SI_Estimate::get_instance( $estimate_id );
		if ( ! is_a( $estimate, 'SI_Estimate' ) ) {
			return;
		}

		// reset the totals since payment totals are new.
		$estimate->reset_totals( true );
	}

	/**
	 * Adjust the estimate id
	 * @param  integer $new_post_id
	 * @param  integer $cloned_post_id
	 * @param  string  $new_post_type
	 * @return
	 */
	public static function adjust_cloned_estimate( $new_post_id = 0, $cloned_post_id = 0, $new_post_type = '' ) {
		if ( get_post_type( $new_post_id ) == SI_Estimate::POST_TYPE ) {
			$og_estimate = SI_Estimate::get_instance( $cloned_post_id );
			$og_id       = $og_estimate->get_estimate_id();
			$estimate    = SI_Estimate::get_instance( $new_post_id );

			// Adjust estimate id
			$estimate->set_estimate_id( $new_post_id );

			// Adjust Estimate Post Title.
			if ( ! SA_Addons::is_enabled( 'sprout-invoices-add-on-advanced-id-generation' ) ) {
				$estimate->set_title( $og_estimate->get_title() );
			}

			// Adjust status
			$estimate->set_pending();
		}
	}

	/////////////
	// utility //
	/////////////

	public static function is_edit_screen() {
		$screen = get_current_screen();
		$screen_post_type = str_replace( 'edit-', '', $screen->id );
		if ( $screen_post_type == SI_Estimate::POST_TYPE ) {
			return true;
		}
		return false;
	}
}

<?php


/**
 * Invoices Controller
 *
 *
 * @package Sprout_Invoice
 * @subpackage Invoices
 */
class SI_Invoices extends SI_Controller {
	const HISTORY_UPDATE = 'si_history_update';
	const HISTORY_STATUS_UPDATE = 'si_history_status_update';
	const VIEWED_STATUS_UPDATE = 'si_viewed_status_update';

	public static function init() {

		// Unique urls
		add_filter( 'wp_unique_post_slug', array( __CLASS__, 'post_slug' ), 10, 4 );

		// Create invoice when estimate is approved.
		add_action( 'doc_status_changed',  array( __CLASS__, 'create_invoice_on_est_acceptance' ), 0 ); // fire before any others
		add_action( 'doc_status_changed',  array( __CLASS__, 'create_payment_when_invoice_marked_as_paid' ) );

		// reset invoice object caches
		add_action( 'si_new_payment',  array( __CLASS__, 'reset_invoice_totals_cache' ), -100 );
		add_action( 'si_payment_status_updated',  array( __CLASS__, 'reset_invoice_totals_cache' ), -100 );

		// reset cached totals
		add_action( 'save_post', array( __CLASS__, 'reset_totals_cache' ) );

		// Send Invoice Avaiable notification on invoice if the status is STATUS_PENDING.
		add_action( 'si_invoice_status_updated', array( __CLASS__, 'maybe_send_invoice_ready' ), 10, 3 );

		// Mark paid or partial after payment
		add_action( 'si_new_payment',  array( __CLASS__, 'change_status_after_new_payment' ) );
		add_action( 'si_payment_status_updated',  array( __CLASS__, 'change_status_after_payment_status_update' ) );

		// Cloning from estimates
		add_action( 'si_cloned_post',  array( __CLASS__, 'associate_invoice_after_clone' ), 10, 3 );

		// Adjust invoice id and status after clone
		add_action( 'si_cloned_post',  array( __CLASS__, 'adjust_cloned_invoice' ), 10, 3 );

		// Notifications
		add_filter( 'wp_ajax_sa_send_est_notification', array( __CLASS__, 'maybe_send_notification' ) );

	}

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

		/**
		 * When an invoice is created from an integration we still need to hash the post ID.
		 */
		if ( apply_filters( 'si_invoice_hash', false ) ) {
			return $hashed_post_slug;
		}

		// (Legacy Code) Possibly cloned.
		if ( SI_Invoice::POST_TYPE === $post_type && false !== strpos( $slug, '-2' ) ) {
			return $hashed_post_slug;
		}

		// (Legacy Code) Change every post that has auto-draft.
		if ( false !== strpos( $slug, __( 'auto-draft' ) ) || SI_Invoice::STATUS_TEMP === $post_status ) {
			return $hashed_post_slug;
		}

		// (Legacy Code) Don't change on front-end edits.
		if ( in_array( $post_status, array( SI_Invoice::STATUS_PENDING, SI_Invoice::STATUS_PARTIAL, SI_Invoice::STATUS_PAID, SI_Invoice::STATUS_WO ), true ) || apply_filters( 'si_is_invoice_currently_custom_status', $post_ID ) ) {
			return $slug;
		}

		// (Legacy Code) Make sure it's a new post.
		if ( ( ! isset( $_POST['post_name'] ) || $_POST['post_name'] == '' ) && SI_Invoice::POST_TYPE === $post_type ) {
			return $hashed_post_slug;
		}

		return $slug;
	}

	/**
	 * Send notification for Invoices and Estimates.
	 *
	 * @return void
	 */
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
			self::ajax_fail( 'Forget something (nonce)?' );
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['sa_send_metabox_notification_nonce'] ) ), SI_Controller::NONCE ) ) {
			self::ajax_fail( 'Not going to fall for it!' );
		}

		if ( ! isset( $_REQUEST['sa_send_metabox_doc_id'] ) ) {
			self::ajax_fail( 'Forget something (id)?' );
		}

		if ( get_post_type( sanitize_text_field( wp_unslash( $_REQUEST['sa_send_metabox_doc_id'] ) ) ) !== SI_Invoice::POST_TYPE ) {
			return;
		}

		$recipients = ( isset( $_REQUEST['sa_metabox_recipients'] ) ) ? array_map( 'sanitize_text_field', ( wp_unslash( $_REQUEST['sa_metabox_recipients'] ) ) ) : array();

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

		$invoice = isset( $_REQUEST['sa_send_metabox_doc_id'] )
			? SI_Invoice::get_instance( sanitize_text_field( wp_unslash( $_REQUEST['sa_send_metabox_doc_id'] ) ) )
			: false;
		$sender_note = isset( $_REQUEST['sa_send_metabox_sender_note'] )
			? sanitize_text_field( wp_unslash( $_REQUEST['sa_send_metabox_sender_note'] ) )
			: '';
		$invoice->set_sender_note( $sender_note );

		$from_email = null;
		$from_name  = null;
		if ( isset( $_REQUEST['sa_send_metabox_send_as'] ) ) {
			$name_and_email = SI_Notifications_Control::email_split( sanitize_text_field( wp_unslash( $_REQUEST['sa_send_metabox_send_as'] ) ) );
			if ( is_email( $name_and_email['email'] ) ) {
				$from_name = $name_and_email['name'];
				$from_email = $name_and_email['email'];
			}
		}

		$types = apply_filters( 'si_invoice_notifications_manually_send', array( 'send_invoice' => __( 'Invoice Available', 'sprout-invoices' ), 'reminder_payment' => __( 'Payment Reminder', 'sprout-invoices' ), 'deposit_payment' => __( 'Deposit Payment Received', 'sprout-invoices' ), 'final_payment' => __( 'Invoice Paid', 'sprout-invoices' ) ) );

		$type = ( isset( $_REQUEST['sa_send_metabox_type'] ) && array_key_exists( $_REQUEST['sa_send_metabox_type'], $types ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['sa_send_metabox_type'] ) ) : 'send_invoice' ; //phpcs:ignore

		$data = array(
			'invoice' => $invoice,
			'client' => $invoice->get_client(),
		);

		// Set Estimate
		$estimate = '';
		if ( $estimate_id = $invoice->get_estimate_id() ) {
			$estimate = SI_Estimate::get_instance( $estimate_id );
			$data['estimate'] = $estimate;
		}

		// Set Payment
		$payment = false;
		$inv_payments = $invoice->get_payments();
		if ( ! empty( $inv_payments ) ) {
			$payment = SI_Payment::get_instance( $inv_payments[0] );
			$data['payment'] = $payment;
		}

		if ( ! $payment && in_array( $type, array( 'deposit_payment', 'final_payment' ) ) ) {
			self::ajax_fail( 'Invoice has no payments yet.' );
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

		// Adds the ability to add a custom notification.
		do_action( 'SI_custom_invoice_notification' );

		// If status is temp than change to pending.
		if ( ! in_array( $invoice->get_status(), array( SI_Invoice::STATUS_PENDING, SI_Invoice::STATUS_PARTIAL, SI_Invoice::STATUS_PAID ) ) ) {
			$invoice->set_pending();
		}

		header( 'Content-type: application/json' );
		if ( self::DEBUG ) { header( 'Access-Control-Allow-Origin: *' ); }
		echo wp_json_encode( array( 'response' => __( 'Notification Queued', 'sprout-invoices' ) ) );
		exit();
	}

	////////////
	// Misc. //
	////////////

	/**
	 * Send Invoice Avaiable notification on invoice if the status is STATUS_PENDING.
	 *
	 * @since 20.5.2
	 *
	 * @param  SI_Invoice $invoice invoice object.
	 * @param  string     $status  new status.
	 * @param  string     $current_status  current status.
	 *
	 * @return void
	 */
	public static function maybe_send_invoice_ready( $invoice, $status, $current_status ) {
		if ( ! is_a( $invoice, 'SI_Invoice' ) ) {
			return;
		}

		// don't send pending notifications
		$suppress_notification = apply_filters( 'suppress_notifications_pending', false );
		if ( $suppress_notification ) {
			return;
		}

		if ( $invoice->get_status() === SI_Invoice::STATUS_PENDING ) {

			$client = $invoice->get_client();
			if ( ! is_a( $client, 'SI_Client' ) ) {
				return;
			}

			$recipients = $client->get_associated_users();
			if ( empty( $recipients ) ) {
				return;
			}
			do_action( 'send_invoice', $invoice, $recipients );
		}
	}

	public static function reset_totals_cache( $invoice_id = 0 ) {
		$invoice = SI_Invoice::get_instance( $invoice_id );
		if ( ! is_a( $invoice, 'SI_Invoice' ) ) {
			return;
		}

		// reset the totals since payment totals are new.
		$invoice->reset_totals( true );
	}

	public static function reset_invoice_totals_cache( SI_Payment $payment ) {

		$invoice_id = $payment->get_invoice_id();
		self::reset_totals_cache( $invoice_id );

	}

	public static function change_status_after_new_payment( SI_Payment $payment ) {
		if ( $payment->get_status() === SI_Payment::STATUS_VOID ) {
			return;
		}
		self::change_status_after_payment_status_update( $payment );
	}

	public static function change_status_after_payment_status_update( SI_Payment $payment ) {

		$invoice_id = $payment->get_invoice_id();
		$invoice = SI_Invoice::get_instance( $invoice_id );
		if ( ! is_a( $invoice, 'SI_Invoice' ) ) {
			return;
		}

		switch ( $payment->get_status() ) {

			case SI_Payment::STATUS_PENDING:
				$invoice->set_pending();
			case SI_Payment::STATUS_AUTHORIZED:

				// payments are not retroactivly set to pending or authorized, so don't downgrade the status.
				if ( SI_Invoice::STATUS_TEMP === $invoice->get_status() ) {
					$invoice->set_pending();
				}

				break;

			case SI_Payment::STATUS_COMPLETE:

				if ( $invoice->get_balance() >= 0.01 ) {
					$invoice->set_as_partial();
				} else { // else there's no balance
					$invoice->set_as_paid();
				}

			case SI_Payment::STATUS_VOID:
			case SI_Payment::STATUS_REFUND:

				if ( $invoice->get_balance() >= 0.01 ) {
					if ( $invoice->get_payments_total( false ) >= 0.01 ) {
						$invoice->set_as_partial();
					} else {
						$invoice->set_pending();
					}
				} else { // else there's no balance
					$invoice->set_as_paid();
				}

				break;

			case SI_Payment::STATUS_RECURRING:
			case SI_Payment::STATUS_CANCELLED:
			default:

				// no nothing at this time.

				break;
		}

	}

	/**
	 * Create invoice when estimate is accepted.
	 * @param  object $doc estimate or invoice object
	 * @return int cloned invoice id.
	 */
	public static function create_payment_when_invoice_marked_as_paid( $doc ) {
		if ( ! is_a( $doc, 'SI_Invoice' ) ) {
			return;
		}
		// Check if status changed was to approved.
		if ( SI_Invoice::STATUS_PAID !== $doc->get_status() ) {
			return;
		}
		$balance = $doc->get_balance();
		if ( $balance < 0.01 ) {
			return;
		}
		SI_Admin_Payment::create_admin_payment( $doc->get_id(), $balance, '', 'Now', __( 'This payment was automatically added to settle the balance after it was marked as "Paid".', 'sprout-invoices' ) );
	}

	/**
	 * Create invoice when estimate is accepted.
	 * @param  object $doc estimate or invoice object
	 * @return int cloned invoice id.
	 */
	public static function create_invoice_on_est_acceptance( $doc ) {
		if ( ! is_a( $doc, 'SI_Estimate' ) ) {
			return;
		}
		// Check if status changed was to approved.
		if ( SI_Estimate::STATUS_APPROVED !== $doc->get_status() ) {
			return;
		}
		if ( apply_filters( 'si_disable_create_invoice_on_est_acceptance', false, $doc ) ) {
			return;
		}

		$invoice_post_id = self::clone_post( $doc->get_id(), SI_Invoice::STATUS_PENDING, SI_Invoice::POST_TYPE );
		$invoice         = SI_Invoice::get_instance( $invoice_post_id );

		// Reset Totals.
		$invoice->reset_totals();

		// Reset sender notes.
		$invoice->set_sender_note();

		// Transfer over notes from estimate.
		$estimate_notes = $doc->get_notes();
		$invoice->set_notes( $estimate_notes );

		if ( apply_filters( 'si_estimate_create_invoice_temp_status', false, $doc ) ) {
			// Set Invoice to Draft.
			$invoice->set_status('Draft');
			return;
		}else{
			// Set Invoice to Pending payment.
			$invoice->set_pending();
		}

		do_action( 'si_create_invoice_on_est_acceptance', $invoice, $doc );
		return $invoice_post_id;
	}

	/**
	 * Associate a newly cloned invoice with the estimate cloned from
	 * @param  integer $new_post_id
	 * @param  integer $cloned_post_id
	 * @param  string  $new_post_type
	 * @return
	 */
	public static function associate_invoice_after_clone( $new_post_id = 0, $cloned_post_id = 0, $new_post_type = '' ) {
		if ( SI_Estimate::POST_TYPE === get_post_type( $cloned_post_id ) ) {
			if ( SI_Invoice::POST_TYPE === $new_post_type ) {
				$invoice = SI_Invoice::get_instance( $new_post_id );
				$invoice->set_estimate_id( $cloned_post_id );
				$invoice->set_as_temp();
			}
		}
	}

	/**
	 * Adjust the invoice id. Adjust Post title to use accepted estimate title.
	 *
	 * @param  integer $new_post_id    new post id for invoice.
	 * @param  integer $cloned_post_id cloned post id from estimate.
	 * @param  string  $new_post_type  new post type.
	 *
	 * @return void
	 */
	public static function adjust_cloned_invoice( $new_post_id = 0, $cloned_post_id = 0, $new_post_type = '' ) {
		if ( get_post_type( $cloned_post_id ) === SI_Estimate::POST_TYPE ) {
			$estimate = SI_Estimate::get_instance( $cloned_post_id );
			$est_id   = $estimate->get_estimate_id();
			$invoice  = SI_Invoice::get_instance( $new_post_id );
			if ( ! is_a( $invoice, 'SI_Invoice' ) ) {
				return;
			}
			// Adjust invoice id
			$invoice->set_invoice_id( $new_post_id );

			// Update invoice post title to match the cloned estimate.
			wp_update_post(
				array(
					'ID'         => $new_post_id,
					'post_title' => get_the_title( $estimate->get_id() ),
				)
			);

			// Adjust status
			$invoice->set_as_temp();
		} elseif ( get_post_type( $cloned_post_id ) === SI_Invoice::POST_TYPE ) {
			$og_invoice = SI_Invoice::get_instance( $cloned_post_id );
			$og_id      = $og_invoice->get_invoice_id();
			$invoice    = SI_Invoice::get_instance( $new_post_id );

			if ( ! is_a( $invoice, 'SI_Invoice' ) ) {
				return;
			}

			// Adjust invoice id
				$invoice->set_invoice_id( $new_post_id );

			// Adjust Estimate Post Title.
			if ( ! SA_Addons::is_enabled( 'sprout-invoices-add-on-advanced-id-generation' ) ) {
				$invoice->set_title( $og_invoice->get_title() );
			}

			// Adjust status
			$invoice->set_pending();

		}
	}

	//////////////
	// Utility //
	//////////////


	/**
	 * Used to add the invoice post type to some taxonomy registrations.
	 * @param array $post_types
	 */
	public static function add_invoice_post_type_to_taxonomy( $post_types ) {
		$post_types[] = SI_Invoice::POST_TYPE;
		return $post_types;
	}

	public static function is_edit_screen() {
		$screen = get_current_screen();
		$screen_post_type = str_replace( 'edit-', '', $screen->id );
		if ( SI_Invoice::POST_TYPE === $screen_post_type ) {
			return true;
		}
		return false;
	}
}

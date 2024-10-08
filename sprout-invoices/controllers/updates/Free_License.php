<?php


/**
 * Updates class
 *
 * @package Sprout_Invoice
 * @subpackage Updates
 */
class SI_Free_License extends SI_Controller {
	const LICENSE_KEY_OPTION = 'si_free_license_key';
	const LICENSE_UID_OPTION = 'si_uid';
	const API_CB = 'https://sproutinvoices.com/';
	protected static $license_key;
	protected static $uid;

	public static function init() {
		self::$license_key = trim( get_option( self::LICENSE_KEY_OPTION, '' ) );
		self::$uid = trim( get_option( self::LICENSE_UID_OPTION, 0 ) );

		if ( is_admin() ) {
			// AJAX
			add_action( 'wp_ajax_si_get_license',  array( __CLASS__, 'maybe_get_free_license' ), 10, 0 );
			// Register Settings
			add_filter( 'si_settings', array( __CLASS__, 'register_settings' ) );
		}

		add_filter( 'si_get_purchase_link', array( __CLASS__, 'add_uid_to_url' ) );
		add_filter( 'si_get_sa_link', array( __CLASS__, 'add_uid_to_url' ) );

		// Messaging
		add_action( 'si_settings_page',  array( __CLASS__, 'thank_for_registering' ), 10, 0 );

		// upgrade messaging for free
		add_action( 'sprout_settings_inner_header', array( __CLASS__, 'maybe_show_upgrade_messaging' ), 10, 0 );

		//add_action( 'admin_notices',  array( __CLASS__, 'my_promo_message' ), 10, 0 );

		// callback for license
		add_action( 'admin_init', array( __CLASS__, 'init_si_fs_callback' ) );

	}

	public static function license_key() {
		return self::$license_key;
	}

	public static function uid() {
		return self::$uid;
	}

	public static function license_status() {
		return ( self::$license_key ) ? true : false;
	}

	/**
	 * Hooked on init add the settings page and options.
	 *
	 */
	public static function register_settings( $settings = array() ) {
		// Settings
		$settings['si_activation'] = array(
				'title' => __( 'Sprout Invoices Premium', 'sprout-invoices' ),
				'description' => __( 'You\'re using the free version of Sprout Invoices. Upgrade today to unlock more features.', 'sprout-invoices' ),
				'weight' => -PHP_INT_MAX,
				'tab' => 'licensing',
			);
		return $settings;

	}

	///////////
	// AJAX //
	///////////

	public static function maybe_get_free_license() {
		if ( ! isset( $_REQUEST['security'] ) ) {
			self::ajax_fail( 'Forget something?' );
		}

		$nonce = sanitize_text_field( wp_unslash( $_REQUEST['security'] ) );
		if ( ! wp_verify_nonce( $nonce, self::NONCE ) ) {
			self::ajax_fail( 'Not going to fall for it!' );
		}

		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		if ( ! isset( $_REQUEST['license'] ) ) {
			self::ajax_fail( 'No email submitted' );
		}

		$license = sanitize_text_field( wp_unslash( $_REQUEST['license'] ) );

		if ( ! is_email( $license ) ) {
			self::ajax_fail( 'No Email Submitted' );
		}

		$license_response = self::get_free_license( $license );
		if ( is_object( $license_response ) ) {
			$message = __( 'Thank you for registering Sprout Invoices with us.', 'sprout-invoices' );
			$response = array(
					'license' => $license_response->license_key,
					'uid' => $license_response->uid,
					'response' => $message,
					'error' => ! isset( $license_response->license_key ),
				);

			update_option( self::LICENSE_KEY_OPTION, $license_response->license_key );
			update_option( self::LICENSE_UID_OPTION, $license_response->uid );
		} else {
			$message = __( 'License not created.', 'sprout-invoices' );
			$response = array(
					'response' => $message,
					'error' => 1,
				);
		}

		header( 'Content-type: application/json' );
		echo wp_json_encode( $response );
		exit();
	}

	public static function thank_for_registering() {
		if ( ! self::$uid ) {
			return;
		}
	}

	////////////
	// Promos //
	////////////

	public static function my_promo_message() {
		if ( false === SI_Free_License::license_status() ) {
			return;
		}
		printf(
			// translators: 1: opening tags, 2: closing 'strong' tag, 3: opening a tag, 4: closing a tag. 5: closing tags.
			esc_html__(
				'%1$sSprout Invoices Pro Discount%2$s: Just %3$sgenerate a free license key%4$s for your site and a discount will be sent to you instantly.%5$s',
				'sprout-invoices'
			),
			'<div class="updated notice is-dismissible"><p><span class="icon-sproutapps-flat"></span><strong>',
			'</strong>',
			'<a href="' . esc_url( admin_url( 'admin.php?page=sprout-invoices-reports' ) ) . '">',
			'</a>',
			'</p></div>'
		);
	}

	public static function maybe_show_upgrade_messaging() {
		if ( apply_filters( 'show_upgrade_messaging', true ) ) {
			printf(
				// translators: 1: opening tags, 2: closing 'strong' tag, 3: opening a tag, 4: closing tags.
				esc_html__( '%1$sUpgrade Available:%2$s Add awesome reporting and support the future of Sprout Invoices by %3$supgrading', 'sprout-invoices' ),
				'<div class="upgrade_message clearfix"><p><span class="icon-sproutapps-flat"></span><strong>',
				'</strong>',
				'<a href="' . esc_url( si_get_purchase_link() ) . '">',
				'</a>.</p></div>'
			);
		}
	}

	//////////////
	// Utility //
	//////////////


	public static function get_free_license( $license = '' ) {
		$first_name = '';
		$last_name = '';
		$user = get_user_by( 'email', $license );
		if ( is_a( $user, 'WP_User' ) ) {
			$first_name = $user->first_name;
			$last_name = $user->last_name;
		}

		// data to send in our API request
		$api_params = array(
			'action' => 'sgmnt_free_license',
			'item_name' => urlencode( self::PLUGIN_NAME ),
			'url' => urlencode( home_url() ),
			'uid' => $license,
			'first_name' => $first_name,
			'last_name' => $last_name,
		);

		// Call the custom API.
		$response = wp_safe_remote_get( add_query_arg( $api_params, self::API_CB . 'wp-admin/admin-ajax.php' ), array( 'timeout' => 15, 'sslverify' => false ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) ) {
			return false; }

		// decode the license data
		$license_response = json_decode( wp_remote_retrieve_body( $response ) );

		return $license_response;
	}

	public static function add_uid_to_url( $url = '' ) {
		if ( ! self::$uid ) {
			return $url;
		}
		return add_query_arg( array( 'suid' => self::$uid ), $url );
	}

	public static function init_si_fs_callback() {
		if ( ! function_exists( 'si_fs' ) ) {
			return;
		}
		if ( ! self::$uid && si_fs()->is_registered() ) {
			self::after_si_account_connection( si_fs()->get_user() );
		}
	}

	public static function after_si_account_connection( FS_User $user ) {
		if ( ! is_admin() ) {
			return;
		}

		$email = $user->email;

		if ( '' === $email ) {
			return;
		}
		$license = self::get_free_license( $email );
		update_option( self::LICENSE_KEY_OPTION, $license->license_key );
		update_option( self::LICENSE_UID_OPTION, $license->uid );
	}
}

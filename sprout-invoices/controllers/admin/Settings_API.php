<?php

/**
 * Admin settings pages and meta controller.
 *
 * Add APIs for easily adding admin menus and meta boxes.
 *
 * @package Sprout_Invoice
 * @subpackage Settings
 */
class SI_Settings_API extends SI_Controller {

	/**
	 * The name of the settings page.
	 *
	 * @var string
	 *
	 * @since 1.0.0
	 */
	protected static $settings_page;

	/**
	 * The settings array.
	 *
	 * @var array
	 *
	 * @since 1.0.0
	 */
	private static $settings = array();

	/**
	 * Initialize the settings API class.
	 *
	 * @since 20.7.0 added sub admin pages hook.
	 */
	public static function init() {

		// tabs for settings pages
		add_action( 'sprout_settings_header', array( __CLASS__, 'sprout_settings_header' ) );

		add_action( 'sprout_settings_messages', array( __CLASS__, 'sprout_admin_messages' ) );
		add_action( 'sprout_settings_progress', array( __CLASS__, 'sprout_progress_window' ) );
		add_action( 'wp_ajax_si_progress_view', array( __CLASS__, 'ajax_view_sprout_progress_window' ), 10, 0 );

		// Wizard Admin pages
		add_action( 'admin_menu', array( __CLASS__, 'add_sub_admin_pages' ), 20, 0 );

		// scripts
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'register_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_gtag_script' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'script_localization' ) );

		// Rest API
		add_action( 'rest_api_init', array( __CLASS__, 'register_rest_route' ) );

		add_action( 'si_display_settings', array( __CLASS__, 'display_settings' ), 10, 2 );

		add_filter( 'si_settings_sanitize_pre_save', array( __CLASS__, 'maybe_sanitize_value' ), 10, 2 );

		add_action( 'wp_ajax_si_gtag_option_action', array( __CLASS__, 'si_gtag_option' ), '1' );
		add_action( 'wp_ajax_si_stripe_option_action', array( __CLASS__, 'si_stripe_option' ), '1' );

	}

	/**
	 * Add Sub admin pages for Wizard settings.
	 *
	 * This creates the sub admin pages for the Wizard settings buttons to link to.
	 *
	 * @since 20.7.0
	 *
	 * @return void
	 */
	public static function add_sub_admin_pages() {
		$defaults = array(
			'parent'            => '',
			'slug'              => 'undefined_slug',
			'title'             => 'Undefined Title',
			'menu_title'        => 'Undefined Menu Title',
			'tab_title'         => false,
			'weight'            => 10,
			'reset'             => false,
			'section'           => 'theme',
			'show_tabs'         => true,
			'tab_only'          => false,
			'callback'          => array( __CLASS__, 'si_settings_render_settings_page' ),
			'ajax'              => false,
			'ajax_full_page'    => false,
			'add_new'           => '',
			'add_new_post_type' => '',
			'capability'        => 'manage_sprout_invoices_options',
		);

		$sub_pages = apply_filters( 'si_sub_admin_pages', array() );
		if ( empty( $sub_pages ) ) {
			do_action( 'si_error', 'No Subpages', $sub_pages );
			return;
		}

		uasort( $sub_pages, array( __CLASS__, 'sort_by_weight' ) );

		foreach ( $sub_pages as $menu_slug => $args ) {
			do_action( 'si_adding_sub_admin_page', $menu_slug, $args );

			$parsed_args = wp_parse_args( $args, $defaults );

			add_submenu_page(
				self::TEXT_DOMAIN,
				$parsed_args['title'],
				$parsed_args['menu_title'],
				$parsed_args['capability'],
				self::TEXT_DOMAIN . '-' . $menu_slug,
				$parsed_args['callback']
			);

			do_action( 'si_added_sub_admin_page', $menu_slug, $args );
		}
	}

	/**
	 * Render the settings page.
	 *
	 * Renders the settings page for the addon.
	 *
	 * @since 20.7.0
	 *
	 * @return void
	 */
	public static function si_settings_render_settings_page() {
		$sub_pages = apply_filters( 'si_sub_admin_pages', array() );

		uasort( $sub_pages, array( __CLASS__, 'sort_by_weight' ) );

		$current_page = ( isset( $_GET['page'] ) ) ?
			str_replace( 'sprout-invoices-', '', sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) : '';

		$args = array(
			'allsettings'  => self::get_si_settings(),
			'tabs'         => self::get_general_settings_tabs(),
			'current_page' => $current_page,
			'sub_pages'    => $sub_pages,
		);

		self::load_view( 'admin/settings.php', $args );
	}

	/**
	 * Filtered settings.
	 *
	 * This function returns the settings array after it has been filtered by the si_settings filter.
	 *
	 * @since 1.0.0
	 *
	 * @return array $settings Filtered settings.
	 */
	public static function get_si_settings() {
		$settings = apply_filters( 'si_settings', array() );
		uasort( $settings, array( __CLASS__, 'sort_by_weight' ) );
		self::$settings = $settings;
		return $settings;
	}

	/**
	 * Get the general settings tabs.
	 *
	 * This function returns the general settings tabs.
	 *
	 * @since 1.0.0
	 *
	 * @return array $tabs General settings tabs.
	 */
	public static function get_general_settings_tabs() {
		$tabs = array(
			'start'        => __( 'Company Info', 'sprout-invoices' ),
			'invoices'     => __( 'Invoices', 'sprout-invoices' ),
			'estimates'    => __( 'Estimates', 'sprout-invoices' ),
			'localization' => __( 'Currency Formatting', 'sprout-invoices' ),
			'licensing'    => __( 'Licensing', 'sprout-invoices' ),
		);
		$tabs = apply_filters( 'si_general_settings_tabs', $tabs );
		return $tabs;
	}

	/**
	 * Build the tabs for all the admin settings.
	 *
	 * @return void|null if no page is not set.
	 */
	public static function sprout_settings_header() {
		if ( ! isset( $_GET['page'] ) ) {
			return;
		}

		$sub_pages = apply_filters( 'si_sub_admin_pages', array() );

		uasort( $sub_pages, array( __CLASS__, 'sort_by_weight' ) );

		$args = array(
			'sub_pages' => $sub_pages,
		);

		if ( is_plugin_active( 'sprout-invoices-pro/sprout-invoices-pro.php' ) ) {
			self::load_view( 'admin/settings-nav-pro.php', $args );
		} else {
			self::load_view( 'admin/settings-nav.php', $args );
		}
	}

	/**
	 * Sprout Admin Messages.
	 *
	 * This function will display admin messages.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function sprout_admin_messages() {
		$args = array(
			'messages' => apply_filters( 'si_admin_notices', array() ),
		);
		self::load_view( 'admin/si-notices.php', $args );
	}

	/**
	 * Update Sprout Progress Window.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function ajax_view_sprout_progress_window() {
		self::sprout_progress_window();
		exit();
	}

	/**
	 * Sprout Progress Window.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function sprout_progress_window() {
		$args = array(
			'progress' => self::progress_track(),
		);
		self::load_view( 'admin/settings-progress.php', $args );
	}

	/**
	 * Progress Tracker.
	 *
	 * @since 1.0.0
	 *
	 * @return array $progress Progress tracker.
	 */
	public static function progress_track() {
		$num_est                 = wp_count_posts( SI_Estimate::POST_TYPE );
		$num_est->{'auto-draft'} = 0; // remove auto-drafts
		$total_estimates         = array_sum( (array) $num_est );
		$num_inv                 = wp_count_posts( SI_Estimate::POST_TYPE );
		$num_inv->{'auto-draft'} = 0; // remove auto-drafts
		$total_invoices          = array_sum( (array) $num_inv );

		$license_active = false;
		if ( class_exists( 'SI_Updates' ) ) {
			$license_active = ( false !== SI_Updates::license_status() && 'valid' === SI_Updates::license_status() );
		} else {
			$license_active = SI_Free_License::license_status();
		}

		$address     = get_option( SI_Admin_Settings::ADDRESS_OPTION, array() );
		$enabled_pps = get_option( SI_Payment_Processors::ENABLED_PROCESSORS_OPTION, array() );
		$progress    = array(
			array(
				'label'      => __( 'Activate License', 'sprout-invoices' ),
				'aria-label' => __( 'Activate your license to get updates', 'sprout-invoices' ),
				'link'       => admin_url( 'admin.php?page=sprout-invoices-settings' ),
				'status'     => ( $license_active ) ? true : false,
			),
			array(
				'label'      => __( 'Update Company Info', 'sprout-invoices' ),
				'aria-label' =>
					__( 'Update your business information for display on your invoices and estimates.', 'sprout-invoices' ),
				'link'       => admin_url( 'admin.php?page=sprout-invoices-settings' ),
				'status'     => ( empty( $address ) ) ? false : true,
			),
			array(
				'label'      => __( 'Create an Estimate', 'sprout-invoices' ),
				'aria-label' =>
					__( 'Create your first estimate' ),
				'link'       => admin_url( 'post-new.php?post_type=sa_estimate' ),
				'status'     => ( $total_estimates > 0 ) ? true : false,
			),
			array(
				'label'      => __( 'Create an Invoice', 'sprout-invoices' ),
				'aria-label' =>
					__( 'Create an invoice, or accept the estimate to create one automatically' ),
				'link'       => admin_url( 'post-new.php?post_type=sa_invoice' ),
				'status'     => ( $total_invoices > 0 ) ? true : false,
			),
			array(
				'label'      => __( 'Customize with Your Logo & Colors', 'sprout-invoices' ),
				'aria-label' =>
					__( 'Use the customizer to add a custom logo to your invoices and estimates, and alter the the colors to match.', 'sprout-invoices' ),
				'link'       => admin_url( 'admin.php?page=sprout-invoices-settings' ),
				'status'     => ( get_theme_mod( 'si_logo', false ) ) ? true : false,
			),
			array(
				'label'      => __( 'Activate Additional Features', 'sprout-invoices' ),
				'aria-label' =>
					__( 'Manage add-ons, including Client Dashboards and Recurring Payments.', 'sprout-invoices' ),
				'link'       => admin_url( 'admin.php?page=sprout-invoices-addons' ),
				'status'     => ( '' !== get_option( SA_Addons::PROGRESS_TRACKER, '' ) ) ? true : false,
			),
			array(
				'label'      => __( 'Setup Notifications', 'sprout-invoices' ),
				'aria-label' =>
					__( 'Personalize your notifications, and maybe make them pretty with some HTML.', 'sprout-invoices' ),
				'link'       => admin_url( 'admin.php?page=sprout-invoices-notifications' ),
				'status'     => ( '' !== get_option( SI_Notifications_Control::EMAIL_FROM_EMAIL, '' ) ) ? true : false,
			),
			array(
				'label'      => __( 'Setup Payments', 'sprout-invoices' ),
				'aria-label' =>
					__( 'Enable ways for you to get paid!', 'sprout-invoices' ),
				'link'       => admin_url( 'admin.php?page=sprout-invoices-payments' ),
				'status'     => ( ! empty( $enabled_pps ) ) ? true : false,
			),
			array(
				'label'      => __( 'Integrate with a Form Builder', 'sprout-invoices' ),
				'aria-label' =>
					__( 'Mark this complete by installing one of our free integration add-ons from the WordPress.org repo.', 'sprout-invoices' ),
				'link'       => admin_url( 'admin.php?page=sprout-invoices-settings' ),
				'status'     =>
					( class_exists( 'NF_SproutInvoices' ) ||
					class_exists( 'SI_GF_Integration_Addon_Bootstrap' ) ||
					class_exists( 'SI_Formidable' ) ||
					class_exists( 'SI_WPForms' ) ||
					class_exists( 'weforms' ) ) ?
					true : false,
			),
			array(
				'label'      => __( 'Review Import Methods', 'sprout-invoices' ),
				'aria-label' =>
					__( 'If not starting fresh you can import from another source.', 'sprout-invoices' ),
				'link'       => admin_url( 'admin.php?page=sprout-invoices-import' ),
				'status'     => ( '' !== get_option( SI_Importer::PROGRESS_TRACKER, '' ) ) ? true : false,
			),
		);
		return apply_filters( 'si_setup_tracker', $progress );
	}

	/**
	 * Register scripts.
	 *
	 * Register scripts and styles for the settings page.
	 *
	 * @since 1.0.0
	 *
	 * @param string $page The current page.
	 *
	 * @return void
	 */
	public static function register_scripts( $page ) {
		// Check if we are currently viewing our setting page
		if ( strpos( $page, self::TEXT_DOMAIN ) !== false ) {

			// Vue.js
			if ( SI_DEV ) {
				wp_enqueue_script(
					'sprout-invoices-vue',
					SI_URL . '/resources/admin/js/vue.js',
					array(),
					self::SI_VERSION,
					false
				);
			} else {
				wp_enqueue_script(
					'sprout-invoices-vue',
					SI_URL . '/resources/admin/js/vue.min.js',
					array(),
					self::SI_VERSION,
					false
				);
			}
			// SI plugin settings
			wp_enqueue_script( 'sprout-invoices-settings', SI_URL . '/resources/admin/js/settings.js', array( 'sprout-invoices-vue', 'jquery', 'si_admin' ), self::SI_VERSION, true );

			wp_enqueue_style( 'sprout-invoices-settings', SI_URL . '/resources/admin/css/settings.css', array( 'sprout_invoice_admin_css' ), self::SI_VERSION );
		}
	}

	/**
	 * Ajax for stripe option setting.
	 *
	 * This function will be removed in a future release. It is only here to support the current version of Sprout Invoices Pro with Stripe.
	 * After December 1st 2023, this function will be removed as well as Stripe.
	 *
	 * @since 20.5.5
	 *
	 * @return void
	 */
	public static function si_stripe_option() {
		if ( ! wp_verify_nonce( $_POST['nonce'], self::NONCE ) ) {
			wp_die( 'Nonce verification failed', 403 );
		}
		if ( isset( $_POST['data']['si_stripe_option'] ) ) {
			update_option( 'si_stripe_option', $_POST['data']['si_stripe_option'] );
		}
		wp_send_json_success();
	}

	/**
	 * Ajax for gtag option setting.
	 *
	 * @since 20.4.2
	 *
	 * @return void
	 */
	public static function si_gtag_option() {
		if ( ! wp_verify_nonce( $_POST['nonce'], self::NONCE ) ) {
			wp_die( 'Nonce verification failed', 403 );
		}
		if ( isset( $_POST['data']['gtag_option'] ) ) {
			update_option( 'si_gtag_option', $_POST['data']['gtag_option'] );
		}
		wp_send_json_success();
	}

	/**
	 * Create admin notice opt in for Google Analytics.
	 *
	 * @since 20.4.2
	 *
	 * @return void
	 */
	public static function gtag_admin_notice() {
		?>
			<div class="si-gtag-notice notice notice-info is-dismissible">
				<h1><?php esc_html_e( 'Sprout Invoices Analytics', 'sprout-invoices' ); ?></h1>
				<p>
					<?php
					esc_html_e(
						'Sprout Invoices is now using Google Analytics to help us understand how our users are using the plugin. We are not collecting any personal data, and we are not sharing any data with third parties. If you would like to opt out of this, please close this notice.', 'sprout-invoices'
						);
					?>
				</p>
				<p>
					<?php submit_button( 'Allow', 'primary', 'si-gtag-option', false ); ?>
				</p>
			</div>
		<?php
	}

	/**
	 * Enqueue the Google analytics gtag script.
	 *
	 * This script enqueues the gtaag script and adds the gtag configuration on all admin pages of sprout invoices.
	 *
	 * @since 20.4.2
	 */
	public static function enqueue_gtag_script() {

		// Check if Sprout Pro is enabled. We only need to enqueue the opt out message on the free plugin.
		if ( ! is_plugin_active( 'sprout-invoices-pro/sprout-invoices-pro.php' ) ) {
			if ( false !== strpos( get_current_screen()->id, self::TEXT_DOMAIN ) ) {
				if ( ! get_option( 'si_gtag_option' ) ) {
					add_action( 'admin_notices', array( __CLASS__, 'gtag_admin_notice' ) );
				}
			}
			// If the user has opted out of analytic or has not responded to the notice, return and do not enqueue Google Analytics.
			if ( 'false' === get_option( 'si_gtag_option' ) || ! get_option( 'si_gtag_option' ) ) {
				return;
			}
		}

		if ( false !== strpos( get_current_screen()->id, self::TEXT_DOMAIN ) ) {
			$gtag_src = 'https://www.googletagmanager.com/gtag/js?id=G-B88FMGB5RH'; // Sprout Invoices ID.
			// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
			wp_enqueue_script( 'si_google_gtagjs', $gtag_src, false, null, false );
			wp_script_add_data( 'si_google_gtagjs', 'script_execution', 'async' );
			wp_add_inline_script( 'si_google_gtagjs', 'window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);}' );
			wp_add_inline_script( 'si_google_gtagjs', 'gtag("js", new Date());' );
			wp_add_inline_script( 'si_google_gtagjs', 'gtag("set", "G-B88FMGB5RH", true);' ); // Sprout Invoices ID.
		}
	}

	/**
	 * Script localization.
	 *
	 * Localize the settings script.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function script_localization() {
		// Sending data to our plugin settings JS file
		wp_localize_script(
			'sprout-invoices-settings',
			'SI_Settings',
			array(
				'nonce'            => wp_create_nonce( 'wp_rest' ),
				'restURL'          => esc_url_raw( rest_url() ),
				'options'          => apply_filters( 'si_settings_options', self::add_settings_options() ),
				'addons'           => SA_Addons::get_addons_vue(),
				'payment_gateways' => SI_Payment_Processors::get_all_processors(),
				'ccPP'             => SI_Payment_Processors::available_cc_processors(),
			)
		);
	}

	/**
	 * Add settings options.
	 *
	 * @since 1.0.0
	 *
	 * @param array $options The options to add.
	 *
	 * @return array $options The options to add.
	 */
	public static function add_settings_options( $options = array() ) {
		$settings     = self::get_si_settings();
		$san_settings = self::_build_settings_array( $settings );
		return array_merge( $san_settings, $options );
	}


	/**
	 * Build seetings array.
	 *
	 * @since 1.0.0
	 *
	 * @todo Update function name to be in line with standards. Will not be a part of the Wizard update.
	 *
	 * @param array $settings The settings to build.
	 *
	 * @return array $opts The settings array.
	 */
	public static function _build_settings_array( $settings = array() ) {
		$opts = array();
		foreach ( $settings as $section => $setting_section ) {
			if ( isset( $setting_section['settings'] ) ) {
				$options = self::_sanitize_input_array_for_vue( $setting_section['settings'] );
				$opts    = array_merge( $options, $opts );
			} else {
				if ( ! is_array( $setting_section ) ) {
					continue;
				}
				foreach ( $setting_section as $key => $set_sec ) {
					if ( isset( $set_sec['settings'] ) ) {
						$options = self::_sanitize_input_array_for_vue( $set_sec['settings'] );
						$opts    = array_merge( $options, $opts );
					}
				}
			}
		}
		return $opts;
	}

	/**
	 * Sanitize input array for vue.
	 *
	 * @since 1.0.0
	 *
	 * @todo Update function name to be in line with standards. Will not be a part of the Wizard update.
	 *
	 * @param array $settings The settings to sanitize.
	 *
	 * @return array $options The sanitized settings.
	 */
	public static function _sanitize_input_array_for_vue( $settings = array() ) {
		$options = array();
		if ( ! is_array( $settings ) ) {
			return $options;
		}
		foreach ( $settings as $key => $data ) {
			$default = ( isset( $data['option']['default'] ) ) ? $data['option']['default'] : '';
			$options[ self::_sanitize_input_for_vue( $key ) ] = $default;
		}
		return $options;
	}

	/**
	 * Sanitize input for vue.
	 *
	 * @since 1.0.0
	 *
	 * @todo Update function name to be in line with standards. Will not be a part of the Wizard update.
	 *
	 * @param string $key The key to sanitize.
	 *
	 * @return string $key The sanitized key.
	 */
	public static function _sanitize_input_for_vue( $key = '' ) {
		return str_replace( '-', '', $key );
	}

	/**
	 * Maybe sanitize value.
	 *
	 * @since 1.0.0
	 *
	 * @param string $value value to sanitize.
	 * @param string $key key to sanitize.
	 *
	 * @return sanitized value
	 */
	public static function maybe_sanitize_value( $value = '', $key = '' ) {
		$settings = self::get_si_settings();
		foreach ( $settings as $k => $section ) {
			if ( isset( $section['settings'] ) && !empty( $section['settings'] ) ) {
				foreach ( $section['settings'] as $kee => $field ) {
					if ( $key !== $kee ) {
						continue;
					}
					if ( isset( $field['sanitize_callback'] ) && is_callable( $field['sanitize_callback'] ) ) {
						$value = call_user_func( $field['sanitize_callback'], $value );
					}
				}
			}
		}
		return $value;
	}

	/**
	 * Register the rest route.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function register_rest_route() {
		register_rest_route(
			'si-settings/v1',
			'/save',
			array(
				'methods'             => 'POST',
				'permission_callback' => function () {
					return current_user_can( 'manage_sprout_invoices_options' );
				},
				'callback'            => array( __CLASS__, 'rest_save_callback' ),
			)
		);

		register_rest_route(
			'si-settings/v1',
			'/manage-addon',
			array(
				'methods' => 'POST',
				'permission_callback' => function () {
					return current_user_can( 'manage_sprout_invoices_options' );
				},
				'callback' => function () {

					$_POST = stripslashes_deep( $_POST );

					if ( isset( $_POST['activate'] ) ) {
						SA_Addons::activate_addon( sanitize_text_field( wp_unslash( $_POST['activate'] ) ) );
					}

					if ( isset( $_POST['deactivate'] ) ) {
						SA_Addons::deactivate_addon( sanitize_text_field( wp_unslash( $_POST['deactivate'] ) ) );

					}

					do_action( 'si_addons_managed' );

					die( '1' );
				},
			)
		);

		register_rest_route(
			'si-settings/v1',
			'/manage-pp',
			array(
				'methods' => 'POST',
				'permission_callback' => function () {
					return current_user_can( 'manage_sprout_invoices_options' );
				},
				'callback' => function () {

					$_POST = stripslashes_deep( $_POST );

					$update_cc = (isset( $_POST['update_cc'] ) && sanitize_text_field( wp_unslash( $_POST['update_cc'] ) ) ) ? true : false;
					if ( isset( $_POST['activate'] ) ) {
						$active_pp = SI_Payment_Processors::activate_pp( sanitize_text_field( wp_unslash( $_POST['activate'] ) ), $update_cc );
					}

					if ( isset( $_POST['deactivate'] ) ) {
						$active_pp = SI_Payment_Processors::deactivate_pp( sanitize_text_field( wp_unslash( $_POST['deactivate'] ) ) );
					}

					do_action( 'si_pps_managed' );

					wp_send_json_success( $active_pp );
				},
			)
		);
	}

	/**
	 * Save settings rest callback.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function rest_save_callback() {

		// Nonce already checked in rest_cookie_check_errors() from WordPress Rest API.
		$settings = stripslashes_deep( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		foreach ( $settings as $option_key => $value ) {
			if ( substr( $option_key, 0, strlen( 'si_' ) ) === 'si_' ) {
				$value = apply_filters( 'si_settings_sanitize_pre_save', $value, $option_key );
				update_option( $option_key, $value );
			}
		}

		// TODO REMOVE - don't flush the rewrite rules every time settings are saved...this will help those that have already installed though.
		flush_rewrite_rules();

		// Execute functions to save specific settings after options are saved.
		do_action( 'si_settings_saved' );

		$authentication = apply_filters( 'si_api_authentication', 'authentication' );

		// Return 401 if authentication variable is not empty. If there are no payment proccessors active $authentication will be set to 'authentication' and the API will return a 200.
		if ( ! empty( $authentication ) && 'authentication' !== $authentication ) {
				$message = $authentication;
				$status  = 401; // Unauthorized
		} else {
				$message = __( 'Settings saved.', 'sprout-invoices' );
				$status  = 200; // HTTP 200 OK
		}

		wp_send_json( $message, $status );
	}

	/**
	 * Display Settings.
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings The settings to display.
	 * @param bool  $vue Whether to use vue or not.
	 *
	 * @return void
	 */
	public static function display_settings( $settings = array(), $vue = false ) {
		foreach ( $settings as $key => $field ) {
			if ( isset( $field['option'] ) && is_callable( $field['option'] ) ) {
				call_user_func_array( $field['option'], array( $field ) );
			} else {
				printf(
					'<div class="si_field_wrap">%s</div>',
					wp_kses( self::get_display_field( $key, $field, $vue ), self::get_allowed_html() )
				);
			}
		}
	}

	/**
	 * Return an array of allowed HTML tags.
	 *
	 * @return array
	 */
	public static function get_allowed_html() {
		$allowed_html = array(
			// formatting
			'b'        => array(),
			'br'       => array(),
			'strong'   => array(),
			'em'       => array(),
			'style'    => array(
				'type'  => array(),
				'media' => array(),
			),
			'a'        => array(
				'href'              => true,
				'id'                => true,
				'title'             => array(),
				'target'            => array(),
				'data-payment-type' => array(),
				'data-ref'          => array(),
				'data-invoice-id'   => array(),
				'class'             => array(),
				'v-on:click'        => array(),
				'v-bind:class'      => array(),
				'v-if'              => array(),
			),
			'input'    => array(
				'type'              => array(),
				'name'              => array(),
				'id'                => array(),
				'value'             => array(),
				'class'             => array(),
				'placeholder'       => array(),
				'v-model'           => array(),
				'v-if'              => array(),
				'v-else'            => array(),
				'data-payment-type' => array(),
				'checked'           => array(),
				'default'           => array(),
				'v-on:change'       => array(),
				'v-model.lazy'      => array(),
			),
			'label'    => array(
				'for'   => array(),
				'class' => array(),
			),
			'select'   => array(
				'name'        => array(),
				'id'          => array(),
				'class'       => array(),
				'placeholder' => array(),
				'v-model'     => array(),
				'selected'    => array(),
				'value'       => array(),
				'multiple'    => array(),
			),
			'option'   => array(
				'value'            => array(),
				'selected'         => array(),
				'data-action_name' => array(),
				'data-record-type' => array(),
			),
			'span'     => array(
				'id'          => array(),
				'class'       => array(),
				'title'       => array(),
				'v-on:click'  => array(),
				'v-if'        => array(),
				'v-else'      => array(),
				'data-id'     => array(),
				'data-doc-id' => array(),
				'data-nonce'  => array(),
				'default'     => array(),
				'value'       => array(),
			),
			'text'     => array(
				'class'   => array(),
				'value'   => array(),
				'v-model' => array(),
			),
			'textarea' => array(
				'name'        => array(),
				'id'          => array(),
				'class'       => array(),
				'placeholder' => array(),
				'value'       => array(),
				'v-model'     => array(),
				'rows'        => array(),
				'cols'        => array(),
				'style'       => array(),
				'default'     => array(),
			),
			'radio'    => array(
				'name'        => array(),
				'id'          => array(),
				'class'       => array(),
				'placeholder' => array(),
				'value'       => array(),
				'v-model'     => array(),
			),
			'checkbox' => array(
				'name'        => array(),
				'id'          => array(),
				'class'       => array(),
				'placeholder' => array(),
				'value'       => array(),
				'v-model'     => array(),
			),
			'hidden'   => array(
				'name'        => array(),
				'id'          => array(),
				'class'       => array(),
				'placeholder' => array(),
				'value'       => array(),
				'v-model'     => array(),
			),
			'file'     => array(
				'name'        => array(),
				'id'          => array(),
				'class'       => array(),
				'value'       => array(),
				'placeholder' => array(),
				'v-model'     => array(),
			),
			'h1'       => array(),
			'p'        => array(
				'id'    => array(),
				'class' => array(),
			),
			'img'      => array(
				'src'   => array(),
				'class' => array(),
				'v-if'  => array(),
				'id'    => array(),
				'alt'   => array(),
			),
			'button'   => array(
				'id'              => array(),
				'class'           => array(),
				'href'            => array(),
				'v-on:click'      => array(),
				'v-bind:class'    => array(),
				'disabled'        => array(),
				'v-bind:disabled' => array(),
				'data-invoice_id' => array(),
				'data-client_id'  => array(),
			),
			'div'      => array(
				'id'          => array(),
				'class'       => array(),
				'data-doc-id' => array(),
				'v-show'      => array(),
			),
			'ul'       => array(
				'id'    => array(),
				'class' => array(),
			),
			'ol'       => array(
				'id'    => array(),
				'class' => array(),
			),
			'li'       => array(
				'id'    => array(),
				'class' => array(),
			),
			'h3'       => array(
				'id'    => array(),
				'class' => array(),
			),
			'h1'       => array(
				'id'    => array(),
				'class' => array(),
			),
			'h2'       => array(
				'id'    => array(),
				'class' => array(),
			),
			'table'    => array(
				'id'    => array(),
				'class' => array(),
			),
			'thead'    => array(),
			'tbody'    => array(),
			'tr'       => array(
				'id'           => array(),
				'class'        => array(),
				'v-bind:class' => array(),
			),
			'th'       => array(),
			'td'       => array(
				'id'    => array(),
				'class' => array(),
			),
		);

		return $allowed_html;
	}

	/**
	 * Get display field.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The key to get.
	 * @param array  $field The field to get.
	 * @param bool   $vue Whether to use vue or not.
	 *
	 * @return string $fld The field to display.
	 */
	public static function get_display_field( $key = '', $field = array(), $vue = false ) {
		// all inputs need attributes
		if ( ! isset( $field['option']['attributes'] ) || ! is_array( $field['option']['attributes'] ) ) {
			$field['option']['attributes'] = array();
		}
		switch ( $field['option']['type'] ) {
			case 'text':
			case 'input':
				$fld = self::input_field_wrap( self::get_input_field( $key, $field ), $field );
				break;
			case 'textarea':
				$fld = self::input_field_wrap( self::get_textarea_field( $key, $field ), $field );
				break;
			case 'wysiwyg':
				$field['option']['attributes'] = array_merge( array( 'class' => 'si_wysiwyg' ), $field['option']['attributes'] );
				$field['option']['cols']       = 100;
				$fld                           = self::input_field_wrap( self::get_textarea_field( $key, $field ), $field );
				break;
			case 'small-input':
				$field['option']['attributes'] = array_merge( array( 'class' => 'small-input' ), $field['option']['attributes'] );
				$fld                           = self::input_field_wrap( self::get_input_field( $key, $field ), $field );
				break;
			case 'checkbox':
				$fld = self::input_field_wrap( self::get_checkbox_field( $key, $field ), $field );
				break;
			case 'radio':
			case 'radios':
				$fld = self::input_field_wrap( self::get_radio_field( $key, $field ), $field );
				break;
			case 'select':
				$fld = self::input_field_wrap( self::get_select_field( $key, $field ), $field );
				break;
			case 'group-select':
			case 'select-state':
				$fld = self::input_field_wrap( self::get_group_select_field( $key, $field ), $field );
				break;
			case 'file':
				$fld = self::input_field_wrap( self::get_file_input_field( $key, $field ), $field );
				break;
			case 'hidden':
				$fld = self::input_field_wrap( self::get_hidden_input_field( $key, $field ), $field );
				break;
			case 'bypass':
				// don't add view since it's bypassed
				return self::input_field_wrap( $field['option']['output'], $field );

				break;
			default:
				$fld = self::input_field_wrap( self::get_input_field( $key, $field ), $field );
				break;
		}
		if ( $vue ) {
			$fld = str_replace( 'type=', sprintf( 'v-model="vm.%s" type=', self::_sanitize_input_for_vue( $key ) ), $fld );
		}
		return $fld;
	}


	/**
	 * Input field wrap.
	 *
	 * @since 1.0.0
	 *
	 * @param string $input The input to wrap.
	 * @param array  $field The field to wrap.
	 *
	 * @return string $input The wrapped input.
	 */
	public static function input_field_wrap( $input, $field ) {
		$description = ( isset( $field['option']['description'] ) && '' !== $field['option']['description'] ) ? $field['option']['description'] : false;
		if ( $description ) {
			$description = sprintf( '<span class="input_desc help_block">%s</span>', $description );
		}
		return sprintf( '<div class="si_input_field_wrap si_field_wrap_input_%s">%s%s</div>', $field['option']['type'], $input, $description );
	}

	/**
	 * Get input field.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The key to get.
	 * @param array  $field The field to get.
	 *
	 * @return string $input The hidden input.
	 */
	public static function get_input_field( $key, $field = array() ) {
		$default    = ( isset( $field['option']['default'] ) ) ? $field['option']['default'] : '';
		$attributes = '';

		if ( isset( $field['option']['attributes']['class'] ) ) {
			$field['option']['attributes']['class'] = $field['option']['attributes']['class'] . ' si_input';
		} else {
			$field['option']['attributes']['class'] = 'si_input';
		}

		foreach ( $field['option']['attributes'] as $attr => $attr_value ) {
			$attributes .= esc_attr( $attr ) . '="' . esc_attr( $attr_value ) . '" ';
		}

		ob_start();

		?>
		<label for="<?php echo esc_attr( $key ); ?>" class="si_input_label"><?php echo esc_html( $field['label'] ) ?></label>
		<input
			type="<?php echo esc_attr( $field['option']['type'] ); ?>"
			name="<?php echo esc_attr( $key ); ?>"
			id="<?php echo esc_attr( $key ); ?>"
			value="<?php echo esc_attr( $default ); ?>"
			placeholder="<?php echo isset( $field['option']['placeholder'] ) ? esc_attr( $field['option']['placeholder'] ) : ''; ?>"
			size="<?php echo isset( $field['option']['size'] ) ? esc_attr( $field['option']['size'] ) : 40; ?>"
			<?php
				echo $attributes; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
			<?php
				if ( isset( $field['option']['required'] ) && $field['option']['required'] ) {
					echo 'required';
				}
			?>
		/>
		<?php

		return apply_filters( 'si_admin_settings_input_field', ob_get_clean(), $field );
	}


	/**
	 * Get textarea field.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The key to get.
	 * @param array  $field The field to get.
	 *
	 * @return string $input The textarea input.
	 */
	public static function get_textarea_field( $key, $field = array() ) {
		$default    = ( isset( $field['option']['default'] ) ) ? $field['option']['default'] : '';
		$attributes = '';
		foreach ( $field['option']['attributes'] as $attr => $attr_value ) {
			$attributes .= esc_attr( $attr ) . '="' . esc_attr( $attr_value ) . '" ';
		}
		ob_start();
		?>
			<label for="<?php echo esc_attr( $key ); ?>" class="si_input_label"><?php echo esc_html( $field['label'] ) ?></label>
			<textarea type="textarea" name="<?php echo esc_attr( $key ); ?>"
				id="<?php echo esc_attr( $key ); ?>"
				rows="<?php echo isset( $field['option']['rows'] ) ? esc_attr( $field['option']['rows'] ) : 4; ?>"
				cols="<?php echo isset( $field['option']['cols'] ) ? esc_attr( $field['option']['cols'] ) : 40; ?>"
				<?php echo $attributes; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			>
				<?php echo esc_textarea( $default ); ?>
			</textarea>
		<?php
		return apply_filters( 'si_admin_settings_textarea_field', ob_get_clean(), $field );
	}

	/**
	 * Get file checkbox field.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The key to get.
	 * @param array  $field The field to get.
	 *
	 * @return string $input The file input.
	 */
	public static function get_checkbox_field( $key, $field = array() ) {
		$default    = ( isset( $field['option']['default'] ) ) ? $field['option']['default'] : '';
		$attributes = '';
		foreach ( $field['option']['attributes'] as $attr => $attr_value ) {
			$attributes .= esc_attr( $attr ) . '="' . esc_attr( $attr_value ) . '" ';
		}
		ob_start();
		?>
		<label for="<?php echo esc_attr( $key ); ?>" class="si_input_label si_checkbox_label">
			<input type="checkbox" name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>"
				class="si-checkbox" <?php checked( $field['option']['value'], $default ); ?>
				value="<?php echo isset( $field['option']['value'] ) ? esc_attr( $field['option']['value'] ) : 'On'; ?>"
				<?php echo $attributes; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php
					if ( isset( $field['option']['required'] ) && $field['option']['required'] ) {
						echo 'required';
					}
				?>
			/>
			&nbsp;<?php echo esc_html( $field['label'] ); ?>
		</label>
		<?php
		return apply_filters( 'si_admin_settings_checkbox_field', ob_get_clean(), $field );
	}

	/**
	 * Get file radio field.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The key to get.
	 * @param array  $field The field to get.
	 *
	 * @return string $input The file input.
	 */
	public static function get_radio_field( $key, $field = array() ) {
		$default    = ( isset( $field['option']['default'] ) ) ? $field['option']['default'] : '';
		$attributes = '';
		foreach ( $field['option']['attributes'] as $attr => $attr_value ) {
			$attributes .= esc_attr( $attr ) . '="' . esc_attr( $attr_value ) . '" ';
		}
		ob_start();
		?>
		<label for="<?php echo esc_attr( $key ); ?>" class="si_input_label"><?php echo esc_html( $field['label'] ); ?></label>
		<?php foreach ( $field['option']['options'] as $option_key => $option_label ) : ?>
			<label for="<?php echo esc_attr( $key ); ?>_<?php esc_attr_e( $option_key ); ?>" class="si_radio_label">
				<input type="radio" name="<?php echo esc_attr( $key ); ?>"
					id="<?php echo esc_attr( $key ); ?>_<?php esc_attr_e( $option_key ); ?>"
					value="<?php esc_attr_e( $option_key ); ?>"
					<?php checked( $option_key, $default ); ?>
					<?php echo $attributes; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				/>
				&nbsp;<?php echo esc_html( $option_label ); ?>
			</label>
		<?php endforeach; ?>
		<?php
		return apply_filters( 'si_admin_settings_input_field', ob_get_clean(), $field );
	}

	/**
	 * Get file select field.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The key to get.
	 * @param array  $field The field to get.
	 *
	 * @return string $input The file input.
	 */
	public static function get_select_field( $key, $field = array() ) {
		$default    = ( isset( $field['option']['default'] ) ) ? $field['option']['default'] : '';
		$attributes = '';
		foreach ( $field['option']['attributes'] as $attr => $attr_value ) {
			$attributes .= esc_attr( $attr ) . '="' . esc_attr( $attr_value ) . '" ';
		}
		ob_start();
		?>
		<label for="<?php echo esc_attr( $key ); ?>" class="si_input_label"><?php echo esc_html( $field['label'] ) ?></label>
		<select
			type="select"
			name="<?php echo esc_attr( $key ); ?>"
			id="<?php echo esc_attr( $key ); ?>"
			<?php echo $attributes; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php
				if ( isset( $field['option']['required'] ) && $field['option']['required'] ) {
					echo 'required';
				}
			?>
		>
			<?php foreach ( $field['option']['options'] as $option_key => $option_label ) : ?>
				<option
					value="<?php echo esc_attr( $option_key ); ?>"
					<?php selected( $option_key, $field['option']['default'] ) ?>
				>
					<?php echo esc_html( $option_label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
		return apply_filters( 'si_admin_settings_input_field', ob_get_clean(), $field );
	}

	/**
	 * Get group select field.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The key to get.
	 * @param array  $field The field to get.
	 *
	 * @return string $input The file input.
	 */
	public static function get_group_select_field( $key, $field = array() ) {
		$default    = ( isset( $field['option']['default'] ) ) ? $field['option']['default'] : '';
		$attributes = '';
		foreach ( $field['option']['attributes'] as $attr => $attr_value ) {
			$attributes .= esc_attr( $attr ) . '="' . esc_attr( $attr_value ) . '" ';
		}
		ob_start();
		?>
		<label for="<?php echo esc_attr( $key ); ?>" class="si_input_label"><?php echo esc_html( $field['label'] ); ?></label>
		<select
			type="select"
			name="<?php echo esc_attr( $key ); ?>"
			id="<?php echo esc_attr( $key ); ?>"
			<?php echo $attributes; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php
				if ( isset( $field['option']['required'] ) && $field['option']['required'] ) {
					echo 'required';
				}
			?>
		>
			<?php foreach ( $field['options'] as $group => $opts ) : ?>
				<optgroup label="<?php echo esc_attr( $group ); ?>">
					<?php foreach ( $opts as $option_key => $option_label ) : ?>
						<option
							value="<?php echo esc_attr( $option_key ); ?>"
							<?php selected( $option_key, $field['option']['default'] ) ?>
						>
							<?php echo esc_html( $option_label ); ?>
						</option>
					<?php endforeach; ?>
				</optgroup>
			<?php endforeach; ?>
		</select>
		<?php
		return apply_filters( 'si_admin_settings_input_field', ob_get_clean(), $field );
	}

	/**
	 * Get file input field.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The key to get.
	 * @param array  $field The field to get.
	 *
	 * @return string $input The file input.
	 */
	public static function get_file_input_field( $key, $field = array() ) {
		$default    = ( isset( $field['option']['default'] ) ) ? $field['option']['default'] : '';
		$attributes = '';
		foreach ( $field['option']['attributes'] as $attr => $attr_value ) {
			$attributes .= esc_attr( $attr ) . '="' . esc_attr( $attr_value ) . '" ';
		}
		ob_start();
		?>
			<label
				for="<?php echo esc_attr( $key ); ?>"
				class="si_input_label"
			>
				<?php echo esc_html( $field['label'] ); ?>
			</label>

			<input
				type="file"
				name="<?php echo esc_attr( $key ); ?>"
				id="<?php echo esc_attr( $key ); ?>"
				class="si_input_file"
			/>
			<label for="<?php echo esc_attr( $key ); ?>">
				<span>
					<strong>
						<span class="dashicons dashicons-paperclip">
						</span>
						<?php esc_html_e( 'Choose a file&hellip;', 'sprout-invoices' ); ?>
					</strong>
				</span>
			</label>
			<?php
		return apply_filters( 'si_admin_settings_file_input_field', ob_get_clean(), $field );
	}

	/**
	 * Get hidden input field.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The key to get.
	 * @param array  $field The field to get.
	 *
	 * @return string $input The hidden input.
	 */
	public static function get_hidden_input_field( $key, $field = array() ) {
		$attributes = '';
		foreach ( $field['option']['attributes'] as $attr => $attr_value ) {
			$attributes .= esc_attr( $attr ) . '="' . esc_attr( $attr_value ) . '" ';
		}
		ob_start();
		?>
			<input
				type="hidden"
				name="<?php echo esc_attr( $key ); ?>"
				id="<?php echo esc_attr( $key ); ?>"
				value="<?php echo esc_attr( $field['option']['value'] ); ?>"
				<?php echo $attributes; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php
					if ( isset( $field['option']['required'] ) && $field['option']['required'] ) {
						echo 'required';
					}
				?>
				class="si-hidden-input"
			/>
		<?php
		return apply_filters( 'si_admin_settings_input_field', ob_get_clean(), $field );
	}

	/**
	 * Get multi file input field.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The key to get.
	 * @param array  $field The field to get.
	 *
	 * @return string $fld The field to display.
	 */
	public static function get_multi_file_input_field( $key, $field = array() ) {
		$default    = ( isset( $field['option']['default'] ) ) ? $field['option']['default'] : '';
		$attributes = '';
		foreach ( $field['option']['attributes'] as $attr => $attr_value ) {
			$attributes .= esc_attr( $attr ) . '="' . esc_attr( $attr_value ) . '" ';
		}
		ob_start();
		?>
			<input
				type="file"
				name="<?php echo esc_attr( $key ); ?>"
				id="<?php echo esc_attr( $key ); ?>"
				class="si_input_file"
				data-multiple-caption="<?php esc_html_e( '{count} files selected', 'sprout-invoices' ); ?>"
				multiple
			/>
			<label for="<?php echo esc_attr( $key ); ?>">
				<span>
					<strong>
						<span class="dashicons dashicons-paperclip">
						</span>
						<?php esc_html_e( 'Choose a file&hellip;', 'sprout-invoices' ); ?>
					</strong>
				</span>
			</label>
		<?php
		return apply_filters( 'si_admin_settings_file_input_field', ob_get_clean(), $field );
	}
}

<?php

/**
 * Admin settings controller.
 *
 * @package Sprout_Invoice
 * @subpackage Settings
 */
class SI_Admin_Settings extends SI_Controller {
	const WELCOME_SETTINGS_SLUG = 'welcome';
	const ADDRESS_OPTION = 'si_address';
	const CURRENCY_FORMAT_OPTION = 'si_localeconv_setting';
	const COUNTRIES_OPTION = 'si_countries_filter';
	const STATES_OPTION = 'si_states_filter';
	const MENU_ID = 'si_menu';
	protected static $address;
	protected static $option_countries;
	protected static $option_states;
	protected static $localeconv_options;

	public static function init() {
		// Store options
		self::$address = get_option( self::ADDRESS_OPTION, false );
		self::$option_countries = get_option( self::COUNTRIES_OPTION, false );
		self::$option_states = get_option( self::STATES_OPTION, false );
		self::$localeconv_options = get_option( self::CURRENCY_FORMAT_OPTION, array() );

		// register settings
		add_action( 'admin_menu', array( __CLASS__, 'add_admin_page' ), 10, 0 );
		add_filter( 'si_sub_admin_pages', array( __CLASS__, 'register_admin_pages' ) );
		add_filter( 'si_settings', array( __CLASS__, 'register_settings' ) );
		add_filter( 'si_settings_options', array( __CLASS__, 'add_settings_options' ) );
		add_action( 'si_settings_saved', array( get_class(), 'save_specialties' ) );

		// Help Sections
		add_action( 'admin_menu', array( get_class(), 'help_sections' ) );

		// Redirect after activation
		add_action( 'admin_init', array( __CLASS__, 'redirect_on_activation' ), 20, 0 );

		// Admin bar
		add_action( 'admin_bar_menu', array( get_class(), 'sa_admin_bar' ), 62 );

		add_filter( 'si_localeconv', array( __CLASS__, 'localeconv_options' ), 0 );

		// plugin menu
		add_filter( 'plugin_action_links', array( __CLASS__, 'plugin_action_links' ), 10, 2 );

		// Form Integrations
		add_action( 'form_integration_cta', array( __CLASS__, 'advanced_form_integration_view' ) );
	}

	public static function plugin_action_links( $actions, $plugin_file ) {
		static $si;

		if ( ! isset( $plugin ) ) {
			$si = plugin_basename( SI_PLUGIN_FILE );
		}
		if ( $si == $plugin_file ) {

			$settings = array( 'settings' => '<a href="admin.php?page=sprout-invoices-settings">' . __( 'Settings', 'General' ) . '</a>' );
			$support_link = array( 'support' => sprintf( '<a href="%s" style="color:#FF0000" target="_blank"><b>%s</b></a>', si_get_sa_link( 'https://sproutinvoices.com/support/my-tickets/' ), __( 'Need Help?', 'sprout-invoices' ) ) );
			$site_link = array( 'site' => sprintf( '<a href="%s">%s</a>', 'admin.php?page=sprout-invoices-addons', __( 'Add-ons', 'sprout-invoices' ) ) );

			$actions = array_merge( $support_link, $actions );
			$actions = array_merge( $site_link, $actions );
			$actions = array_merge( $settings, $actions );

		}

		return $actions;
	}


	/**
	 * Get the default localeconv options.
	 *
	 * @since 1.0.0
	 *
	 * @return array $localeconv The default localeconv options.
	 */
	public static function localeconv_options() {
		$localeconv = self::$localeconv_options;
		if ( empty( $localeconv ) || $localeconv['int_curr_symbol'] == '' ) {
			$localeconv = array(
				'decimal_point'     => '.',
				'thousands_sep'     => ',',
				'int_curr_symbol'   => 'USD',
				'currency_symbol'   => '$',
				'mon_decimal_point' => '.',
				'mon_thousands_sep' => ',',
				'positive_sign'     => '',
				'negative_sign'     => '-',
				'int_frac_digits'   => 2,
				'frac_digits'       => 2,
				'p_cs_precedes'     => 1,
				'p_sep_by_space'    => 0,
				'n_cs_precedes'     => 1,
				'n_sep_by_space'    => 0,
				'p_sign_posn'       => 1,
				'n_sign_posn'       => 1,
				'grouping'          => array(),
				'mon_grouping'      => array( 3, 3 ),
			);
		}
		return $localeconv;
	}


	//////////////
	// Settings //
	//////////////


	public static function add_admin_page() {

		$icon_svg = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+CjwhLS0gQ3JlYXRlZCB3aXRoIElua3NjYXBlIChodHRwOi8vd3d3Lmlua3NjYXBlLm9yZy8pIC0tPgoKPHN2ZwogICB3aWR0aD0iMTUuMTM5Mjk0bW0iCiAgIGhlaWdodD0iMTUuNjAxNTQxbW0iCiAgIHZpZXdCb3g9IjAgMCAxNS4xMzkyOTQgMTUuNjAxNTQxIgogICB2ZXJzaW9uPSIxLjEiCiAgIGlkPSJzdmc5NjQiCiAgIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIKICAgeG1sbnM6c3ZnPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CiAgPGRlZnMKICAgICBpZD0iZGVmczk2MSIgLz4KICA8ZwogICAgIGlkPSJsYXllcjEiCiAgICAgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoLTk5LjI5MzE3LC05MC44NDE5MDYpIj4KICAgIDxwYXRoCiAgICAgICBpZD0icGF0aDkzMiIKICAgICAgIHN0eWxlPSJmaWxsLW9wYWNpdHk6MTtzdHJva2U6bm9uZTtzdHJva2Utd2lkdGg6MDtzdHJva2UtbWl0ZXJsaW1pdDo0O3N0cm9rZS1kYXNoYXJyYXk6bm9uZSIKICAgICAgIGQ9Im0gNDA0LjYzODY3LDM0My4zMzk4NCBjIC0yLjM1Mzc5LC0wLjAxMTUgLTQuODMyODcsMC4yNjI2NyAtNy40Mjc3MywwLjg2MTMzIC0zNy4xOTYyLDguNTgxNTIgLTIzLjI2NTc1LDY1LjY5OTMzIDEzLjMzMjAzLDU3LjI1NTg2IDM0LjQ0MjA3LC03Ljk0NjA5IDI1LjIxODAxLC01Ny45NjQ0NCAtNS45MDQzLC01OC4xMTcxOSB6IG0gMTEuMjM4MjgsOS40ODgyOCBjIDYuNTQ5MzIsMjAuNDgyIC0wLjYxMzc1LDM2LjcyMTQ1IC0yMi42NjYwMSw0MS4zMzM5OSBsIDYuODI0MjIsLTE2LjgzNTc3IC02LjU4NTQzLDEwLjg2NDk5IC0xLjU3Mjc4LDkuOTcwNzggYyAtMTYuNTg2MjgsLTIyLjI4NDkzIDMuOTMyLC0zOS42ODEzMiAyNCwtNDUuMzMzOTkgeiIKICAgICAgIHRyYW5zZm9ybT0ic2NhbGUoMC4yNjQ1ODMzMykiIC8+CiAgPC9nPgo8L3N2Zz4K';

		$page_title = __( 'SI Settings', 'sprout-invoices' );
		$menu_title = __( 'Sprout Invoices', 'sprout-invoices' );
		$capability = 'manage_sprout_invoices_options';
		$menu_slug = self::TEXT_DOMAIN;
		$callback = array( __CLASS__, 'si_settings_render_welcome_page' );

		add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $callback, $icon_svg );

		add_submenu_page( $menu_slug, __( 'Getting Started', 'sprout-invoices' ), __( 'Getting Started', 'sprout-invoices' ), $capability, $menu_slug );
	}

	public static function register_admin_pages( $admin_pages = array() ) {

		// Dashboard
		$admin_pages['settings'] = array(
			'slug'       => 'dashboard',
			'title'      => 'Settings',
			'menu_title' => __( 'General Settings', 'sprout-invoices' ),
			'weight'     => -PHP_INT_MAX,
			'reset'      => false,
			'section'    => 'settings',
		);

		return $admin_pages;
	}

	public static function add_settings_options( $options = array() ) {
		$settings = array();
		$addy_fields = self::address_form_fields( false );
		$currency_fields = self::$localeconv_options;
		$fields = array_merge( $addy_fields, $currency_fields );
		foreach ( $fields as $key => $data ) {
			$default = ( isset( $data['option']['default'] ) ) ? $data['option']['default'] : '' ;
			$settings[ SI_Settings_API::_sanitize_input_for_vue( 'sa_metabox_' . $key ) ] = $default;
		}
		return array_merge( $settings, $options );
	}

	public static function save_specialties() {
		self::save_address();
		self::save_currency_locale();
	}

	/**
	 * Hooked on init add the settings page and options.
	 *
	 */
	public static function register_settings( $settings = array() ) {
		// Settings
		$settings['si_site_settings'] = array(
			'title'       => __( 'Company Info', 'sprout-invoices' ),
			'weight'      => 20,
			'tab'         => 'start',
			'description' => __( 'The company name and address will be shown on the estimates and invoices.', 'sprout-invoices' ),
			'settings'    => self::settings_address_fields(),
		);

		$settings['si_currency_settings'] = array(
			'title'       => __( 'Currency Formatting', 'sprout-invoices' ),
			'weight'      => 250,
			'tab'         => 'localization',
			'description' => sprintf(
				// translators: 1:opening p tag, 2:opening a tag with href, 3:currency documentation link, 4: end of href, 5:closing a tag, 6:closing p tag
				esc_html__( '%1$sManually set your currency formatting. More information about these settings and using a filter can be found in the %2$s"%3$s"%4$sdocumentation%1$s.%5$s', 'sprout-invoices' ),
				'<p>',
				'<a href=',
				'https://sproutinvoices.com/support/knowledgebase/sprout-invoices/troubleshooting/troubleshooting-moneycurrency-issues/',
				'>',
				'</a>',
				'</p>'
			),
			'settings'    => self::settings_currency_locale_fields(),
		);
		return apply_filters( 'si_general_settings', $settings );
	}

	/**
	 * Check if the plugin has been activated, redirect if true and delete the option to prevent a loop.
	 * @package Sprout_Invoices
	 * @subpackage Base
	 * @ignore
	 */
	public static function redirect_on_activation() {
		if ( get_option( 'si_do_activation_redirect', false ) ) {
			// Flush the rewrite rules after SI is activated.
			flush_rewrite_rules();
			delete_option( 'si_do_activation_redirect' );
			wp_redirect( admin_url( 'admin.php?page=sprout-invoices' ) );
		}
	}

	/**
	 * Render the Dashboard page.
	 *
	 * @return void
	 */
	public static function si_settings_render_welcome_page() {
		$sub_pages = apply_filters( 'si_sub_admin_pages', array() );
		uasort( $sub_pages, array( __CLASS__, 'sort_by_weight' ) );
		$current_page = ( isset( $_GET['page'] ) ) ? str_replace( 'sprout-invoices-', '', sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) : '';

		$view = ( ! SI_FREE_TEST && ( SA_Addons::is_pro_installed() || SA_Addons::is_biz_installed() ) ) ? 'admin/sprout-invoices-dashboard-premium.php' : 'admin/sprout-invoices-dashboard.php';
		$args = array(
			'current_page' => $current_page,
			'sub_pages'    => $sub_pages,
		);
		self::load_view( $view, $args );
	}

	public static function si_settings_render_dashboard_page() {
		self::load_view( 'admin/sprout-apps-dashboard.php', array() );
	}

	//////////////////////
	// General Settings //
	//////////////////////

	public static function settings_address_fields() {
		$fields = array();
		foreach ( self::address_form_fields( false ) as $key => $data ) {
			$data['option'] = $data;
			$fields[ 'sa_metabox_' . $key ] = $data;
		}
		return $fields;
	}

	public static function address_form_fields( $required = true ) {
		$fields = array();
		$fields['name'] = array(
			'weight' => 1,
			'label' => __( 'Company Name', 'sprout-invoices' ),
			'type' => 'text',
			'required' => $required,
			'default' => ( isset( self::$address['name'] ) ) ? self::$address['name'] : get_bloginfo( 'name' ),
		);
		$fields['email'] = array(
			'weight' => 2,
			'label' => __( 'Contact Email', 'sprout-invoices' ),
			'type' => 'text',
			'required' => $required,
			'default' => ( isset( self::$address['email'] ) ) ? self::$address['email'] : get_bloginfo( 'admin_email' ),
		);
		$fields['phone'] = array(
			'weight' => 3,
			'label' => __( 'Phone', 'sprout-invoices' ),
			'type' => 'text',
			'required' => $required,
			'default' => ( isset( self::$address['phone'] ) ) ? self::$address['phone'] : '',
		);
		$fields['fax'] = array(
			'weight' => 4,
			'label' => __( 'Fax', 'sprout-invoices' ),
			'type' => 'text',
			'required' => $required,
			'default' => ( isset( self::$address['fax'] ) ) ? self::$address['fax'] : '',
		);

		$fields = array_merge( $fields, self::get_standard_address_fields( $required ) );

		// Default
		$fields['first_name']['default']  = isset( self::$address['first_name'] ) ? self::$address['first_name'] : '';
		$fields['last_name']['default']   = isset( self::$address['last_name'] ) ? self::$address['last_name'] : '';
		$fields['street']['default']      = isset( self::$address['street'] ) ? self::$address['street'] : '';
		$fields['street_2']['default']    = isset( self::$address['street_2'] ) ? self::$address['street_2'] : '';
		$fields['city']['default']        = isset( self::$address['city'] ) ? self::$address['city'] : '';
		$fields['zone']['default']        = isset( self::$address['zone'] ) ? self::$address['zone'] : '';
		$fields['postal_code']['default'] = isset( self::$address['postal_code'] ) ? self::$address['postal_code'] : '';
		$fields['country']['default']     = isset( self::$address['country'] ) ? self::$address['country'] : '';

		$fields = apply_filters( 'si_site_address_form_fields', $fields );
		uasort( $fields, array( __CLASS__, 'sort_by_weight' ) );
		return $fields;
	}

	public static function save_address( $address = array() ) {
		$fields = self::address_form_fields( false );
		$address = array();
		foreach ( $fields as $key => $value ) {
			$address[ $key ] = isset( $_POST[ 'sa_metabox_' . $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'sa_metabox_' . $key ] ) ) : '';
		}
		$address = stripslashes_deep( $address );
		update_option( self::ADDRESS_OPTION, $address );
	}

	public static function get_site_address() {
		return self::$address;
	}


	///////////////////////
	// Currency options //
	///////////////////////

	public static function settings_currency_locale_fields() {
		$fields = array();
		foreach ( self::currency_locale_fields() as $key => $data ) {
			$data['option'] = $data;
			$fields[ 'sa_metabox_' . $key ] = $data;
		}
		return $fields;
	}


	/**
	 * Currency locale fields.
	 *
	 * This function returns the fields for the currency locale settings.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public static function currency_locale_fields() {
		$localeconv = self::localeconv_options();

		$fields['int_curr_symbol']   = array(
			'weight'      => 1,
			'label'       => __( 'International Currency Symbol', 'sprout-invoices' ),
			'type'        => 'small-input',
			'default'     => ( isset( $localeconv['int_curr_symbol'] ) ) ? $localeconv['int_curr_symbol'] : '',
			'description' => __( 'U.S. default is <code>USD</code>', 'sprout-invoices' ),
		);
		$fields['currency_symbol']   = array(
			'weight'      => 1,
			'label'       => __( 'Currency Symbol', 'sprout-invoices' ),
			'type'        => 'small-input',
			'default'     => ( isset( $localeconv['currency_symbol'] ) ) ? $localeconv['currency_symbol'] : '',
			'description' => __( 'U.S. default is <code>$</code>', 'sprout-invoices' ),
		);
		$fields['mon_decimal_point'] = array(
			'weight'      => 5,
			'label'       => __( 'Decimal Point', 'sprout-invoices' ),
			'type'        => 'small-input',
			'default'     => ( isset( $localeconv['mon_decimal_point'] ) ) ? $localeconv['mon_decimal_point'] : '.',
			'description' => __( 'U.S. default is <code>.</code>', 'sprout-invoices' ),
		);
		$fields['mon_thousands_sep'] = array(
			'weight'      => 10,
			'label'       => __( 'Thousands Separator', 'sprout-invoices' ),
			'type'        => 'small-input',
			'default'     => ( isset( $localeconv['mon_thousands_sep'] ) ) ? $localeconv['mon_thousands_sep'] : ',',
			'description' => __( 'U.S. default is <code>,</code>', 'sprout-invoices' ),
		);
		$fields['positive_sign']     = array(
			'weight'      => 15,
			'label'       => __( 'Positive Sign', 'sprout-invoices' ),
			'type'        => 'small-input',
			'default'     => ( isset( $localeconv['positive_sign'] ) ) ? $localeconv['positive_sign'] : '',
			'description' => __( 'U.S. default is blank', 'sprout-invoices' ),
		);
		$fields['negative_sign']     = array(
			'weight'      => 1,
			'label'       => __( 'Negative Sign', 'sprout-invoices' ),
			'type'        => 'small-input',
			'default'     => ( isset( $localeconv['negative_sign'] ) ) ? $localeconv['negative_sign'] : '',
			'description' => __( 'U.S. default is <code>-</code>', 'sprout-invoices' ),
		);
		$fields['int_frac_digits']   = array(
			'weight'      => 1,
			'label'       => __( 'Fraction Digits', 'sprout-invoices' ),
			'type'        => 'small-input',
			'default'     => ( isset( $localeconv['int_frac_digits'] ) ) ? $localeconv['int_frac_digits'] : '',
			'description' => __( 'U.S. default is <code>2</code>', 'sprout-invoices' ),
		);
		$fields['mon_grouping']      = array(
			'weight'      => 1,
			'label'       => __( 'Money Grouping', 'sprout-invoices' ),
			'type'        => 'checkbox',
			'type'        => 'small-input',
			'default'     => ( ! empty( $localeconv['mon_grouping'] ) ) ? implode( ',', $localeconv['mon_grouping'] ) : '3, 3',
			'description' => __( 'U.S. default is <code>3, 3</code>', 'sprout-invoices' ),
		);
		$fields['p_cs_precedes']     = array(
			'weight'      => 1,
			'label'       => __( 'Currency Symbol Precedes (Positive)', 'sprout-invoices' ),
			'type'        => 'checkbox',
			'default'     => ( isset( $localeconv['p_cs_precedes'] ) ) ? intval( $localeconv['p_cs_precedes'] ) : 1,
			'description' => __( 'U.S. default is checked.', 'sprout-invoices' ),
		);
		$fields['p_sep_by_space']    = array(
			'weight'      => 1,
			'label'       => __( 'Space Separation (Positive)', 'sprout-invoices' ),
			'type'        => 'checkbox',
			'default'     => ( isset( $localeconv['p_sep_by_space'] ) ) ? intval( $localeconv['p_sep_by_space'] ) : 0,
			'description' => __( 'U.S. default is unchecked.', 'sprout-invoices' ),
		);
		$fields['p_sign_posn']       = array(
			'weight'      => 1,
			'label'       => __( 'Positive Position', 'sprout-invoices' ),
			'type'        => 'checkbox',
			'default'     => ( isset( $localeconv['p_sign_posn'] ) ) ? intval( $localeconv['p_sign_posn'] ) : 1,
			'description' => __( 'U.S. default is checked.', 'sprout-invoices' ),
		);
		$fields['n_cs_precedes']     = array(
			'weight'      => 1,
			'label'       => __( 'Currency Symbol Precedes (Negative)', 'sprout-invoices' ),
			'type'        => 'checkbox',
			'default'     => ( isset( $localeconv['n_cs_precedes'] ) ) ? intval( $localeconv['n_cs_precedes'] ) : 1,
			'description' => __( 'U.S. default is checked.', 'sprout-invoices' ),
		);
		$fields['n_sep_by_space']    = array(
			'weight'      => 1,
			'label'       => __( 'Space Separation (Negative)', 'sprout-invoices' ),
			'type'        => 'checkbox',
			'default'     => ( isset( $localeconv['n_sep_by_space'] ) ) ? intval( $localeconv['n_sep_by_space'] ) : 0,
			'description' => __( 'U.S. default is unchecked.', 'sprout-invoices' ),
		);
		$fields['n_sign_posn']       = array(
			'weight'      => 1,
			'label'       => __( 'Positive Position', 'sprout-invoices' ),
			'type'        => 'checkbox',
			'default'     => ( isset( $localeconv['n_sign_posn'] ) ) ? intval( $localeconv['n_sign_posn'] ) : 1,
			'description' => __( 'U.S. default is checked.', 'sprout-invoices' ),
		);
		return $fields;
	}

	/**
	 * Save the currency locale options
	 *
	 * @since 20.7.0
	 *
	 * @param array $locale The locale options.
	 *
	 * @return void
	 */
	public static function save_currency_locale( $locale = array() ) {
		$localeconv = array();
		$lc_options = array(
			'decimal_point'     => '.',
			'thousands_sep'     => ',',
			'int_curr_symbol'   => 'USD',
			'currency_symbol'   => '$',
			'mon_decimal_point' => '.',
			'mon_thousands_sep' => ',',
			'positive_sign'     => '',
			'negative_sign'     => '-',
			'int_frac_digits'   => 2,
			'frac_digits'       => 2,
			'p_cs_precedes'     => true,
			'p_sep_by_space'    => false,
			'n_cs_precedes'     => true,
			'n_sep_by_space'    => false,
			'p_sign_posn'       => true,
			'n_sign_posn'       => true,
			'grouping'          => array(),
			'mon_grouping'      => array( 3, 3 ),
		);
		foreach ( $lc_options as $key => $default ) {
			if ( isset( $_POST[ 'sa_metabox_' . $key ] ) && 'false' === $_POST[ 'sa_metabox_' . $key ] ) {
				$_POST[ 'sa_metabox_' . $key ] = false;
			}
			if ( isset( $_POST[ 'sa_metabox_' . $key ] ) && 'true' === $_POST[ 'sa_metabox_' . $key ] ) {
				$_POST[ 'sa_metabox_' . $key ] = true;
			}
			$localeconv[ $key ] = isset( $_POST[ 'sa_metabox_'.$key ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'sa_metabox_'.$key ] ) ) : '';
		}
		if ( isset( $_POST['sa_metabox_mon_grouping'] ) ) {
			$mon_grouping = explode( ',', sanitize_text_field( wp_unslash( $_POST['sa_metabox_mon_grouping'] ) ) );
			if ( is_array( $mon_grouping ) ) {
				$localeconv['mon_grouping'] = array_map( 'trim', $mon_grouping );
			}
		}
		$localeconv = stripslashes_deep( $localeconv );
		update_option( self::CURRENCY_FORMAT_OPTION, $localeconv );
	}

	////////////////////////////////
	// State and Country Settings //
	////////////////////////////////

	public static function display_internationalization_section() {
		echo '<p>'. esc_html_e( 'Select the states and countries/provinces for all forms, e.g. purchase, estimates and registration.', 'sprout-invoices' ) . '</p>';

	}

	/**
	 * Display for countries option
	 * @return string
	 */
	public static function display_option_states() {
		echo '<div class="sprout_state_options">';
		echo '<select name="' . esc_attr( self::STATES_OPTION ) . '[]" multiple="multiple" class="select2" style="min-width:50%;">';
		foreach ( parent::$grouped_states as $group => $states ) {
			echo '<optgroup label="' . esc_attr( $group ) . '">';
			foreach ( $states as $key => $name ) {
				$selected = ( empty( self::$option_states ) || ( isset( self::$option_states[ $group ] ) && in_array( $name, self::$option_states[ $group ] ) ) ) ? 'selected="selected"' : null ;
				echo '<option value="' . esc_attr( $key ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $name ) . '</option>';
			}
			echo '</optgroup>';
		}
		echo '</select>';
		echo '</div>';
	}

	/**
	 * Display for countries option
	 * @return string
	 */
	public static function display_option_countries() {
	?>
			<div class="sprout_country_options">
				<select name="<?php echo esc_attr( self::COUNTRIES_OPTION ); ?>[]" multiple="multiple" class="select2" style="min-width:50%;">
					<?php foreach ( parent::$countries as $key => $name ) : ?>
					<?php $selected = ( empty( self::$option_countries ) || in_array( $name, self::$option_countries ) ) ? true : false ;  ?>
					<option value="<?php echo esc_attr( $name ) ?>" <?php selected( $selected, true, true ); ?>><?php echo esc_html( $name ) ?></option>
				<?php endforeach ?>
				</select>
			</div> <?php
	}

		/**
	 * Save callback for saving states
	 * @param  array  $selected
	 * @return $selected
	 */
	public static function save_states( $selected = array() ) {
		$sanitized_options = array();
		if ( is_array( $selected ) ) {
			foreach ( self::$grouped_states as $group => $states ) {
				$sanitized_options[ $group ] = array();
				foreach ( $states as $key => $name ) {
					if ( in_array( $key, $selected ) ) {
						$sanitized_options[ $group ][ $key ] = $name;
					}
				}
				// Unset the empty groups
				if ( empty( $sanitized_options[ $group ] ) ) {
					unset( $sanitized_options[ $group ] );
				}
			}
		}
		return $sanitized_options;
	}

		/**
	 * Save callback for saving countries
	 * @param  array  $options
	 * @return $options
	 */
	public static function save_countries( $options = array() ) {
		$sanitized_options = array();
		if ( is_array( $options ) ) {
			foreach ( self::$countries  as $key => $name ) {
				if ( in_array( $name, $options ) ) {
					$sanitized_options[ $key ] = $name;
				}
			}
		}
		return $sanitized_options;
	}

	/**
	 * Template for displaying Form Integrations.
	 *
	 * This template displays the information about the available form integrations and a link to download them.
	 *
	 * @return void
	 */
	public static function advanced_form_integration_view() {
		self::load_view( 'admin/options/form-integrations-cta.php', array() );
	}


	/**
	 * Creates an Admin Bar Option Offer and submenu for any registered sub-menus ( admin submenu )
	 *
	 * @static
	 * @return void
	 */
	public static function sa_admin_bar( WP_Admin_Bar $wp_admin_bar ) {

		if ( ! current_user_can( 'manage_sprout_invoices_options' ) ) {
			return; }

		$menu_items = apply_filters( 'si_admin_bar', array() );
		$sub_menu_items = apply_filters( 'si_admin_bar_sub_items', array() );

		$wp_admin_bar->add_node( array(
			'id' => self::MENU_ID,
			'parent' => false,
			'title' => '<span class="icon-sproutapps-flat ab-icon"></span>'.__( 'Sprout Invoices', 'sprout-invoices' ),
			'href' => admin_url( 'admin.php?page=sprout-invoices-reports' ),
		) );

		uasort( $menu_items, array( get_class(), 'sort_by_weight' ) );
		foreach ( $menu_items as $item ) {
			$wp_admin_bar->add_node( array(
				'parent' => self::MENU_ID,
				'id' => $item['id'],
				'title' => __( $item['title'], 'sprout-invoices' ),
				'href' => $item['href'],
			) );
		}

		$wp_admin_bar->add_group( array(
			'parent' => self::MENU_ID,
			'id'     => self::MENU_ID.'_options',
			'meta'   => array( 'class' => 'ab-sub-secondary' ),
		) );

		uasort( $sub_menu_items, array( get_class(), 'sort_by_weight' ) );
		foreach ( $sub_menu_items as $item ) {
			$wp_admin_bar->add_node( array(
				'parent' => self::MENU_ID.'_options',
				'id' => $item['id'],
				'title' => __( $item['title'], 'sprout-invoices' ),
				'href' => $item['href'],
			) );
		}
	}



		////////////////
		// Admin Help //
		////////////////

	public static function help_sections() {
		add_action( 'load-sprout-apps_page_sprout-apps/settings', array( __CLASS__, 'help_tabs' ) );
	}

	public static function help_tabs() {

		$screen = get_current_screen();
		if ( ! isset( $_GET['tab'] ) ) {
			// get screen and add sections.
			$screen = get_current_screen();

			$screen->add_help_tab( array(
				'id' => 'general-about',
				'title' => __( 'License', 'sprout-invoices' ),
				'content' => sprintf( '<p>%s</p>', __( 'Activate your license if you have not done so already.', 'sprout-invoices' ) ),
			) );

			$screen->add_help_tab( array(
				'id' => 'general-leads',
				'title' => __( 'Credit Card Processing', 'sprout-invoices' ),
				'content' => sprintf( '<p>%s</p><p>%s</p>', __( 'To get you started, Sprout Invoices provides a fully customizable form for estimate submissions. Add the shortcode below to a page to use this default form: <code>[estimate_submission]Thank you![/estimate_submission]</code>', 'sprout-invoices' ), __( 'Additional documentation is available to customize the default estimate form and using the integration add-on.', 'sprout-invoices' ) ),
			) );

			$screen->add_help_tab( array(
				'id' => 'general-estimate',
				'title' => __( 'Estimate/Invoice Settings', 'sprout-invoices' ),
				'content' => sprintf( '<p>%s</p>', __( 'The Default Terms and Default Notes will be added to each estimate unless an estimate has customized Terms and/or Notes.', 'sprout-invoices' ) ),
			) );

			$screen->add_help_tab( array(
				'id' => 'general-notification',
				'title' => __( 'Notification Settings', 'sprout-invoices' ),
				'content' => sprintf( '<p>%s</p><p>%s</p>', __( 'The from name and from e-mail is used for all Sprout Invoice notifications. Example, “Walker Bueler future@dodgers.com.', 'sprout-invoices' ), __( 'Changing the email format to “HTML” will make the default notifications unformatted and look like garbage; if you want to create some pretty HTML notifications make sure to modify all notification formatting.', 'sprout-invoices' ) ),
			) );

			$screen->add_help_tab( array(
				'id' => 'general-company',
				'title' => __( 'Company Info', 'sprout-invoices' ),
				'content' => sprintf( '<p>%s</p>', __( 'This information is used on all estimates and invoices. You’ll want to make sure to set this information before sending out any invoices/estimates.', 'sprout-invoices' ) ),
			) );

			$screen->add_help_tab( array(
				'id' => 'general-advanced',
				'title' => __( 'Advanced', 'sprout-invoices' ),
				'content' => sprintf( '<p>%s</p>', __( 'The option to Save Logs is for debugging purposes and not recommended, unless advised. It’s important to note that turning enabling this option on a live site may cause private transaction data to be saved in the DB unencrypted, i.e. CC data.', 'sprout-invoices' ) ),
			) );

			$screen->set_help_sidebar(
				sprintf( '<p><strong>%s</strong></p>', __( 'For more information:', 'sprout-invoices' ) ) .
				sprintf( '<p><a href="%s" class="button">%s</a></p>', 'https://sproutinvoices.com/support/knowledgebase/sprout-invoices/sprout-invoices-getting-started/', __( 'Documentation', 'sprout-invoices' ) ) .
				sprintf( '<p><a href="%s" class="button">%s</a></p>', si_get_sa_link( 'https://sproutinvoices.com/support/' ), __( 'Support', 'sprout-invoices' ) )
			);
		}
	}
}

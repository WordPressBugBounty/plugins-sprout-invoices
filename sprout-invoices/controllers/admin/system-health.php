<?php
/**
 * File: sprout-invoices/controllers/admin/system-health.php
 *
 * This file is used to create the system health check.
 *
 * @since 20.5.2
 * @package Sprout Invoices/Admin/System Health
 * @subpackage SI_Controller
 */

/**
 * System Health
 *
 * This class is used to grab system information for debugging purposes. The information includes:
 *
 * 1. WordPress Information.
 * 2. Sprout Version.
 * 3. PHP Settings.
 * 4. Sprout Invoices Settings.
 * 5. Active Payment Processors.
 * 6. Active Addons.
 *
 * @since 20.5.2
 * @package SI_System_Health
 * @subpackage SI_Controller
 */
class SI_System_Health extends SI_Controller {

	/**
	 * Initialize the system health check.
	 *
	 * @since 20.5.2
	 *
	 * @return void
	 */
	public static function init() {
		add_filter( 'wp_ajax_si_system_health_check', array( __CLASS__, 'system_health_check' ) );
	}

	/**
	 * Create the system health check.
	 *
	 * @since 20.5.2
	 *
	 * @return void
	 */
	public static function system_health_check() {
		$nonce = isset( $_POST['si_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['si_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'sprout_invoices_controller_nonce' ) ) {
			wp_send_json( array( 'error' => 'Invalid Nonce' ), 403 );
		}

		$system_info = self::format_to_block_string( self::get_system_info() );

		wp_send_json( $system_info, 200 );
	}

	/**
	 * Format system info array to a block string separated by new lines.
	 *
	 * @since 20.5.2
	 *
	 * @param array|string $system_info Array of system information could be string based on the level of recursion.
	 * @param string       $indent      Indentation string.
	 *
	 * @return string $system_info_string returns a string of system information seperated into new lines.
	 */
	public static function format_to_block_string( $system_info, $indent = '' ) {
		$system_info_string = '';

		// Loop through system info and format it into a block string.
		foreach ( $system_info as $key => $value ) {
			if ( is_array( $value ) ) {
				$system_info_string .= $indent . $key . ":\n";
				$system_info_string .= self::format_to_block_string( $value, $indent . "\t" );
			} else {
				$system_info_string .= $indent . $key . ': ' . $value . "\n";
			}
		}

		return $system_info_string;
	}

	/**
	 * Get the system information.
	 *
	 * @since 20.5.2
	 *
	 * @return array $system_info returns an array of system information.
	 */
	public static function get_system_info() {
		$system_info = array(
			'wordpress'      => array(
				'version'              => get_bloginfo( 'version' ),
				'url'                  => get_bloginfo( 'url' ),
				'wp_multisite'         => is_multisite(),
				'wp_debug'             => defined( 'WP_DEBUG' ) ? WP_DEBUG : 'disabled',
				'wp_environment_type'  => defined( 'WP_ENVIRONMENT_TYPE' ) ? WP_ENVIRONMENT_TYPE : '',
				'wp_developement_mode' => defined( 'WP_DEVELOPMENT_MODE' ) ? WP_DEVELOPMENT_MODE : '',
				'wp_cron'              => defined( 'DISABLE_WP_CRON' ) ? DISABLE_WP_CRON : 'enabled',
				'script_debug'         => defined( 'SCRIPT_DEBUG' ) ? SCRIPT_DEBUG : 'disabled',
			),
			'active_plugins' => get_option( 'active_plugins' ),
			'sprout'         => array(
				'version' => self::SI_VERSION,
			),
			'php'            => array(
				'version'            => phpversion(),
				'memory_limit'       => ini_get( 'memory_limit' ),
				'max_execution_time' => ini_get( 'max_execution_time' ),
			),
			'si_settings'    => array(
				'si_processors' => self::get_payment_processors(),
				'si_addons'     => self::get_active_addons(),
				'si_settings'   => self::build_settings_array(),
			),
			'si_templating'  => array(
				'invoice'   => SI_Templating_API::get_invoice_theme_option(),
				'estimate'  => SI_Templating_API::get_estimate_theme_option(),
				'overrides' => is_dir( get_template_directory() . '/sa_templates' ) ? 'enabled' : 'disabled',
			),
		);

		return $system_info;
	}

	/**
	 * Build Addon and General settings array for health check.
	 *
	 * @since 20.5.2
	 *
	 * @return array returns array of addon and general settings.
	 */
	public static function build_settings_array() {
		$si_settings_array = array();
		$si_settings       = SI_Settings_API::get_si_settings();

		// Loop through settings and build the array we will use for the health check.
		foreach ( $si_settings as $section => $setting_section ) {
			if ( isset( $setting_section['settings'] ) ) {
				$si_settings_array[ $section ] = self::format_settings( $setting_section['settings'] );
			} else {
				foreach ( $setting_section as $setting => $setting_value ) {
					$si_settings_array[ $section ] = $setting_value;
				}
			}
		}

		return self::sanitize_settings_array( $si_settings_array );
	}

	/**
	 * Sanitize settings array for health check.
	 *
	 * @since 20.5.2
	 *
	 * @param array $settings Array of settings.
	 *
	 * @return array $settings Array of sanitized settings.
	 */
	public static function sanitize_settings_array( $settings ) {
		$filtered_keys = array(
			'si_activation'      => SI_Updates::license_status(),
			'destroy_everything' => false,
		);

		// Loop through settings for filtered keys and remove key or update value.
		foreach ( $settings as $setting_key => $setting_value ) {
			if ( ! array_key_exists( $setting_key, $filtered_keys ) ) {
				continue;
			}

			if ( false === $filtered_keys[ $setting_key ] ) {
				unset( $settings[ $setting_key ] );
			} else {
				$settings[ $setting_key ] = $filtered_keys[ $setting_key ];
			}
		}

		return $settings;
	}

	/**
	 * Format settings information for health check.
	 *
	 * @since 20.5.2
	 *
	 * @param array $settings Array of settings.
	 * @return array
	 */
	public static function format_settings( $settings ) {

		// Loop through settings and only add the setting key and default value to the array.
		foreach ( $settings as $key => $data ) {
			$default          = ( isset( $data['option']['default'] ) ) ? $data['option']['default'] : '';
			$settings[ $key ] = $default;
		}

		return $settings;
	}

	/**
	 * Get Active Payments Processors and their settings.
	 *
	 * @since 20.5.2
	 *
	 * @return array returns an array of active payment processors.
	 */
	public static function get_payment_processors() {
		return SI_Payment_Processors::enabled_processors();
	}

	/**
	 * Get Active Addons and their settings.
	 *
	 * @since 20.5.2
	 *
	 * @return array $enabled_addons returns an array of active addons.
	 */
	public static function get_active_addons() {
		$enabled_addons = array();
		$all_addons     = SA_Addons::get_addons();

		// SA_Addons::get_addons() can return and filtered value which may or may not be an array.
		if ( ! is_array( $all_addons ) ) {
			return $enabled_addons;
		}

		// Loop through all addons and only add the active ones to the array.
		foreach ( $all_addons as $key => $addon ) {
			if ( 0 === $addon['active'] ) {
				continue;
			}
			$enabled_addons[ $addon['settingID'] ] = $key;
		}

		return $enabled_addons;
	}
}

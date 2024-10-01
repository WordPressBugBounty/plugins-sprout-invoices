<?php

/**
 * Addons: Admin purchasing, check for updates, etc.
 */
class SA_Addons extends SI_Controller {
	const SETTINGS_PAGE           = 'addons';
	const ADDON_OPTION            = 'si_active_addons_v3';
	const API_CB                  = 'https://sproutinvoices.com/';
	const PROGRESS_TRACKER        = 'si_addons_progress';
	private static $active_addons = array();

	public static function init() {
		self::$active_addons = get_option( self::ADDON_OPTION, array() );
		if ( ! self::$active_addons || empty( self::$active_addons ) ) {
			self::$active_addons = self::default_active_addons();
			update_option( self::ADDON_OPTION, self::$active_addons );
		}

		self::si_update_addon_names();

		// register settings
		add_filter( 'si_sub_admin_pages', array( __CLASS__, 'register_admin_page' ) );

		add_filter( 'si_settings_options', array( __CLASS__, 'add_settings_options' ) );

		// register actions
		add_action( 'si_settings_tab_content', array( __CLASS__, 'render_addon_settings_content' ) );
		add_action( 'wp_ajax_render_addon_settings_content', array( __CLASS__, 'render_addon_settings_content' ) );

		self::load_addons();
	}

	public static function is_pro_installed() {
		return file_exists( SI_PATH . '/bundles/sprout-invoices-addon-client-dash/client-dashboard.php' );
	}

	public static function is_biz_installed() {
		return file_exists( SI_PATH . '/bundles/sprout-invoices-addon-custom-numbering/sprout-invoice-custom-ids.php' );
	}

	public static function is_corp_installed() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		return is_plugin_active( 'sprout-invoices-addon-auto-billing/auto-billing.php ' );
	}

	/**
	 * Update old addon names to current names.
	 */
	public static function si_update_addon_names() {
		$addon_prefix = 'sprout-invoices-add-on-';
		$addon_names  = array(
			$addon_prefix . 'adobe-sign'                   => $addon_prefix . 'pdf-service-for-wordpress-invoices-estimates',
			$addon_prefix . 'attachments-and-downloads'    => $addon_prefix . 'downloadable-attachments-for-invoices- and-estimates',
			$addon_prefix . 'austrailian-gst-requirements' => $addon_prefix . 'australian-invoicing-requirements-customizations',
			$addon_prefix . 'canadian-advance-tax'         => $addon_prefix . 'canadian-invoicing-requirements-and-customizations',
			$addon_prefix . 'client-dashboard'             => $addon_prefix . 'client-dashboard',
			$addon_prefix . 'digital-signature'            => $addon_prefix . 'digital-siginig-for-wordpress-invoices-estimates',
			$addon_prefix . 'esp-advanced-tax-irpf-only'   => $addon_prefix . 'esp-invoicing-requirements-irpf-only-customizations',
			$addon_prefix . 'esp-advanced-tax'             => $addon_prefix . 'esp-invoicing-requirements-iva-irpf-customizations',
			$addon_prefix . 'eu-advanced-tax'              => $addon_prefix . 'eu-invoicing-requirements-customizations',
			$addon_prefix . 'expense-tracking'             => $addon_prefix . 'project-expense-tracking',
			$addon_prefix . 'login'                        => $addon_prefix . 'protected-invoices-estaimates-advanced',
			$addon_prefix . 'payment-terms'                => $addon_prefix . 'invoice-payment-terms-simple-payment-scheduling',
			$addon_prefix . 'service-fee'                  => $addon_prefix . 'service-convenience-fees-per-payment-processor',
			$addon_prefix . 'time-tracking'                => $addon_prefix . 'time-tracking-for-projects',
			$addon_prefix . 'woocommerce-tools'            => $addon_prefix . 'sprout-invoices-woocommerce',
		);

		$active_addons_wp = get_option( self::ADDON_OPTION, array() );
		foreach ( $active_addons_wp as $key => $value ) {
			if ( isset( $addon_names[ $value ] ) ) {
				foreach ( $addon_names as $old_name => $new_name ) {
					if ( $value === $old_name ) {
						$active_addons_wp[ $key ] = $new_name;
					}
				}
			}
		}
		update_option( self::ADDON_OPTION, $active_addons_wp );
	}
	/**
	 * Register the Addons settings page in WordPress settings menu
	 *
	 * @param  array $admin_pages Admin pages array.
	 * @return array $admin_pages
	 */
	public static function register_admin_page( $admin_pages = array() ) {
		$admin_pages[ self::SETTINGS_PAGE ] = array(
			'slug'       => self::SETTINGS_PAGE,
			'title'      => __( 'Add-ons', 'sprout-invoices' ),
			'menu_title' => __( 'Add-ons', 'sprout-invoices' ),
			'weight'     => 20,
			'section'    => 'add-ons',
			'callback'   => array( __CLASS__, 'render_settings_page' ),
			'tab_only'   => true,
		);
		return $admin_pages;
	}

	/**
	 * Add the settings addons to Vue data
	 *
	 * @return array $addons array of addons
	 */
	public static function get_addons_vue() {
		$addons   = self::get_addons();
		$settings = apply_filters( 'si_settings', array() );
		foreach ( $addons as $key => $addon ) {
			if ( ! array_key_exists( $key, $settings ) ) {
				$addons[ $key ]['settings'] = false;
			} else {
				$addons[ $key ]['settings'] = true;
			}
		}
		return $addons;
	}

	/**
	 * Render the settings page for addons.
	 *
	 * @return void
	 */
	public static function render_settings_page() {
		$sub_pages    = apply_filters( 'si_sub_admin_pages', array() );
		uasort( $sub_pages, array( __CLASS__, 'sort_by_weight' ) );
		$current_page = ( isset( $_GET['page'] ) ) ? str_replace( 'sprout-invoices-', '', sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) : '';
		$addons       = self::get_addons();
		$form_ints    = array(
			'formidable_integration'    => 'SI_Formidable',
			'wpforms_integration'       => 'SI_WPForms',
			'nf_integration'            => 'NF_SproutInvoices',
			'gravity_forms_integration' => 'SI_GF_Integration_Addon',
		);
		$tax_addons   = array(
			'sprout-invoices-add-on-australian-invoicing-requirements-and-customizations' => 'SA_Hearts_Australia',
			'sprout-invoices-add-on-canadian-invoicing-requirements-and-customizations'   => 'SA_Hearts_Canada',
			'sprout-invoices-add-on-esp-invoicing-requirements-irpf-only-customizations'  => 'SI_Hearts_ESP2_2',
			'sprout-invoices-add-on-esp-invoicing-requirements-iva-irpf-customizations'   => 'SI_Hearts_ESP',
			'sprout-invoices-add-on-eu-invoicing-requirements-customizations'             => 'SI_Hearts_EU',
		);
		$args         = array(
			'sub_pages'         => $sub_pages,
			'current_page'      => $current_page,
			'addons'            => $addons,
			'form_integrations' => $form_ints,
			'tax_addons'        => $tax_addons,
			'option'            => self::ADDON_OPTION,
			'mp_addons'         => self::get_marketplace_addons(),
			'enabled_addons'    => self::$active_addons,
			'allsettings'       => SI_Settings_API::get_si_settings(),
		);
		if ( empty( $addons ) ) {
			self::load_view( 'admin/addons/free-settings.php', $args );
		} else {
			self::load_view( 'admin/addons/settings.php', $args );
		}
	}

	/**
	 * Get Addon by Class Name.
	 *
	 * @since 20.7.0
	 *
	 * @param string $class_name array of addon to filter.
	 *
	 * @return array $addon_data Array of addon data.
	 */
	public static function get_addon_by_class_name( $class_name ) {
		$addons      = self::get_addons();
		$allsettings = SI_Settings_API::get_si_settings();
		foreach ( $addons as $addon => $addon_data ) {
			if ( $class_name === $addon && isset( $allsettings[ $class_name ] ) ) {
				return $allsettings[ $class_name ];
			}
		}
		return array();
	}

	/**
	 * Format addon data for settings page.
	 *
	 * @since 20.7.0
	 *
	 * @param array $addon_data array of addon data.
	 *
	 * @return array $fields array of addon settings fields.
	 */
	public static function format_addon_settings( $addon_data ) {
		$fields = array();
		foreach ( $addon_data['settings'] as $field_name => $field_configs ) {
			$field_attributes = array(
				'placeholder' => $field_configs['label'],
				'value'       => isset( $field_configs['option']['default'] ) ? $field_configs['option']['default'] : '',
				'required'    => isset( $field_configs['option']['required'] ) ? $field_configs['option']['required'] : false,
				'attributes'  => array(
					'v-model' => 'vm.' . esc_attr( $field_name ),
				),
			);

			switch ( $field_configs['option']['type'] ) {
				case 'select':
					$field_attributes['options'] = $field_configs['option']['options'];
					break;
				case 'bypass':
					$field_attributes = array(
						'output' => $field_configs['option']['output'],
					);
					break;
				case 'checkbox':
					$field_attributes['attributes']['type'] = $field_configs['option']['type'];
					break;
				case 'textarea':
					$field_attributes['attributes']['rows'] = 4;
					$field_attributes['attributes']['cols'] = 40;
					$field_attributes['attributes']['type'] = $field_configs['option']['type'];
					break;
			}

			$fields[] = SI_Field_Factory::create(
				$field_name,
				$field_configs['label'],
				isset( $field_configs['option']['description'] ) ? $field_configs['option']['description'] : '',
				$field_configs['option']['type'],
				$field_attributes
			);
		}

		return $fields;
	}

	/**
	 * Render the content section of each add-on settings tab.
	 *
	 * @return void
	 */
	public static function render_addon_settings_content( $addon ) {
		if ( ! empty( $_POST ) && isset( $_POST['addon'] ) ) {
			$addon = $_POST['addon'];
		}
		$args = array(
			'addon'       => $addon,
			'allsettings' => SI_Settings_API::get_si_settings(),
		);

		self::load_view( 'admin/addons/settings-content.php', $args );
		if ( ! empty( $_POST ) && isset( $_POST['addon'] ) ) {
			die();
		}
	}

	public static function add_settings_options( $options = array() ) {
		$addon_options = array();
		$addons = self::get_addons();
		foreach ( $addons as $path => $details ) {
			$key = self::get_addon_key( $path, $details );
			$addon_options[ SI_Settings_API::_sanitize_input_for_vue( $key ) ] = self::is_enabled( $key );
		}
		return array_merge( $addon_options, $options );
	}

	public static function activate_addon( $addon_key = '' ) {
		update_option( self::PROGRESS_TRACKER, $addon_key );
		$active_addons = get_option( self::ADDON_OPTION, false );
		if ( ! is_array( $active_addons ) ) {
			$active_addons = array();
		}

		$active_addons[] = $addon_key;
		$active_addons = array_unique( array_filter( $active_addons ) );

		update_option( self::ADDON_OPTION, $active_addons );

		do_action( 'si_addon_activated', $addon_key );
	}

	public static function deactivate_addon( $addon_key = '' ) {
		update_option( self::PROGRESS_TRACKER, $addon_key );
		$active_addons = get_option( self::ADDON_OPTION, false );
		if ( ! is_array( $active_addons ) ) {
			return;
		}
		$array_key = array_search( $addon_key, $active_addons );
		unset( $active_addons[ $array_key ] );
		$active_addons = array_unique( array_filter( $active_addons ) );
		update_option( self::ADDON_OPTION, $active_addons );

		do_action( 'si_addon_deactivated', $addon_key );
	}

	/**
	 * Default is to activate all add-ons
	 *
	 * @return array
	 */
	public static function default_active_addons() {
		$addons = self::get_addons();
		$active_addons = array();
		foreach ( $addons as $path => $details ) {
			if ( isset( $details['AutoActive'] ) && true == $details['AutoActive'] ) {
				$key = self::get_addon_key( $path, $details );
				$active_addons[] = $key;
			}
		}
		return $active_addons;
	}

	/**
	 * Is addon enabled
	 *
	 * @param  string $addon_key path of the plugin.
	 * @return boolean
	 */
	public static function is_enabled( $addon_key ) {
		if ( in_array( $addon_key, self::$active_addons ) ) {
			return true;
		}
		return false;
	}

	public static function load_addons() {
		if ( SI_FREE_TEST ) {
			return;
		}

		/**
		 * `!apply_filters( 'is_bundle_addon', false )` is used within plugins
		 * to determine if the add-on is a plugin or loaded as a bundle.
		 */
		add_filter( 'is_bundle_addon', '__return_true' );
		$addons = self::get_addons();
		foreach ( $addons as $path => $data ) {
			$key = self::get_addon_key( $path, $data );
			if ( in_array( $key, self::$active_addons ) ) {
				if ( file_exists( SI_PATH . '/bundles/' . $data['path'] ) ) {
					require SI_PATH . '/bundles/' . $data['path'];
				}
			}
		}

		remove_all_filters( 'is_bundle_addon' );
	}

	public static function get_addons( $addon_folder = '' ) {

		if ( ! $cache_addons = wp_cache_get( 'si_addons', 'si_addons' ) ) {
			$cache_addons = array();
		}

		if ( isset( $cache_addons[ $addon_folder ] ) ) {
			$valid_cache = true;
			foreach ( $cache_addons as $path => $data ) {
				if ( ! file_exists( SI_PATH.'/bundles/' . $path ) ) {
					wp_cache_delete( 'si_addons', 'si_addons' );
					$valid_cache = false;
					break;
				}
			}
			if ( $valid_cache ) {
				//return apply_filters( 'si_get_addons', $cache_addons[ $addon_folder ], true );
			}
		}

		$si_addons = array();
		$addon_root = SI_PATH . '/bundles/';

		if ( ! empty( $addon_folder ) ) {
			$addon_root .= $addon_folder;
		}

		// Files in wp-content/addons directory
		$addons_dir = @ opendir( $addon_root );

		$addon_files = array();
		if ( $addons_dir ) {
			while ( ($file = readdir( $addons_dir ) ) !== false ) {
				if ( substr( $file, 0, 1 ) == '.' ) {
					continue;
				}
				// payment processors are not add-ons, and are loaded separatly.
				if ( false !== strpos( $file, 'sprout-invoices-payments' ) ) {
					continue;
				}
				if ( is_dir( $addon_root.'/'.$file ) ) {
					$addons_subdir = @ opendir( $addon_root.'/'.$file );
					if ( $addons_subdir ) {
						while ( ( $subfile = readdir( $addons_subdir ) ) !== false ) {
							if ( substr( $subfile, 0, 1 ) == '.' ) {
								continue;
							}
							if ( substr( $subfile, -4 ) == '.php' ) {
								$addon_files[] = "$file/$subfile";
							}
						}
						closedir( $addons_subdir );
					}
				} else {
					if ( substr( $file, -4 ) == '.php' ) {
						$addon_files[] = $file;
					}
				}
			}
			closedir( $addons_dir );
		}

		if ( empty( $addon_files ) ) {
			return apply_filters( 'si_get_addons', $si_addons );
		}

		$active_addons = array();
		$inactive_addons = array();
		foreach ( $addon_files as $addon_file ) {
			if ( ! is_readable( "$addon_root/$addon_file" ) ) {
				continue;
			}

			$addon_data = self::get_addon_data( "$addon_root/$addon_file" );

			if ( empty( $addon_data['Name'] ) ) {
				continue;
			}

			$path = plugin_basename( $addon_file );
			$key = self::get_addon_key( $path, $addon_data );
			if ( self::is_enabled( $key ) ) {
				$addon_data['active']  = 1;
				$addon_data['path']    = $path;
				$active_addons[ $key ] = $addon_data;
			} else {
				$addon_data['active']    = 0;
				$addon_data['path']      = $path;
				$inactive_addons[ $key ] = $addon_data;
			}
		}

		// Filter out addons that should not be shown to customers for inactive addons.
		$inactive_addons = self::filter_addons( $active_addons, $inactive_addons )['inactive'];

		uasort( $active_addons, array( __CLASS__, 'add_on_sort' ) );
		uasort( $inactive_addons, array( __CLASS__, 'add_on_sort' ) );

		$si_addons = array_merge( $active_addons, $inactive_addons );

		$cache_addons[ $addon_folder ] = $si_addons;
		wp_cache_set( 'si_addons', $cache_addons, 'si_addons' );

		return apply_filters( 'si_get_addons', $si_addons );
	}

	/**
	 * Filter Addons.
	 *
	 * There are instances where we want to filter out inactive addons that should not be shown to customers
	 * based on what addons are enabled. This function will filter out addons that should not be shown.
	 *
	 * i.e. If recurring invoices are enabled, then the subscription payments addon should not be shown.
	 *
	 * @since 20.8.0
	 *
	 * @param array $active_addons Array of active addons.
	 * @param array $inactive_addons Array of inactive addons.
	 *
	 * @return array $filtered_addons Array of filtered addons.
	 */
	public static function filter_addons( $active_addons, $inactive_addons ) {
		$active_addons = array_keys( $active_addons );

		// If recurring invoices are enabled, then the subscription payments addon should not be shown.
		if ( in_array( 'sprout-invoices-add-on-recurring-invoices', $active_addons, true ) ) {
			unset( $inactive_addons['sprout-invoices-add-on-recurring-aka-subscription-payments'] );
		}

		// If subscription payments are enabled, then the recurring invoices addon should not be shown.
		if ( in_array( 'sprout-invoices-add-on-recurring-aka-subscription-payments', $active_addons, true ) ) {
			unset( $inactive_addons['sprout-invoices-add-on-recurring-invoices'] );
		}

		return array(
			'active'   => $active_addons,
			'inactive' => $inactive_addons,
		);
	}

	public static function get_addon_data( $addon_file ) {
		$default_headers = array(
			'Name'        => 'Plugin Name',
			'PluginURI'   => 'Plugin URI',
			'Version'     => 'Version',
			'Description' => 'Description',
			'Author'      => 'Author',
			'ID'          => 'ID',
			'AuthorURI'   => 'Author URI',
			'AutoActive'  => 'Auto Active',
			'ShortName'   => 'Short Name',
			'settingID'   => 'Settings ID',
			'link'        => 'link',
		);

		$addon_data = get_file_data( $addon_file, $default_headers, 'plugin' );

		$addon_data['Title']      = $addon_data['Name'];
		$addon_data['AuthorName'] = $addon_data['Author'];

		if ( ! isset( $addon_data['link'] ) ) {
			$addon_data['link'] = '';
		}

		return $addon_data;
	}

	public static function get_addon_key( $addon_file = '', $data = array() ) {
		if ( empty( $data ) ) {
			$data = self::get_addon_data( $addon_file );
		}
		$key = str_replace( '.php', '', $data['Name'] );
		return sanitize_title( $key );
	}


	/**
	 * Get the add-on data for a MarketPlace add-ons.
	 *
	 * @return array $marketplace_items The marketplace items.
	 */
	public static function get_marketplace_addons() {
		$cache_key     = '_si_marketplace_addons_v18' . self::SI_VERSION;
		$cached_addons = get_transient( $cache_key );
		if ( $cached_addons ) {
			if ( ! empty( $cached_addons ) ) {
				return $cached_addons;
			}
		}

		$uid = ( class_exists( 'SI_Free_License' ) ) ? SI_Free_License::uid() : 0 ;
		$ref = ( $uid ) ? $uid : 'na' ;
		// data to send in our API request
		$api_params = array(
			'action'    => 'sa_marketplace_api',
			'item_name' => urlencode( self::PLUGIN_NAME ),
			'url'       => urlencode( home_url() ),
			'uid'       => $uid,
			'ref'       => $ref,
		);

		// Call the custom API.
		$response = wp_safe_remote_get(
			add_query_arg(
				$api_params,
				self::API_CB . 'wp-admin/admin-ajax.php'
			),
			array(
				'timeout'   => 15,
				'sslverify' => false,
			)
		);
		// make sure the response came back okay
		if ( is_wp_error( $response ) ) {
			return false;
		}

		// decode the license data
		$marketplace_items = json_decode( wp_remote_retrieve_body( $response ) );
		// sort
		$biz_addons  = array();
		$free_addons = array();
		$corp_addons = array();

		foreach ( $marketplace_items as $addon_id => $addon ) {
			if ( 44588 === $addon->id ) {
				$corp_addons[ $addon_id ] = $addon;
			} elseif ( $addon->biz_bundled ) {
				$biz_addons[ $addon_id ] = $addon;
			} else {
				$free_addons[ $addon_id ] = $addon;
			}
		}

		$marketplace_items = array_merge( $corp_addons, $free_addons, $biz_addons );
		uasort( $marketplace_items, array( __CLASS__, 'mp_add_on_sort' ) );
		set_transient( $cache_key, $marketplace_items, DAY_IN_SECONDS * 3 );
		return $marketplace_items;
	}

	public static function add_on_sort_by_active( $a, $b ) {
		return $b['active'] - $a['active'];
	}

	public static function mp_add_on_sort( $a, $b ) {
		return strcmp( $a->post_title, $b->post_title );
	}

	public static function add_on_sort( $a, $b ) {
		return strcmp( $a['Title'], $b['Title'] );
	}

	/**
	 * Get Addon row options for the Addons table.
	 *
	 * @since 20.7.0
	 *
	 * @param string $class_name array of addon to filter.
	 * @param string $addon_data array of addon to filter.
	 *
	 * @return array $options Array of options for the addons table.
	 */
	public static function get_addon_row_options( $class_name, $addon_data ) {
		$name            = sprintf( '%1$s["%2$s"]', self::ADDON_OPTION, $class_name );
		$vm_key          = SI_Settings_API::_sanitize_input_for_vue( $class_name );
		$no_settings     = apply_filters( 'si_has_settings', array() );
		$settings_button = '';

		if ( ! empty( self::get_addon_by_class_name( $class_name ) ) ) {
			$settings_button = SI_Field_Factory::create(
				$class_name,
				$addon_data['Title'],
				'',
				'button',
				array(
					'button_text' => __( 'Settings', 'sprout-invoices' ),
					'attributes'  => array(
						'class'      => 'si-settings-link button button-secondary settings_' . esc_attr( $addon_data['settingID'] ),
						'v-if'       => 'vm.' . esc_attr( $vm_key ),
						'v-on:click' => 'loadSettingsPage("' . esc_attr( $class_name ) . '")',
					),
				)
			);
		}

		$options = array(
			'addon'       => array(
				'attributes' => array(
					'v-bind:class' => '{ activating : isSaving == true }',
					'id'           => esc_attr( $vm_key ),
				),
				'contents'   => $addon_data['ShortName'],
			),
			'enabled'     => array(
				'attributes' => array(
					'class' => 'si-addon-status',
					'width' => '1%',
				),
				'contents'   => SI_Field_Factory::create(
					$class_name,
					$addon_data['Title'],
					'',
					'checkbox',
					array(
						'vueifs'     => array(
							'v-if'   => '"vm.' . esc_attr( $vm_key ) . ' )"',
							'v-else' => '',
						),
						'attributes' => array(
							'id'           => 'addon-status',
							'name'         => esc_attr( $name ),
							'type'         => 'checkbox',
							'v-model.lazy' => 'vm.' . esc_attr( $vm_key ),
							'v-on:change'  => 'activateAddOn( "' . esc_attr( $class_name ) . '", $event )',
						),
					)
				),
			),
			'description' => array(
				'attributes' => array(),
				'contents'   => $addon_data['Description'],
			),
			'actions'     => array(
				'attributes' => array(
					'width' => '1%',
				),
				'contents'   => $settings_button,
			),
		);

		return $options;
	}
}

<?php
/**
 * File: sprout-invoices/controllers/getting-started-wizard/si-startup-wizard.php
 *
 * Sprout Invoices Setup Wizard.
 *
 * @package Sprout Invoices
 *
 * @since 20.7.0
 */

/**
 * Sprout Invoices Setup Wizard.
 *
 * @since 20.7.0
 */
class SI_Admin_Setup_Wizard {

	/**
	 * Class constructor.
	 *
	 * @since 20.7.0
	 */
	public function __construct() {
		add_action( 'si_getting_started_wizard', array( $this, 'load_gs_wizard' ) );
		add_action( 'si_wizard_header', array( $this, 'setup_wizard_header' ) );
		add_action( 'si_wizard_footer', array( $this, 'setup_wizard_footer' ) );
		add_action( 'si_wizard_content', array( $this, 'setup_wizard_content' ) );
		add_action( 'wp_ajax_si_wizard_finished', array( $this, 'maybe_finished_wizard' ) );

		require_once SI_PATH . '/controllers/getting-started-wizard/si-setup-wizard-util.php';
	}

	/**
	 * Load the getting started wizard.
	 *
	 * @since 20.7.0
	 *
	 * @return void
	 */
	public function load_gs_wizard() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Load the wizard.
		$args = array(
			'page_title' => __( 'Sprout Invoices Setup Wizard', 'sprout-invoices' ),
		);

		$tab     = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';
		$section = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : '';

		if ( ! isset( $_GET['page'] ) || 'sprout-invoices' !== sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) {
			return;
		}

		if ( ! empty( $tab ) && ! empty( $section ) ) {
			add_action( 'si_wizard_content_' . sanitize_text_field( wp_unslash( $_GET['tab'] ) ) . '_' . sanitize_text_field( wp_unslash( $_GET['section'] ) ), array( $this, 'si_settings_tabs' ) );
			$args['current_tab']     = $tab;
			$args['current_section'] = $section;
		}

		// Load view
		SI_Settings_API::load_view( 'admin/si-setup-wizard.php', $args );
	}

	/**
	 * Maybe finished wizard.
	 *
	 * @since 20.7.0
	 *
	 * @return void
	 */
	public function maybe_finished_wizard() {
		// Todo: Add functionality to disable wizard when finished.
	}

	/**
	 * Get SI Settings.
	 *
	 * @since 20.7.0
	 *
	 * @return void
	 */
	public function si_settings_tabs( $args ) {
		$tabs = SI_Field_Factory::create(
			'si_settings_' . $args['current_tab'],
			'tabs',
			'',
			'tabs',
			array(
				'args' => array(
					'next_button'   => false,
					'settings_page' => true,
				),
				'tabs' => $this->si_settings_tab( $args ),
			)
		);

		$tabs->render();
	}

	/**
	 * Get SI settings tab.
	 *
	 * @since 20.7.0
	 *
	 * @param array $args args.
	 *
	 * @return array
	 */
	public function si_settings_tab( $args ) {
		/**
		 * Returns the correct Payment Gateway settings tab and associated settings in the group.
		 */
		if ( 'payment_processors' === $args['current_tab'] ) {
			$all_processors = SI_Payment_Processors::get_all_processors();
			if ( array_key_exists( $args['current_section'], $all_processors ) ) {
				$label = $all_processors[ $args['current_section'] ];
				return array(
					SI_Field_Factory::create(
						'payment_processor_settings',
						__( 'Payment Processor Settings', 'sprout-invoices' ),
						'',
						'tab',
						array(
							'fields' => $this->si_gateway_settings( $args['current_section'], $label ),
						),
					),
				);
			}
		}

		/**
		 * Returns the correct Addon settings tab and associated settings in the group.
		 */
		if ( 'addons' === $args['current_tab'] ) {
			$all_addons = SA_Addons::get_addons();
			if ( array_key_exists( $args['current_section'], $all_addons ) ) {
				return array(
					SI_Field_Factory::create(
						'addon_settings',
						__( 'Addon Settings', 'sprout-invoices' ),
						'',
						'tab',
						array(
							'fields' => $this->si_addon_settings( $args['current_section'], $args['current_section'] ),
						),
					),
				);
			}
		}
	}

	/**
	 * Get SI Settings Group.
	 *
	 * @since 20.7.0
	 *
	 * @return array
	 */
	public function si_gateway_settings( $class_name, $label ) {
		return array(
			SI_Field_Factory::create(
				'si-wizard-back',
				'',
				'',
				'button',
				array(
					'button_text' => __( 'Back', 'sprout-invoices' ),
					'attributes'  => array(
						'class'      => 'si-button button button-primary',
						'v-on:click' => 'goBack()',
					),
				)
			),
			SI_Field_Factory::create(
				$class_name,
				$label,
				'',
				'group_box',
				array(
					'content' => SI_Payment_Processors::get_processor_settings( $class_name ),
				)
			),
			SI_Field_Factory::create(
				'si-controls',
				'',
				'',
				'button',
				array(
					'button_text' => __( 'Save', 'sprout-invoices' ),
					'attributes'  => array(
						'class'      => 'si-button button button-primary',
						'v-on:click' => 'saveOptions',
					),
				)
			),
			SI_Field_Factory::create(
				'si-save-success',
				'', // No label for this field.
				'', // No description for this field.
				'paragraph',
				array(
					'attributes'     => array(
						'class' => 'si-save-success',
						'v-if'  => 'message',
					),
					'paragraph_text' => __( '{{message}}', 'sprout-invoices' ),
				)
			),
		);
	}

	/**
	 * Get SI Addon Settings Group.
	 *
	 * @since 20.7.0
	 *
	 * @return array
	 */
	public function si_addon_settings( $class_name, $label ) {
		$settings = SA_Addons::get_addon_by_class_name( $class_name );
		return array(
			SI_Field_Factory::create(
				'si-wizard-back',
				'',
				'',
				'button',
				array(
					'button_text' => __( 'Back', 'sprout-invoices' ),
					'attributes'  => array(
						'class'      => 'si-button button button-primary',
						'v-on:click' => 'goBack()',
					),
				)
			),
			SI_Field_Factory::create(
				$class_name,
				$settings['title'],
				isset( $settings['description'] ) ? $settings['description'] : '',
				'group_box',
				array(
					'content' => SA_Addons::format_addon_settings( $settings ),
				)
			),
			SI_Field_Factory::create(
				'si-controls',
				'',
				'',
				'button',
				array(
					'button_text' => __( 'Save', 'sprout-invoices' ),
					'attributes'  => array(
						'class'      => 'si-button button button-primary',
						'v-on:click' => 'saveOptions',
					),
				)
			),
			SI_Field_Factory::create(
				'si-save-success',
				'', // No label for this field.
				'', // No description for this field.
				'paragraph',
				array(
					'attributes'     => array(
						'class' => 'si-save-success',
						'v-if'  => 'message',
					),
					'paragraph_text' => __( '{{message}}', 'sprout-invoices' ),
				)
			),
		);
	}

	/**
	 * Get wizard tabs.
	 *
	 * Currently the company setup and notifications tabs are using the old way of displaying fields.
	 * All other tabs are using the new way of displaying fields via SI_Field and SI_Field_Factory.
	 *
	 * @since 20.7.0
	 *
	 * @return array $wizard_tabs wizard tabs.
	 */
	public function get_wizard_tabs() {
		return array(
			SI_Field_Factory::create(
				'start',
				__( 'Welcome', 'sprout-invoices' ),
				'',
				'tab',
				array(
					'fields' => $this->get_tab_fields( 'start' ),
				)
			),
			SI_Field_Factory::create(
				'company_setup',
				__( 'Company Setup', 'sprout-invoices' ),
				'',
				'tab',
				array(
					'fields' => $this->get_tab_fields( 'company_setup' ),
				)
			),
			SI_Field_Factory::create(
				'notifications',
				__( 'Notifications', 'sprout-invoices' ),
				'',
				'tab',
				array(
					'fields' => $this->get_tab_fields( 'notifications' ),
				)
			),
			SI_Field_Factory::create(
				'addons',
				__( 'Addons', 'sprout-invoices' ),
				'',
				'tab',
				array(
					'fields' => $this->get_tab_fields( 'addons' ),
				)
			),
			SI_Field_Factory::create(
				'payment_processors',
				__( 'Payment Processors', 'sprout-invoices' ),
				'',
				'tab',
				array(
					'fields' => $this->get_tab_fields( 'payment_processors' ),
				)
			),
			SI_Field_Factory::create(
				'finish',
				__( 'Finish', 'sprout-invoices' ),
				'',
				'tab',
				array(
					'fields' => $this->get_tab_fields( 'finish' ),
				)
			),
		);
	}

	/**
	 * Get Tab Fields
	 *
	 * @param string $tab_name tab name.
	 *
	 * @return array
	 */
	public function get_tab_fields( $tab_name ) {
		switch ( $tab_name ) {
			case 'start':
				return array(
					SI_Field_Factory::create(
						'si_welcome',
						'',
						'',
						'group',
						array(
							'attributes' => array(
								'class' => 'si-wizard-nav-wrap',
							),
							'content'    => SI_Setup_Wizard_Util::get_wizard_nav( $tab_name ),
						),
					),
					SI_Field_Factory::create(
						'si-welcome',
						__( 'Welcome to Sprout Invoices', 'sprout-invoices' ),
						'',
						'group_box',
						array(
							'content' => SI_Setup_Wizard_Util::get_welcome_fields(),
						),
					),
				);
			case 'company_setup':
				return array(
					SI_Field_Factory::create(
						'company_setup',
						'',
						'',
						'group',
						array(
							'attributes' => array(
								'class' => 'si-wizard-nav-wrap',
							),
							'content'    => SI_Setup_Wizard_Util::get_wizard_nav( $tab_name ),
						),
					),
					SI_Field_Factory::create(
						'company_settings',
						__( 'Company Information', 'sprout-invoices' ),
						'',
						'group_box',
						array(
							'content' => SI_Setup_Wizard_Util::get_company_info_fields(),
						),
					),
					SI_Field_Factory::create(
						'localization_settings',
						__( 'Currency Settings', 'sprout-invoices' ),
						'',
						'group_box',
						array(
							'content' => SI_Setup_Wizard_Util::get_locale_fields(),
						),
					),
				);
			case 'notifications':
				return array(
					SI_Field_Factory::create(
						'notifications',
						'',
						'',
						'group',
						array(
							'attributes' => array(
								'class' => 'si-wizard-nav-wrap',
							),
							'content'    => SI_Setup_Wizard_Util::get_wizard_nav( $tab_name ),
						),
					),
					SI_Field_Factory::create(
						'notification_settings',
						__( 'Notification Settings', 'sprout-invoices' ),
						'',
						'group_box',
						array(
							'content' => SI_Setup_Wizard_Util::get_notification_fields(),
						),
					),
				);
			case 'addons':
				$addons = SI_Setup_Wizard_Util::get_wizard_addons();

				if ( empty( $addons ) ) {
					return array(
						SI_Field_Factory::create(
							'addons',
							'',
							'',
							'group',
							array(
								'attributes' => array(
									'class' => 'si-wizard-nav-wrap',
								),
								'content'    => SI_Setup_Wizard_Util::get_wizard_nav( $tab_name ),
							),
						),
						SI_Field_Factory::create(
							'no_addons',
							esc_html__( 'Addons', 'sprout-invoices' ),
							'',
							'group_box',
							array(
								'content' => SI_Setup_Wizard_Util::get_free_addons_fields(),
							),
						),
					);
				}
				return array(
					SI_Field_Factory::create(
						'addons',
						'',
						'',
						'group',
						array(
							'attributes' => array(
								'class' => 'si-wizard-nav-wrap',
							),
							'content'    => SI_Setup_Wizard_Util::get_wizard_nav( $tab_name ),
						),
					),
					SI_Field_Factory::create(
						'integrations',
						esc_html__( 'Integrations', 'sprout-invoices' ),
						'',
						'table',
						array(
							'columns' => array(
								'addon'       => esc_html__( 'Addon', 'sprout-invoices' ),
								'activated'   => esc_html__( 'Activated', 'sprout-invoices' ),
								'description' => esc_html__( 'Description', 'sprout-invoices' ),
								'actions'     => '',
							),
							'rows'    => $addons['integrations'],
						),
					),
					SI_Field_Factory::create(
						'project_addons',
						esc_html__( 'Project Addons', 'sprout-invoices' ),
						'',
						'table',
						array(
							'columns' => array(
								'addon'       => esc_html__( 'Addon', 'sprout-invoices' ),
								'activated'   => esc_html__( 'Activated', 'sprout-invoices' ),
								'description' => esc_html__( 'Description', 'sprout-invoices' ),
								'actions'     => '',
							),
							'rows'    => $addons['project-addons'],
						),
					),
					SI_Field_Factory::create(
						'advanced_addons',
						esc_html__( 'Advanced Addons', 'sprout-invoices' ),
						'',
						'table',
						array(
							'columns' => array(
								'addon'       => esc_html__( 'Addon', 'sprout-invoices' ),
								'activated'   => esc_html__( 'Activated', 'sprout-invoices' ),
								'description' => esc_html__( 'Description', 'sprout-invoices' ),
								'actions'     => '',
							),
							'rows'    => $addons['advanced-addons'],
						),
					),
					SI_Field_Factory::create(
						'other_addons',
						esc_html__( 'Other Addons', 'sprout-invoices' ),
						'',
						'table',
						array(
							'columns' => array(
								'addon'       => esc_html__( 'Addon', 'sprout-invoices' ),
								'activated'   => esc_html__( 'Activated', 'sprout-invoices' ),
								'description' => esc_html__( 'Description', 'sprout-invoices' ),
								'actions'     => '',
							),
							'rows'    => $addons['other-addons'],
						),
					),
				);
			case 'payment_processors':
				$all_processors         = SI_Payment_Processors::get_all_processors();
				$recommended_processors = SI_Payment_Processors::get_processor_recommendations();
				$rec_processsor_array   = SI_Setup_Wizard_Util::get_rec_processor_rows();
				$all_processors_array   = SI_Setup_Wizard_Util::get_all_processor_rows();

				return array(
					SI_Field_Factory::create(
						'payment_processors',
						'',
						'',
						'group',
						array(
							'attributes' => array(
								'class' => 'si-wizard-nav-wrap',
							),
							'content'    => SI_Setup_Wizard_Util::get_wizard_nav( $tab_name ),
						),
					),
					SI_Field_Factory::create(
						'recommended_payment_processors',
						esc_html__( 'Recommended Payment Processors', 'sprout-invoices' ),
						'',
						'table',
						array(
							'columns' => array(
								'processor'   => esc_html__( 'Processor', 'sprout-invoices' ),
								'enabled'     => esc_html__( 'Enabled', 'sprout-invoices' ),
								'description' => esc_html__( 'Description', 'sprout-invoices' ),
								'actions'     => '',
							),
							'rows'    => $rec_processsor_array,
						),
					),
					SI_Field_Factory::create(
						'other_payment_processors',
						esc_html__( 'Other Payment Processors', 'sprout-invoices' ),
						'',
						'table',
						array(
							'columns' => array(
								'processor'   => esc_html__( 'Processor', 'sprout-invoices' ),
								'enabled'     => esc_html__( 'Enabled', 'sprout-invoices' ),
								'description' => esc_html__( 'Description', 'sprout-invoices' ),
								'actions'     => '',
							),
							'rows'    => $all_processors_array,
						),
					),
				);
			case 'finish':
				return array(
					SI_Field_Factory::create(
						'finish',
						'',
						'',
						'group',
						array(
							'attributes' => array(
								'class' => 'si-wizard-nav-wrap',
							),
							'content'    => SI_Setup_Wizard_Util::get_wizard_nav( $tab_name ),
						),
					),
					SI_Field_Factory::create(
						'finish',
						__( 'Sprout Invoices Wizard Completed', 'sprout-invoices' ),
						'',
						'group_box',
						array(
							'content' => SI_Setup_Wizard_Util::get_finish_fields(),
						),
					),
				);
			default:
				return array();
		}
	}

	/**
	 * Get setup wizard content.
	 *
	 * @return void
	 */
	public function setup_wizard_content( $setup_wizard_tab ) {
		$tabs_config = $this->get_wizard_tabs();

		$tabs = SI_Field_Factory::create(
			'getting-started-wizard',
			'tabs',
			'',
			'tabs',
			array(
				'tabs' => $tabs_config,
			)
		);

		$tabs->render();
	}

	/**
	 * Load the wizard header.
	 *
	 * @since 20.7.0
	 *
	 * @return void
	 */
	public function setup_wizard_header() {
		$args = array(
			'logo' => $this->si_wizard_logo(),
		);
		SI_Settings_API::load_view( 'admin/si-setup-wizard-header.php', $args );
	}

	/**
	 * Load the wizard footer.
	 *
	 * @since 20.7.0
	 *
	 * @return void
	 */
	public function setup_wizard_footer() {
		$args = array();
		SI_Settings_API::load_view( 'admin/si-setup-wizard-footer.php', $args );
	}

	/**
	 * Get Sprout Invoices Setup Wizard Logo
	 *
	 * @since 20.7.0
	 *
	 * @return string
	 */
	public function si_wizard_logo() {
		return SI_RESOURCES . 'admin/img/si-wizard-logo.png';
	}

}

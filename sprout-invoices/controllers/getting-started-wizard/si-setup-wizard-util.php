<?php
/**
 * File: sprout-invoices/controllers/getting-started-wizard/si-setup-wizard-util.php
 *
 * Sprout Invoices Setup Wizard.
 *
 * @package Sprout Invoices
 *
 * @since 20.7.0
 */

/**
 * Sprout Invoices Setup Wizard Utility Class.
 *
 * @since 20.7.0
 */
class SI_Setup_Wizard_Util {

	/**
	 * Get Wizard Navigation bar for each tab
	 *
	 * @since 20.7.0
	 *
	 * @param string $position position of the wizard.
	 *
	 * @return array array of navigation buttons.
	 */
	public static function get_wizard_nav( $position ) {
		switch ( $position ) {
			case 'start':
				return array(
					SI_Field_Factory::create(
						'si-wizard-skip',
						'',
						'',
						'button',
						array(
							'button_text' => __( 'Skip Sprout Invoices Wizard', 'sprout-invoices' ),
							'attributes'  => array(
								'class' => 'button button-secondary',
								'href'  => admin_url( 'admin.php?page=sprout-invoices-settings' ),
							),
						)
					),
					SI_Field_Factory::create(
						'si-wizard-next',
						'',
						'',
						'button',
						array(
							'button_text' => __( 'Next', 'sprout-invoices' ),
							'attributes'  => array(
								'id'         => 'si-submit-settings',
								'class'      => 'button button-secondary',
								'v-on:click' => 'siWizardNext("company_setup")',
							),
						),
					),
				);
			case 'company_setup':
				return array(
					SI_Field_Factory::create(
						'si-wizard-back',
						'',
						'',
						'button',
						array(
							'button_text' => __( 'Back', 'sprout-invoices' ),
							'attributes'  => array(
								'id'         => 'si-submit-settings',
								'class'      => 'button button-secondary',
								'v-on:click' => 'siWizardNext("start")',
							),
						),
					),
					SI_Field_Factory::create(
						'si-wizard-skip',
						'',
						'',
						'button',
						array(
							'button_text' => __( 'Skip Sprout Invoices Wizard', 'sprout-invoices' ),
							'attributes'  => array(
								'class' => 'button button-secondary',
								'href'  => admin_url( 'admin.php?page=sprout-invoices-settings' ),
							),
						)
					),
					SI_Field_Factory::create(
						'si-wizard-next',
						'',
						'',
						'button',
						array(
							'button_text' => __( 'Next', 'sprout-invoices' ),
							'attributes'  => array(
								'id'         => 'si-submit-settings',
								'class'      => 'button button-secondary',
								'v-on:click' => 'siWizardNext("notifications")',
							),
						),
					),

				);
			case 'notifications':
				return array(
					SI_Field_Factory::create(
						'si-wizard-back',
						'',
						'',
						'button',
						array(
							'button_text' => __( 'Back', 'sprout-invoices' ),
							'attributes'  => array(
								'id'         => 'si-submit-settings',
								'class'      => 'button button-secondary',
								'v-on:click' => 'siWizardNext("company_setup")',
							),
						),
					),
					SI_Field_Factory::create(
						'si-wizard-skip',
						'',
						'',
						'button',
						array(
							'button_text' => __( 'Skip Sprout Invoices Wizard', 'sprout-invoices' ),
							'attributes'  => array(
								'class' => 'button button-secondary',
								'href'  => admin_url( 'admin.php?page=sprout-invoices-notifications' ),
							),
						)
					),
					SI_Field_Factory::create(
						'si-wizard-next',
						'',
						'',
						'button',
						array(
							'button_text' => __( 'Next', 'sprout-invoices' ),
							'attributes'  => array(
								'id'         => 'si-submit-settings',
								'class'      => 'button button-secondary',
								'v-on:click' => 'siWizardNext("addons")',
							),
						),
					),
				);
			case 'addons':
				return array(
					SI_Field_Factory::create(
						'si-wizard-back',
						'',
						'',
						'button',
						array(
							'button_text' => __( 'Back', 'sprout-invoices' ),
							'attributes'  => array(
								'id'         => 'si-submit-settings',
								'class'      => 'button button-secondary',
								'v-on:click' => 'siWizardNext("notifications")',
							),
						),
					),
					SI_Field_Factory::create(
						'si-wizard-skip',
						'',
						'',
						'button',
						array(
							'button_text' => __( 'Skip Sprout Invoices Wizard', 'sprout-invoices' ),
							'attributes'  => array(
								'class' => 'button button-secondary',
								'href'  => admin_url( 'admin.php?page=sprout-invoices-addons' ),
							),
						)
					),
					SI_Field_Factory::create(
						'si-wizard-next',
						'',
						'',
						'button',
						array(
							'button_text' => __( 'Next', 'sprout-invoices' ),
							'attributes'  => array(
								'id'         => 'si-submit-settings',
								'class'      => 'button button-secondary',
								'v-on:click' => 'siWizardNext("payment_processors")',
							),
						),
					),
				);
			case 'payment_processors':
				return array(
					SI_Field_Factory::create(
						'si-wizard-back',
						'',
						'',
						'button',
						array(
							'button_text' => __( 'Back', 'sprout-invoices' ),
							'attributes'  => array(
								'id'         => 'si-submit-settings',
								'class'      => 'button button-secondary',
								'v-on:click' => 'siWizardNext("addons")',
							),
						),
					),
					SI_Field_Factory::create(
						'si-wizard-skip',
						'',
						'',
						'button',
						array(
							'button_text' => __( 'Skip Sprout Invoices Wizard', 'sprout-invoices' ),
							'attributes'  => array(
								'class'      => 'button button-secondary',
								'href' => admin_url( 'admin.php?page=sprout-invoices-payments' ),
							),
						)
					),
					SI_Field_Factory::create(
						'si-wizard-next',
						'',
						'',
						'button',
						array(
							'button_text' => __( 'Next', 'sprout-invoices' ),
							'attributes'  => array(
								'id'         => 'si-submit-settings',
								'class'      => 'button button-secondary',
								'v-on:click' => 'siWizardNext("finish")',
							),
						),
					),
				);
			case 'finish':
				return array(
					SI_Field_Factory::create(
						'si-wizard-back',
						'',
						'',
						'button',
						array(
							'button_text' => __( 'Back', 'sprout-invoices' ),
							'attributes'  => array(
								'id'         => 'si-submit-settings',
								'class'      => 'button button-secondary',
								'v-on:click' => 'siWizardNext("payment_processors")',
							),
						),
					),
					SI_Field_Factory::create(
						'si-wizard-finish',
						'',
						'',
						'button',
						array(
							'button_text' => __( 'Go to Sprout Invoices Settings', 'sprout-invoices' ),
							'attributes'  => array(
								'class' => 'button button-secondary',
								'href'  => admin_url( 'admin.php?page=sprout-invoices-settings' ),
							),
						)
					),
				);
			default:
				return array();
		}
	}

	/**
	 * Get Welcome Page Fields
	 *
	 * This section will include license field and description of the plugin.
	 *
	 * @since 20.7.0
	 *
	 * @return array $fields
	 */
	public static function get_welcome_fields() {
		if ( class_exists( 'SI_Updates' ) ) {
			$license_key    = trim( get_option( SI_Updates::LICENSE_KEY_OPTION, '' ) );
			$license_status = get_option( SI_Updates::LICENSE_STATUS, false );

			$welcome_fields = array(
				SI_Field_Factory::create(
					'welcome_paragraph',
					'', // No label for this field.
					'', // No description for this field.
					'paragraph',
					array(
						'attributes'     => array(
							'class' => 'si-welcome-paragraph',
						),
						'paragraph_text' => __( 'Sprout Invoices is a complete invoicing solution for WordPress. It is designed to be simple and easy to use, while providing the flexibility and power you need.', 'sprout-invoices' ),
					)
				),
				SI_Field_Factory::create(
					'si_license_key',
					__( 'License Key', 'sprout-invoices' ),
					'',
					'text',
					array(
						'attributes'  => array(
							'class' => $license_status,
						),
						'placeholder' => __( 'Enter your license key', 'sprout-invoices' ),
						'value'       => $license_key,
						'required'    => true,
					)
				),
				SI_Field_Factory::create(
					'si_license_message',
					'', // No label for this field.
					'', // No description for this field.
					'paragraph',
					array(
						'attributes'     => array(
							'id' => 'si_html_message',
						),
						'paragraph_text' => '',
					)
				),
			);

			// Display the correct button based on the license status.
			if ( SI_Updates::license_status() !== false && SI_Updates::license_status() === 'valid' ) {
				array_push(
					$welcome_fields,
					SI_Field_Factory::create(
						'license_key_submit',
						'',
						'',
						'button',
						array(
							'button_text' => 'Deactivate License',
							'attributes'  => array(
								'id'              => 'deactivate_license',
								'class'           => 'si_admin_button lg',
								'v-on:click'      => "activateLicense('si_deactivate_license')",
								'v-bind:disabled' => 'isSaving',
							),
						)
					)
				);
				array_push(
					$welcome_fields,
					SI_Field_Factory::create(
						'license_key_submit',
						'',
						'',
						'button',
						array(
							'button_text' => 'Activate Pro License',
							'attributes'  => array(
								'id'              => 'activate_license',
								'class'           => 'si_admin_button lg hidden',
								'v-on:click'      => "activateLicense('si_activate_license')",
								'v-bind:disabled' => 'isSaving',
							),
						)
					)
				);
			} else {
				array_push(
					$welcome_fields,
					SI_Field_Factory::create(
						'license_key_submit',
						'',
						'',
						'button',
						array(
							'button_text' => 'Activate Pro License',
							'attributes'  => array(
								'id'              => 'activate_license',
								'class'           => 'si_admin_button lg',
								'v-on:click'      => "activateLicense('si_activate_license')",
								'v-bind:disabled' => 'isSaving',
							),
						)
					)
				);
			}

			// If Updates class exists, show license fields.
			return $welcome_fields;
		}

		return array(
			SI_Field_Factory::create(
				'welcome_paragraph',
				'', // No label for this field.
				'', // No description for this field.
				'paragraph',
				array(
					'attributes'     => array(
						'class' => 'si-welcome-paragraph',
					),
					'paragraph_text' => __( 'Sprout Invoices is a complete invoicing solution for WordPress. It is designed to be simple and easy to use, while providing the flexibility and power you need.', 'sprout-invoices' ),
				)
			),
		);
	}

	/**
	 * Get Company Info Settings Fields
	 *
	 * @since 20.7.0
	 *
	 * @return array $fields
	 */
	public static function get_company_info_fields() {
		$raw_settings_fields = SI_Admin_Settings::settings_address_fields();
		$fields              = array();
		foreach ( $raw_settings_fields as $field_name => $field_configs ) {
			if ( 'text' === $field_configs['type'] ) {
				$fields[] = SI_Field_Factory::create(
					$field_name,
					$field_configs['label'],
					'',
					$field_configs['type'],
					array(
						'placeholder' => $field_configs['label'],
						'value'       => $field_configs['option']['default'],
						'required'    => isset( $field_configs['option']['required'] ) ? $field_configs['option']['required'] : false,
						'attributes'  => array(
							'v-model' => 'vm.' . esc_attr( $field_name ),
						),
					)
				);
			} elseif ( 'select' === $field_configs['type'] ) {
				$fields[] = SI_Field_Factory::create(
					$field_name,
					$field_configs['label'],
					'',
					$field_configs['type'],
					array(
						'placeholder' => $field_configs['label'],
						'value'       => $field_configs['option']['default'],
						'required'    => isset( $field_configs['option']['required'] ) ? $field_configs['option']['required'] : false,
						'options'     => $field_configs['option']['options'],
						'attributes'  => array(
							'v-model' => 'vm.' . esc_attr( $field_name ),
						),
					)
				);
			} elseif ( 'select-state' === $field_configs['type'] ) {
				// Update field type to match class name.
				$field_configs['type'] = 'select_state';

				$fields[] = SI_Field_Factory::create(
					$field_name,
					$field_configs['label'],
					'',
					$field_configs['type'],
					array(
						'placeholder' => $field_configs['label'],
						'value'       => $field_configs['option']['default'],
						'required'    => isset( $field_configs['option']['required'] ) ? $field_configs['option']['required'] : false,
						'options'     => $field_configs['option']['options'],
						'attributes'  => array(
							'v-model' => 'vm.' . esc_attr( $field_name ),
						),
					)
				);
			}
		}
		return $fields;
	}

	/**
	 * Get Locale Settings Fields
	 *
	 * @since 20.7.0
	 *
	 * @return array $fields
	 */
	public static function get_locale_fields() {
		$raw_settings_fields = SI_Admin_Settings::settings_currency_locale_fields();
		$fields              = array();
		foreach ( $raw_settings_fields as $field_name => $field_configs ) {
			if ( 'small-input' === $field_configs['type'] ) {
				$fields[] = SI_Field_Factory::create(
					$field_name,
					$field_configs['label'],
					'',
					'text',
					array(
						'placeholder' => $field_configs['label'],
						'value'       => $field_configs['option']['default'],
						'required'    => isset( $field_configs['option']['required'] ) ? $field_configs['option']['required'] : false,
						'attributes'  => array(
							'v-model' => 'vm.' . esc_attr( $field_name ),
						),
					)
				);
			} elseif ( 'checkbox' === $field_configs['type'] ) {
				$fields[] = SI_Field_Factory::create(
					$field_name,
					$field_configs['label'],
					'',
					'Checkbox',
					array(
						'attributes' => array(
							'id'           => esc_attr( $field_name ),
							'name'         => esc_attr( $field_name ),
							'type'         => 'checkbox',
							'v-model.lazy' => 'vm.' . esc_attr( $field_name ),
						),
					)
				);
			}
		}

		return $fields;
	}

	/**
	 * Get Notification Settings Fields
	 *
	 * @since 20.7.0
	 *
	 * @return array $fields
	 */
	public static function get_notification_fields() {
		$raw_settings_fields = SI_Notifications_Control::notification_settings()['default_settings']['settings'];
		$fields              = array();
		foreach ( $raw_settings_fields as $field_name => $field_configs ) {
			if ( 'text' === $field_configs['option']['type'] ) {
				$fields[] = SI_Field_Factory::create(
					$field_name,
					$field_configs['label'],
					'',
					$field_configs['option']['type'],
					array(
						'placeholder' => $field_configs['label'],
						'value'       => $field_configs['option']['default'],
						'required'    => isset( $field_configs['option']['required'] ) ? $field_configs['option']['required'] : false,
						'attributes'  => array(
							'v-model' => 'vm.' . esc_attr( $field_name ),
						),
					)
				);
			} elseif ( 'select' === $field_configs['option']['type'] ) {
				$fields[] = SI_Field_Factory::create(
					$field_name,
					$field_configs['label'],
					'',
					$field_configs['option']['type'],
					array(
						'placeholder' => $field_configs['label'],
						'value'       => $field_configs['option']['default'],
						'required'    => isset( $field_configs['option']['required'] ) ? $field_configs['option']['required'] : false,
						'options'     => $field_configs['option']['options'],
						'attributes'  => array(
							'v-model' => 'vm.' . esc_attr( $field_name ),
						),
					)
				);
			}
		}

		return $fields;
	}

	/**
	 * Get available addons for the setup wizard.
	 *
	 * @since 20.7.0
	 *
	 * @return array array of addons for the setup wizard.
	 */
	public static function get_wizard_addons() {
		$addons = self::get_addon_groups();

		if ( empty( $addons ) ) {
			return array();
		}

		// Project Addons.
		$project_addon_rows = array();
		foreach ( $addons['project_addons'] as $addon => $addon_data ) {
			$vm_key               = SI_Settings_API::_sanitize_input_for_vue( $addon );
			$project_addon_rows[] = SI_Field_Factory::create(
				$vm_key,
				$addon_data['Title'],
				'',
				'table_row',
				array(
					'columns' => SA_Addons::get_addon_row_options( $addon, $addon_data ),
				),
			);
		}

		// Integrations Addons.
		$integrations_addon_rows = array();
		foreach ( $addons['integrations'] as $addon => $addon_data ) {
			$vm_key                    = SI_Settings_API::_sanitize_input_for_vue( $addon );
			$integrations_addon_rows[] = SI_Field_Factory::create(
				$vm_key,
				$addon_data['Title'],
				'',
				'table_row',
				array(
					'columns' => SA_Addons::get_addon_row_options( $addon, $addon_data ),
				),
			);
		}

		// Advanced Addons.
		$advanced_addon_rows = array();
		foreach ( $addons['advanced_addons'] as $addon => $addon_data ) {
			$vm_key                = SI_Settings_API::_sanitize_input_for_vue( $addon );
			$advanced_addon_rows[] = SI_Field_Factory::create(
				$vm_key,
				$addon_data['Title'],
				'',
				'table_row',
				array(
					'columns' => SA_Addons::get_addon_row_options( $addon, $addon_data ),
				),
			);
		}

		// Other Addons.
		$other_addon_rows = array();
		foreach ( $addons['other_addons'] as $addon => $addon_data ) {
			$vm_key             = SI_Settings_API::_sanitize_input_for_vue( $addon );
			$other_addon_rows[] = SI_Field_Factory::create(
				$vm_key,
				$addon_data['Title'],
				'',
				'table_row',
				array(
					'columns' => SA_Addons::get_addon_row_options( $addon, $addon_data ),
				),
			);
		}

		return array(
			'project-addons'  => $project_addon_rows,
			'integrations'    => $integrations_addon_rows,
			'advanced-addons' => $advanced_addon_rows,
			'other-addons'    => $other_addon_rows,
		);
	}

	/**
	 * Get Addon Groups.
	 *
	 * @since 20.7.0
	 *
	 * @return array $addon_groups addon groups.
	 */
	public static function get_addon_groups() {
		$addons = SA_Addons::get_addons();

		// If there are no addons, i.e. free user, return an empty array.
		if ( empty( $addons ) ) {
			return array();
		}

		$prefix = 'sprout-invoices-add-on-';

		$project_addons  = array(
			$prefix . 'time-tracking-for-projects'                          => $addons[ $prefix . 'time-tracking-for-projects' ],
			$prefix . 'downloadable-attachments-for-invoices-and-estimates' => $addons[ $prefix . 'downloadable-attachments-for-invoices-and-estimates' ],
			$prefix . 'departments'                                         => $addons[ $prefix . 'departments' ],
		);
		$integrations    = array(
			$prefix . 'sprout-invoices-woocommerce'                            => $addons[ $prefix . 'sprout-invoices-woocommerce' ],
			$prefix . 'legally-binding-digital-signatures-with-wp-e-signature' => $addons[ $prefix . 'legally-binding-digital-signatures-with-wp-e-signature' ],
			$prefix . 'time-tracking-w-toggl'                                  => $addons[ $prefix . 'time-tracking-w-toggl' ],
			$prefix . 'twilio-sms-notifications'                               => $addons[ $prefix . 'twilio-sms-notifications' ],
			$prefix . 'zapier'                                                 => $addons[ $prefix . 'zapier' ],
		);
		$advanced_addons = array(
			$prefix . 'digital-document-signing-for-wordpress-invoices-estimates' => $addons[ $prefix . 'digital-document-signing-for-wordpress-invoices-estimates' ],
			$prefix . 'protected-invoices-estimates-advanced'                     => $addons[ $prefix . 'protected-invoices-estimates-advanced' ],
			$prefix . 'tos-agreement'                                             => $addons[ $prefix . 'tos-agreement' ],
			$prefix . 'embeds'                                                    => $addons[ $prefix . 'embeds' ],
		);
		$other_addons    = array(
			$prefix . 'shipping-address'                      => $addons[ $prefix . 'shipping-address' ],
			$prefix . 'estimate-invoice-line-item-commenting' => $addons[ $prefix . 'estimate-invoice-line-item-commenting' ],
			$prefix . 'client-payment-processor-limits'       => $addons[ $prefix . 'client-payment-processor-limits' ],
			$prefix . 'point-of-contact-for-clients'          => $addons[ $prefix . 'point-of-contact-for-clients' ],

		);

		/**
		 * List of addon groups.
		 *
		 * Tax Addons are not included yet as they are installed as seperate plugins for now.
		 * See check_for_tax_addons() in SI_Setup_Wizard_Util class.
		 */
		$addon_groups = array(
			'project_addons'  => $project_addons,
			'integrations'    => $integrations,
			'advanced_addons' => $advanced_addons,
			'other_addons'    => $other_addons,
		);

		// Remove empty addons.
		foreach ( $addon_groups as $group => $addons ) {
			foreach ( $addons as $addon => $addon_data ) {
				if ( empty( $addon_data ) ) {
					unset( $addon_groups[ $group ][ $addon ] );
				}
			}
		}

		return $addon_groups;
	}

	/**
	 * Get Free Addons Fields.
	 *
	 * Get fields for addon section for free users. Text to say they are on free and
	 *
	 * @since 20.7.0
	 *
	 * @return array $fields
	 */
	public static function get_free_addons_fields() {
		$addons_text = sprintf(
			/* translators: %s: Sprout Invoices Pro page UTM URL */
			esc_html__( 'You are currently using the free Version of sprout. Upgrade to %s for more addons .', 'sprout-invoices' ),
			'<a href="https://sproutinvoices.com/pricing/?_sa_d=si19BIGdiscount" target="_blank">' . __( 'Sprout Invoices Pro', 'sprout-invoices' ) . '</a>'
		);
		return array(
			SI_Field_Factory::create(
				'free_addons_paragraph',
				'', // No label for this field.
				'', // No description for this field.
				'paragraph',
				array(
					'attributes'     => array(
						'class' => 'si-addon-paragraph',
					),
					'paragraph_text' => $addons_text,
				)
			),
		);
	}

	/**
	 * Get Recommended Payment Processor Rows.
	 *
	 * Gets the recommended payment processor rows for the setup wizard.
	 *
	 * @since 20.7.0
	 *
	 * @return array $rec_processsor_array recommended payment processor rows.
	 */
	public static function get_rec_processor_rows() {
		$all_processors         = SI_Payment_Processors::get_all_processors();
		$recommended_processors = SI_Payment_Processors::get_processor_recommendations();
		$rec_processsor_array   = array();

			foreach ( $recommended_processors as $class_name => $label ) {
				switch ( $class_name ) {
					case 'SA_Square':
						$rec_processsor_array[] = SI_Field_Factory::create(
							$class_name,
							$label,
							'',
							'table_row',
							array(
								'columns' => class_exists( 'SA_Square' ) ? SI_Payment_Processors::get_processor_row_options( $class_name, $label ) : SI_Payment_Processors::get_processor_row_upsell( $class_name, $label ),
							),
						);
						break;
					case 'SI_Stripe_Checkout':
						$rec_processsor_array[] = SI_Field_Factory::create(
							$class_name,
							$label,
							'',
							'table_row',
							array(
								'columns' => class_exists( 'SI_Stripe_Checkout' ) ? SI_Payment_Processors::get_processor_row_options( $class_name, $label ) : SI_Payment_Processors::get_processor_row_upsell( $class_name, $label ),
							),
						);
						break;
					case 'SI_PayPal_Checkout':
						$rec_processsor_array[] = SI_Field_Factory::create(
							$class_name,
							$label,
							'',
							'table_row',
							array(
								'columns' => class_exists( 'SI_PayPal_Checkout' ) ? SI_Payment_Processors::get_processor_row_options( $class_name, $label ) : SI_Payment_Processors::get_processor_row_upsell( $class_name, $label ),
							),
						);
						break;
					case 'SI_Woo_Payment_Processor':
						$rec_processsor_array[] = SI_Field_Factory::create(
							$class_name,
							$label,
							'',
							'table_row',
							array(
								'columns' => class_exists( 'SI_Woo_Payment_Processor' ) ? SI_Payment_Processors::get_processor_row_options( $class_name, $label ) : SI_Payment_Processors::get_processor_row_upsell( $class_name, $label ),
							),
						);
						break;
					default: // All other processors.
						$rec_processsor_array[] = SI_Field_Factory::create(
							$class_name,
							$label,
							'',
							'table_row',
							array(
								'columns' => SI_Payment_Processors::get_processor_row_options( $class_name, $label ),
							),
						);
						break;
				}
			}
		return $rec_processsor_array;
	}

	/**
	 * Get All Payment Processor Rows.
	 *
	 * Gets all payment processor rows for the setup wizard minus the recommended processor
	 * this is done to avoid duplicates across the two tables.
	 *
	 * @since 20.7.0
	 *
	 * @return array $all_processors_array other payment processor rows.
	 */
	public static function get_all_processor_rows() {
		$all_processors         = SI_Payment_Processors::get_all_processors();
		$recommended_processors = SI_Payment_Processors::get_processor_recommendations();

		// Remove recommended processors from all processors so there are not duplicates.
		foreach ( $recommended_processors as $class_name => $label ) {
			unset( $all_processors[ $class_name ] );
		}

		$all_processors_array = array();
		foreach ( $all_processors as $class_name => $label ) {
			$all_processors_array[] = SI_Field_Factory::create(
				$class_name,
				$label,
				'',
				'table_row',
				array(
					'columns' => SI_Payment_Processors::get_processor_row_options( $class_name, $label ),
				),
			);
		}

		return $all_processors_array;
	}

	/**
	 * Get Finish Fields.
	 *
	 * Final page of the wizard. Will direct user to Invoice or Estimate creation. Will also allow the user to go to
	 * the settings page.
	 *
	 * @since 20.7.0
	 *
	 * @return array $fields
	 */
	public static function get_finish_fields() {
		$fields = array(
			SI_Field_Factory::create(
				'finish_paragraph',
				'', // No label for this field.
				'', // No description for this field.
				'paragraph',
				array(
					'attributes'     => array(
						'class' => 'si-welcome-paragraph',
					),
					'paragraph_text' => __( 'You have successfully completed the setup wizard. What would you like to do next?', 'sprout-invoices' ),
				)
			),
			SI_Field_Factory::create(
				'create_invoice',
				'',
				'',
				'button',
				array(
					'button_text' => __( 'Create an Invoice', 'sprout-invoices' ),
					'attributes'  => array(
						'class' => 'button button-primary',
						'href'  => admin_url( 'post-new.php?post_type=sa_invoice' ),
					),
				)
			),
			SI_Field_Factory::create(
				'create_estimate',
				'',
				'',
				'button',
				array(
					'button_text' => __( 'Create an Estimate', 'sprout-invoices' ),
					'attributes'  => array(
						'class' => 'button button-primary',
						'href'  => admin_url( 'post-new.php?post_type=sa_estimate' ),
					),
				)
			),
		);

		return $fields;
	}


}

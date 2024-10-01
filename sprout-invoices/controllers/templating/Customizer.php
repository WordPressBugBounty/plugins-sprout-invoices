<?php

/**
 * Controller
 * Adds meta boxes to client admin.
 */
class SI_Customizer extends SI_Controller {

	public static function init() {
		add_action( 'customize_register', array( __CLASS__, 'customizer' ) );

		// Admin bar
		add_filter( 'si_admin_bar', array( get_class(), 'add_link_to_admin_bar' ), 10, 1 );
	}


	//////////////
	// Utility //
	//////////////


	public static function add_link_to_admin_bar( $items ) {
		if ( is_single() && si_get_doc_context() ) {
			$items[] = array(
				'id' => 'customizer',
				'title' => __( 'Customize', 'sprout-invoices' ),
				'href' => esc_url_raw( add_query_arg( array( 'url' => urlencode( get_permalink() ) ), admin_url( 'customize.php' ) ) ),
				'weight' => 1000,
			);
		}
		return $items;
	}


	/**
	 * Add customizer options for Sprout Invoices Themes.
	 *
	 * @since 2.0.0
	 *
	 * @todo Create seperate functions for each section, so that they can be used across themes. Each theme has a customizer.php file, and the goal is to refactor that file to use these functions.
	 *
	 * @param  object $wp_customize WP_Customize_Manager object.
	 *
	 * @return void
	 */
	public static function customizer( $wp_customize ) {

		// Sprout Invoices Panel.
		$wp_customize->add_panel(
			'si_customizer_section',
			array(
				'priority'       => 300,
				'theme_supports' => '',
				'title'          => esc_html__( 'Sprout invoices', 'woocommerce' ),
			)
		);

		// Logo uploader
		$wp_customize->add_section(
			'si_logo_section',
			array(
				'title'       => esc_html__( 'Estimate/Invoice Logo', 'sprout-invoices' ),
				'priority'    => 10,
				'description' => esc_html__( 'Upload a logo to replace the default estimate/invoice logo.', 'sprout-invoices' ),
				'panel'       => 'si_customizer_section',
			)
		);

		$wp_customize->add_setting(
			'si_logo',
			array(
				'sanitize_callback' => 'esc_url_raw',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Image_Control(
				$wp_customize,
				'si_logo',
				array(
					'label'    => esc_html__( 'Invoice & Estimate Logo', 'sprout-invoices' ),
					'section'  => 'si_logo_section',
					'settings' => 'si_logo',
				)
			)
		);

		$invoice_theme  = SI_Templating_API::get_invoice_theme_option();
		$estimate_theme = SI_Templating_API::get_estimate_theme_option();

		// Themes Customizer Options.
		self::theme_customizer_options(
			$wp_customize,
			self::get_invoice_customizer_options( $invoice_theme ),
			self::get_estimate_customizer_options( $estimate_theme ),
			$invoice_theme,
			$estimate_theme
		);
	}

	/**
	 * Add customizer options for Sprout Invoices Themes.
	 *
	 * @since 20.6.1
	 *
	 * @param  object $wp_customize WP_Customize_Manager object.
	 * @param  array  $invoice_customizer_settings Array of settings to add.
	 * @param  array  $estimate_customizer_settings Array of settings to add.
	 * @param  string $invoice_theme The invoice theme to get options for.
	 * @param  string $estimate_theme The estimate theme to get options for.
	 *
	 * @return void
	 */
	public static function theme_customizer_options(
		$wp_customize,
		$invoice_customizer_settings,
		$estimate_customizer_settings,
		$invoice_theme,
		$estimate_theme
		) {

		$wp_customize->add_section(
			'si_' . $invoice_theme . '_invoice',
			array(
				'title'    => sprintf(
					/* translators: %s: theme name */
					esc_html__( '%s Theme Invoice Customization', 'sprout-invoices' ),
					ucfirst( $invoice_theme )
				),
				'priority' => 300,
				'panel'    => 'si_customizer_section',
			)
		);

		$wp_customize->add_section(
			'si_' . $estimate_theme . '_estimate',
			array(
				'title'    => sprintf(
					/* translators: %s: theme name */
					esc_html__( '%s Theme Estimate Customization', 'sprout-invoices' ),
					ucfirst( $estimate_theme )
				),
				'priority' => 300,
				'panel'    => 'si_customizer_section',
			)
		);

		self::add_settings_controls( $wp_customize, $invoice_customizer_settings );
		self::add_settings_controls( $wp_customize, $estimate_customizer_settings );
	}


	/**
	 * Add customizer settings and controls for Sprout Invoices Themes.
	 *
	 * @since 20.6.1
	 *
	 * @param  object $wp_customize WP_Customize_Manager object.
	 * @param  array  $si_customizer_settings Array of settings to add.
	 *
	 * @return void
	 */
	public static function add_settings_controls( $wp_customize, $si_customizer_settings ) {
		foreach ( $si_customizer_settings as $setting ) {
			if ( isset( $setting['type'] ) && 'checkbox' === $setting['type'] ) {
				$wp_customize->add_setting(
					$setting['setting_id'],
					array(
						'default' => $setting['default'],
					)
				);
				$wp_customize->add_control(
					$setting['setting_id'],
					array(
						'label'       => $setting['label'],
						'section'     => $setting['section'],
						'description' => $setting['description'],
						'settings'    => $setting['setting_id'],
						'type'        => 'checkbox',
					)
				);
				continue;
			} else {
				$wp_customize->add_setting(
					$setting['setting_id'],
					array(
						'default'           => $setting['default'],
						'sanitize_callback' => array( __Class__, $setting['sanitize_cb'] ),
					)
				);
				$wp_customize->add_control(
					new WP_Customize_Color_Control(
						$wp_customize,
						$setting['setting_id'],
						array(
							'label'    => $setting['label'],
							'section'  => $setting['section'],
							'settings' => $setting['setting_id'],
						)
					)
				);
			}
		}
	}

	/**
	 * Get customizer options for the Invoice Theme.
	 *
	 * @since 20.6.1
	 *
	 * @param  string $theme The theme to get options for.
	 *
	 * @return array $si_customizer_settings Array of settings to add.
	 */
	public static function get_invoice_customizer_options( $theme ) {
		switch ( $theme ) {
			case 'basic':
				$si_customizer_settings = array(
					array(
						'setting_id'  => 'si_basic_paybar_top',
						'label'       => __( 'Paybar Top', 'sprout-invoices' ),
						'type'        => 'checkbox',
						'description' => __( 'Move the Payment button to the top of the Invoice.', 'sprout-invoices' ),
						'section'     => 'si_basic_invoice',
						'default'     => false,
						'sanitize_cb' => 'sanitize_checkbox',
					),
					array(
						'setting_id'  => 'si_basic_inv_primary_color',
						'label'       => __( 'Block Background Color', 'sprout-invoices' ),
						'section'     => 'si_basic_invoice',
						'default'     => '#000000',
						'sanitize_cb' => 'sanitize_hex_color',
					),
					array(
						'setting_id'  => 'si_basic_inv_secondary_color',
						'label'       => __( 'Block Text Color', 'sprout-invoices' ),
						'section'     => 'si_basic_invoice',
						'default'     => '#ffffff',
						'sanitize_cb' => 'sanitize_hex_color',
					),
					array(
						'setting_id'  => 'si_basic_est_paybar_background_color',
						'label'       => __( 'Payment Bar Background Color', 'sprout-invoices' ),
						'section'     => 'si_basic_estimate',
						'default'     => '#ffffff',
						'sanitize_cb' => 'sanitize_hex_color',
					),
					array(
						'setting_id'  => 'si_basic_inv_paybar_color',
						'label'       => __( 'Payment Button Background Color', 'sprout-invoices' ),
						'section'     => 'si_basic_invoice',
						'default'     => '#2373be',
						'sanitize_cb' => 'sanitize_hex_color',
					),
					array(
						'setting_id'  => 'si_basic_inv_text_color',
						'label'       => __( 'Payment Button Text Color', 'sprout-invoices' ),
						'section'     => 'si_basic_invoice',
						'default'     => '#ffffff',
						'sanitize_cb' => 'sanitize_hex_color',
					),
					array(
						'setting_id'  => 'si_payment_button',
						'label'       => __( 'Payment Processor Button Color', 'sprout-invoices' ),
						'section'     => 'si_basic_invoice',
						'default'     => '#2373be',
						'sanitize_cb' => 'sanitize_hex_color',
					),
				);
				return $si_customizer_settings;
			case 'default':
				$si_customizer_settings = array(
					array(
						'setting_id'  => 'si_paybar_top',
						'label'       => __( 'Paybar Top', 'sprout-invoices' ),
						'type'        => 'checkbox',
						'description' => __( 'Move the Payment button to the top of the Invoice.', 'sprout-invoices' ),
						'section'     => 'si_default_invoice',
						'default'     => false,
						'sanitize_cb' => 'sanitize_checkbox',
					),
					array(
						'setting_id'  => 'si_inv_primary_color',
						'label'       => __( 'Invoice Primary Background Color', 'sprout-invoices' ),
						'section'     => 'si_default_invoice',
						'default'     => '#4086b0',
						'sanitize_cb' => 'sanitize_hex_color',
					),
					array(
						'setting_id'  => 'si_inv_secondary_color',
						'label'       => __( 'Invoice Header Background', 'sprout-invoices' ),
						'section'     => 'si_default_invoice',
						'default'     => '#438cb7',
						'sanitize_cb' => 'sanitize_hex_color',
					),
					array(
						'setting_id'  => 'si_inv_text_color',
						'label'       => __( 'Invoice Primary Text Color', 'sprout-invoices' ),
						'section'     => 'si_default_invoice',
						'default'     => '',
						'sanitize_cb' => 'sanitize_hex_color',
					),
					array(
						'setting_id'  => 'si_inv_paybar_background_color',
						'label'       => __( 'Paybar Background Color', 'sprout-invoices' ),
						'section'     => 'si_default_invoice',
						'default'     => '#ffffff',
						'sanitize_cb' => 'sanitize_hex_color',
					),
					array(
						'setting_id'  => 'si_inv_paybar_color',
						'label'       => __( 'Paybar Button Color', 'sprout-invoices' ),
						'section'     => 'si_default_invoice',
						'default'     => '#000000',
						'sanitize_cb' => 'sanitize_hex_color',
					),
					array(
						'setting_id'  => 'si_payment_button',
						'label'       => __( 'Payment Processor Button Color', 'sprout-invoices' ),
						'section'     => 'si_default_invoice',
						'default'     => '#2373be',
						'sanitize_cb' => 'sanitize_hex_color',
					),
				);
				return $si_customizer_settings;
			case 'original':
				$si_customizer_settings = array(
					array(
						'setting_id'  => 'si_invoices_color',
						'label'       => __( 'Invoice Highlight Color (Original Theme)', 'sprout-invoices' ),
						'section'     => 'si_original_invoice',
						'default'     => '#FF5B4D',
						'sanitize_cb' => 'sanitize_hex_color',
					),
					array(
						'setting_id'  => 'si_payment_button',
						'label'       => __( 'Payment Processor Button Color', 'sprout-invoices' ),
						'section'     => 'si_original_invoice',
						'default'     => '#2373be',
						'sanitize_cb' => 'sanitize_hex_color',
					),
					array(
						'setting_id'  => 'si_payment_button_text',
						'label'       => __( 'Payment Processor Text Color', 'sprout-invoices' ),
						'section'     => 'si_original_invoice',
						'default'     => '#ffffff',
						'sanitize_cb' => 'sanitize_hex_color',
					),
				);
				return $si_customizer_settings;
			case 'slate':
				$si_customizer_settings = array(
					array(
						'setting_id'  => 'si_invoices_color',
						'label'       => __( 'Invoice Highlight Color (Slate Theme)', 'sprout-invoices' ),
						'section'     => 'si_slate_invoice',
						'default'     => '#FF5B4D',
						'sanitize_cb' => 'sanitize_hex_color',
					),
					array(
						'setting_id'  => 'si_payment_button',
						'label'       => __( 'Payment Processor Button Color', 'sprout-invoices' ),
						'section'     => 'si_slate_invoice',
						'default'     => '#2373be',
						'sanitize_cb' => 'sanitize_hex_color',
					),
					array(
						'setting_id'  => 'si_payment_button_text',
						'label'       => __( 'Payment Processor Text Color', 'sprout-invoices' ),
						'section'     => 'si_slate_invoice',
						'default'     => '#ffffff',
						'sanitize_cb' => 'sanitize_hex_color',
					),
				);
				return $si_customizer_settings;
			default:
				break;
		}

	}

	/**
	 * Get customizer options for the Estimate Theme.
	 *
	 * @since 20.6.1
	 *
	 * @param  string $theme The theme to get options for.
	 *
	 * @return array $si_customizer_settings Array of settings to add.
	 */
	public static function get_estimate_customizer_options( $theme ) {
		switch ( $theme ) {
			case 'basic':
				$si_customizer_settings = array(
					array(
						'setting_id'  => 'si_basic_paybar_top',
						'label'       => __( 'Lock Acceptance Bar to Top', 'sprout-invoices' ),
						'type'        => 'checkbox',
						'description' => __( 'Move the Acceptance button to the top of the Invoice.', 'sprout-invoices' ),
						'section'     => 'si_basic_estimate',
						'default'     => false,
						'sanitize_cb' => 'sanitize_checkbox',
					),
					array(
						'setting_id'  => 'si_basic_est_primary_color',
						'label'       => __( 'Block Background Color', 'sprout-invoices' ),
						'section'     => 'si_basic_estimate',
						'default'     => '#000000',
						'sanitize_cb' => 'sanitize_hex_color',
					),
					array(
						'setting_id'  => 'si_basic_est_secondary_color',
						'label'       => __( 'Block Text Color', 'sprout-invoices' ),
						'section'     => 'si_basic_estimate',
						'default'     => '#ffffff',
						'sanitize_cb' => 'sanitize_hex_color',
					),
					array(
						'setting_id'  => 'si_basic_est_paybar_background_color',
						'label'       => __( 'Acceptance Bar Background Color', 'sprout-invoices' ),
						'section'     => 'si_basic_estimate',
						'default'     => '#ffffff',
						'sanitize_cb' => 'sanitize_hex_color',
					),
					array(
						'setting_id'  => 'si_basic_est_paybar_color',
						'label'       => __( 'Accept Button Background Color', 'sprout-invoices' ),
						'section'     => 'si_basic_estimate',
						'default'     => '#ffffff',
						'sanitize_cb' => 'sanitize_hex_color',
					),
					array(
						'setting_id'  => 'si_basic_est_text_color',
						'label'       => __( 'Accept Button Text Color', 'sprout-invoices' ),
						'section'     => 'si_basic_estimate',
						'default'     => '#000000',
						'sanitize_cb' => 'sanitize_hex_color',
					),
				);
				return $si_customizer_settings;
			case 'default':
				$si_customizer_settings = array(
					array(
						'setting_id'  => 'si_basic_paybar_top',
						'label'       => __( 'Lock Acceptance Bar to Top', 'sprout-invoices' ),
						'type'        => 'checkbox',
						'description' => __( 'Move the Acceptance button to the top of the Invoice.', 'sprout-invoices' ),
						'section'     => 'si_basic_estimate',
						'default'     => false,
						'sanitize_cb' => 'sanitize_checkbox',
					),
					array(
						'setting_id'  => 'si_est_primary_color',
						'label'       => __( 'Estimate Primary Background Color', 'sprout-invoices' ),
						'section'     => 'si_default_estimate',
						'default'     => '#4086b0',
						'sanitize_cb' => 'sanitize_hex_color',
					),
					array(
						'setting_id'  => 'si_est_secondary_color',
						'label'       => __( 'Estimate Header Background', 'sprout-invoices' ),
						'section'     => 'si_default_estimate',
						'default'     => '#438cb7',
						'sanitize_cb' => 'sanitize_hex_color',
					),
					array(
						'setting_id'  => 'si_est_text_color',
						'label'       => __( 'Estimate Primary Text Color', 'sprout-invoices' ),
						'section'     => 'si_default_estimate',
						'default'     => '',
						'sanitize_cb' => 'sanitize_hex_color',
					),
					array(
						'setting_id'  => 'si_est_title_text_color',
						'label'       => __( 'Estimate Accent Text Color', 'sprout-invoices' ),
						'section'     => 'si_default_estimate',
						'default'     => '',
						'sanitize_cb' => 'sanitize_hex_color',
					),
					array(
						'setting_id'  => 'si_est_paybar_background_color',
						'label'       => __( 'Paybar Background Color', 'sprout-invoices' ),
						'section'     => 'si_default_estimate',
						'default'     => '',
						'sanitize_cb' => 'sanitize_hex_color',
					),
					array(
						'setting_id'  => 'si_est_paybar_color',
						'label'       => __( 'Paybar Text Color', 'sprout-invoices' ),
						'section'     => 'si_default_estimate',
						'default'     => '',
						'sanitize_cb' => 'sanitize_hex_color',
					),
				);
				return $si_customizer_settings;
			case 'original':
				$si_customizer_settings = array(
					array(
						'setting_id'  => 'si_estimates_color',
						'label'       => __( 'Estimate Highlight Color (Original Theme)', 'sprout-invoices' ),
						'section'     => 'si_original_estimate',
						'default'     => '#4D9FFF',
						'sanitize_cb' => 'sanitize_hex_color',
					),
				);
				return $si_customizer_settings;
			case 'slate':
				$si_customizer_settings = array(
					array(
						'setting_id'  => 'si_estimates_color',
						'label'       => __( 'Estimate Highlight Color (Slate Theme)', 'sprout-invoices' ),
						'section'     => 'si_slate_estimate',
						'default'     => '#FF5B4D',
						'sanitize_cb' => 'sanitize_hex_color',
					),
				);
				return $si_customizer_settings;
			default:
				break;
		}
	}

	public static function save_theme_option( $value ) {
		update_option( SI_Templating_API::INV_THEME_OPION, $value );
		return $value;
	}
	public static function save_est_theme_option( $value ) {
		update_option( SI_Templating_API::EST_THEME_OPION, $value );
		return $value;
	}

	/**
	* Sanitizes a hex color. Identical to core's sanitize_hex_color(), which is not available on the wp_head hook.
	*
	* Returns either '', a 3 or 6 digit hex color (with #), or null.
	* For sanitizing values without a #, see sanitize_hex_color_no_hash().
	*
	* @since 1.7
	*/
	public static function sanitize_hex_color( $color ) {
		if ( '' === $color ) {
			return '';
		}
		// 3 or 6 hex digits, or the empty string.
		if ( preg_match( '|^#([A-Fa-f0-9]{3}){1,2}$|', $color ) ) {
			return $color;
		}
		return null;
	}

	public static function sanitize_checkbox( $checked = false ) {
		 return ( ( isset( $checked ) && true == $checked ) ? true : false );
	}
}

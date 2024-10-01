<?php

/**
 * Templating API
 * shortcodes, page creation, etc.
 *
 * @package Sprout_Invoice
 * @subpackage TEmplating
 */
class SI_Templating_API extends SI_Controller {
	const TEMPLATE_OPTION = '_doc_template_option';
	const FILTER_QUERY_VAR = 'filter_doc';
	const BLANK_SHORTCODE = 'si_blank';
	const INV_THEME_OPION = 'si_inv_theme_template';
	const EST_THEME_OPION = 'si_est_theme_template';

	private static $pages = array();
	private static $shortcodes = array();
	private static $inv_theme_option = '';
	private static $est_theme_option = '';
	private static $themes = array(
			'default' => 'Default',
			'basic' => 'Basic',
			'slate' => 'Slate',
			'original' => 'Original',
		);

	public static function get_template_pages() {
		return self::$pages;
	}

	public static function get_shortcodes() {
		return self::$shortcodes;
	}

	public static function theme_templates() {
		return self::$themes;
	}

	public static function get_invoice_theme_option() {
		// defaults to old theme but new installs get a default saved when activated
		$option = get_option( self::INV_THEME_OPION, 'original' );
		return $option;
	}

	public static function get_estimate_theme_option() {
		// defaults to old theme but new installs get a default saved when activated
		$option = get_option( self::EST_THEME_OPION, 'original' );
		return $option;
	}

	public static function init() {

		// Theme Selection
		self::$inv_theme_option = self::get_invoice_theme_option();
		self::$est_theme_option = self::get_estimate_theme_option();

		// Register Settings
		add_filter( 'si_settings', array( __CLASS__, 'register_settings' ) );

		// Register Shortcodes
		add_action( 'sprout_shortcode', array( __CLASS__, 'register_shortcode' ), 0, 3 );
		// Add shortcodes
		add_action( 'init', array( __CLASS__, 'add_shortcodes' ) );

		// SI Info
		add_action( 'wp_footer', array( __CLASS__, 'add_info_to_footer' ) );
		add_action( 'si_footer', array( __CLASS__, 'add_info_to_footer' ) );

		// Determine template for estimates or invoices
		add_filter( 'template_include', array( __CLASS__, 'override_template' ) );
		add_action( 'template_redirect', array( __CLASS__, 'add_theme_functions' ), 0 );

		add_filter( 'sprout_invoice_template_possibilities', array( __CLASS__, 'add_theme_template_possibilities' ) );
		add_filter( 'si_locate_file_possibilites', array( __CLASS__, 'add_theme_template_possibilities' ) );

		add_action( 'doc_information_meta_box_client_row_last', array( __CLASS__, 'doc_template_selection' ) );
		add_action( 'si_save_line_items_meta_box', array( __CLASS__, 'save_doc_template_selection' ) );

		// Enqueue
		add_action( 'si_head', array( __CLASS__, 'head_scripts' ) );
		add_action( 'si_footer', array( __CLASS__, 'footer_scripts' ) );

		// Client option
		add_filter( 'si_client_adv_form_fields', array( __CLASS__, 'client_option' ) );
		add_action( 'SI_Clients::save_meta_box_client_adv_information', array( __CLASS__, 'save_client_options' ) );

		// blank shortcode
		do_action( 'si_version_upgrade', self::BLANK_SHORTCODE, array( __CLASS__, 'blank_shortcode' ) );

		// set defaults for new installs
		add_action( 'si_plugin_activation_hook', array( __CLASS__, 'set_defaults' ), 0 );

	}

	public static function set_defaults( $upgraded_from ) {
		$si_version = get_option( 'si_current_version', false );
		if ( ! $si_version ) { // wasn't activated before
			update_option( self::INV_THEME_OPION, 'basic' );
			update_option( self::EST_THEME_OPION, 'basic' );
		}
	}

	/////////////////
	// Shortcodes //
	/////////////////

	/**
	 * Wrapper for the add_shorcode function WP provides
	 * @param string the shortcode
	 * @param array $callback
	 * @param array $args FUTURE
	 */
	public static function register_shortcode( $tag = '', $callback = array(), $args = array() ) {
		// FUTURE $args
		self::$shortcodes[ $tag ] = $callback;
	}

	/**
	 * Loop through registered shortcodes and use the WP function.
	 * @return
	 */
	public static function add_shortcodes() {
		foreach ( self::$shortcodes as $tag => $callback ) {
			add_shortcode( $tag, $callback );
		}
	}


	////////////////////
	// Doc Templates //
	////////////////////

	/**
	 * Get all invoice templates within a user's theme
	 *
	 * @return array
	 */
	public static function get_invoice_templates() {
		$templates  = array( '' => __( 'Default Template', 'sprout-invoices' ) );
		$templates += self::get_doc_templates( 'invoice' );
		return $templates;
	}

	/**
	 * Get all estimate templates within a user's theme
	 *
	 * @return array $templates array of templates.
	 */
	public static function get_estimate_templates() {
		$templates  = array( '' => __( 'Default Template', 'sprout-invoices' ) );
		$templates += self::get_doc_templates( 'estimate' );
		return $templates;
	}

	/**
	 * Get the template for the current doc
	 *
	 * @param  string $doc_id invoice/estimate id.
	 * @return string $template_id template id.
	 */
	public static function get_doc_current_template( $doc_id ) {
		$template_id = get_post_meta( $doc_id, self::TEMPLATE_OPTION, true );
		if ( $template_id == '' ) {
			switch ( get_post_type( $doc_id ) ) {
				case SI_Invoice::POST_TYPE:
					$invoice = SI_Invoice::get_instance( $doc_id );
					$client_id = $invoice->get_client_id();
					$template_id = self::get_client_invoice_template( $client_id );
					break;
				case SI_Estimate::POST_TYPE:
					$estimate = SI_Estimate::get_instance( $doc_id );
					$client_id = $estimate->get_client_id();
					$template_id = self::get_client_estimate_template( $client_id );
					break;

				default:
					break;
			}
		}
		if ( ! $template_id ) {
			$template_id = '';
		}
		return $template_id;
	}

	public static function head_scripts( $v2_theme = false ) {
		global $wp_scripts;
		global $wp_styles;

		if ( ! $v2_theme ) {
			wp_enqueue_style( 'open-sans-css', '//fonts.googleapis.com/css?family=Open+Sans%3A300italic%2C400italic%2C600italic%2C300%2C400%2C600&amp;subset=latin%2Clatin-ext', array(), false );
			wp_enqueue_style( 'dashicons-css', includes_url( 'css/dashicons.min.css' ), array(), false );
			wp_enqueue_style( 'qtip-css', SI_RESOURCES . 'admin/plugins/qtip/jquery.qtip.min.css', array(), false );
			wp_enqueue_style( 'dropdown-css', SI_RESOURCES . 'admin/plugins/dropdown/jquery.dropdown.css', array(), false );
			wp_enqueue_style( 'sprout_doc_style-css', SI_RESOURCES . 'deprecated-front-end/css/sprout-invoices.style.css', array(), false );

			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'dropdown', SI_RESOURCES . 'admin/plugins/dropdown/jquery.dropdown.min.js', array(), false );
			wp_enqueue_script( 'jquery-qtip', SI_RESOURCES . 'admin/plugins/qtip/jquery.qtip.min.js', array( 'jquery' ), false, true );
			wp_enqueue_script( 'jquery-ui-droppable' );
			wp_enqueue_script( 'deprecated-front-end', SI_RESOURCES . 'deprecated-front-end/js/sprout-invoices.js', array( 'jquery', 'jquery-ui-droppable', 'jquery-qtip' ), false, true );
		}

		self::load_custom_stylesheet();
		self::load_custom_js();

		// Adds inline JS to header.
		wp_register_script( 'si-localized', '' );
		wp_enqueue_script( 'si-localized' );
		wp_add_inline_script( 'si-localized', 'var si_js_object = ' . wp_json_encode( SI_Controller::get_localized_js() ) . ';' );

		/*
		 * Since we do not call 'wp_head' when displaying
		 * Invoice and Estimate templates, the css and js files
		 * are not enqueued or printed in the normal way, so we have to call
		 * wp_print_scripts and wp_print_styles here manually.
		 */
		echo '<!--SI Scripts-->';
		wp_print_scripts();
		echo '<!--SI Styles-->';
		wp_print_styles();
	}

	public static function load_custom_stylesheet() {
		$context = si_get_doc_context();
		if ( '' === $context ) {
			return;
		}

		$theme_option = ( 'invoice' === $context ) ? self::$inv_theme_option : self::$est_theme_option ;

		$base_stylesheet_path = self::locate_template( array(
			'theme/' . $theme_option . '/docs.css',
			$theme_option . '.css',
		), false );

		if ( $base_stylesheet_path ) {
			$stylesheet_url = _convert_content_file_path_to_url( $base_stylesheet_path );
			wp_enqueue_style( 'sprout_doc_style-' . $theme_option . '-css', $stylesheet_url, array(), false );
		}

		$context_stylesheet_path = self::locate_template( array(
			'theme/' . $theme_option . '/' . $context . '/' . $context . 's.css',
			$context . 's.css',
			$context . '/' . $context . 's.css',
		), false );

		if ( $context_stylesheet_path ) {
			$stylesheet_url = _convert_content_file_path_to_url( $context_stylesheet_path );
			wp_enqueue_style( 'sprout_doc_style-' . $context . '-css', $stylesheet_url, array(), false );
		}

		$stylesheet_path = self::locate_template( array(
			'sprout-invoices.css',
		), false );

		if ( $stylesheet_path ) {
			$general_stylesheet_url = _convert_content_file_path_to_url( $stylesheet_path );
			wp_enqueue_style( 'sprout_doc_style-general-css', $general_stylesheet_url, array(), false );
		}
	}

	public static function load_custom_js() {
		global $wp_scripts;

		$context = si_get_doc_context();
		if ( '' === $context ) {
			return;
		}

		$theme_option = ( 'invoice' === $context ) ? self::$inv_theme_option : self::$est_theme_option ;

		$base_js_path = self::locate_template( array(
			'theme/' . $theme_option . '/docs.js',
			$theme_option . '.js',
		), false );

		if ( $base_js_path ) {
			$js_url = _convert_content_file_path_to_url( $base_js_path );
			wp_enqueue_script( 'sprout_doc_style-' . $theme_option . '-css', $js_url, array( 'jquery' ), false, false );
		}

		$context_js_path = self::locate_template( array(
			'theme/' . $theme_option . '/' . $context . '/' . $context . 's.js',
			$context . 's.js',
			$context . '/' . $context . 's.js',
		), false );

		if ( $context_js_path ) {
			$js_url = _convert_content_file_path_to_url( $context_js_path );
			wp_enqueue_script( 'sprout_doc_style-' . $context . '-css', $js_url, array( 'jquery' ), false, false );
		}

		$js_path = self::locate_template( array(
			'sprout-invoices.js',
		), false );

		if ( $js_path ) {
			$general_js_url = _convert_content_file_path_to_url( $js_path );
			wp_enqueue_script( 'sprout_doc_style-general-css', $general_js_url, array( 'jquery' ), false, false );
		}
	}

	public static function footer_scripts() {
		global $wp_scripts;
		global $wp_customize;

		if ( current_user_can( 'edit_post', get_the_id() ) ) {
			wp_admin_bar_render();
		}

		if ( isset( $wp_customize ) && current_user_can( 'edit_post', get_the_id() ) ) {
			wp_enqueue_script( 'json2' );
			wp_enqueue_script( 'underscore' );
			wp_enqueue_script( 'customize-base' );
			wp_enqueue_script( 'customize-preview' );
			wp_enqueue_script( 'si-customizer', SI_RESOURCES . 'admin/js/customizer.js', array( 'customize-preview' ), false, true );
		}

		/*
		 * Since we do not call 'wp_footer' when displaying
		 * Invoice and Estimate templates, the js files
		 * are not enqueued or printed in the normal way, so we have to call
		 * wp_print_footer_scripts here manually.
		 */
		wp_print_footer_scripts();
	}

	/**
	 * Save the template selection for a doc by post id
	 * @param  integer $post_id
	 * @param  string  $doc_template
	 * @return
	 */
	public static function save_doc_current_template( $doc_id = 0, $doc_template = '' ) {
		update_post_meta( $doc_id, self::TEMPLATE_OPTION, $doc_template );
	}

	/**
	 * Get the template for a client
	 * @param  string $doc
	 * @return
	 */
	public static function get_client_invoice_template( $client_id ) {
		$template_id = get_post_meta( $client_id, self::TEMPLATE_OPTION, true );
		return $template_id;
	}

	/**
	 * Get the template for a client
	 * @param  string $doc
	 * @return
	 */
	public static function get_client_estimate_template( $client_id ) {
		$template_id = get_post_meta( $client_id, self::TEMPLATE_OPTION.'_est', true );
		return $template_id;
	}

	/**
	 * Save the template selection for a client by post id
	 * @param  integer $post_id
	 * @param  string  $doc_template
	 * @return
	 */
	public static function save_client_invoice_template( $client_id = 0, $doc_template = '' ) {
		update_post_meta( $client_id, self::TEMPLATE_OPTION, $doc_template );
	}

	/**
	 * Save the template selection for a client by post id
	 * @param  integer $post_id
	 * @param  string  $doc_template
	 * @return
	 */
	public static function save_client_estimate_template( $client_id = 0, $doc_template = '' ) {
		update_post_meta( $client_id, self::TEMPLATE_OPTION.'_est', $doc_template );
	}

	/**
	 * Override the template and use something custom.
	 * @param  string $template
	 * @return string           full path.
	 */
	public static function override_template( $template ) {

		// Invoicing
		if ( SI_Invoice::is_invoice_query() ) {

			if ( is_single() ) {

				if ( ! current_user_can( 'edit_sprout_invoices' ) && apply_filters( 'si_redirect_temp_status', true ) ) {
					$status = get_post_status();
					if ( in_array( $status, array( SI_Invoice::STATUS_TEMP, SI_Invoice::STATUS_ARCHIVED ) ) ) {
						wp_safe_redirect( add_query_arg( array( 'si_id' => get_the_id() ), get_home_url() ) );
						exit();
					}
				}

				$custom_template = self::get_doc_current_template( get_the_id() );
				$custom_path = ( $custom_template != '' ) ? 'invoice/'.$custom_template : '' ;
				$template = self::locate_template( array(
					$custom_path,
					'theme/' . self::$inv_theme_option . '/invoice/invoice.php',
					'invoice-'.get_locale().'.php',
					'invoice.php',
					'invoice/invoice.php',
				), $template );

			} else {
				$status = get_query_var( self::FILTER_QUERY_VAR );
				$template = self::locate_template( array(
					'invoice/'.$status.'-invoices.php',
					$status.'-invoices.php',
					'invoices.php',
					'invoice/invoices.php',
				), $template );
			}
			$template = apply_filters( 'si_doc_template', $template, 'invoice' );
		}

		// Estimates
		if ( SI_Estimate::is_estimate_query() ) {

			if ( is_single() ) {

				if ( ! current_user_can( 'edit_sprout_invoices' ) && apply_filters( 'si_redirect_temp_status', true ) ) {
					$status = get_post_status();
					if ( in_array( $status, array( SI_Estimate::STATUS_TEMP, SI_Estimate::STATUS_ARCHIVED ) ) ) {
						wp_safe_redirect( add_query_arg( array( 'si_id' => get_the_id() ), get_home_url() ) );
						exit();
					}
				}

				$custom_template = self::get_doc_current_template( get_the_id() );
				$custom_path = ( $custom_template != '' ) ? 'estimate/'.$custom_template : '' ;
				$template = self::locate_template( array(
					$custom_path,
					'theme/' . self::$est_theme_option . '/estimate/estimate.php',
					'estimate-'.get_locale().'.php',
					'estimate.php',
					'estimate/estimate.php',
				), $template );
			} else {
				$status = get_query_var( self::FILTER_QUERY_VAR );
				$template = self::locate_template( array(
					'estimate/'.$status.'-estimates.php',
					$status.'-estimates.php',
					'estimates.php',
					'estimate/estimates.php',
				), $template );
			}

			$template = apply_filters( 'si_doc_template', $template, 'estimate' );
		}
		return $template;
	}

	public static function add_theme_functions() {
		$theme = ( SI_Invoice::is_invoice_query() ) ? self::$inv_theme_option : self::$est_theme_option ;

		$template = SI_Controller::locate_template( array(
			'theme/'.$theme.'/functions.php',
		) );
		include $template;
	}


	public static function add_theme_template_possibilities( $possibilities ) {
		$possibilities = array_filter( $possibilities );
		$theme = ( SI_Invoice::is_invoice_query() ) ? self::$inv_theme_option : self::$est_theme_option;

		$new_possibilities = array();
		foreach ( $possibilities as $key => $path ) {
			if ( '' === $path ) {
				continue;
			}
			$new_possibilities[] = 'theme/' . $theme . '/' . str_replace( 'templates/', '', $path );
		}
		return array_merge( $new_possibilities, $possibilities );
	}

	////////////
	// admin //
	////////////

	/**
	 * Register the setting for invoice/estimate templating.
	 * Hooked on init add the settings page and options.
	 *
	 * @param  array $settings settings array.
	 * @return array $settings
	 */
	public static function register_settings( $settings = array() ) {
		// Settings
		$settings['invoice_template_selection']  = array(
			'title'       => __( 'Invoice Template', 'sprout-invoices' ),
			'description' => self::theme_selection_desc(),
			'weight'      => 100,
			'tab'         => 'invoices',
			'settings'    => array(
				self::INV_THEME_OPION => array(
					'label'  => __( 'Invoice Theme', 'sprout-invoices' ),
					'option' => array(
						'type'        => 'select',
						'options'     => self::theme_templates(),
						'default'     => self::$inv_theme_option,
						'description' => __( 'Select the theme your invoices should use.', 'sprout-invoices' ),
					),
				),
				'customizer'          => array(
					'label'  => __( 'Logo & Color Styling', 'sprout-invoices' ),
					'option' => array(
						'type'   => 'bypass',
						'output' => self::how_to_style_invoice(),
					),
				),
			),
		);
		$settings['estimate_template_selection'] = array(
			'title'       => __( 'Estimate Template', 'sprout-invoices' ),
			'description' => self::theme_selection_desc(),
			'weight'      => 100,
			'tab'         => 'estimates',
			'settings'    => array(
				self::EST_THEME_OPION => array(
					'label'  => __( 'Estimate Theme', 'sprout-invoices' ),
					'option' => array(
						'type'        => 'select',
						'options'     => self::theme_templates(),
						'default'     => self::$est_theme_option,
						'description' => __( 'Select the theme your estimate should use.', 'sprout-invoices' ),
					),
				),
				'customizer'          => array(
					'label'  => __( 'Logo & Color Styling', 'sprout-invoices' ),
					'option' => array(
						'type'   => 'bypass',
						'output' => self::how_to_style_estimate(),
					),
				),
			),
		);
		return $settings;
	}

	public static function theme_selection_desc() {
		$desc  = '<div class="si_theme_previews">';
		$desc .= sprintf( '<div class="basic_theme"><img src="%s"/>%s</div>', SI_RESOURCES . 'admin/img/basic.png', __( 'Basic Theme', 'sprout-invoices' ) );
		$desc .= sprintf( '<div class="default_theme"><img src="%s"/>%s</div>', SI_RESOURCES . 'admin/img/default.png', __( 'Default Theme', 'sprout-invoices' ) );

		$desc .= sprintf( '<div class="original_theme"><img src="%s"/>%s</div>', SI_RESOURCES . 'admin/img/original.png', __( 'Original Theme', 'sprout-invoices' ) );

		$desc .= sprintf( '<div class="slate_theme"><img src="%s"/>%s</div>', SI_RESOURCES . 'admin/img/slate.png', __( 'Slate Theme', 'sprout-invoices' ) );
		$desc .= '</div>';
		return $desc;
	}

	/**
	 * Add styling instructions for Invoice to the relevant settings tab.
	 *
	 * @return string html of how to style.
	 */
	public static function how_to_style_invoice() {
		$post_args = array(
			'post_type'      => SI_Invoice::POST_TYPE,
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
		);
		$invoices  = get_posts( $post_args );
		$args      = array(
			'invoice_id'       => ( empty( $invoices ) ) ? false : $invoices[0],
			'invoice_template' => true,
		);
		return self::load_view_to_string( 'admin/options/how-to-style', $args );
	}

	/**
	 * Add styling instructions for Estimate to the relevant settings tab.
	 *
	 * @return string html of how to style.
	 */
	public static function how_to_style_estimate() {
		$post_args = array(
			'post_type'      => SI_Estimate::POST_TYPE,
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
		);
		$estimates = get_posts( $post_args );
		$args      = array(
			'estimate_id'       => ( empty( $estimates ) ) ? false : $estimates[0],
			'estimate_template' => true,
		);
		return self::load_view_to_string( 'admin/options/how-to-style', $args );
	}

	/////////////////
	// Meta boxes //
	/////////////////

	public static function doc_template_selection( $doc ) {
		if ( is_a( $doc, 'SI_Invoice' ) ) {
			$template_options = self::get_invoice_templates();
		} elseif ( is_a( $doc, 'SI_Estimate' ) ) {
			$template_options = self::get_estimate_templates();
		}
		if ( ! isset( $template_options ) || empty( $template_options ) ) {
			return;
		}
		$doc_type_name = ( is_a( $doc, 'SI_Invoice' ) ) ? __( 'invoice', 'sprout-invoices' ) : __( 'estimate', 'sprout-invoices' );
		$template = self::get_doc_current_template( $doc->get_id() ); ?>
		<div class="misc-pub-section" data-edit-id="template" data-edit-type="select">
			<span id="template" class="wp-media-buttons-icon"><b><?php echo esc_html( $template_options[ $template ] ); ?></b>
			<span title="<?php printf( esc_html__( 'Select a custom %s template.', 'sprout-invoices' ), esc_html( $doc_type_name ) ); ?>" class="helptip"></span></span>

				<a href="#edit_template" class="edit-template hide-if-no-js edit_control" >
					<span aria-hidden="true"><?php esc_html_e( 'Edit', 'sprout-invoices' ); ?></span> <span class="screen-reader-text"><?php esc_html_e( 'Select different template', 'sprout-invoices' ); ?></span>
				</a>

				<div id="template_div" class="control_wrap hide-if-js">
					<div class="template-wrap">
						<?php if ( count( $template_options ) > 1 ) : ?>
							<select name="doc_template">
								<?php foreach ( $template_options as $template_key => $template_name ) : ?>
									<?php
									printf(
										'<option value="%s" %s>%s</option>',
										esc_attr( $template_key ),
										esc_attr( selected( $template_key, $template, false ) ),
										esc_html( $template_name )
									);
									?>
								<?php endforeach ?>
							</select>
						<?php else : ?>
							<span>
								<?php
								printf(
									esc_html__( 'No %1$sCustom Templates%2$s Found', 'sprout-invoices' ),
									'<a href="https://sproutinvoices.com/support/knowledgebase/sprout-invoices/customizing-templates/" target="_blank">',
									'</a>'
								);
								?>
								</span>
						<?php endif ?>
			 		</div>
					<p>
						<a href="#edit_template" class="save_control save-template hide-if-no-js button"><?php esc_html_e( 'OK', 'sprout-invoices' ) ?></a>
						<a href="#edit_template" class="cancel_control cancel-template hide-if-no-js button-cancel"><?php esc_html_e( 'Cancel', 'sprout-invoices' ) ?></a>
					</p>
			 	</div>
		</div>
		<?php
	}

	/**
	 * Add additional options in the advanced client meta box.
	 * @param  array  $adv_fields
	 * @return
	 */
	public static function client_option( $adv_fields = array() ) {
		$adv_fields['inv_template_options'] = array(
			'weight' => 200,
			'label' => __( 'Invoice Template', 'sprout-invoices' ),
			'type' => 'bypass',
			'output' => self::client_template_options( 'invoice', get_the_ID() ),
			'description' => __( 'This invoice template will override the default invoice template, unless another template is selected when creating/editing an invoice.', 'sprout-invoices' ),
		);
		$adv_fields['est_template_options'] = array(
			'weight' => 210,
			'label' => __( 'Estimate Template', 'sprout-invoices' ),
			'type' => 'bypass',
			'output' => self::client_template_options( 'estimate', get_the_ID() ),
			'description' => __( 'This estimate template will override the default estimate template, unless another template is selected when creating/editing an estimate.', 'sprout-invoices' ),
		);
		return $adv_fields;
	}

	/**
	 * Save the template selection for a doc by post id
	 * @param  integer $post_id
	 * @param  string  $doc_template
	 * @return
	 */
	public static function save_doc_template_selection( $post_id = 0 ) {
		$doc_template = ( isset( $_POST['doc_template'] ) ) ? sanitize_text_field( wp_unslash( $_POST['doc_template'] ) ) : '' ;
		self::save_doc_current_template( $post_id, $doc_template );
	}

	/**
	 * Save client options on advanced meta box save action
	 * @param  integer $post_id
	 * @return
	 */
	public static function save_client_options( $post_id = 0 ) {
		$doc_template_invoice = ( isset( $_POST['doc_template_invoice'] ) ) ? sanitize_text_field( wp_unslash( $_POST['doc_template_invoice'] ) ) : '';
		self::save_client_invoice_template( $post_id, $doc_template_invoice );

		$doc_template_estimate = ( isset( $_POST['doc_template_estimate'] ) ) ? sanitize_text_field( wp_unslash( $_POST['doc_template_estimate'] ) ) : '';
		self::save_client_estimate_template( $post_id, $doc_template_estimate );
	}

	//////////////
	// Utility //
	//////////////

	/**
	 * Template selection for advanced client options
	 * @param  string  $type      invoice/estimate
	 * @param  integer $client_id
	 * @return
	 */
	public static function client_template_options( $type = 'invoice', $client_id = 0 ) {
		ob_start();
		$template_options = ( $type != 'estimate' ) ? self::get_invoice_templates() : self::get_estimate_templates();
		$doc_type_name = ( $type != 'estimate' ) ? __( 'invoice', 'sprout-invoices' ) : __( 'estimate', 'sprout-invoices' );
		$template = ( $type != 'estimate' ) ? self::get_client_invoice_template( $client_id ) : self::get_client_estimate_template( $client_id ); ?>
		<div class="misc-pub-section" data-edit-id="template" data-edit-type="select">
			<span id="template" class="wp-media-buttons-icon"><b><?php echo esc_html( $template_options[ $template ] ); ?></b> <span title="
			<?php printf( esc_html__( 'Select a custom %s template.', 'sprout-invoices' ), esc_html( $doc_type_name ) ); ?>" class="helptip"></span></span>

			<a href="#edit_template" class="edit-template hide-if-no-js edit_control" >
				<span aria-hidden="true"><?php esc_html_e( 'Edit', 'sprout-invoices' ) ?></span> <span class="screen-reader-text"><?php esc_html_e( 'Select different template', 'sprout-invoices' ) ?></span>
			</a>

			<div id="template_div" class="control_wrap hide-if-js">
				<div class="template-wrap">
					<?php if ( count( $template_options ) > 1 ) : ?>
						<select name="doc_template_<?php echo esc_attr( $doc_type_name ); ?>">
							<?php foreach ( $template_options as $template_key => $template_name ) : ?>
								<?php printf( '<option value="%s" %s>%s</option>', esc_attr( $template_key ), esc_attr( selected( $template_key, $template, false ) ), esc_html( $template_name ) ) ?>
							<?php endforeach ?>
						</select>
					<?php else : ?>
						<span>
							<?php
							printf(
								esc_html__( 'No %1$sCustom Templates%2$s Found', 'sprout-invoices' ),
								'<a href="https://sproutinvoices.com/support/knowledgebase/sprout-invoices/customizing-templates/" target="_blank">',
								'</a>'
							);
							?>
							</span>
					<?php endif ?>
		 		</div>
				<p>
					<a href="#edit_template" class="save_control save-template hide-if-no-js button"><?php esc_html_e( 'OK', 'sprout-invoices' ) ?></a>
					<a href="#edit_template" class="cancel_control cancel-template hide-if-no-js button-cancel"><?php esc_html_e( 'Cancel', 'sprout-invoices' ) ?></a>
				</p>
		 	</div>
		</div>
		<?php
		$view = ob_get_clean();
		return $view;
	}

	/**
	 * Search for files in the templates, within the sa directory.
	 * @return array
	 */
	public static function get_sa_files( $type = '' ) {
		if ( $type != '' ) {
			$type = '/'.$type;
		}
		$theme = wp_get_theme();
		$files = (array) self::scandir( $theme->get_stylesheet_directory().'/'.self::get_template_path().$type, 'php', 1 );

		if ( $theme->parent() ) {
			$files += (array) self::scandir( $theme->get_template_directory().'/'.self::get_template_path().$type, 'php', 1 );
		}

		return array_filter( $files );
	}


	/**
	 * Returns the theme's doc templates.
	 *
	 * @since 3.4.0
	 * @access public
	 *
	 * @param WP_Post|null $post Optional. The post being edited, provided for context.
	 * @return array Array of page templates, keyed by filename, with the value of the translated header name.
	 */
	public static function get_doc_templates( $type = null ) {

		$doc_templates = false;

		if ( ! is_array( $doc_templates ) ) {
			$doc_templates = array();

			$files = (array) self::get_sa_files( $type );

			foreach ( $files as $file => $full_path ) {
				if ( ! preg_match( '|SA Template Name:(.*)$|mi', file_get_contents( $full_path ), $header ) ) {
					continue; }
				$doc_templates[ $file ] = _cleanup_header_comment( $header[1] );
			}

			// add cache
		}

		$return = apply_filters( 'theme_doc_templates', $doc_templates, $type );

		return array_intersect_assoc( $return, $doc_templates );
	}

	/**
	 * Scans a directory for files of a certain extension.
	 *
	 * Copied from WP_Theme
	 * @since 3.4.0
	 * @access private
	 *
	 * @param string $path Absolute path to search.
	 * @param mixed  Array of extensions to find, string of a single extension, or null for all extensions.
	 * @param int $depth How deep to search for files. Optional, defaults to a flat scan (0 depth). -1 depth is infinite.
	 * @param string $relative_path The basename of the absolute path. Used to control the returned path
	 * 	for the found files, particularly when this function recurses to lower depths.
	 */
	private static function scandir( $path, $extensions = null, $depth = 0, $relative_path = '' ) {
		if ( ! is_dir( $path ) ) {
			return false; }

		$_extensions = '';
		if ( $extensions ) {
			$extensions = (array) $extensions;
			$_extensions = implode( '|', $extensions );
		}

		$relative_path = trailingslashit( $relative_path );
		if ( '/' == $relative_path ) {
			$relative_path = ''; }

		$results = scandir( $path );
		$files = array();

		foreach ( $results as $result ) {
			if ( '.' == $result[0] ) {
				continue;
			}
			if ( is_dir( $path . '/' . $result ) ) {
				if ( ! $depth || 'CVS' == $result ) {
					continue;
				}
				$found = self::scandir( $path . '/' . $result, $extensions, $depth - 1 , $relative_path . $result );
				$files = array_merge_recursive( $files, $found );
			} elseif ( ! $extensions || preg_match( '~\.(' . $_extensions . ')$~', $result ) ) {
				$files[ $relative_path . $result ] = $path . '/' . $result;
			}
		}

		return $files;
	}

	public static function add_info_to_footer() {
		printf( '<!-- Sprout Invoices v%s -->', esc_html( self::SI_VERSION ) );
	}



	/////////////////
	// Shortcodes //
	/////////////////

	public static function blank_shortcode( $atts = array() ) {
		return '';
	}
}

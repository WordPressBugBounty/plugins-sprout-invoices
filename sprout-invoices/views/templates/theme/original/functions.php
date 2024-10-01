<?php

/**
 * Update Original Theme CSS with customizer options.
 *
 * @since 15.1.0
 *
 * @return void
 */
function si_original_theme_inject_css() {
	$inv_color              = SI_Customizer::sanitize_hex_color( get_theme_mod( 'si_invoices_color' ) );
	$est_color              = SI_Customizer::sanitize_hex_color( get_theme_mod( 'si_estimates_color' ) );
	$processor_button_color = SI_Customizer::sanitize_hex_color( get_theme_mod( 'si_payment_button' ) );
	?>
		<!-- Debut customizer CSS -->
		<style>
		#doc .doc_total,
		.button.primary_button {
			background-color: <?php echo esc_attr( $est_color ); ?>;
		}

		#invoice #doc .doc_total,
		#invoice .button.primary_button {
			background-color: <?php echo esc_attr( $inv_color ); ?>;
		}

		#invoice.paid #doc .doc_total,
		#invoice .button.deposit_paid {
			background-color: <?php echo esc_attr( $est_color ); ?>;
		}

		#line_total {
			color: <?php echo esc_attr( $est_color ); ?>;
		}

		#invoice #line_total {
			color: <?php echo esc_attr( $inv_color ); ?>;
		}
		<?php if ( $processor_button_color ) : ?>
			li.payment_option a.payment_option.toggle,
			li.payment_option a.payment_option {
				background: <?php echo esc_attr( $processor_button_color ); ?>;
				color: <?php echo esc_attr( $processor_text_color ); ?>;
			}

			.payment_option .process_label .si_payment_button {
				background: <?php echo esc_attr( $processor_button_color ); ?>;
				color: <?php echo esc_attr( $processor_text_color ); ?>;
			}

			.panel .inner .toggle {
				background: <?php echo esc_attr( $processor_button_color ); ?>;
			}

			#payment_selection.dropdown ul.si-dropdown-menu li a:hover {
				background: <?php echo esc_attr( $processor_button_color ); ?>;
				opacity: .8;
			}
		<?php endif ?>
		</style>
	<?php
}
add_action( 'si_head', 'si_original_theme_inject_css' );

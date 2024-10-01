<div class="dashboard_widget inside">

	<div id="invoice_payments" class="chart_filter">
		<span class="spinner si_inline_spinner"></span>
		<input type="text" name="invoice_payments_chart_segment_span" value="6" id="invoice_payments_chart_segment_span" class="small-input"/>
		<select id="invoice_payments_chart_segment_select" name="invoice_payments_chart_segment" class="chart_segment_select">
			<option value="weeks"><?php esc_html_e( 'Weeks', 'sprout-invoices' ) ?></option>
			<option value="months"><?php esc_html_e( 'Months', 'sprout-invoices' ) ?></option>
		</select>
		<button id="invoice_payments_chart_filter" class="button" disabled="disabled"><?php esc_html_e( 'Show', 'sprout-invoices' ) ?></button>
	</div>

	<div class="main">
		<canvas id="invoice_payments_chart" min-height="300" max-height="500"></canvas>
		<script type="text/javascript" charset="utf-8">
			var inv_data                = {};
			var invoice_payments_chart  = null;
			var invoice_payments_button = jQuery('#invoice_payments_chart_filter');

			/**
			 * Load the invoice payments chart.
			 *
			 * @return void
			 */
			function load_invoice_payments_chart() {
				var can = jQuery('#invoice_payments_chart');
				var ctx = can.get(0).getContext("2d");
				// destroy current chart
				if ( invoice_payments_chart !== null ) {
					invoice_payments_chart.destroy();
				};
				invoice_payments_chart = new Chart(ctx).Line( inv_data );
			}

			/**
			 * Load the invoice payments chart data.
			 *
			 * @return void
			 */
			var inv_chart_data = function () {
				var segment = jQuery('#invoice_payments_chart_segment_select').val(),
					span    = jQuery('#invoice_payments_chart_segment_span').val();

				invoice_payments_button.prop('disabled', 'disabled');
				jQuery('#invoice_payments .spinner').css('visibility','visible');

				/**
				 * Ajax request to get the invoice payments chart.
				 * Ajax function: SI_Reporting::get_chart_data().
				 */
				jQuery.post( ajaxurl, {
					action: '<?php echo esc_attr( SI_Reporting::AJAX_ACTION ) ?>',
					data: 'invoice_payments',
					segment: segment,
					refresh_cache: si_js_object.reports_refresh_cache,
					span: span,
					security: '<?php echo esc_attr( wp_create_nonce( SI_Reporting::AJAX_NONCE ) ) ?>'
					},
					function( response ) {
						if ( response.error ) {
							invoice_payments_button.after('<span class="inline_error_message">' + response.response + '</span>');
							return;
						};
						inv_data = {
							labels: response.data.labels,
							datasets: [
								{
									label: "<?php esc_html_e( 'Invoiced', 'sprout-invoices' ) ?>",
									fillColor: "rgba(134,189,72,0.2)",
									strokeColor: "rgba(134,189,72,1)",
									pointColor: "rgba(134,189,72,1)",
									pointStrokeColor: "#fff",
									pointHighlightFill: "#fff",
									pointHighlightStroke: "rgba(134,189,72)",
									data: response.data.invoices
								},
								{
									label: "<?php esc_html_e( 'Payments', 'sprout-invoices' ) ?>",
									fillColor: "rgba(38,41,44,0.2)",
									strokeColor: "rgba(38,41,44,1)",
									pointColor: "rgba(38,41,44,1)",
									pointStrokeColor: "#fff",
									pointHighlightFill: "#fff",
									pointHighlightStroke: "rgba(38,41,44,1)",
									data: response.data.payments
								}
							]
						}
						load_invoice_payments_chart();
						// enable select
						invoice_payments_button.prop('disabled', false);
						jQuery('#invoice_payments .spinner').css('visibility','hidden');
					}
				);
			};

			/**
			 * When the page loads run the functions to load invoice payment chart data.
			 */
			jQuery(document).ready(function($) {
				// load chart from the start
				inv_chart_data();
				// change data if select changes
				invoice_payments_button.on( 'click', function( e ) {
					// load chart
					inv_chart_data();
				} );
			});
		</script>
		<p class="description"><?php esc_html_e( 'Compares total invoiced and the total payments.', 'sprout-invoices' ) ?></p>
	</div>
</div>

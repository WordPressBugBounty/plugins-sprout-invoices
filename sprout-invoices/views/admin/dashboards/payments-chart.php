<div class="dashboard_widget inside">

	<div id="payments" class="chart_filter">
		<span class="spinner si_inline_spinner"></span>
		<input type="text" name="payments_chart_segment_span" value="6" id="payments_chart_segment_span" class="small-input"/>
		<select id="payments_chart_segment_select" name="payments_chart_segment" class="chart_segment_select">
			<option value="weeks"><?php esc_html_e( 'Weeks', 'sprout-invoices' ) ?></option>
			<option value="months"><?php esc_html_e( 'Months', 'sprout-invoices' ) ?></option>
		</select>
		<button id="payments_chart_filter" class="button" disabled="disabled"><?php esc_html_e( 'Show', 'sprout-invoices' ) ?></button>
	</div>

	<div class="main">
		<canvas id="payments_chart" min-height="300" max-height="500"></canvas>
		<script type="text/javascript" charset="utf-8">
			var payments_data   = {};
			var payments_chart  = null;
			var payments_button = jQuery('#payments_chart_filter');

			/**
			 * Load the payments chart.
			 *
			 * @return void
			 */
			function load_payments_chart() {
				var can = jQuery('#payments_chart');
				var ctx = can.get(0).getContext("2d");
				// destroy current chart
				if ( payments_chart !== null ) {
					payments_chart.destroy();
				};
				payments_chart = new Chart(ctx).Line( payments_data );
			}

			/**
			 * Load the payments chart data.
			 *
			 * @return void
			 */
			var payments_chart_data = function () {
				var segment = jQuery('#payments_chart_segment_select').val(),
					span    = jQuery('#payments_chart_segment_span').val();

				payments_button.prop('disabled', 'disabled');
				jQuery('#payments .spinner').css('visibility','visible');

				/**
				 * Ajax request to get the payments chart.
				 * Ajax function: SI_Reporting::get_chart_data().
				 */
				jQuery.post( ajaxurl, {
					action: '<?php echo esc_attr( SI_Reporting::AJAX_ACTION )?>',
					data: 'payments',
					segment: segment,
					refresh_cache: si_js_object.reports_refresh_cache,
					span: span,
					security: '<?php echo esc_attr( wp_create_nonce( SI_Reporting::AJAX_NONCE ) ) ?>'
					},
					function( response ) {
						if ( response.error ) {
							payments_button.after('<span class="inline_error_message">' + response.response + '</span>');
							return;
						};
						payments_data = {
							labels: response.data.labels,
							datasets: [
								{
									label: "<?php esc_html_e( 'Totals', 'sprout-invoices' ) ?>",
									fillColor: "rgba(134,189,72,0.2)",
									strokeColor: "rgba(134,189,72,1)",
									pointColor: "rgba(134,189,72,1)",
									pointStrokeColor: "#fff",
									pointHighlightFill: "#fff",
									pointHighlightStroke: "rgba(134,189,72)",
									data: response.data.totals
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
						load_payments_chart();
						// enable select
						payments_button.prop('disabled', false);
						jQuery('#payments .spinner').css('visibility','hidden');
					}
				);
			};

			/**
			 * When the page loads run the functions to load payment chart data.
			 */
			jQuery(document).ready(function($) {
				// load chart from the start
				payments_chart_data();
				// change data if select changes
				payments_button.on( 'click', function( e ) {
					// load chart
					payments_chart_data();
				} );
			});
		</script>
		<p class="description"><?php esc_html_e( 'Compares payment totals and the total payments.', 'sprout-invoices' ) ?></p>
	</div>
</div>

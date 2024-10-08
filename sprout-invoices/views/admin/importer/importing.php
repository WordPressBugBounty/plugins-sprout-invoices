<div id="addons_admin">

	<main id="main" class="container site-main" role="main">

		<script type="text/javascript">
			jQuery(function($) {

				// Authenticate and start the process
				start_import_ajax_method('authentication');

				function start_import_ajax_method ( $method ) {
					var $importer = '<?php if ( isset($_POST['importer'] ) ) echo esc_js( sanitize_text_field( wp_unslash( $_POST['importer'] ) ) ) ?>',
						$nonce = si_js_object.security;
					$.post( ajaxurl, { action: 'si_import', importer: $importer, method: $method, security: $nonce },
						function( data ) {
							if ( data.error ) {
								$('#import_error p').html( data.message );
								$('#import_error').removeClass('cloak');
								$('#authentication_import_progress').css( { width: '100%' } ).addClass( 'progress-bar-danger' );
								$('#authentication_import_progress').attr( 'aria-valuenow', 100 );
							}
							else {
								$.each( data, function( method, response ) {
									// update the informational rows
									$('#'+method+'_import_progress').css( { width: response.progress+'%' } );
									$('#'+method+'_import_progress').attr( 'aria-valuenow', response.progress );
									if ( response.progress >= 100 ) {
										$('#'+method+'_import_progress').removeClass( 'active progress-bar-striped' ).addClass( 'progress-bar-success' );
									};
									if ( response.message ) {
										$('#'+method+'_import_information').text( response.message );
									};
									// continue the process until complete
									if ( response.next_step ) {
										if ( response.next_step === 'complete' ) {
											$('#authentication_import_information').text( si_js_object.done_string );
											$('#complete_import').removeClass('cloak');
											return true;
										};
										start_import_ajax_method( response.next_step );
									};
								});

							};
							return true;
						}
					);
				}

			});

		</script>

		<div id="import_error" class="error cloak">
			<p></p>
		</div>
		<div id="si_importer" class="wrap about-wrap">

			<p>
				<div id="authentication_import_information"><?php esc_html_e( 'Attempting to validate credentials...', 'sprout-invoices' ) ?></div>
				<div class="progress">
					<div id="authentication_import_progress" class="progress-bar progress-bar-striped active" style="overflow:hidden;"   role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">&nbsp;</div>
				</div>
			</p>

			<p>
				<div id="clients_import_information"><?php esc_html_e( 'No clients imported yet...', 'sprout-invoices' ) ?></div>
				<div class="progress">
					<div id="clients_import_progress" class="progress-bar progress-bar-striped active" style="overflow:hidden;" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">&nbsp;</div>
				</div>
			</p>

			<p>
				<div id="contacts_import_information"><?php esc_html_e( 'No contacts imported yet...', 'sprout-invoices' ) ?></div>
				<div class="progress">
					<div id="contacts_import_progress" class="progress-bar progress-bar-striped active" style="overflow:hidden;" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">&nbsp;</div>
				</div>
			</p>

			<p>
				<div id="estimates_import_information"><?php esc_html_e( 'No estimates imported yet...', 'sprout-invoices' ) ?></div>
				<div class="progress">
					<div id="estimates_import_progress" class="progress-bar progress-bar-striped active" style="overflow:hidden;" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">&nbsp;</div>
				</div>
			</p>

			<p>
				<div id="invoices_import_information"><?php esc_html_e( 'No invoices imported yet...', 'sprout-invoices' ) ?></div>
				<div class="progress">
					<div id="invoices_import_progress" class="progress-bar progress-bar-striped active" style="overflow:hidden;" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">&nbsp;</div>
				</div>
			</p>

			<p>
				<div id="payments_import_information"><?php esc_html_e( 'No payments imported yet...', 'sprout-invoices' ) ?></div>
				<div class="progress">
					<div id="payments_import_progress" class="progress-bar progress-bar-striped active" style="overflow:hidden;" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">&nbsp;</div>
				</div>
			</p>

			<div id="complete_import" class="cloak"><?php printf( '<a href="%s">All Done!</a>', esc_url( admin_url( 'admin.php?page=sprout-invoices-import' ) ) ) ?></div>

			<?php do_action( 'si_import_progress' ) ?>

		</div>

	</main>
</div>

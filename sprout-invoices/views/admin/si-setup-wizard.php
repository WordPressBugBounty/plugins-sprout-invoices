<div id="si-page-container" class="si-page-container">
	<div id="si-page-top">
		<?php do_action( 'sprout_settings_header' ); ?>
	</div>
	<div class="wp-header-end"></div>
	<div id="si-page-content">
		<div id="si_settings">
			<div id="si_settings" class="si-form si-form-aligned">
				<div id="si-setup-header">
					<?php do_action( 'si_wizard_header' ); ?>
				</div>
				<div id="si-setup-content">
					<div id="col-container" class="wp-clearfix">
						<div>
							<?php
								if ( ! empty( $current_tab ) && ! empty( $current_section ) ) {
									$args = array(
										'current_tab'     => $current_tab,
										'current_section' => $current_section,
									);
									do_action( 'si_wizard_content_' . $current_tab . '_' . $current_section, $args );
								} else {
									do_action( 'si_wizard_content' );
								}
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>


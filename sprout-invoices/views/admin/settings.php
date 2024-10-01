<div class="wrap">
	<div id="si-page-container" class="si-page-container">
		<div id="si-page-top">
			<?php do_action( 'sprout_settings_header' ); ?>
		</div>
		<div class="wp-header-end"></div>
		<div id="si-page-content">
			<h2 class="nav-tab-wrapper">
				<?php
					$ifactive = ( isset( $_GET['page'] ) && 'sprout-invoices' === $_GET['page'] ) ? 'nav-tab-active' : '';
				?>
				<a class="nav-tab <?php echo esc_attr( $ifactive ); ?>" href="<?php echo esc_attr( admin_url( 'admin.php?page=sprout-invoices' ) ); ?>"><?php esc_html_e( 'Getting Started', 'sprout-invoices' ); ?></a>
				<?php foreach ( $sub_pages as $slug => $subpage ) : ?>
					<?php
						$ifactive = ( isset( $_GET['page'] ) && 'sprout-invoices-' . $slug === $_GET['page'] ) ? 'nav-tab-active' : '';
					?>
					<a class="nav-tab <?php echo esc_attr( $ifactive ); ?>" href="<?php echo esc_attr( admin_url( 'admin.php?page=sprout-invoices-' . $slug ) ); ?>"><?php esc_html_e( $subpage['menu_title'] ); ?></a>
				<?php endforeach ?>
			</h2>
			<div id="si-settings-admin" class="si_settings">
				<div id="si_settings">
					<?php do_action( 'sprout_settings_messages' ); ?>
					<div id="general_settings_admin">
						<div id="col-container" class="wp-clearfix">
							<div id="col-left">
								<div id="sticky-wrapper" class="sticky-wrapper">
									<ul class="si-left-nav">
										<?php foreach ( $tabs as $tab => $label ) : ?>
											<a href='#<?php echo esc_attr( $tab ); ?>' v-on:click="makeTabActive('<?php echo esc_attr( $tab ); ?>')" class="si-tab-item si_tab_<?php echo esc_attr( $tab ); ?>" v-bind:class="{ active : isActiveTab('<?php echo esc_attr( $tab ); ?>') == true }"> <?php echo esc_html( $label ); ?></a>
										<?php endforeach ?>
									</ul>
								</div>
							</div>
							<div id="col-right" class="si_settings_tabs">
								<?php foreach ( $tabs as $tab => $label ) : ?>
									<div id="<?php echo esc_attr( $tab ); ?>" class="row" v-show="isActiveTab('<?php echo esc_attr( $tab ); ?>')">
										<div class="si-box">
											<?php uasort( $allsettings, array( 'SI_Controller', 'sort_by_weight' ) ); ?>
											<?php foreach ( $allsettings as $key => $section_settings ) : ?>
												<?php
													// all settings for this tab
													if ( $tab !== $section_settings['tab'] ) :
														continue;
														endif;
												?>
												<div id="section_<?php echo esc_attr( $key ); ?>">
													<div class="si-box-top">
														<?php if ( isset( $section_settings['title'] ) && '' !== $section_settings['title'] ) : ?>
															<?php echo esc_html( $section_settings['title'] ); ?>
														<?php endif ?>
													</div>
													<?php if ( 'si_estimate_settings' === $key || 'si_invoice_settings' === $key ) : ?>
													<div class="si-box-bottom invoice-estimate-settings">
														<?php if ( isset( $section_settings['description'] ) && '' !== $section_settings['description'] ) : ?>
															<?php
																$wp_kses_section_setting = array(
																	'div'    => array(
																		'class' => array(),
																	),
																	'img'    => array(
																		'src' => array(),
																	),
																	'button' => array(),
																	'a'      => array(
																		'href' => array(),
																	),
																)
															?>
															<p>
															<?php echo wp_kses( $section_settings['description'], $wp_kses_section_setting ); ?>
															</p>

														<?php endif ?>

														<?php if ( ! empty( $section_settings['settings'] ) ) : ?>

															<?php do_action( 'si_display_settings', $section_settings['settings'], true ); ?>

														<?php endif ?>
													</div>
													<?php else : ?>
													<div class="si-box-bottom">
														<?php if ( isset( $section_settings['description'] ) && '' !== $section_settings['description'] ) : ?>
															<?php
																$wp_kses_section_setting = array(
																	'div'    => array(
																		'class' => array(),
																	),
																	'img'    => array(
																		'src' => array(),
																	),
																	'button' => array(),
																	'a'      => array(
																		'href' => array(),
																	),
																)
															?>
															<p>
															<?php echo wp_kses( $section_settings['description'], $wp_kses_section_setting ); ?>
															</p>

														<?php endif ?>

														<?php if ( ! empty( $section_settings['settings'] ) ) : ?>

															<?php do_action( 'si_display_settings', $section_settings['settings'], true ); ?>

														<?php endif ?>
													</div>
													<?php endif; ?>
												</div><!-- #section_php -->
											<?php endforeach ?>
										</div>
									</div>
								<?php endforeach ?>
								<div class="si-controls">
									<button
										@click='saveOptions'
										:disabled='isSaving'
										id='si-submit-settings' class="button button-primary"><?php esc_html_e( 'Save Changes', 'sprout-invoices' ); ?></button>
									<img
										v-if='isSaving == true'
										id='loading-indicator' src='<?php get_site_url(); ?>/wp-admin/images/wpspin_light-2x.gif' alt='Loading indicator' />
								</div>
								<p v-if='message'>{{ message }}</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

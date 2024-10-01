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
			<div id="si_payment_processors_admin" class="si_settings">
				<div id="si_settings">
					<?php do_action( 'sprout_settings_messages' ); ?>
					<div id="payment_processors_admin">
						<div id="col-container" class="wp-clearfix">
							<div id="col-left">
								<div id="sticky-wrapper" class="sticky-wrapper">
									<ul class="si-left-nav">
										<a class="si-tab-item" href='#start' v-on:click="makeTabActive('start')" v-bind:class="{ active : isActiveTab('start') == true }"><?php esc_html_e( 'General Payment Settings', 'sprout-invoices' ); ?></a>
										<a class="si-tab-item" href='#section-payment-processors' v-on:click="makeTabActive('payment-processors')" v-bind:class="{ active : isActiveTab('payment-processors') == true }"><?php esc_html_e( 'Payment Processors', 'sprout-invoices' ); ?></a>
									</ul>
								</div>
							</div>
							<div id="col-right" class="si_settings_tabs">
								<div id="start" class="row" v-show="isActiveTab('start')">
									<?php foreach ( $settings as $key => $section_settings ) : ?>
										<div class="si-box">
											<div id="section_<?php echo esc_attr( $key ); ?>">
												<div class="si-box-top">
													<?php if ( isset( $section_settings['title'] ) && '' !== $section_settings['title'] ) : ?>
														<?php echo esc_html( $section_settings['title'] ); ?>
													<?php endif ?>
												</div>
												<div class="si-box-bottom">
													<?php if ( isset( $section_settings['description'] ) && '' !== $section_settings['description'] ) : ?>
														<p><?php echo wp_kses( $section_settings['description'], SI_Settings_API::get_allowed_html() ); ?></p>
													<?php endif ?>
													<?php if ( ! empty( $section_settings['settings'] ) ) : ?>
														<?php do_action( 'si_display_settings', $section_settings['settings'], true ); ?>
													<?php endif ?>
												</div>
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
											id='loading-indicator' src='<?php echo esc_url( get_site_url() ); ?>/wp-admin/images/wpspin_light-2x.gif' alt='Loading indicator' />
									</div>
									<p class="si_setting_message" v-if='message'>{{ message }}</p>
								</div>
								<?php
									$all_processors = array_merge( $offsite, $credit );
								?>
								<div id="payment-processors" class="row" v-show="isActiveTab('payment-processors')">
									<table class="si-processors widefat form-table" cellspacing="0">
										<thead>
											<tr>
												<th>Processor</th>
												<th>Status</th>
												<th class="settings"></th>
											</tr>
										</thead>
										<tbody>
											<?php foreach ( $all_processors as $class_name => $label ) : ?>
												<tr v-bind:class="{ activated : vm.<?php echo esc_attr( $class_name ); ?> == true }">
													<td v-bind:class="{ activating : isSaving == true }"><?php echo wp_kses( $label, wp_kses_allowed_html( 'post' ) ); ?></td>
													<td class="status">
														<label for="pp-status">
															<span v-if="vm.<?php echo esc_attr( $class_name ); ?> == true"><?php esc_html_e( 'Enabled', 'sprout-invoices' ); ?></span>
															<span v-else><?php esc_html_e( 'Disabled', 'sprout-invoices' ); ?></span>
															<input v-if="Object.values(ccPP).includes('<?php echo esc_attr( $class_name ); ?>')" id="pp-status" type="checkbox" name="<?php echo esc_attr( $class_name ); ?>" v-model.lazy="vm.<?php echo esc_attr( $class_name ); ?>" v-on:change="activateCCPP">
															<input v-else id="pp-status" type="checkbox" name="<?php echo esc_attr( $class_name ); ?>" v-model.lazy="vm.<?php echo esc_attr( $class_name ); ?>" v-on:change="activatePP">
														</label>
													</td>
													<td>
														<?php if ( method_exists( $class_name, 'register_settings' ) ) : ?>
															<div class="settings_<?php echo esc_attr( $label ); ?>">
																<a class="si-settings-link button button-secondary" v-if="vm.<?php echo esc_attr( $class_name ); ?>" v-on:click="makeTabActive('<?php echo esc_attr( $class_name ); ?>')" v-bind:class="{ active : isActiveTab('<?php echo esc_attr( $class_name ); ?>') == true }"><?php esc_html_e( 'Settings', 'sprout-invoices' ); ?></a>
															</div>
														<?php endif ?>
													</td>
												</tr>
											<?php endforeach ?>
										</tbody>
									</table>
								</div>
								<?php foreach ( $all_processors as $class_name => $label ) : ?>
									<div id="<?php echo esc_attr( $class_name ); ?>" class="row" v-show="isActiveTab('<?php echo esc_attr( $class_name ); ?>')">
										<div class="si-box">
											<?php if ( method_exists( $class_name, 'register_settings' ) ) : ?>
												<?php
													$pp_settings = call_user_func( array( $class_name, 'register_settings' ) );
													$processor_settings = reset( $pp_settings );
												?>
												<?php if ( in_array( $class_name, array_keys( $credit ) ) ) : ?>
													<?php include 'cc-settings.php'; ?>
												<?php else : ?>
													<?php include 'settings.php'; ?>
												<?php endif ?>
												<?php unset( $processor_settings ); ?>
											<?php else : ?>
												<?php if ( in_array( $class_name, array_keys( $credit ) ) ) : ?>
													<?php include 'cc-no-settings.php'; ?>
												<?php else : ?>
													<?php include 'no-settings.php'; ?>
												<?php endif ?>
											<?php endif ?>
										</div>
									</div>
								<?php endforeach ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

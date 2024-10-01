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
			<div class="si_settings">
				<div id="si_settings" class="si-form si-form-aligned">
					<?php do_action( 'sprout_settings_messages' ); ?>
					<div id="col-container" class="wp-clearfix">
						<div id="addons_admin">
							<div id="col-left">
								<div id="sticky-wrapper" class="sticky-wrapper">
									<ul class="si-left-nav">
										<a href='#start' v-on:click="makeTabActive('start')"  v-bind:class="{ active : isActiveTab('start') == true }" class="si-tab-item"><?php esc_html_e( 'Manage Bundled Add-ons', 'sprout-invoices' ); ?></a>
										<a href='#marketplace_addons' v-on:click="makeTabActive('marketplace_addons')" v-bind:class="{ active : isActiveTab('marketplace_addons') == true }" class="si-tab-item"><?php esc_html_e( 'Additional Add-ons', 'sprout-invoices' ); ?></a>
										<a href='#addons' v-on:click="makeTabActive('addons')" v-bind:class="{ active : isActiveTab('addons') == true }" class="si-tab-item"><?php esc_html_e( 'Form Integrations', 'sprout-invoices' ); ?></a>
										<a href='#tax_addons' v-on:click="makeTabActive('tax_addons')" v-bind:class="{ active : isActiveTab('tax_addons') == true }" class="si-tab-item"><?php esc_html_e( 'Tax Add-ons', 'sprout-invoices' ); ?></a>
									</ul>
								</div>
							</div>
							<div id="col-right" class="si_settings_tabs">
								<div id="start" class="row" v-show="isActiveTab('start')">
									<div id="section_start">
										<h1><?php esc_html_e( 'Bundled Add-ons', 'sprout-invoices' ); ?></h1>
										<p><?php esc_html_e( 'Here are the add-ons you have available. Individually enable or disable them to make Sprout Invoices work best for you!', 'sprout-invoices' ); ?></p>
										<p><?php esc_html_e( 'To enable or disable an Addon you can click the image of the specified Addon.', 'sprout-invoices' ); ?></p>
										<div class="addons_grid si-card-container">
											<?php $mp_addons_shown = array(); ?>
											<?php $settings_link = array(); ?>
											<?php $settings_button = array(); ?>
											<?php foreach ( $addons as $key => $details ) : ?>
												<?php
													$vm_key  = SI_Settings_API::_sanitize_input_for_vue( $key );
													if ( isset( $details['link'] ) && ! empty( $details['link'] ) ) {
														$settings_link[ $key ] = $details['link'];
													}
													$name    = sprintf( '%1$s["%2$s"]', $option, $key );
													$mpaddon = false;
													if ( isset( $details['ID'] ) && (int) $details['ID'] ) {
														foreach ( $mp_addons as $aokey => $ao ) {
															if ( (int) $details['ID'] === (int) $ao->id ) {
																$mp_addons_shown[] = $aokey;
																$mpaddon = $ao;
															}
														}
													}

													if ( ! $mpaddon ) {
														$url         = ( isset( $details['PluginURI'] ) && $details['PluginURI'] !== '' ) ? esc_url( $details['PluginURI'] ) : false;
														$title       = str_replace( 'Sprout Invoices Add-on - ', '', $details['Name'] );
														$img         = SI_URL . '/bundles/default.png';
														$description = sprintf( '<p>%s</p>', $details['Description'] );
													} else {
														$url         = $mpaddon->url;
														$title       = str_replace( 'Sprout Invoices Add-on - ', '', $details['Name'] );
														//$title       = $mpaddon->post_title;
														$img         = $mpaddon->thumb_url;
														$description = $mpaddon->excerpt;
													}
												?>

												<?php include 'settings-template-addon.php'; ?>

											<?php endforeach ?>
										</div>
									</div>
								</div>

								<div id="marketplace_addons" class="row" v-show="isActiveTab('marketplace_addons')">

									<div>

										<h1><?php esc_html_e( 'More Add-ons', 'sprout-invoices' ); ?></h1>

										<?php if ( apply_filters( 'show_upgrade_messaging', true ) ) : ?>
											<p>
												<?php
													printf(
													// translators: 1: opening a tag, 2: purchase pro link, 3: closing href tag, 4: closing a tag.
													esc_html__( 'Here are some add-ons currently available if you were to %1$s%2$s%3$supgrade%4$s to a pro license.', 'sprout-invoices' ),
													'<a href="',
													esc_url( si_get_purchase_link() ),
													'">',
													'</a>'
													);
												?>
											</p>
										<?php else : ?>
											<p>
												<?php
													printf(
													// translators: 1: opening a tag, 2: purchase pro link, 3: closing href tag, 4: closing a tag.
													esc_html__( 'Here are some add-ons currently available that you you don\'t have bundled, maybe you need to %1$s%2$s%3$supgrade%4$s your license.', 'sprout-invoices' ),
													'<a href="',
													esc_url( si_get_purchase_link() ),
													'">',
													'</a>'
													);
												?>
											</p>
										<?php endif ?>

										<div class="addons_grid si-card-container">

											<?php
												foreach ( $mp_addons as $mp_addon_key => $mp_addon ) :

												if ( $mp_addon->pro_bundled && SA_Addons::is_pro_installed() ) {
													continue;
												}

												if ( $mp_addon->biz_bundled && SA_Addons::is_biz_installed() ) {
													continue;
												}
												if ( $mp_addon->corp_bundled && SA_Addons::is_corp_installed() ) {
													continue;
												}
											?>

												<?php
													$url         = $mp_addon->url;
													$title       = $mp_addon->post_title;
													$img         = $mp_addon->thumb_url;
													$description = $mp_addon->excerpt;
												?>

												<?php include 'settings-template-mp-addon.php'; ?>

											<?php endforeach ?>

										</div>
									</div>
								</div>
								<div id="addons" class="row" v-show="isActiveTab('addons')">
									<?php foreach ( $form_integrations as $form_int => $form_class ) : ?>
										<?php if ( array_key_exists( $form_int, $allsettings ) ) : ?>
											<?php $forms_array = array( $allsettings[ $form_int ] ); ?>
											<div id="<?php echo esc_attr( $form_int ); ?>" class="row">
												<div class="si-box-top">
													<?php if ( isset( $allsettings[ $form_int ]['title'] ) && '' !== $allsettings[ $form_int ]['title'] ) : ?>
														<?php echo esc_html( $allsettings[ $form_int ]['title'] ); ?>
													<?php endif ?>
												</div>
												<div class="si-box-bottom form-int">
													<?php if ( isset( $allsettings[ $form_int ]['description'] ) && '' !== $allsettings[ $form_int ]['description'] ) : ?>
														<p>
														<?php echo wp_kses_post( $allsettings[ $form_int ]['description'] ); ?>
														</p>
													<?php endif ?>
													<?php if ( ! empty( $allsettings[ $form_int ]['settings'] ) ) : ?>
														<?php do_action( 'si_display_settings', $allsettings[ $form_int ]['settings'], true ); ?>
													<?php endif ?>
												</div>
											</div>
										<?php endif ?>
									<?php endforeach ?>
									<?php if ( ! empty( $forms_array ) ) : ?>
									<div class="si-controls">
									<button
										@click='saveOptions'
										:disabled='isSaving'
										id='si-submit-settings' class="button button-primary"><?php esc_html_e( 'Save Changes', 'sprout-invoices' ) ?></button>
										<img
										v-if='isSaving == true'
										id='loading-indicator' src='<?php get_site_url() ?>/wp-admin/images/wpspin_light-2x.gif' alt='Loading indicator' />
									</div>
									<p v-if='message'>{{ message }}</p>
									<?php else : ?>
										<div><?php SI_Admin_Settings::advanced_form_integration_view(); ?></div>
									<?php endif ?>
								</div>
								<div id="addon_settings_tabs_wrap">
									<div id="settings_tab_content">
										<?php foreach ( $addons as $addon ) : ?>
											<div id="<?php echo esc_attr( $addon['settingID'] ); ?>" class="row" v-show="isActiveTab('<?php echo esc_attr( $addon['settingID'] ); ?>')">
												<?php do_action( 'si_settings_tab_content', $addon ); ?>
												<div class="si-controls">
													<button
														@click='saveOptions'
														:disabled='isSaving'
														id='si-submit-settings' class="button button-primary"><?php esc_html_e( 'Save Changes', 'sprout-invoices' ) ?>
													</button>
													<img
														v-if='isSaving == true'
														id='loading-indicator' src='<?php get_site_url() ?>/wp-admin/images/wpspin_light-2x.gif' alt='Loading indicator'
													/>
												</div>
												<p v-if='message'>{{ message }}</p>
											</div>
										<?php endforeach ?>
									</div>
								</div>
								<div id="tax_addons" class="row" v-show="isActiveTab('tax_addons')">
									<?php foreach ( $tax_addons as $tax_addon => $tax_class ) : ?>
										<?php if ( array_key_exists( $tax_addon, $allsettings ) ) : ?>
											<?php $tax_addon_array = array( $allsettings[ $tax_addon ] ); ?>
											<div id="<?php echo esc_attr( $tax_addon ); ?>" class="row">
												<div class="si-box-top">
													<?php if ( isset( $allsettings[ $tax_addon ]['title'] ) && '' !== $allsettings[ $tax_addon ]['title'] ) : ?>
														<?php echo esc_html( $allsettings[ $tax_addon ]['title'] ); ?>
													<?php endif ?>
												</div>
												<div class="si-box-bottom form-int">
													<?php if ( isset( $allsettings[ $tax_addon ]['description'] ) && '' !== $allsettings[ $tax_addon ]['description'] ) : ?>
														<p>
														<?php echo wp_kses_post( $allsettings[ $tax_addon ]['description'] ); ?>
														</p>
													<?php endif ?>
													<?php if ( ! empty( $allsettings[ $tax_addon ]['settings'] ) ) : ?>
														<?php do_action( 'si_display_settings', $allsettings[ $tax_addon ]['settings'], true ); ?>
													<?php endif ?>
												</div>
											</div>
										<?php endif ?>
									<?php endforeach ?>
									<?php if ( ! empty( $tax_addon_array ) ) : ?>
									<div class="si-controls">
									<button
										@click='saveOptions'
										:disabled='isSaving'
										id='si-submit-settings' class="button button-primary"><?php esc_html_e( 'Save Changes', 'sprout-invoices' ) ?></button>
										<img
										v-if='isSaving == true'
										id='loading-indicator' src='<?php get_site_url() ?>/wp-admin/images/wpspin_light-2x.gif' alt='Loading indicator' />
									</div>
									<p v-if='message'>{{ message }}</p>
									<?php else : ?>
										<div>
											<?php
												printf(
													// translators: 1: opening a tag, 2: purchase pro link, 3: closing href tag, 4: closing a tag.
													esc_html__( 'You currently do not have any of the Tax Add-ons enable, if you need to download them click %1$s%2$s%3$shere%4$s.', 'sprout-invoices' ),
													'<a href="',
													esc_url( 'https://sproutinvoices.com/marketplace/category/free/' ),
													'">',
													'</a>'
												);
											?>
										</div>
									<?php endif ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
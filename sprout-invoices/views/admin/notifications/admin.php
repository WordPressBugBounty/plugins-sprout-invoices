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
			<div id="si_notifications_admin" class="si_settings">
				<div id="si_settings">
					<?php do_action( 'sprout_settings_messages' ); ?>
					<div id="importer_admin">
						<div id="col-container" class="wp-clearfix">
							<div id="col-left">
								<div id="sticky-wrapper" class="sticky-wrapper">
									<ul class="si-left-nav">
										<a href='#start' v-on:click="makeTabActive('start')" v-bind:class="{ active : isActiveTab('start') == true }" class="si-tab-item"><span></span> <?php esc_html_e( 'Notification Settings', 'sprout-invoices' ); ?></a>
										<?php $assigned = array(); ?>
										<?php foreach ( $notifications as $notification_key => $data ) : ?>
											<?php
											if ( ! isset( $data['post_id'] ) || ! is_int( $data['post_id'] ) ) {
												continue;
											}
												$notification_id       = $data['post_id'];
												$assigned[]            = $notification_id;
												$name                  = SI_Notifications::$notifications[ $notification_key ]['name'];
												$notification          = SI_Notification::get_instance( $notification_id );
												$status                = ( $notification->get_disabled() ) ? '<span class="si_status_icon dashicons dashicons-no"></span>' : '<span class="si_status_icon dashicons dashicons-yes"></span>';
												$wp_kses_notifications = array( 'span' => array( 'class' => array() ) );
											?>
												<a href='#notification_<?php echo esc_attr( $notification_key ); ?>' v-on:click="makeTabActive('<?php echo esc_attr( $notification_key ); ?>')" v-bind:class="{ active : isActiveTab('<?php echo esc_attr( $notification_key ); ?>') == true }" class="si-tab-item">
													<?php
														printf(
														// translators: 1: dashicon span, 2: notification name, 3: notification status.
														esc_html__( '%1$s %2$s %3$s', 'sprout-invoices' ),
														'<span></span>',
														esc_html( $name ),
														wp_kses( $status, $wp_kses_notifications )
														)
												?>
												</a>
										<?php endforeach ?>
										<?php foreach ( $notification_posts as $notification_post_id ) : ?>
											<?php
											if ( in_array( $notification_post_id, $assigned, true ) ) {
												continue;
											}
												$name                  = esc_html__( 'Archived', 'sprout-invoices' );
												$status                = '<span class="si_status_icon dashicons dashicons-no"></span>';
												$wp_kses_notifications = array( 'span' => array( 'class' => array() ) );
											?>
												<a href='#notification_<?php echo esc_attr( $notification_post_id ); ?>' v-on:click="makeTabActive('<?php echo esc_attr( $notification_post_id ); ?>')" v-bind:class="{ active : isActiveTab('<?php echo esc_attr( $notification_post_id ); ?>') == true }" class="si-tab-item">
													<?php
														printf(
														// translators: 1: dashicon span, 2: notification name, 3: notification status.
														esc_html__( '%1$s %2$s %3$s', 'sprout-invoices' ),
														'<span></span>',
														esc_html( $name ),
														wp_kses( $status, $wp_kses_notifications )
														)
													?>
												</a>
										<?php endforeach ?>
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

													<p><?php echo esc_html( $section_settings['description'] ); ?></p>

												<?php endif ?>
												<?php if ( ! empty( $section_settings['settings'] ) ) : ?>

													<?php do_action( 'si_display_settings', $section_settings['settings'], true ); ?>

												<?php endif ?>
											</div>
										</div>
									</div>
									<?php endforeach ?>
									<div class="si-controls">
										<div class="si-notifications-controls">
											<button class="button button-primary" v-on:click='resetNotificationTemplates'><?php esc_html_e( 'Reset All Notifications', 'sprout-invoices' ); ?></button>
											<p class="reset-notification"><?php esc_html_e( 'This will reset all notifications to their default state.', 'sprout-invoices' ); ?></p>
										</div>
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
								<?php $shown = array(); ?>
								<?php foreach ( $notifications as $notification_key => $data ) : ?>
									<?php
										if ( ! isset( $data['post_id'] ) || ! is_int( $data['post_id'] ) ) {
											continue;
										}
										$notification_id = $data['post_id'];
										$name            = SI_Notifications::$notifications[ $notification_key ]['name'];
										$desc            = SI_Notifications::$notifications[ $notification_key ]['description'];
										$notification    = SI_Notification::get_instance( $notification_id );
										$shown[]         = $data['post_id'];
									?>
									<div id="<?php echo esc_attr( $notification_key ); ?>" class="row" v-show="isActiveTab('<?php echo esc_attr( $notification_key ); ?>')" style="display: none;">
										<div class="si-box">
											<div id="section_<?php echo esc_attr( $notification_key ); ?>">
												<div class="si-box-top">
													<?php echo esc_html( $name ); ?>
													<a class="page-title-action notification-title-action-primary" href="<?php echo esc_url( get_edit_post_link( $notification_id ) ); ?>"><?php esc_html_e( 'Edit Notification', 'sprout-invoices' ); ?></a>&nbsp;<a class="page-title-action add-new" href="<?php echo esc_url( add_query_arg( array( 'refresh-notification' => $notification_id ) ) ) ?>" aria-label="<?php esc_html_e( 'This will reset the notification to the default template', 'sprout-invoices' ) ?>"><?php esc_html_e( 'Reset', 'sprout-invoices' ) ?></a>
													<p><?php echo esc_html( $desc ); ?></p>
												</div>
												<div id="si-settings-notifications" class="si-box-bottom">
													<h2><?php echo esc_html( $notification->get_title() ); ?></h2>
													<div class="notification_content">
														<iframe defer src="<?php echo esc_url( add_query_arg( array( 'show-notification' => $notification_id ) ) ); ?>"></iframe>
													</div>
												</div>
											</div>
										</div>
									</div>
								<?php endforeach ?>
								<?php foreach ( $notification_posts as $notification_post_id ) : ?>
									<?php
									if ( in_array( $notification_post_id, $shown, true ) ) {
										continue;
									}
									$name         = esc_html__( 'Archived & Unassigned', 'sprout-invoices' );
									$status       = '<span class="si_status_icon dashicons dashicons-no"></span>';
									$notification = SI_Notification::get_instance( $notification_id );
									?>
									<div id="<?php echo esc_attr( $notification_post_id ); ?>" class="row" v-show="isActiveTab('<?php echo esc_attr( $notification_post_id ); ?>')" style="display: none;">
										<div class="si-box">
											<div id="section_<?php echo esc_attr( $notification_post_id ); ?>">
												<div class="si-box-top">
													<?php echo esc_html( $notification->get_title() ); ?>
													<a class="page-title-action notification-title-action-primary" href="<?php echo esc_url( get_edit_post_link( $notification_post_id ) ); ?>"><?php esc_html_e( 'Edit Notification', 'sprout-invoices' ); ?></a>&nbsp;<?php if ( current_user_can( 'delete_post', $notification_post_id ) ) { ?><a class="page-title-action add-new" aria-label="<?php esc_html_e( 'Delete this unnassigned notification', 'sprout-invoices' ); ?>" href="<?php echo get_delete_post_link( $notification_post_id, null, true ); ?>"><?php esc_html_e( 'Delete', 'sprout-invoices' ); ?></a><?php } ?>
												</div>
												<div class="si-box-bottom">
													<div class="description">
															<p><?php esc_html_e( 'This notification is no longer assigned, maybe becuase an add-on has been deactivated, Sprout Invoices kept it just in case you needed it later.', 'sprout-invoices' ); ?></p>
													</div>
												</div>
											</div>
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

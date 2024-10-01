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
					<div class="si-support-page">
						<div class="si-free-support si-box">
							<div class="si-box-top"><?php esc_html_e( 'Free Support', 'sprout-invoices' ); ?></div>
							<div class="si-box-bottom">
								<ul>
									<li><?php esc_html_e( 'Ask on', 'sprout-invoices' ); ?> <a href="https://wordpress.org/support/plugin/sprout-invoices/" target="_blank">WordPress.org</a></li>
									<li><?php esc_html_e( 'Browse our', 'sprout-invoices' ); ?> <a href="https://sproutapps.co/support/" target="_blank">Support Guides</a></li>
								</ul>
							</div>
						</div>
						<div class="si-premium-support si-box">
							<div class="si-box-top"><?php esc_html_e( 'Premium Support', 'sprout-invoices' ); ?></div>
							<?php if ( SI_PRO ) : ?>
							<div class="si-box-bottom">
								<ul>
									<li><?php esc_html_e( 'Create ticket at', 'sprout-invoices' ); ?> <a href="https://sproutinvoices.com/support/my-tickets/" target="_blank">SproutInvoice.com</a></li>
									<li><?php esc_html_e( 'Browse our', 'sprout-invoices' ); ?> <a href="https://sproutapps.co/support/" target="_blank">Support Guides</a></li>
								</ul>
								<br>
								<p><?php esc_html_e( 'If you are having issues with Sprout Invoices, please click the System Health Check button and attach it to your support ticket.', 'sprout-invoices' ); ?></p>
								<button class="button button-primary" id="si_system_health_check" data-nonce=" <?php echo esc_attr( $nonce ) ?> "><?php esc_html_e( 'Copy System Health Check', 'sprout-invoices' ); ?></button>
								<br>
								<div id="si_health_check_error">
									<p><?php esc_html_e( 'There was an error generating the System Health Check.', 'sprout-invoices' ); ?></p>
								</div>
								<div id="si_health_check_text">
									<textarea id="si_health_check_textarea" rows="100" cols="75" readonly="readonly"></textarea>
								</div>
							</div>
							<?php else : ?>
							<div id="si-upgrade" class="si-box-bottom">
								<div class="si-upgrade-message">
									<p><?php esc_html_e( 'Upgrade to receive Premium support from Sprout Invoices.', 'sprout-invoices' ); ?></p>
									<p><a href="https://sproutinvoices.com/pricing/?_sa_d=si19BIGdiscount" class="button button-success" target="_blank">Get Premium</a></p>
								</div>
							</div>
							<?php endif ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

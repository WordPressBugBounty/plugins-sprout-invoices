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
			<div id="si_reports_admin" class="si_settings">
				<div id="si_basic_settings">
					<div id="si_report">
						<div id="col-container" class="wp-clearfix">
							<div id="col-left">
								<div id="sticky-wrapper" class="sticky-wrapper">
									<ul class="si-left-nav">
										<a href="<?php echo esc_url( get_admin_url() . 'admin.php?page=sprout-invoices-reports' ) ?>" class="<?php if ( 'dashboard' == $current_report || '' == $current_report ) { echo ' active'; } ?> si-tab-item "><?php esc_html_e( 'Dashboard', 'sprout-invoices' ) ?></a>
										<a href="<?php echo esc_url( add_query_arg( $query_var, 'invoices' ) ) ?>" class="<?php if ( 'invoices' == $current_report ) { echo ' active'; } ?> si-tab-item "><?php esc_html_e( 'Invoice Reports', 'sprout-invoices' ) ?></a>
										<a href="<?php echo esc_url( add_query_arg( $query_var, 'estimates' ) ) ?>" class="<?php if ( 'estimates' == $current_report ) { echo ' active'; } ?> si-tab-item "><?php esc_html_e( 'Estimate Reports', 'sprout-invoices' ) ?></a>
										<a href="<?php echo esc_url( add_query_arg( $query_var, 'payments' ) ) ?>" class="<?php if ( 'payments' == $current_report ) { echo ' active'; } ?> si-tab-item "><?php esc_html_e( 'Payment Reports', 'sprout-invoices' ) ?></a>
										<a href="<?php echo esc_url( add_query_arg( $query_var, 'clients' ) ) ?>" class="<?php if ( 'clients' == $current_report ) { echo ' active'; } ?> si-tab-item "><?php esc_html_e( 'Client Reports', 'sprout-invoices' ) ?></a>
									</ul>
								</div>
							</div>
							<div id="col-right" class="si_settings_tabs">
								<?php if ( ! empty( $current_report ) ): ?>
									<?php do_action( 'si_settings_page_pre_load_reports', $view ); ?>
								<?php endif ?>
								<?php self::load_view( $view, array() ); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

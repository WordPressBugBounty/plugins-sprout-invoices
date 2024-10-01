<div class="wrap">
	<div id="si-page-container" class="si-page-container">
		<div id="si-page-top">
			<?php do_action( 'sprout_settings_header' ); ?>
		</div>
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
		<div id="si_importer_admin" class="si_settings">
			<div id="si_settings">
				<?php do_action( 'sprout_settings_messages' ) ?>
				<?php foreach ( $settings as $key => $section_settings ) :  ?>
					<?php if ( ! empty( $section_settings['settings'] ) ) :  ?>
						<div id="section_<?php echo esc_attr( $key ) ?>">
							<?php do_action( 'si_display_settings', $section_settings['settings'], true ) ?>
					</div>
					<?php endif ?>
				<?php endforeach ?>
			</div>
		</div>
	</div>
</div>

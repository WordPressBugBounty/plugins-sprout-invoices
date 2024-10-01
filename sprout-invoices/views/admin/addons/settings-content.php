<?php uasort( $allsettings, array( 'SI_Controller', 'sort_by_weight' ) ); ?>
<?php foreach ( $allsettings as $key => $section_settings ) : ?>
	<?php
	// all settings for this tab
	if ( $addon['settingID'] !== $section_settings['tab'] ) :
	continue;
	endif;
	?>
	<?php if ( isset( $section_settings ) ) : ?>
		<div id="section_<?php echo esc_attr( $key ); ?>" class="addon-settings">
			<div class="si-box-top">
				<?php if ( isset( $section_settings['title'] ) && '' !== $section_settings['title'] ) : ?>
					<?php echo esc_html( $section_settings['title'] ); ?>
				<?php endif ?>
			</div>
			<div class="si-box-bottom">
				<?php if ( isset( $section_settings['description'] ) && '' !== $section_settings['description'] ) : ?>
					<p>
					<?php echo wp_kses_post( $section_settings['description'] ); ?>
					</p>
				<?php endif ?>
				<?php if ( ! empty( $section_settings['settings'] ) ) : ?>
					<?php do_action( 'si_display_settings', $section_settings['settings'], true ); ?>
				<?php endif ?>
			</div>
		</div>
	<?php endif ?>
<?php endforeach ?>

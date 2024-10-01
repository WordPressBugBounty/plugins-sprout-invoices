<?php foreach ( $processor_settings as $key => $section_settings ) : ?>
	<div class="si-box-top">
		<?php if ( isset( $section_settings['title'] ) && '' !== $section_settings['title'] ) : ?>
			<?php echo esc_html( $section_settings['title'] ); ?>
		<?php endif ?>
		<a id="si-back" href='#section-payment-processors' v-on:click="makeTabActive('payment-processors')" v-bind:class="{ active : isActiveTab('payment-processors') == true }"><span title="Back To Processors" class="dashicons dashicons-undo"></span></a>
	</div>
	<div class="si-box-bottom">
		<?php if ( isset( $section_settings['description'] ) && '' !== $section_settings['description'] ) : ?>
			<p><?php echo esc_html( $section_settings['description'] ); ?></p>
		<?php endif ?>
		<?php if ( ! empty( $section_settings['settings'] ) ) : ?>
				<?php do_action( 'si_display_settings', $section_settings['settings'], true ); ?>
		<?php endif ?>
	</div>
<?php endforeach ?>
<div class="si-controls si-processor-submit">
	<button
		@click='saveOptions'
		:disabled='isSaving'
		id='si-submit-settings' class="button button-primary"><?php esc_html_e( 'Save Changes', 'sprout-invoices' ); ?></button>
		<img
		v-if='isSaving == true'
		id='loading-indicator' src='<?php echo esc_url( get_site_url() ); ?>/wp-admin/images/wpspin_light-2x.gif' alt='Loading indicator' />
</div>
<p class="si_setting_message" v-if='message'>{{ message }}</p>

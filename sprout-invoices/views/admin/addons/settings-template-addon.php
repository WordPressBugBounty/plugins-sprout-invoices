<article class="type_addon si-card" v-bind:class="{ activated : vm.<?php echo esc_attr( $vm_key ); ?> == true }">
	<div class="section" v-bind:class="{ activating : isSaving == true }">
		<div class="si-card-title">
			<strong><?php echo wp_kses( $title, wp_kses_allowed_html( 'post' ) ); ?></strong>
			<button v-if="vm.<?php echo esc_attr( $vm_key ); ?> == true" class="si-button button-secondary" v-model.lazy="vm.<?php echo esc_attr( $vm_key );  ?>" v-on:click="deactivateAddOn( '<?php echo esc_attr( $key ) ?>', $event )">
				<span class="si-action-addon-<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Disable', 'sprout-invoices' ); ?></span>
			</button>
			<button v-else class="si-button button-primary" v-model.lazy="vm.<?php echo esc_attr( $vm_key );  ?>" v-on:click="activateAddOn( '<?php echo esc_attr( $key ) ?>', $event )">
				<span class="si-action-addon-<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Enable', 'sprout-invoices' ); ?></span>
			</button>
		</div>
		<div class="si-card-icon">
			<div class="img_wrap">
				<img src="<?php echo esc_attr( $img )  ?>" />
			</div>
		</div>
		<div class="si-card-footer">
			<div class="addon_description">
				<div class="addon_description">
					<?php echo wp_kses( $description, wp_kses_allowed_html( 'post' ) ); ?>
				</div>
			</div>
		</div>
		<div id="button_<?php echo esc_attr( $key ) ?>" class="si-card-links">
			<div class="addon_info_link">
				<a href="<?php echo esc_url( si_get_sa_link( $url, 'add-ons' ) ); ?>" class="si-button" target="_blank"><?php esc_html_e( 'Learn More', 'sprout-invoices' ); ?></a>
			</div>
			<?php if ( ! empty( $settings_link[ $key ] ) ) : ?>
				<div class="settings_<?php echo esc_attr( $details['settingID'] ); ?>">
					<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=' . $settings_link[ $key ] ) ); ?>"><?php esc_html_e( 'Settings', 'sprout-invoices' ); ?></a>
				</div>
			<?php else : ?>
				<div class="settings_<?php echo esc_attr( $details['settingID'] ); ?>">
					<a href="#<?php echo esc_attr( $details['settingID'] ); ?>" class="si-settings-link button button-primary" v-on:click="makeTabActive('<?php echo esc_attr( $details['settingID'] ); ?>')" v-bind:class="{ active : isActiveTab(<?php echo esc_attr( $details['settingID'] ); ?>) == true }"><?php esc_html_e( 'Settings', 'sprout-invoices' ); ?></a>
				</div>
			<?php endif ?>
		</div>
	</div>
</article>

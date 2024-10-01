<div id="section_<?php echo esc_attr( $class_name ) ?>">
	<div class="si-box-top">
		<?php echo esc_html( $label ) ?>
	</div>
	<div class="si-box-bottom">
			<input type="checkbox" name='<?php echo esc_attr( $class_name ) ?>' id="<?php echo esc_attr( $class_name ) ?>" v-model.lazy="vm.<?php echo esc_attr( $class_name ) ?>" v-on:change="activatePP" />
			<label for="<?php echo esc_attr( $class_name ) ?>">
				<span v-if="vm.<?php echo esc_attr( $class_name ) ?> == true">
					<?php printf( '%s Active', esc_html( $label ) ) ?>
				</span>
				<span v-else>
					<?php printf( '%s Disabled', esc_html( $label ) ) ?>
				</span>
			</label>
	</div>
</div>

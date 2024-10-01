<?php
/**
 * File: sprout-invoices/controllers/sprout-invoices-field-textarea.php
 *
 * Sprout Invoices Field Textarea Controller.
 *
 * @package Sprout Invoices
 *
 * @since 20.7.0
 */

/**
 * Sprout Invoices Field Textarea Controller.
 *
 * Stores and Renders the field textarea for Sprout Invoices.
 *
 * @since 20.7.0
 */
class SI_Field_Textarea extends SI_Field {
	/**
	 * Get Field Markup
	 *
	 * @since 20.7.0
	 */
	public function get_field_markup() {
		ob_start();
		?>
			<div
				class="si_input_field_wrap si_field_wrap_input_<?php echo esc_attr( $this->field_type ); ?>"
			>
				<label for="<?php echo esc_attr( $this->field_name ); ?>" class="si_input_label"><?php echo esc_html( $this->field_label ); ?></label>
				<textarea
					name="<?php echo esc_attr( $this->field_name ); ?>"
					id="<?php echo esc_attr( $this->field_name ); ?>"
					value="<?php echo esc_attr( $this->field_options['value'] ); ?>"
					placeholder="<?php echo isset( $this->field_options['placeholder'] ) ? esc_attr( $this->field_options['placeholder'] ) : ''; ?>"
					size="<?php echo isset( $this->field_options['size'] ) ? esc_attr( $this->field_options['size'] ) : 40; ?>"
					<?php
						if ( isset( $this->field_options['attributes'] ) ) {
							$this->esc_field_attributes( $this->field_options['attributes'] );
						}
					?>
					<?php
						if ( isset( $this->field_options['required'] ) && $this->field_options['required'] ) {
							echo 'required';
						}
					?>
				>
				</textarea>
				<span class="input_desc help_block"> <?php echo wp_kses_post( $this->field_description ); ?></span>
			</div>
		<?php
		return ob_get_clean();
	}
}

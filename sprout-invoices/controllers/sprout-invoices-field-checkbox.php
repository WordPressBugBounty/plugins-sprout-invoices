<?php
/**
 * File: sprout-invoices/controllers/sprout-invoices-field-checkbox.php
 *
 * Sprout Invoices Field Checkbox Controller.
 *
 * @package Sprout Invoices
 *
 * @since 20.7.0
 */

/**
 * Sprout Invoices Field Checkbox Controller.
 *
 * Stores and Renders the field checkbox for Sprout Invoices.
 *
 * @since 20.7.0
 */
class SI_Field_Checkbox extends SI_Field {

	/**
	 * Get Field Markup
	 *
	 * @since 20.7.0
	 */
	public function get_field_markup() {
		ob_start();
		?>
			<label for="<?php echo esc_attr( $this->field_options['attributes']['id'] ); ?>">
				<?php if ( isset( $this->field_options['vueifs'] ) ) : ?>
					<?php foreach ( $this->field_options['vueifs'] as $attribute => $value ) : ?>
						<input
						<?php echo esc_attr( $attribute ) . '="' . esc_attr( $value ) . '" '; // In order to make vue work this is seperate from other attributes for output ?>
						<?php $this->esc_field_attributes( $this->field_options['attributes'] ); // Sub attributes outside of vueifs ?>
						>
					<?php endforeach; ?>
				<?php else : ?>
					<input
						<?php $this->esc_field_attributes( $this->field_options['attributes'] ); ?>
					>
					<?php echo esc_html( $this->field_label ); ?>
				<?php endif; ?>
			</label>
		<?php
		return ob_get_clean();
	}
}

<?php
/**
 * File: sprout-invoices/controllers/sprout-invoices-field-paragraph.php
 *
 * Sprout Invoices Field Paragraph Controller.
 *
 * @package Sprout Invoices
 *
 * @since 20.7.0
 */

/**
 * Sprout Invoices Field Paragraph Controller.
 *
 * Stores and Renders the field paragraph for Sprout Invoices.
 *
 * @since 20.7.0
 */
class SI_Field_Paragraph extends SI_Field {
	/**
	 * Get Field Markup
	 *
	 * @since 20.7.0
	 */
	public function get_field_markup() {
		ob_start();
		?>
			<p
				<?php $this->esc_field_attributes( $this->field_options['attributes'] ); ?>
			>
				<?php echo wp_kses_post( $this->field_options['paragraph_text'] ); ?>
			</p>
		<?php
		return ob_get_clean();
	}
}

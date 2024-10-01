<?php
/**
 * File: sprout-invoices/controllers/sprout-invoices-field-text.php
 *
 * Sprout Invoices Field Text Controller.
 *
 * @package Sprout Invoices
 *
 * @since 20.7.0
 */

/**
 * Sprout Invoices Field Text Controller.
 *
 * Stores and Renders the field text for Sprout Invoices.
 *
 * @since 20.7.0
 */
class SI_Field_Error extends SI_Field {
	/**
	 * Get Field Markup
	 *
	 * @since 20.7.0
	 */
	public function get_field_markup() {
		?>
			<div class="si_input_field_wrap si_field_wrap_input_<?php echo esc_attr( $this->field_type ); ?>">
				<p> <?php echo esc_html( $this->field_type . 'Field Could Not be Rendered' ); ?></p>
			</div>
		<?php
	}
}

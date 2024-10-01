<?php
/**
 * File: sprout-invoices/controllers/sprout-invoices-field-button.php
 *
 * Sprout Invoices Field Button Controller.
 *
 * @package Sprout Invoices
 *
 * @since 20.7.0
 */

/**
 * Sprout Invoices Field Button Controller.
 *
 * Stores and Renders the field button for Sprout Invoices.
 *
 * @since 20.7.0
 */
class SI_Field_Button extends SI_Field {

	/**
	 * Get Field Markup
	 *
	 * @since 20.7.0
	 *
	 * @return string
	 */
	public function get_field_markup() {
		ob_start();
		?>
			<?php if ( empty( $this->field_name ) ) : ?>
			<div>
			<?php else : ?>
			<div class="settings_<?php echo esc_attr( $this->field_name ); ?>">
			<?php endif; ?>
				<a
					<?php $this->esc_field_attributes( $this->field_options['attributes'] ); ?>
				>
					<?php echo esc_html( $this->field_options['button_text'] ); ?>
				</a>
			</div>
		<?php
		return ob_get_clean();
	}
}

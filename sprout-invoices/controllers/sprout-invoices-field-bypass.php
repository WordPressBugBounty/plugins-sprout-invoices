<?php
/**
 * File: sprout-invoices/controllers/sprout-invoices-bypass.php
 *
 * Sprout Invoices Bypass Controller.
 *
 * @package Sprout Invoices
 *
 * @since 20.7.0
 */

/**
 * Sprout Invoices Field Bypass Controller.
 *
 * This field is from the old format.
 *
 * @since 20.7.0
 */
class SI_Field_Bypass extends SI_Field {
	/**
	 * Get Field Markup
	 *
	 * @since 20.7.0
	 *
	 * @return string
	 */
	public function get_field_markup() {
		$label   = isset( $this->field_label ) ? $this->field_label : '';
		$content = isset( $this->field_options['output'] ) ? $this->field_options['output'] : '';

		ob_start();
		?>
		<div
			class="si_field_wrap_input_<?php echo esc_attr( $this->field_type ); ?>"
		>
			<label for="<?php echo esc_attr( $this->field_name ); ?>" class="si_input_label"><?php echo esc_html( $label ); ?></label>
			<div>
			<?php
				echo $content;
			?>
			</div>
			<span class="input_desc help_block">
				<?php echo esc_html__( $this->field_description, 'sprout-invoices' ); ?>
			</span>
		</div>
		<?php
		return ob_get_clean();
	}
}

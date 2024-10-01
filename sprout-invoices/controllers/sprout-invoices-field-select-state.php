<?php
/**
 * File: sprout-invoices/controllers/sprout-invoices-field-select-state.php
 *
 * Sprout Invoices Field Text Controller.
 *
 * @package Sprout Invoices
 *
 * @since 20.7.0
 */

/**
 * Sprout Invoices Field Select Controller.
 *
 * Stores and Renders the field text for Sprout Invoices.
 *
 * @since 20.7.0
 */
class SI_Field_Select_State extends SI_Field {
	/**
	 * Get Field Markup
	 *
	 * @since 20.7.0
	 */
	public function get_field_markup() {
		?>
			<div
				class="si_input_field_wrap si_field_wrap_input_<?php echo esc_attr( $this->field_type ); ?>"
			><?php echo esc_html( $this->field_description ); ?>
			<label for="<?php echo esc_attr( $this->field_name ); ?>" class="si_input_label"><?php echo esc_html( $this->field_label ); ?></label>
				<select
				name="<?php echo esc_attr( $this->field_name ); ?>"
				id="<?php echo esc_attr( $this->field_name ); ?>"
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
				<?php
				foreach ( $this->field_options['options'] as $group => $options ) {
					?>
					<optgroup
						label="<?php echo esc_attr( $group ); ?>"
					>
						<?php foreach ( $options as $option => $value ) : ?>
							<option
								value="<?php echo esc_attr( $option ); ?>" <?php echo esc_attr( $option === $this->field_options['value'] ? 'selected' : '' ); ?>><?php echo esc_html( $value ); ?>
							</option>
						<?php endforeach; ?>
					</optgroup>
					<?php
				}
				?>
				</select>
			</div>
		<?php
	}
}

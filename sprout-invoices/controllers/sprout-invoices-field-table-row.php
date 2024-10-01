<?php
/**
 * File: sprout-invoices/controllers/sprout-invoices-field-table-row.php
 *
 * Sprout Invoices Field Table Row Controller.
 *
 * @package Sprout Invoices
 *
 * @since 20.7.0
 */

/**
 * Sprout Invoices Field Table Row Controller.
 *
 * Stores and Renders the field table row for Sprout Invoices.
 *
 * @since 20.7.0
 */
class SI_Field_Table_Row extends SI_Field {
	/**
	 * Render the field.
	 *
	 * @since SINCEVERSION
	 */
	public function render() {
		$field_markup = $this->get_field_markup();
		?>
		<?php echo $field_markup; ?>
		<?php
	}

	/**
	 * Get Field Markup
	 *
	 * @since 20.7.0
	 *
	 * @return string
	 */
	public function get_field_markup() {
		$field_styles = $this->get_field_styles();
		$class_name   = isset( $this->field_name ) ? $this->field_name : '';
		$columns      = isset( $this->field_options['columns'] ) ? $this->field_options['columns'] : array();

		ob_start();
		?>
		<tr
			v-bind:class="{ activated : vm.<?php echo esc_attr( $class_name ); ?> == true }"
			style="<?php echo esc_attr( $field_styles ); ?>"
		>
		<?php
		foreach ( $columns as $column_id => $column_options ) {
			?>
			<td
			<?php
				if ( isset( $column_options['attributes'] ) ) {
					$this->esc_field_attributes( $column_options['attributes'] );
				}
				?>
			>
			<?php
				$contents = $column_options['contents'];
				if ( is_string( $contents ) ) {
					echo esc_html( $contents );
				} elseif ( is_object( $contents ) && is_subclass_of( $contents, 'SI_Field' ) ) {
					$contents->render();
				}
			?>
			</td>
			<?php
		}
		?>
		</tr>
		<?php

		return ob_get_clean();
	}
}

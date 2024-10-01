<?php
/**
 * File: sprout-invoices/controllers/sprout-invoices-field-table.php
 *
 * Sprout Invoices Field Table Controller.
 *
 * @package Sprout Invoices
 *
 * @since 20.7.0
 */

/**
 * Sprout Invoices Field Table Controller.
 *
 * Stores and Renders the field table for Sprout Invoices.
 *
 * @since 20.7.0
 */
class SI_Field_Table extends SI_Field {
	/**
	 * Get Field Markup
	 *
	 * @since 20.7.0
	 *
	 * @return string
	 */
	public function get_field_markup() {
		$label   = isset( $this->field_options['label'] ) ? $this->field_options['label'] : '';
		$columns = isset( $this->field_options['columns'] ) ? $this->field_options['columns'] : array();
		$rows    = isset( $this->field_options['rows'] ) ? $this->field_options['rows'] : array();

		ob_start();
		?>

		<div id="payment-processors" class="row">
			<div>
				<h2><?php echo esc_html( $this->field_label ); ?></h2>
			</div>
			<table class="si-processors widefat form-table" cellspacing="0">
				<thead>
					<tr>
						<?php foreach ( $columns as $column_id => $column_label ) : ?>
							<th><?php echo esc_html( $column_label ); ?></th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ( $rows as $row ) {
						$row->render();
					}
					?>
				</tbody>
			</table>
		</div>

		<?php
		return ob_get_clean();
	}
}

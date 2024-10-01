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
 * Sprout Invoices Field Group Controller.
 *
 * This creates the grouping of items resolved divs not nesting correctly.
 *
 * @since 20.7.0
 */
class SI_Field_Group extends SI_Field {
	/**
	 * Get Field Markup
	 *
	 * @since 20.7.0
	 *
	 * @return string
	 */
	public function get_field_markup() {
		$label    = isset( $this->field_label ) ? $this->field_label : '';
		$contents = isset( $this->field_options['content'] ) ? $this->field_options['content'] : array();

		ob_start();
		?>
		<div
			<?php $this->esc_field_attributes( $this->field_options['attributes'] ); ?>
		>
			<?php foreach ( $contents as $content ) : ?>
					<?php $content->render(); ?>
			<?php endforeach; ?>
		</div>
		<?php
		return ob_get_clean();
	}
}

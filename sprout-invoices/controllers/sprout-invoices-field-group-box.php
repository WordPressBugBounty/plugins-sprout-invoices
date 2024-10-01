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
class SI_Field_Group_Box extends SI_Field {
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
		<div class="si-box-top">
			<h3><?php echo esc_html( $label ); ?></h3>
		</div>
		<div class="si-box-bottom">
			<?php if ( isset( $this->field_description ) && ! empty( $this->field_description ) ) : ?>
				<p class="si-box-description"><?php echo esc_html( $this->field_description ); ?></p>
			<?php endif; ?>
			<?php foreach ( $contents as $content ) : ?>
				<div class="si_field_wrap">
					<?php $content->render(); ?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
		return ob_get_clean();
	}
}

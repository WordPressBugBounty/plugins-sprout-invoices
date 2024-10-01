<?php
/**
 * File: sprout-invoices/controllers/sprout-invoices-field-tab.php
 *
 * Sprout Invoices Field Tab Controller.
 *
 * @package Sprout Invoices
 *
 * @since 20.7.0
 */

/**
 * Sprout Invoices Field Tab Controller.
 *
 * Stores and Renders the field tab for Sprout Invoices.
 *
 * @since 20.7.0
 */
class SI_Field_Tab extends SI_Field {
	/**
	 * Get Field Markup
	 *
	 * @since 20.7.0
	 *
	 * @return string
	 */
	public function get_field_markup() {
		$markup = '';
		$fields = isset( $this->field_options['fields'] ) ? $this->field_options['fields'] : array();

		foreach ( $fields as $field ) {
			$markup .= '<div id="' . esc_attr( $field->field_name ) . '" class="si-field-group-item">';
			$markup .= $field->get_field_markup();
			$markup .= '</div>';
		}
		return $markup;
	}
}

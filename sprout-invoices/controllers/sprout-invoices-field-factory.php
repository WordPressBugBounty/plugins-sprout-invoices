<?php
/**
 * File: sprout-invoices/controllers/sprout-invoices-field-factory.php
 *
 * Sprout Invoices Field Factory Controller.
 *
 * @package Sprout Invoices
 *
 * @since 20.7.0
 */

/**
 * Sprout Invoices Field Factory.
 *
 * @since 20.7.0
 */
class SI_Field_Factory {
	/**
	 * Create
	 *
	 * @since 1.0.0
	 *
	 * @param string $field_name Name.
	 * @param string $field_label field_label.
	 * @param string $field_description field_description.
	 * @param string $field_type field_type.
	 * @param array  $field_options Field Specific Options.
	 */
	public static function create(
		$field_name,
		$field_label,
		$field_description,
		$field_type,
		$field_options
	) {
		$field_class_name = 'SI_Field_' . ucwords( $field_type );

		if ( ! class_exists( $field_class_name ) ) {
			$field_class_name = 'SI_Field_Error';
		}

		return new $field_class_name(
			$field_name,
			$field_label,
			$field_description,
			$field_type,
			$field_options
		);
	}
}

<?php
/**
 * file: sprout-invoices/controllers/sprout-invoices-fields.php
 *
 * Sprout Invoices Fields Controller.
 *
 * @package Sprout Invoices
 *
 * @since 20.7.0
 */

/**
 * Sprout Invoices Fields Controller.
 *
 * Stores and Renders the fields for Sprout Invoices.
 *
 * @since 20.7.0
 */
class SI_Field extends SI_Controller {

	/**
	 * The Field Name
	 *
	 * @since SINCEVERSION
	 *
	 * @var string
	 */
	public $field_name = '';

	/**
	 * The Field Label
	 *
	 * @since SINCEVERSION
	 *
	 * @var string
	 */
	public $field_label = '';

	/**
	 * The Field Description
	 *
	 * @since SINCEVERSION
	 *
	 * @var string
	 */
	public $field_description = '';

	/**
	 * The Field Type
	 *
	 * @since SINCEVERSION
	 *
	 * @var string
	 */
	public $field_type = '';

	/**
	 * The Field Options
	 *
	 * @since SINCEVERSION
	 *
	 * @var array
	 */
	public $field_options = array();

	/**
	 * Allowed HTML
	 *
	 * @since SINCEVERSION
	 *
	 * @var array
	 */
	public $allowed_html = array(
		'input' => array(
			'type'  => array(),
			'name'  => array(),
			'id'    => array(),
			'class' => array(),
			'value' => array(),
		),
		'label' => array(
			'for' => array(),
		),
		'p'     => array(
			'class' => array(),
		),
		'span'  => array(
			'class' => array(),
		),
		'br'    => array(),
		'hr'    => array(),
	);

	/**
	 * Constructor
	 *
	 * @since SINCEVERSION
	 *
	 * @param string $field_name Field Name.
	 * @param string $field_label Field Label.
	 * @param string $field_description Field Description.
	 * @param string $field_type Field Type.
	 * @param array  $field_options Field Options.
	 */
	public function __construct(
		string $field_name,
		string $field_label,
		string $field_description,
		string $field_type,
		array $field_options
	) {
		$this->field_name        = $field_name;
		$this->field_label       = $field_label;
		$this->field_description = $field_description;
		$this->field_type        = $field_type;
		$this->field_options     = $field_options;
	}

	/**
	 * Render the field.
	 *
	 * @since SINCEVERSION
	 */
	public function render() {
		$field_markup = $this->get_field_markup();
		$field_styles = $this->get_field_styles();
		?>
		<div
			class="si_field_<?php echo esc_attr( $this->field_type ); ?>"
			style="<?php echo esc_attr( $field_styles ); ?>"
		>
		<?php echo wp_kses( $field_markup, SI_Settings_API::get_allowed_html() ); ?>
		</div>
		<?php
	}

	/**
	 * Get the field attributes.
	 *
	 * Get the field attributes and escape them for output.
	 *
	 * @since 20.7.0
	 *
	 * @param array $attributes The field attributes.
	 *
	 * @return void
	 */
	public function esc_field_attributes( $attributes ) {
		if ( empty( $attributes || ! is_array( $attributes ) ) ) {
			echo '';
		}
		foreach ( $attributes as $attribute => $value ) {
			echo esc_attr( $attribute ) . '="' . esc_attr( $value ) . '" ';
		}
	}

	/**
	 * Get the field markup.
	 *
	 * @since SINCEVERSION
	 */
	public function get_field_markup() {
		// This is empty, will be overridden by child classes.
	}

	/**
	 * Get the field styles.
	 *
	 * @since SINCEVERSION
	 */
	public function get_field_styles() {
		// This is empty, will be overridden by child classes.
	}

}


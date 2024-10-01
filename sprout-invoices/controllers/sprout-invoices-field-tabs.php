<?php
/**
 * File: sprout-invoices/controllers/sprout-invoices-field-tabs.php
 *
 * Sprout Invoices Field Tabs Controller.
 *
 * @package Sprout Invoices
 *
 * @since 20.7.0
 */

/**
 * Sprout Invoices Field Tabs Controller.
 *
 * Stores and Renders the field tabs for Sprout Invoices.
 *
 * @since 20.7.0
 */
class SI_Field_Tabs extends SI_Field {
	/**
	 * Get Field Markup
	 *
	 * @since 20.7.0
	 */
	public function get_field_markup() {
		$tabs = isset( $this->field_options['tabs'] ) ? $this->field_options['tabs'] : array();
		ob_start();
		?>
		<div id="col-left">
			<div id="sticky-wrapper" class="sticky-wrapper">
				<?php if ( empty( $this->field_options['args']['settings_page'] ) ) : ?>
					<ul class="si-left-nav">
						<?php foreach ( $tabs as $tab ) : ?>
							<a
								class="si-tab-item"
								href='#<?php echo esc_attr( $tab->field_name ); ?>'
								v-on:click="makeTabActive('<?php echo esc_attr( $tab->field_name ); ?>')"
								v-bind:class="{ active : isActiveTab('<?php echo esc_attr( $tab->field_name ); ?>') == true }"
							>
								<?php echo esc_html( $tab->field_label ); ?>
							</a>
						<?php endforeach ?>
					</ul>
				<?php endif ?>
			</div>
		</div>
		<div id="col-right" class="si_wizard_tabs">
			<?php foreach ( $tabs as $tab ) : ?>
			<div
				id="<?php echo esc_attr( $tab->field_name ); ?>"
				class="row"
				v-show="isActiveTab('<?php echo esc_attr( $tab->field_name ); ?>')"
			>
				<div class="si-tabs-content">
					<?php $tab->render(); ?>
				</div>
			</div>
			<?php endforeach ?>
		</div>
		<?php

		return ob_get_clean();
	}
}

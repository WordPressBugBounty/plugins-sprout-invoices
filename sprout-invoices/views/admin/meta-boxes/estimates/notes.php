<?php 
	$editor_settings = array(
			'media_buttons'     => false,
			'textarea_rows'     => 4,
			'theme_styles'		=> false,
		);
 ?>

<h3><?php esc_html_e( 'Terms', 'sprout-invoices' ) ?> <span class="helptip" title="<?php esc_html_e( 'Terms will be shown on the estimate.', 'sprout-invoices' ) ?>"></span></h3>
<?php
	wp_editor_styleless( $terms, 'estimate_terms', $editor_settings ); ?>

<h3><?php esc_html_e( 'Notes', 'sprout-invoices' ) ?> <span class="helptip" title="<?php esc_html_e( 'These notes will be shown on the estimate.', 'sprout-invoices' ) ?>"></span></h3>
<?php
	wp_editor_styleless( $notes, 'estimate_notes', $editor_settings ); ?>

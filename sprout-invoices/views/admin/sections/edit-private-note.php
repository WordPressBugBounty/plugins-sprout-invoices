<div id="private_note_edit_modal">
	<div id="tt_body" class="admin_fields clearfix">
		<?php sa_admin_fields( $fields, 'note' ); ?>
	</div><!-- #tt_body -->
	<div id="tt_save">
		<p>
			<button id="save_edit_private_note" class="button button-primary" data-id="<?php echo esc_attr( $record_id ) ?>"><?php esc_html_e( 'Edit', 'sprout-invoices' ) ?></button>
		</p>
	</div><!-- #tt_save -->
</div><!-- #private_note_edit_modal -->

<script type="text/javascript">
	jQuery(function() {
		jQuery('#sa_note_note').redactor();
	});
</script>
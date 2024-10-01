<?php

/**
 * Copy of the post_submit_meta_box function
 *
 */

$post_type = $post->post_type;
$post_type_object = get_post_type_object( $post_type );
$can_publish = current_user_can( $post_type_object->cap->publish_posts );
?>
<div class="submitbox" id="submitpost">

	<?php // Hidden submit button early on so that the browser chooses the right button when form is submitted with Return key ?>
	<div style="display:none;">
		<?php submit_button( __( 'Save' ), 'button', 'save' ); ?>
	</div>

	<div id="minor-publishing">
		<p>
			<b><?php esc_html_e( 'Invoices: ', 'sprout-invoices' ) ?></b>
			<?php if ( ! empty( $invoices ) ) : ?>
				<dl>
					<?php foreach ( $invoices as $invoice_id ) : ?>
						<dt><?php echo esc_html( get_post_time( get_option( 'date_format' ), false, $invoice_id ) ) ?></dt>
						<dd><?php printf( '<a href="%s">%s</a>', esc_html( get_edit_post_link( $invoice_id ) ), esc_html( get_the_title( $invoice_id ) ) ) ?></dd>
					<?php endforeach ?>
				</dl>
			<?php else : ?>
				<em><?php esc_html_e( 'No invoices', 'sprout-invoices' ) ?></em>
			<?php endif ?>
		</p>
		<hr/>
		<p>
			<b><?php esc_html_e( 'Estimates: ', 'sprout-invoices' ) ?></b>
			<?php if ( ! empty( $estimates ) ) : ?>
				<dl>
					<?php foreach ( $estimates as $estimate_id ) : ?>
						<dt><?php echo esc_html( get_post_time( get_option( 'date_format' ), false, $estimate_id ) ) ?></dt>
						<dd><?php printf( '<a href="%s">%s</a>', esc_html( get_edit_post_link( $estimate_id ) ), esc_html( get_the_title( $estimate_id ) ) ) ?></dd>
					<?php endforeach ?>
				</dl>
			<?php else : ?>
				<em><?php esc_html_e( 'No estimates', 'sprout-invoices' ) ?></em>
			<?php endif ?>
		</p>
		<div class="clear"></div>
	</div><!-- #minor-publishing -->

	<div id="si-major-publishing-actions">
		<?php do_action( 'post_submitbox_start' ); ?>
		<div id="delete-action">
			<?php
			if ( current_user_can( 'delete_post', $post->ID ) ) { ?>
				<a class="submitdelete deletion" href="<?php echo get_delete_post_link( $post->ID, null, true ); ?>"><?php esc_html_e( 'Delete', 'sprout-invoices' ) ?></a><?php
			} ?>
		</div>

		<div id="si-publishing-action">
			<?php
			if ( ! in_array( $post->post_status, array('publish', 'future', 'private') ) || 0 == $post->ID ) {
				if ( $can_publish ) : ?>
					<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Publish' ) ?>" />
					<?php submit_button( esc_html__( 'Create' ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
				<?php else : ?>
					<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Submit for Review' ) ?>" />
					<?php submit_button( esc_html__( 'Submit for Review' ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
				<?php endif;
			} else { ?>
					<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Update' ) ?>" />
					<input name="save" type="submit" class="button button-primary button-large" id="publish" accesskey="p" value="<?php esc_attr_e( 'Update' ) ?>" />
			<?php
			} ?>
			<span class="spinner"></span>
		</div>
	<div class="clear"></div>
	</div><!-- #si-major-publishing-actions -->
</div>
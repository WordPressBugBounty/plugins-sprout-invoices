<?php do_action( 'sprout_settings_messages' ); ?>

<div id="line_item_types_wrap">
	<div id="nestable" class="nestable dd">
		<ol id="line_item_list" class="items_list">
			<?php do_action( 'si_get_line_item_type_section', $id ) ?>
		</ol>
	</div>
</div>
<div id="line_items_footer" class="clearfix">
	<div class="mngt_wrap clearfix">
		<div id="add_line_item">

			<?php do_action( 'si_add_line_item' ) ?>

			<?php if ( apply_filters( 'show_upgrade_messaging', true ) ) : ?>
				<span title="<?php esc_attr_e( 'Predefined line items can be created to help with invoice creation by adding default descriptions. This is a premium feature that will be added with a pro version upgrade.', 'sprout-invoices' ) ?>" class="helptip add_item_help"></span>

				<span id="time_importing">
					<?php
						printf(
						'<button id="time_import_question_answer_upgrade" class="button disabled si_tooltip" title="%s">%s</button>',
						esc_attr__( 'Any billable time can be imported from your projects into your invoices dynamically with a pro version upgrade.', 'sprout-invoices' ),
						esc_html__( 'Import Time', 'sprout-invoices' )
						)
					?>
				</span>
			<?php endif ?>

			<?php do_action( 'si_post_add_line_item' ) ?>
			<?php do_action( 'mb_item_types' ) // TODO deprecated ?>

			<div id="invoice_status_updates" class="sticky_save">
				<div id="si-publishing-action">
					<?php
					$post_type = $post->post_type;
					$post_type_object = get_post_type_object( $post_type );
					$can_publish = current_user_can( $post_type_object->cap->publish_posts );
					if ( 0 == $post->ID || $status == 'auto-draft' ) {
						if ( $can_publish ) : ?>
							<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Publish' ) ?>" />
							<?php submit_button( esc_html__( 'Save' ), 'primary', 'save', false, array( 'accesskey' => 'p' ) ); ?>
						<?php else : ?>
							<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Submit for Review' ) ?>" />
							<?php submit_button( esc_html__( 'Submit for Review' ), 'primary', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
						<?php endif;
					} else { ?>
							<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Update' ) ?>" />
							<input name="save" type="submit" class="button button-primary" id="save" accesskey="p" value="<?php esc_attr_e( 'Save' ) ?>" />
					<?php
					} ?>
				<span class="spinner"></span>
				</div>
			</div>

			<?php do_action( 'mb_invoice_save' ) ?>

		</div>

		<?php do_action( 'si_get_line_item_totals_section', $id ) ?>

	</div>
</div>
<?php if ( ! empty( $item_types ) ) : ?>
	<div class="cloak">
		<!-- Used to insert descriptions from adding a pre-defined task -->
		<?php foreach ( $item_types as $term ) : ?>
			<span id="term_desc_<?php echo (int) $term->term_id ?>"><?php echo esc_html( $term->description ) ?></span>
		<?php endforeach ?>
	</div>
<?php endif ?>


<?php
	$num_posts = wp_count_posts( SI_Invoice::POST_TYPE );
	$num_posts->{'auto-draft'} = 0; // remove auto-drafts
	$total_posts = array_sum( (array) $num_posts );
	if ( $total_posts >= 10 && apply_filters( 'show_upgrade_messaging', true ) ) {
		$class         = 'upgrade_message';
		$message_src   = SI_RESOURCES . 'admin/img/sprout/yipee.png';
		$message_style = 'float: left;margin-top: 0px;margin-right: 8px;z-index: auto;padding: 0 10px;';
		$message       = sprintf(
							// translators: 1: image source and style, 2:style, 3: total posts, 4: closing strong tag, 5: upgrade link, 6: closing a tag, 7: opening a tag, 8: closing a tag with break, 9: closing small tag.
							esc_html__(
								'%1$s%2$sCongrats on your %3$s Invoice!%4$s Please consider supporting the future of Sprout Invoices by purchasing a %5$sdiscounted pro license%6$s' .
								'and/or writing a %7$spositive 5 &#9733; review%8$sSprout Invoices Team%9$s',
								'sprout-invoices'
							),
							'<img class="header_sa_logo" src=" ' . esc_attr( $message_src ) . '" height="64" width="auto" style="' . esc_attr( $message_style ) . '"/>',
							'<strong style="font-size: 1.3em;margin-bottom: 5px;display: block;">',
							esc_html( self::number_ordinal_suffix( $total_posts ) ),
							'</strong>',
							'<a href="' . esc_url( si_get_purchase_link() ) . '%s">',
							'</a>',
							'<a href="http://wordpress.org/support/view/plugin-reviews/sprout-invoices?filter=5">',
							'</a>.<br/><small>',
							' </small>'
						);
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), wp_kses_post( $message ) );
	}
?>

<?php
if ( ! defined( 'ABSPATH' ) ) exit;
do_action( 'sprout_settings_messages' ); ?>

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
							<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Publish', 'default' ) // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch -- Reusing WordPress core string translation ?>" />
							<?php submit_button( esc_html__( 'Save', 'sprout-invoices' ), 'primary', 'save', false, array( 'accesskey' => 'p' ) ); ?>
						<?php else : ?>
							<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Submit for Review', 'default' ) // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch -- Reusing WordPress core string translation ?>" />
							<?php submit_button( esc_html__( 'Submit for Review', 'sprout-invoices' ), 'primary', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
						<?php endif;
					} else { ?>
							<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Update', 'default' ) // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch -- Reusing WordPress core string translation ?>" />
							<input name="save" type="submit" class="button button-primary" id="save" accesskey="p" value="<?php esc_attr_e( 'Save', 'default' ) // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch -- Reusing WordPress core string translation ?>" />
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
							// translators: 1: image tag, 2: congrats text with invoice count, 3: supporting text, 4: purchase URL, 5: discounted pro license text, 6: review intro text, 7: review link text, 8: team name.
							'%1$s<strong style="font-size: 1.3em;margin-bottom: 5px;display: block;">%2$s</strong> %3$s<a href="%4$s">%5$s</a> %6$s<a href="https://wordpress.org/support/plugin/sprout-invoices/reviews/">%7$s</a>.<br/><small>%8$s</small>',
							'<img class="header_sa_logo" src="' . esc_attr( $message_src ) . '" height="64" width="auto" style="' . esc_attr( $message_style ) . '"/>',
							/* translators: %s: ordinal invoice count, e.g. "10th" */
							sprintf( esc_html__( 'Congrats on your %s Invoice!', 'sprout-invoices' ), esc_html( self::number_ordinal_suffix( $total_posts ) ) ),
							esc_html__( 'Please consider supporting the future of Sprout Invoices by purchasing a', 'sprout-invoices' ),
							esc_url( si_get_purchase_link() ),
							esc_html__( 'discounted pro license', 'sprout-invoices' ),
							esc_html__( 'and/or writing a', 'sprout-invoices' ),
							esc_html__( 'positive 5 &#9733; review', 'sprout-invoices' ),
							esc_html__( 'Sprout Invoices Team', 'sprout-invoices' )
						);
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), wp_kses_post( $message ) );
	}
?>

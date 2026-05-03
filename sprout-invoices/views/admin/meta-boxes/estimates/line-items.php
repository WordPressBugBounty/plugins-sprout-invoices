<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
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
				<span title="<?php esc_attr_e( 'Predefined line items can be created to help with estimate creation by adding default descriptions. This is a premium feature that will be added with a pro version upgrade.', 'sprout-invoices' ) ?>" class="helptip add_item_help"></span>
			<?php endif ?>

			<?php do_action( 'si_post_add_line_item' ) ?>

			<div id="estimate_status_updates" class="sticky_save">
				<div id="si-publishing-action">
					<?php
					$can_publish = current_user_can( get_post_type_object( $post->post_type )->cap->publish_posts );
					if ( 0 === $post->ID || 'auto-draft' === $status ) {
						if ( $can_publish ) :
					?>
							<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Publish', 'default' ); // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch -- Reusing WordPress core string translation ?>" />
							<?php submit_button( __( 'Save', 'sprout-invoices' ), 'primary', 'save', false, array( 'accesskey' => 'p' ) ); ?>
						<?php else : ?>
							<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Submit for Review', 'default' ); // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch -- Reusing WordPress core string translation ?>" />
							<?php submit_button( __( 'Submit for Review', 'sprout-invoices' ), 'primary', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
						<?php endif; ?>
					<?php
					} else {
					?>
							<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Update', 'default' ); // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch -- Reusing WordPress core string translation ?>" />
							<input name="save" type="submit" class="button button-primary" id="save" accesskey="p" value="<?php esc_attr_e( 'Save', 'default' ); // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch -- Reusing WordPress core string translation ?>" />
					<?php
					}
					?>
					<span class="spinner"></span>
				</div>
			</div>
		</div>

		<?php do_action( 'si_get_line_item_totals_section', $id ) ?>

	</div>
</div>

<?php
	$num_posts = wp_count_posts( SI_Estimate::POST_TYPE );
	$num_posts->{'auto-draft'} = 0; // remove auto-drafts
	$total_posts = array_sum( (array) $num_posts );
	if ( $total_posts >= 10 && apply_filters( 'show_upgrade_messaging', true ) ) {
		$class         = 'upgrade_message';
		$message_src   = SI_RESOURCES . 'admin/img/sprout/yipee.png';
		$message_style = 'float: left;margin-top: 0px;margin-right: 8px;z-index: auto;padding: 0 10px;';
		$message       = sprintf(
							// translators: 1: image tag, 2: congrats text with estimate count, 3: supporting text, 4: purchase URL, 5: discounted pro license text, 6: review intro text, 7: review link text, 8: team name.
							'%1$s<strong style="font-size: 1.3em;margin-bottom: 5px;display: block;">%2$s</strong> %3$s<a href="%4$s">%5$s</a> %6$s<a href="https://wordpress.org/support/plugin/sprout-invoices/reviews/">%7$s</a>.<br/><small>%8$s</small>',
							'<img class="header_sa_logo" src="' . esc_attr( $message_src ) . '" height="64" width="auto" style="' . esc_attr( $message_style ) . '"/>',
							/* translators: %s: ordinal estimate count, e.g. "10th" */
							sprintf( esc_html__( 'Congrats on your %s Estimate!', 'sprout-invoices' ), esc_html( self::number_ordinal_suffix( $total_posts ) ) ),
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

<dl id="history_list">

	<dt>
		<span class="history_status creation_event"><?php esc_html_e( 'Created', 'sprout-invoices' ) ?></span><br/>
		<span class="history_date"><?php echo esc_html( date_i18n( get_option( 'date_format' ).' @ '.get_option( 'time_format' ), strtotime( $post->post_date ), true ) ) ?></span>
	</dt>

	<dd><p>
		<?php if ( ! empty( $submission_fields ) ) :  ?>
			<?php if ( $invoice->get_client_id() ) :  ?>
				<?php
					printf(
					// translators: 1: opening a tag, 2: invoice client link, 3: closing href, 4: client name, 5: closing a tag.
					esc_html__( 'Submitted by %1$s%2$s%3$s%4$s', 'sprout-invoices' ),
					'<a href="',
					esc_url( get_edit_post_link( $invoice->get_client_id() ) ),
					'">',
					esc_html( get_the_title( $invoice->get_client_id() ) ),
					'</a>'
					)
				?>
			<?php else : ?>
				<?php esc_html_e( 'Submitted', 'sprout-invoices' ) ?>
			<?php endif ?>
		<?php elseif ( is_a( $post, 'WP_Post' ) ) : ?>
			<?php $user = get_userdata( $post->post_author ) ?>
				<?php if ( is_a( $user, 'WP_User' ) ) : ?>
					<?php
						printf(
						// translators: 1: users WP display name.
						esc_html__( 'Added by %s', 'sprout-invoices' ),
						esc_html( $user->display_name )
						)
					?>
				<?php endif ?>
			<?php else : ?>
			<?php esc_html_e( 'Added by SI', 'sprout-invoices' )  ?>
		<?php endif ?>
	</p></dd>

	<?php foreach ( $history as $item_id => $data ) :  ?>
		<dt>
			<span class="history_status <?php echo esc_attr( $data['status_type'] ); ?>"><?php echo esc_attr( $data['type'] ) ?></span><br/>
			<span class="history_date"><?php echo esc_html( date_i18n( get_option( 'date_format' ).' @ '.get_option( 'time_format' ), strtotime( $data['post_date'] ), true ) ) ?></span>
		</dt>
		<?php $wp_kses_history = array(
			'p' => array(),
			'b' => array()
		)
		?>
		<dd>
			<?php if ( $data['status_type'] == SI_Notifications::RECORD ) :  ?>
				<p>
					<?php echo esc_html( $data['update_title'] ); ?>
					<br/><a href="#TB_inline?width=600&height=380&inlineId=notification_message_<?php echo (int) $item_id; ?>" id="show_notification_tb_link_<?php echo (int) $item_id; ?>" class="thickbox si_tooltip notification_message" title="<?php esc_html_e( 'View Message', 'sprout-invoices' ) ?>"><?php esc_html_e( 'View Message', 'sprout-invoices' ) ?></a>
				</p>
				<div id="notification_message_<?php echo (int) $item_id; ?>" class="cloak">
					<?php echo wp_kses_post( wpautop( $data['content'] ) ) ?>
				</div>
			<?php elseif ( $data['status_type'] == SI_Importer::RECORD ) :  ?>
				<p>
					<?php echo esc_html( $data['update_title'] ); ?>
					<br/><a href="#TB_inline?width=600&height=380&inlineId=notification_message_<?php echo (int) $item_id ?>" id="show_notification_tb_link_<?php echo (int) $item_id ?>" class="thickbox si_tooltip notification_message" title="<?php esc_html_e( 'View Data', 'sprout-invoices' ) ?>"><?php esc_html_e( 'View Data', 'sprout-invoices' ) ?></a>
				</p>
				<div id="notification_message_<?php echo (int) $item_id ?>" class="cloak">
					<?php prp( json_decode( $data['content'] ) ); ?>
				</div>
			<?php elseif ( $data['status_type'] == SI_Invoices::VIEWED_STATUS_UPDATE ) : ?>
				<p>
					<?php echo esc_html( $data['update_title'] ); ?>
				</p>
			<?php else : ?>
				<?php echo wp_kses_post( wpautop( $data['content'] ) ) ?>
			<?php endif ?>

		</dd>
	<?php endforeach ?>

</dl>

<div id="private_note_wrap">
	<p>
		<textarea id="private_note" name="private_note" class="clearfix" disabled="disabled" style="height:40px;"></textarea>
		<?php if (  apply_filters( 'show_upgrade_messaging', true ) ) {
			printf(
			// translators: 1: span tags
			esc_html__( '%1$s', 'sprout-invoices' ),
			'<span class="helptip" title="Upgrade for Private Notes"></span>',
			esc_html( si_get_purchase_link() )
			);
		} ?>
	</p>
</div>

<dl id="history_list">

	<dt>
		<span class="history_status creation_event"><?php esc_html_e( 'Created', 'sprout-invoices' ) ?></span><br/>
		<span class="history_date"><?php echo esc_html( date_i18n( get_option( 'date_format' ).' @ '.get_option( 'time_format' ), strtotime( $post->post_date ), true ) ) ?></span>
	</dt>

	<dd><p>
		<?php if ( ! empty( $submission_fields ) ) :  ?>
			<?php if ( $estimate->get_client_id() ) :  ?>
				<?php
					printf(
					// translators: 1: opening a tag, 2: url to estimate, 3: closing href, 4: estimate title, 5: closing a tag.
					esc_html__( 'Submitted by %1$s%2$s%3$s%4$s%5$s', 'sprout-invoices' ),
					'<a href="',
					esc_url( get_edit_post_link( $estimate->get_client_id() ) ),
					'">',
					esc_html( get_the_title( $estimate->get_client_id() ) ),
					'</a>'
				)
				?>
			<?php else : ?>
				<?php esc_html_e( 'Submitted', 'sprout-invoices' ) ?>
			<?php endif ?>
		<?php elseif ( is_a( $post, 'WP_Post' ) ) : ?>
			<?php $user = get_userdata( $post->post_author ) ?>
			<?php
				printf(
					// translators: 1: user display name.
					esc_html__( 'Added by %s', 'sprout-invoices' ),
					esc_html( $user->display_name )
				)
			?>
		<?php else : ?>
			<?php esc_html_e( 'Added by SI', 'sprout-invoices' )  ?>
		<?php endif ?>
	</p></dd>

	<?php foreach ( $history as $item_id => $data ) :  ?>
		<dt class="record record-<?php echo esc_attr( $item_id ) ?>">
			<span class="history_deletion"><button data-id="<?php echo esc_attr( $item_id ) ?>" class="delete_record del_button">X</button></span>
			<span class="history_status <?php echo esc_attr( $data['status_type'] ); ?>"><?php echo esc_html( $data['type'] ) ?></span><br/>
			<span class="history_date"><?php echo esc_html( date_i18n( get_option( 'date_format' ).' @ '.get_option( 'time_format' ), strtotime( $data['post_date'] ), true ) ) ?></span>
		</dt>
		<?php $wp_kses_history = array(
			'p' => array(),
			'b' => array(),
		)
		?>
		<dd class="record record-<?php echo esc_attr( $item_id ) ?>">
			<?php if ( $data['status_type'] == SI_Notifications::RECORD ) :  ?>
				<p>
					<?php echo esc_html( $data['update_title'] ) ?>
					<br/><a href="#TB_inline?width=600&height=380&inlineId=notification_message_<?php echo (int) $item_id ?>" id="show_notification_tb_link_<?php echo (int) $item_id ?>" class="thickbox si_tooltip notification_message" title="<?php esc_html_e( 'View Message', 'sprout-invoices' ) ?>"><?php esc_html_e( 'View Message', 'sprout-invoices' ) ?></a>
				</p>
				<div id="notification_message_<?php echo (int) $item_id ?>" class="cloak">
					<?php echo wp_kses( wpautop( stripslashes_from_strings_only( $data['content'] ) ), $wp_kses_history ) ?>
				</div>
			<?php elseif ( $data['status_type'] == SI_Importer::RECORD ) :  ?>
				<p>
					<?php echo esc_html( $data['update_title'] ) ?>
					<br/><a href="#TB_inline?width=600&height=380&inlineId=notification_message_<?php echo (int) $item_id ?>" id="show_notification_tb_link_<?php echo (int) $item_id ?>" class="thickbox si_tooltip notification_message" title="<?php esc_html_e( 'View Data', 'sprout-invoices' ) ?>"><?php esc_html_e( 'View Data', 'sprout-invoices' ) ?></a>
				</p>
				<div id="notification_message_<?php echo (int) $item_id ?>" class="cloak">
					<?php prp( json_decode( stripslashes_from_strings_only( $data['content'] ) ) ); ?>
				</div>
			<?php elseif ( $data['status_type'] == SI_Estimates::VIEWED_STATUS_UPDATE ) : ?>
				<p>
					<?php echo esc_html( $data['update_title'] ) ?>
				</p>
			<?php else : ?>
				<?php echo wp_kses_post( wpautop( $data['content'] ) ); ?>
			<?php endif ?>

		</dd>
	<?php endforeach ?>
</dl>

<div id="private_note_wrap">
	<p>
		<textarea id="private_note" name="private_note" class="clearfix" disabled="disabled" style="height:40px;"></textarea>
		<?php if (  apply_filters( 'show_upgrade_messaging', true ) ) {
			printf(
			// translators: 1: opening span tag, 2: closing span tag.
			esc_html__( '%1$sUpgrade for Private Notes%2$s', 'sprout-invoices' ),
			'<span class="helptip" title="',
			'"></span>',
			esc_url( si_get_purchase_link() )
			);
		} ?>
	</p>
</div>


<?php if ( ! empty( $submission_fields ) ) :  ?>
	<div id="submission_fields_wrap">
		<h3><?php esc_html_e( 'Form Submission', 'sprout-invoices' ) ?></h3>
		<dl>
			<?php foreach ( $submission_fields as $key => $value ) :  ?>
				<?php if ( isset( $value['data'] ) ) :  ?>
					<?php if ( $value['data']['label'] && $value['data']['type'] != 'hidden' ) :  ?>
						<dt><?php echo esc_html( $value['data']['label'] ) ?></dt>
						<?php if ( is_numeric( $value['value'] ) && strpos( $value['data']['label'], esc_html__( 'Type', 'sprout-invoices' ) ) !== false ) :  ?>
							<dd><p><?php
									$term = get_term_by( 'id', $value['value'], SI_Estimate::PROJECT_TAXONOMY );
							if ( ! is_wp_error( $term ) ) {
								esc_html_e( $term->name, 'sprout-invoices' );
							}
									?></p></dd>
						<?php else : ?>
							<dd><?php echo esc_html( wpautop( $value['value'] ) ) ?></dd>
						<?php endif ?>
					<?php endif ?>
				<?php endif ?>
			<?php endforeach ?>
		</dl>
	</div>

	<?php $media = get_attached_media( '' ); ?>
	<?php if ( ! empty( $media ) ) :  ?>
		<p>
			<h3><?php esc_html_e( 'Attachments', 'sprout-invoices' ) ?></h3>
			<ul>
				<?php foreach ( $media as $id => $mpost ) :  ?>
					<?php  $img = wp_get_attachment_image_src( $id, 'thumbnail', true ); ?>
					<li><a href="<?php echo esc_url( wp_get_attachment_url( $id ) ) ?>" target="_blank" class="attachment_url"><img src="<?php echo esc_url( $img[0] ); ?>" alt="<?php esc_attr( $mpost->post_name ) ?>"></a></li>
				<?php endforeach ?>
			</ul>
		</p>
	<?php endif ?>

<?php endif ?>

<?php
	$current_status = '';
	$disabled = ''; ?>

<div id="subject_header" class="clearfix">
	<div id="subject_header_actions" class="clearfix">
		<div id="subject_input_wrap">
			<?php $title = ( $status != 'auto-draft' && get_the_title( $id ) != esc_html__( 'Auto Draft' ) ) ? get_the_title( $id ) : '' ; ?>
			<input type="text" name="subject" value="<?php echo esc_attr( $title ); ?>" placeholder="<?php esc_html_e( 'Subject...', 'sprout-invoices' ) ?>">
		</div>

		<?php if ( $statuses ) : ?>
			<div id="quick_links">
				
				<?php do_action( 'si_invoice_status_update', $id ) ?>

				<a href="#send_invoice" id="send_doc_quick_link" class="send si_tooltip button" title="<?php esc_html_e( 'Send this invoice', 'sprout-invoices' ) ?>"><span>&nbsp;</span></a>
				
				<a href="<?php echo esc_url( self::get_clone_post_url( $id ) ) ?>" id="duplicate_invoice_quick_link" class="duplicate si_tooltip button" title="<?php esc_html_e( 'Duplicate this invoice', 'sprout-invoices' ) ?>"><span>&nbsp;</span></a>

			</div>
		<?php endif ?>
	</div>


	<div id="edit-slug-box" class="clearfix">
		<b><?php esc_html_e( 'Permalink', 'sprout-invoices' ) ?></b>
		<span id="permalink-select" tabindex="-1"><?php echo esc_url( get_permalink( $id ) ) ?></span>
		<span id="view-post-btn"><a href="<?php echo esc_url( get_permalink( $id ) ) ?>" class="button button-small"><?php esc_html_e( 'View Invoice', 'sprout-invoices' ) ?></a></span>
		<?php if (  apply_filters( 'show_upgrade_messaging', true ) ) {
			printf(
			// translators: 1: span tag. 
			esc_html__( '%1$s', 'sprout-invoices' ),
			'<span class="helptip" title="Upgrade for Private URLs"></span>',
			esc_url( si_get_purchase_link() ) 
			);
		} ?>
	</div>


</div>

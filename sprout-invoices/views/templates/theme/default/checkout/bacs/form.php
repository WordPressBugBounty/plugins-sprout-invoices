<div id="bacs_info_checkout_wrap" class="paytype inactive">
	<p class="description"><?php echo wp_kses_post( $bacs_info ); ?></p>
	<form action="<?php echo esc_attr( si_get_payment_link( get_the_ID(), $type ) ); ?>" autocomplete="on" method="post" accept-charset="utf-8" class="sa-form sa-form-aligned">
		<div class="row">
			<?php sa_form_fields( $bacs_fields, 'bacs' ); ?>
		</div>
		<?php do_action( 'si_bacs_form_controls', $checkout ); ?>
		<?php do_action( 'si_bacs_payment_controls', $checkout ); ?>
		<input type="hidden" name="<?php echo esc_attr( SI_Checkouts::CHECKOUT_ACTION ); ?>" value="<?php echo esc_attr( SI_Checkouts::PAYMENT_PAGE ); ?>" />
		<button type="submit" class="button"><?php esc_html_e( 'Submit BACS Information', 'sprout-invoices' ); ?></button>
	</form>
</div><!-- #credit_card_checkout_wrap -->

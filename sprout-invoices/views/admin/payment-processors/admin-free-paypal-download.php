<h1><?php esc_html_e( 'Download the Free PayPal Integration!', 'sprout-invoices' ) ?></h1>

<div class="single_addon_wrap">
	<article class="type_addon marketplace_addon">
		<div class="section">
			<div class="img_wrap">
				<span class="bundled_addon"><?php esc_html_e( 'Free Download!', 'sprout-invoices' ) ?></span>
				<a href="<?php sa_link( 'https://sproutinvoices.com/marketplace/paypal-payments-express-checkout/' ) ?>" class="si-button" target="_blank"><img src="<?php echo esc_attr( SI_RESOURCES . 'admin/img/Paypal-EC.png' ) ?>" /></a>
			</div>
			<div class="info">
				<strong><?php esc_html_e( 'PayPal Payments with Express Checkout', 'sprout-invoices' ) ?></strong>							
				<div class="addon_description">
					<?php 
						printf( 
						esc_html__( 'Accept Paypal Express payments for your invoices. Including invoice deposits.', 'sprout-invoices' ), esc_url( si_get_sa_link( 'https://sproutinvoices.com/marketplace/advanced-form-integration-gravity-ninja-forms/' ) ) ) ?>
					<div class="addon_info_link">
						<a href="<?php sa_link( 'https://sproutinvoices.com/marketplace/paypal-payments-express-checkout/' ) ?>" class="si-button" target="_blank"><?php esc_html_e( 'Learn More', 'sprout-invoices' ) ?></a>
					</div>
				</div>
			</div>
		</div>
	</article>
</div><!-- #addons_admin-->

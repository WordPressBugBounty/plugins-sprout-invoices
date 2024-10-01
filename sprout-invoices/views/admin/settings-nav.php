<div id="si-page-header" class="si-has-logo">
	<h1><?php esc_html_e( 'Sprout Invoices', 'sprout-invoices' ); ?></h1>
		<a class="si-action-button button button-primary" href="post-new.php?post_type=sa_invoice" >Create Invoice</a>
		<a class="si-action-button button button-primary" href="post-new.php?post_type=sa_estimate" >Create Estimate</a>
		<a
		class="si-action-button button si-buy-pro"
		target="_blank"
		href="<?php echo esc_url( 'https://sproutinvoices.com/pricing/' ); ?>"
		alt="<?php esc_attr_e( 'Get Pro', 'sprout-invoices' ); ?>"
	>
		<?php esc_html_e( 'Get Pro', 'sprout-invoices' ); ?>
	</a>
</div>

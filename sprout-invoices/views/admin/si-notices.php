<?php
	$enabled = SI_Payment_Processors::enabled_processors();
?>
<?php if ( SA_Addons::is_enabled( 'sprout-invoices-add-on-recurring-invoices' ) && SA_Addons::is_enabled( 'sprout-invoices-add-on-recurring-aka-subscription-payments' ) ) : ?>
	<div class="notice notice-warning">
		<p>
			<?php
				esc_html_e(
					'You currently have both Recurring Invoices and Recurring Subscription Payments enabled. Having both may cause issues on your site. We recommend disabling one of these add-ons to avoid any issues. We recommend keeping Recurring Invoices enabled and disabling Recurring Subscription Payments.',
					'sprout-invoices'
				);
			?>
		</p>
	</div>
<?php endif; ?>
<?php if ( ! SI_RECOMMENDED_PHP_VERSION ) : ?>
	<div class="notice notice-error">
		<p>
			<?php esc_html_e( 'We noticed your PHP version is lower than 7.4. We recommend updating PHP to at least 7.4 to ensure full compatibility with Sprout Invoices.', 'sprout-invoices' ); ?>
		</p>
	</div>
<?php endif; ?>

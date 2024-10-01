<section class="row" id="paybar">
	<div class="inner">
		<?php do_action( 'si_default_theme_inner_paybar' ) ?>

		<?php
			$time_left = si_get_invoice_due_date() - current_time( 'timestamp' );
			$days_left = round( (($time_left / 24) / 60) / 60 );
				?>
		<?php if ( $time_left > 0 ) :  ?>

			<?php if ( 1 === $days_left ) :  ?>

				<?php
					printf(
					// translators: 1: opening strong tag, 2: invoice balence, 3: closing strong tag.
					esc_html__( 'Balance of %1$s%2$s%3$s is Due', 'sprout-invoices' ),
					'<strong>',
					esc_html( sa_get_formatted_money( si_get_invoice_balance() ) ),
					'</strong>',
					esc_html( $days_left )
					);
				?>

			<?php else : ?>

				<?php if ( si_has_invoice_deposit( get_the_id(), true ) ) : ?>
					<?php
						printf(
						// translators: 1: open strong tag, 2: invoice balence, 3: closing strong tag, 4: days left to pay, 5: invoice deposit.
						esc_html__( 'Balance of %1$s%2$s%3$s Due in %1$s%4$s Days%3$s & Deposit of %1$s%5$s%3$s Due %1$sNow%3$s', 'sprout-invoices' ),
						'<strong>',
						esc_html( sa_get_formatted_money( si_get_invoice_balance() ) ),
						'</strong>',
						esc_html( $days_left ),
						esc_html( sa_get_formatted_money( si_get_invoice_deposit( get_the_id(), true ) ) )
						);
					?>
				<?php else : ?>
					<?php
						printf(
						// translators: 1: open strong tag, 2: invoice balence, 3: closing strong tag, 4: days left to pay.
						esc_html__( 'Balance of %1$s%2$s%3$s Due in %1$s%4$s Days%3$s', 'sprout-invoices' ),
						'<strong>',
						esc_html( sa_get_formatted_money( si_get_invoice_balance() ) ),
						'</strong>',
						esc_html( $days_left )
						);
					?>
				<?php endif; ?>

			<?php endif ?>

		<?php else : ?>

			<?php
				printf(
				// translators: 1: open strong tag, 2: invoice balence, 3: closing strong tag.
				esc_html__( 'Balance of %1$s%2$s%3$s is %1$sOverdue%3$s', 'sprout-invoices' ),
				'<strong>',
				esc_html( sa_get_formatted_money( si_get_invoice_balance() ) ),
				'</strong>'
				);
			?>

		<?php endif ?>

		<?php do_action( 'si_default_theme_pre_payment_button' ) ?>

		<?php do_action( 'si_pdf_button' ) ?>

		<?php do_action( 'si_signature_button' ) ?>

		<?php if ( si_has_invoice_deposit( get_the_id(), true ) ) : ?>
			<a class="open button" href="#payment"><?php esc_html_e( 'Make a Deposit Payment', 'sprout-invoices' ) ?></a>
		<?php else : ?>
			<a class="open button" href="#payment"><?php esc_html_e( 'Make a Payment', 'sprout-invoices' ) ?></a>
		<?php endif; ?>

		<?php do_action( 'si_default_theme_payment_button' ) ?>
	</div>
</section>

<section class="panel closed" id="payment">
	<a class="close" href="#payment">
		<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
			<path d="M405 136.798L375.202 107 256 226.202 136.798 107 107 136.798 226.202 256 107 375.202 136.798 405 256 285.798 375.202 405 405 375.202 285.798 256z"/>
		</svg>
	</a>

	<div class="inner">

		<h2><?php esc_html_e( 'Make a Payment', 'sprout-invoices' ) ?></h2>

		<?php $payment_options = si_payment_options(); ?>

		<?php do_action( 'si_default_theme_pre_payment_options' ) ?>

		<?php if ( count( $payment_options ) === 0 ) : ?>

			<p class="make_payment_desc"><?php esc_html_e( 'Oh no! So sorry. There are no payment options available for you to make a payment. Please let me know so I can figure out why.', 'sprout-invoices' ) ?></p>

			<?php do_action( 'si_default_theme_no_payment_options_desc' ) ?>

		<?php else : ?>

			<?php if ( count( $payment_options ) > 1 ) : ?>
				<p class="make_payment_desc"><?php esc_html_e( 'Please select your payment type and enter your payment information to pay this invoice. A receipt for your records will be sent to you. Thank you very much!', 'sprout-invoices' ) ?></p>
			<?php else : ?>
				<?php if ( 'paypal' === key( $payment_options ) ) :  ?>
					<p class="make_payment_desc"><?php esc_html_e( 'Select the PayPal button below to be redirected for payment. A receipt for your records will be sent to you. Thank you very much!', 'sprout-invoices' ) ?></p>
				<?php else : ?>
					<p class="make_payment_desc"><?php esc_html_e( 'Please enter your payment information to pay this invoice. A receipt for your records will be sent to you. Thank you very much!', 'sprout-invoices' ) ?></p>
				<?php endif ?>
			<?php endif; ?>

			<?php do_action( 'si_default_theme_payment_options_desc' ) ?>

			<div class="row toggles<?php if ( count( $payment_options ) < 2 ) { echo ' single_payment_option'; } ?>">
				<?php foreach ( $payment_options as $slug => $options ) : ?>
					<?php if ( isset( $options['purchase_button_callback'] ) ) : ?>
						<?php call_user_func_array( $options['purchase_button_callback'], array( get_the_ID() ) ) ?>
					<?php else : ?>
						<a href="<?php si_payment_link( get_the_ID(), $slug ) ?>" data-slug="<?php esc_attr_e( $slug ) ?>" data-id="<?php the_ID() ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( SI_Controller::NONCE ) ) ?>" class="payment_option toggle <?php if ( si_is_cc_processor( $slug ) ) { echo 'cc_processor'; } ?> <?php echo esc_attr( $slug ) ?>">
							<span class="process_label"><?php esc_attr_e( $options['label'] , 'sprout-invoices' ) ?></span>
						</a>
					<?php endif ?>
					<?php do_action( 'si_default_theme_payment_option_desc', $slug ) ?>
				<?php endforeach ?>
			</div>
			<?php do_action( 'si_default_theme_payment_options' ) ?>
		<?php endif; ?>

		<div class="row paytypes">
			<?php do_action( 'si_payments_pane' ); ?>
		</div>

		<?php do_action( 'si_default_theme_pre_payment_panes' ) ?>

	</div>
</section>

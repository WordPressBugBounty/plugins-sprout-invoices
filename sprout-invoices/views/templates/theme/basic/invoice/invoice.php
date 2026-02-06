<?php
/**
 * Sprout Invoices Basic Invoice Template
 *
 * This template is intended for customers to copy into their theme override folder:
 * /wp-content/theme/sa_templates/invoices
 * and customize as needed. Comments are provided to help users understand each section.
 *
 * @package Sprout_Invoices
 */

// Enqueue Roboto font for invoice styling.
wp_enqueue_style( 'roboto-css', 'https://fonts.googleapis.com/css?family=Roboto:400,500,700,900', false, false );
// Action hook before invoice view is rendered. Useful for injecting custom logic or assets.
do_action( 'pre_si_invoice_view' ); ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<html>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
		<title><?php the_title(); ?></title>
		<link rel="profile" href="http://gmpg.org/xfn/11" />

		<?php
		/**
		 * Outputs additional <head> content for Sprout Invoices.
		 * @param bool SI_Header is used over the WordPress header as that is removed.
		 */
		si_head( true );
		?>

		<meta name="robots" content="noindex, nofollow" />
	</head>

	<body id="invoice" <?php body_class( 'si_basic_theme' ); ?>>

		<header class="row" id="header">
			<div class="inner">

				<div class="row messages">
					<?php
					// Display system messages (errors, notices, etc.) for the invoice view.
					si_display_messages();
					?>
				</div>

				<div class="row intro">

					<div id="logo">
						<?php
						// Display custom logo if set in theme mod, otherwise use default invoice logo.
						if ( get_theme_mod( 'si_logo' ) ) : ?>
							<img src="<?php echo esc_url( get_theme_mod( 'si_logo', si_doc_header_logo_url() ) ); ?>" alt="document logo" >
						<?php else : ?>
							<img src="<?php echo esc_url( si_doc_header_logo_url() ); ?>" alt="document logo" >
						<?php endif; ?>
					</div><!-- #logo -->

					<div id="title">

						<div class="current_status">
							<?php
							/**
							 * Display current invoice status.
							 * Statuses: write-off, paid, temp, payment pending.
							 */
							if ( 'write-off' === si_get_invoice_status() ) : ?>
								<span><?php esc_html_e( 'Void', 'sprout-invoices' ); ?></span>
							<?php elseif ( ! si_get_invoice_balance() ) : ?>
								<span><?php esc_html_e( 'Paid', 'sprout-invoices' ); ?></span>
							<?php elseif ( 'temp' === si_get_invoice_status() ) : ?>
								<span><?php esc_html_e( 'Not Yet Published', 'sprout-invoices' ); ?></span>
							<?php else : ?>
								<span><?php esc_html_e( 'Payment Pending', 'sprout-invoices' ); ?></span>
							<?php endif; ?>
						</div>

						<h1><?php the_title(); ?></h1>

					</div><!-- #title -->

				</div>
			</div>
		</header>

		<section class="row" id="intro">
			<div class="inner">
				<div id="fromto_info" class="column">

					<div id="company_info">
						<h2 class="from_to_title">
							<?php
							// Display company name and address info.
							printf(
							// translators: 1: open span tag, 2: company name, 3: closing span tag.
							esc_html__( '%1$sFrom:%2$s %3$s', 'sprout-invoices' ),
							'<span><b>',
							esc_html( si_get_company_name() ),
							'</b></span>'
							);
							?>
						</h2>
						<?php
						// Output SI_Client address block in the Sprout Customer record.
						si_doc_address();
						?>

						<?php if ( si_get_company_phone() ) : ?>
							<div class="company_info">
								<?php
								// Display company phone number if available.
								printf(
								// translators: 1: phone number
								esc_html__( 'Phone: %1$s', 'sprout-invoices' ),
								esc_html( si_get_company_phone() )
								);
								?>
							</div>
						<?php endif ?>

						<?php if ( si_get_company_fax() ) : ?>
							<div class="company_info">
								<?php
								// Display company fax number if available.
								printf(
								// translators: 1: company fax number
								esc_html__( 'Fax: %1$s', 'sprout-invoices' ),
								esc_html( si_get_company_fax() )
								);
								?>
							</div>
						<?php endif ?>

						<?php if ( si_get_company_email() ) : ?>
							<div class="company_info">
								<?php
								// Display company email if available.
								printf(
								// translators: 1: company email
								esc_html__( '%1$s', 'sprout-invoices' ),
								esc_html( si_get_company_email() )
								);
								?>
							</div>
						<?php endif ?>
					</div><!-- #company_info -->

					<?php if ( si_get_invoice_client_id() ) : ?>

						<div id="client_info">

							<h2 class="from_to_title">
								<?php
								// Display client name and address info.
								printf(
								// translators: 1: open span tag, 2: client name, 3: closing span tag.
								esc_html__( '%1$sTo:%2$s %3$s', 'sprout-invoices' ),
								'<span><b>',
								esc_html( get_the_title( si_get_invoice_client_id() ) ),
								'</b></span>'
								);
								?>
							</h2>
							<?php
							// Output client address block.
							si_client_address( si_get_invoice_client_id() );
							?>

							<?php if ( si_get_client_phone() ) : ?>
								<div class="company_info">
									<?php
									// Display client phone number if available.
									printf(
									// translators: 1: client phone
									esc_html__( 'Phone: %1$s', 'sprout-invoices' ),
									esc_html( si_get_client_phone() )
									);
									?>
								</div>
							<?php endif ?>

							<?php if ( si_get_client_fax() ) : ?>
								<div class="company_info">
									<?php
									// Display client fax number if available.
									printf(
									// translators: 1: client fax
									esc_html__( 'Fax: %1$s', 'sprout-invoices' ),
									esc_html( si_get_client_fax() )
									);
									?>
								</div>
							<?php endif ?>

							<?php
							// Action hook for adding custom client address details.
							do_action( 'si_document_client_addy' );
							?>

						</div><!-- #client_info -->

					<?php endif ?>

					<?php
					// Action hook for adding vCards or other contact info blocks.
					do_action( 'si_document_vcards' );
					?>

				</div>

				<div id="invoice_info" class="column">

					<div class="invoice_info">
						<?php
						// Display invoice number.
						printf(
						// translators: 1: invoice number text, 2: invoice number
						'<span class="info">%1$s</span> %2$s',
						esc_html__( 'Invoice #', 'sprout-invoices' ),
						esc_html( si_get_invoice_id() )
						);
						?>
					</div>
					<?php if ( si_get_invoice_po_number() ) : ?>
						<div class="invoice_info">
							<?php
							// Display PO number if available.
							printf(
							// translators: 1: po number text, 2: po number
							'<span class="info">%1$s</span> %2$s',
							esc_html__( 'PO #', 'sprout-invoices' ),
							esc_html( si_get_invoice_po_number() )
							);
							?>
						</div>
					<?php endif ?>

					<div class="invoice_info">
						<?php
						// Display invoice issue date.
						printf(
						// translators: 1: invoice issue date text, 2: invoice date
						'<span class="info">%1$s</span> %2$s',
						esc_html__( 'Issued on', 'sprout-invoices' ),
						esc_html( date_i18n( get_option( 'date_format' ), si_get_invoice_issue_date() ) )
						);
						?>
					</div>
					<div class="invoice_info">
						<?php
						// Display invoice due date.
						printf(
						// translators: 1: invoice due date text, 2: invoice due date
						'<span class="info">%1$s</span> %2$s',
						esc_html__( 'Due on', 'sprout-invoices' ),
						esc_html( date_i18n( get_option( 'date_format' ), si_get_invoice_due_date() ) )
						);
						?>
					</div>

				</div>

			</div>
		</section>

		<section class="row" id="">
			<div class="inner">
				<div id="invoice_info" class="column">
				<?php
				// Display deposit due if invoice has a deposit set.
				if ( si_has_invoice_deposit( get_the_id(), true ) ) : ?>
						<div id="deposit_total" class="invoice_info">
							<?php
							// Display deposit total amount.
							printf(
							// translators: 1: deposit total text, 2: deposit total
							'<span class="info">%1$s</span> %2$s',
							esc_html__( 'Deposit Due', 'sprout-invoices' ),
							esc_html( sa_get_formatted_money( si_get_invoice_deposit() ) )
							);
							?>
						</div>
					<?php endif ?>

					<div id="total_due" class="invoice_info">
						<?php
						// Display balance due for invoice.
						printf(
						// translators: 1: balance due text, 2: balance total
						'<span class="info">%1$s</span> <span class="total">%2$s</span>',
						esc_html__( 'Balance Due', 'sprout-invoices' ),
						esc_html( sa_get_formatted_money( si_get_invoice_balance() ) )
						);
						?>
					</div>

					<?php
					// Display minimum payment due if payment terms are set and less than total balance.
					if ( function_exists( 'si_get_invoice_payment_terms_amount_due' ) && 0 < si_get_invoice_payment_terms_amount_due() && si_get_invoice_payment_terms_amount_due() < si_get_invoice_balance() ) : ?>
						<div id="total_due" class="invoice_info">
							<?php
							printf(
							// translators: 1: minimum payment due text, 2: minimum payment total
							'<span class="info">%1$s</span> <span class="total">%2$s</span>',
							esc_html__( 'Minimum Payment Due', 'sprout-invoices' ),
							esc_html( sa_get_formatted_money( si_get_invoice_payment_terms_amount_due() ) )
							)
							?>
						</div>
					<?php endif ?>
				</div>
			</div>
		</section>

		<section class="row" id="details">
			<div class="inner">
				<div class="row item">
					<?php
					// Action hook for adding more invoice details (custom fields, etc.).
					do_action( 'si_document_more_details' );
					?>
				</div>
			</div>
		</section>

		<?php
		// Action hook for rendering invoice line items (products/services).
		do_action( 'si_doc_line_items', get_the_id() );
		?>

		<section class="row" id="signature">
			<div class="inner">
				<div class="row item">
					<?php
					// Action hook for signature section (digital signature, etc.).
					do_action( 'si_signature_section' );
					?>
				</div>
			</div>
		</section>

		<section class="row" id="notes">
			<div class="inner">
				<?php if ( strlen( si_get_invoice_notes() ) > 1 ) : ?>
					<div class="row title">
						<h2><?php esc_html_e( 'Info &amp; Notes', 'sprout-invoices' ); ?></h2>
					</div>
					<div class="row content">
						<?php
						// Output invoice notes entered by admin.
						si_invoice_notes();
						?>
					</div>
				<?php endif ?>

				<?php if ( strlen( si_get_invoice_terms() ) > 1 ) : ?>

					<div class="row title">
						<h2><?php esc_html_e( 'Terms &amp; Conditions', 'sprout-invoices' ); ?></h2>
					</div>
					<div class="row content">
						<?php
						// Output invoice terms entered by admin.
						si_invoice_terms();
						?>
					</div>
				<?php endif; ?>
			</div>

			<div class="inner">
				<div class="row item">
					<div class="history_message">
						<?php
						// Display last updated message if invoice was recently updated.
						if ( isset( $last_updated ) && si_doc_last_updated() === $last_updated ) : ?>
							<?php $days_since = si_get_days_ago( $last_updated ); ?>
							<?php if ( 2 > $days_since ) : ?>
							<a class="open" href="#history">
								<?php
								printf(
								esc_html__( 'Recently Updated', 'sprout-invoices' ),
								esc_html( $days_since )
								);
								?>
							</a>
							<?php else : ?>
								<a class="open" href="#history">
									<?php
									printf(
									// translators: 1: days since last update
									esc_html__( 'Updated %1$s Days Ago', 'sprout-invoices' ),
									esc_html( $days_since )
									);
									?>
								</a>
							<?php endif ?>
						<?php endif ?>
					</div>
				</div>
			</div>

		</section>

		<?php
		// Show payment options if there is a balance due, otherwise show payment bar with status.
		if ( si_get_invoice_balance() ) : ?>
			<?php
			// Output payment options (PayPal, Stripe, etc.).
			si_payment_options_view();
			?>
		<?php else : ?>
			<section class="row" id="paybar">
				<div class="inner">
					<?php
					// Action hook for customizing paybar section.
					do_action( 'si_default_theme_inner_paybar' );
					?>

					<?php if ( 'complete' === si_get_invoice_status() ) : ?>
						<?php
						// Show paid status and total paid amount.
						printf( 'Total of <strong>%1$s</strong> has been <strong>Paid</strong>', esc_html( sa_get_formatted_money( si_get_invoice_total() ) ) );
						?>
					<?php else : ?>
						<?php
						// Show reconciled status and total reconciled amount.
						printf( 'Total of <strong>%1$s</strong> has been <strong>Reconciled</strong>', esc_html( sa_get_formatted_money( si_get_invoice_total() ) ) );
						?>
					<?php endif ?>

					<?php
					// Action hooks for customizing payment buttons and PDF/signature options.
					do_action( 'si_default_theme_pre_no_payment_button' );
					do_action( 'si_pdf_button' );
					do_action( 'si_signature_button' );
					do_action( 'si_default_theme_no_payment_button' );
					?>
				</div>
			</section>

		<?php endif ?>

		<?php
		// Show invoice history panel if enabled via filter.
		if ( apply_filters( 'si_show_invoice_history', true ) ) : ?>
			<section class="panel closed" id="history">
				<a class="close" href="#history">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
					<path d="M405 136.798L375.202 107 256 226.202 136.798 107 107 136.798 226.202 256 107 375.202 136.798 405 256 285.798 375.202 405 405 375.202 285.798 256z"/>
				</svg>
				</a>

				<div class="inner">
					<h2><?php esc_html_e( 'Invoice History', 'sprout-invoices' ); ?></h2>
					<div class="history">
						<?php
						// Loop through invoice history records and display each update/comment.
						foreach ( si_doc_history_records() as $item_id => $data ) : ?>
							<?php $days_since = (int) si_get_days_ago( strtotime( $data['post_date'] ) ); ?>
							<article class=" <?php echo esc_attr( $data['status_type'] ); ?>">
								<span class="posted">
									<?php
									// Display type of update (comment, status change, etc.) and when it occurred.
									$type = ( 'comment' === $data['status_type'] ) ? sprintf( esc_html__( 'Comment by %1$s ', 'sprout-invoices' ), $data['type'] ) : $data['type'] ;
									?>
									<?php if ( 0 === $days_since ) :  ?>
										<?php printf( '%1$s today', esc_html( $type ) ); ?>
									<?php elseif ( 2 > $days_since ) :  ?>
										<?php printf( '%1$s %2$s day ago', esc_html( $type ), esc_html( $days_since ) ); ?>
									<?php else : ?>
										<?php printf( '%1$s %2$s days ago', esc_html( $type ), esc_html( $days_since ) ); ?>
									<?php endif ?>
								</span>
								<?php
								$wp_kses_history = array(
									'p'   => array(),
									'b'   => array(),
									'img' => array(
										'src' => array(),
										'alt' => array(),
									),
								);
								?>
								<?php if ( SI_Notifications::RECORD === $data['status_type'] ) : ?>
									<p>
										<?php echo esc_html( $update_title ); ?>
										<br/><a href="#TB_inline?width=600&height=380&inlineId=notification_message_<?php echo (int) $item_id; ?>" id="show_notification_tb_link_<?php echo (int) $item_id; ?>" class="thickbox si_tooltip notification_message" title="<?php esc_html_e( 'View Message', 'sprout-invoices' ); ?>"><?php esc_html_e( 'View Message', 'sprout-invoices' ); ?></a>
									</p>
									<div id="notification_message_<?php echo (int) $item_id; ?>" class="cloak">
										<?php echo wp_kses( wpautop( $data['content'] ), $wp_kses_history ); ?>
									</div>
								<?php elseif ( SI_Invoices::VIEWED_STATUS_UPDATE === $data['status_type'] ) : ?>
									<p>
										<?php echo esc_html( $data['update_title'] ); ?>
									</p>
								<?php else : ?>
									<?php echo wp_kses( wpautop( $data['content'] ), $wp_kses_history ); ?>
								<?php endif ?>
							</article>
						<?php endforeach ?>
					</div>
				</div>
			</section>
		<?php endif ?>

		<div id="footer_credit">
			<?php
			// Action hook for customizing footer credit (branding, etc.).
			do_action( 'si_document_footer_credit' );
			?>
			<!--<p><?php esc_attr_e( 'Powered by Sprout Invoices', 'sprout-invoices' ); ?></p>-->
		</div><!-- #footer_messaging -->

	</body>
	<?php
	// Action hook for customizing document footer (scripts, etc.).
	do_action( 'si_document_footer' );
	?>
	<?php
	// Output Sprout Invoices footer (may include tracking, etc.).
	si_footer();
	?>

	<?php
	// Output template version for debugging and support.
	printf( '<!-- Template Version v%s -->', esc_html( Sprout_Invoices::SI_VERSION ) );
	?>
</html>
<?php
// Action hook after invoice is viewed (tracking, analytics, etc.).
do_action( 'invoice_viewed' );
?>

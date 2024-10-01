<?php

/**
 * DO NOT EDIT THIS FILE! Instead customize it via a theme override.
 *
 * Any edit will not be saved when this plugin is upgraded. Not upgrading will prevent you from receiving new features,
 * limit our ability to support your site and potentially expose your site to security risk that an upgrade has fixed.
 *
 * Theme overrides are easy too, so there's no excuse...
 *
 * https://sproutinvoices.com/support/knowledgebase/sprout-invoices/customizing-templates/
 *
 * You find something that you're not able to customize? We want your experience to be awesome so let support know and we'll be able to help you.
 *
 */
wp_enqueue_style( 'roboto-css', 'https://fonts.googleapis.com/css?family=Roboto:400,500,700,900', false, '1.0' );
do_action( 'pre_si_pdf_estimate_view' ); ?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<html>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
		<title><?php the_title() ?></title>
		<link rel="profile" href="http://gmpg.org/xfn/11" />

		<?php si_head( true ); ?>

		<meta name="robots" content="noindex, nofollow" />

		<style type="text/css">
			.row .items .item_with_children {
			    background-color: transparent;
			}
			#notes {
				padding-bottom: 10px;
			}
		</style>
	</head>

	<body id="estimate" class="si_default_theme">
		<header class="row" id="header">
			<div class="inner">

				<?php if ( get_theme_mod( 'si_logo' ) ) : ?>
					<img src="<?php echo esc_url( get_theme_mod( 'si_logo', si_doc_header_logo_url() ) ); ?>" alt="document logo" >
				<?php else : ?>
					<img src="<?php echo esc_url( si_doc_header_logo_url() ) ?>" alt="document logo" >
				<?php endif; ?>

				<div class="row intro">
					<h1><?php the_title() ?></h1>
					<span>
						<?php
							printf(
							// translators: 1: estimate id.
							esc_html__( 'Estimate %1$s', 'sprout-invoices' ),
							esc_html( si_get_estimate_id() )
							)
						?>
					</span>
				</div>
			</div>
		</header>

		<section class="row" id="intro">
			<div class="inner">
				<div class="column">
					<span>
						<?php
							printf(
							// translators: 1: issue date
							esc_html__( 'Issued: %1$s by:', 'sprout-invoices' ),
							esc_html( date_i18n( get_option( 'date_format' ), si_get_estimate_issue_date() ) )
							)
						?>
					</span>
					<h2><?php si_company_name() ?></h2>
					<?php si_doc_address() ?>
				</div>

				<div class="column">
					<?php if ( si_get_estimate_client_id() ) :  ?>
						<span>
							<?php
								printf(
								// translators: 1: expiring date
								esc_html__( 'Expires: %1$s to:', 'sprout-invoices' ),
								esc_html( date_i18n( get_option( 'date_format' ), si_get_estimate_expiration_date() ) )
								)
							?>
						</span>
						<h2><?php echo esc_html( get_the_title( si_get_estimate_client_id() ) ) ?></h2>

						<?php do_action( 'si_document_client_addy' ) ?>

						<?php si_client_address( si_get_estimate_client_id() ) ?>
					<?php else : ?>
						<span><?php esc_html_e( 'Expires:', 'sprout-invoices' ) ?></span>
						<h2><?php si_get_estimate_expiration_date() ?></h2>
					<?php endif ?>

				</div>

				<?php do_action( 'si_document_vcards' ) ?>

			</div>
		</section>

		<section class="row" id="details">
			<div class="inner">
				<div class="row item">
					<?php do_action( 'si_document_more_details' ) ?>
				</div>
			</div>
		</section>

		<?php do_action( 'si_doc_line_items', get_the_id() ) ?>

		<section class="row" id="notes">
			<div class="inner">
				<div class="row item">
					<div class="row header">
						<h3><?php esc_html_e( 'Info &amp; Notes', 'sprout-invoices' ) ?></h3>
					</div>
					<?php si_estimate_notes() ?>
				</div>

				<div class="row item">
					<div class="row header">
						<h3><?php esc_html_e( 'Terms &amp; Conditions', 'sprout-invoices' ) ?></h3>
					</div>
					<?php si_estimate_terms() ?>
				</div>
			</div>
		</section>

	</body>
	<?php do_action( 'si_document_footer' ) ?>
	<?php si_footer() ?>
</html>

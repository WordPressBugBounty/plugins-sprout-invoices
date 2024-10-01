<h1><?php esc_html_e( 'Logo & Color Styling', 'sprout-invoices' ); ?></h1>
<p><?php esc_html_e( 'Styling for Invoice and Estimate templates is done via the WordPress customizer.', 'sprout-invoices' ); ?></p>
<img src=<?php echo esc_attr( SI_RESOURCES . 'admin/img/customizer-how-to.gif' ); ?> class="si_customizer_how_to">

<p><?php esc_html_e( 'You can access the customizer from any invoice or estimate but to make things easier below are some helpful links.', 'sprout-invoices' ); ?></p>

<?php if ( isset( $invoice_template ) ) : ?>
	<label class="si_input_label"><?php esc_html_e( 'Invoices', 'sprout-invoices' ); ?></label>
	<?php if ( ! empty( $invoice_id ) ) : ?>
		<?php $invoice_url = esc_url_raw( add_query_arg( array( 'url' => rawurlencode( get_permalink( $invoice_id ) ) ), admin_url( 'customize.php' ) ) ); ?>
		<p>
			<?php
			printf(
			// translators: 1: opening a tag, 2: invoice url, 3: closing href, 4: closing a tag.
			esc_html__( 'Start customizing your invoices %1$s%2$s%3$shere%4$s.', 'sprout-invoices' ),
			'<a href="',
			esc_url( $invoice_url ),
			'">',
			'</a>'
			)
			?>
		</p>
	<?php else : ?>
		<p>
			<?php
				printf(
				// translators: 1: opening a tag, 2: invoice creation url, 3: closing href, 4: closing a tag.
				esc_html__( 'Before you start you will need to %1$s%2$s%3$screate an invoice%4$s.', 'sprout-invoices' ),
				'<a href="',
				esc_url( admin_url( 'post-new.php?post_type=sa_invoice' ) ),
				'">',
				'</a>'
				)
			?>
		</p>
	<?php endif ?>
<?php endif ?>
<?php if ( isset( $estimate_template ) ) : ?>
	<label class="si_input_label"><?php esc_html_e( 'Estimates', 'sprout-invoices' ); ?></label>
		<?php if ( ! empty( $estimate_id ) ) : ?>
			<?php $estimate_url = esc_url_raw( add_query_arg( array( 'url' => rawurlencode( get_permalink( $estimate_id ) ) ), admin_url( 'customize.php' ) ) ); ?>
			<p>
				<?php
					printf(
					// translators: 1: opening a tag, 2: estimate url, 3: closing href, 4: closing a tag.
					esc_html__( 'Start customizing your estimates %1$s%2$s%3$shere%4$s.', 'sprout-invoices' ),
					'<a href="',
					esc_url( $estimate_url ),
					'">',
					'</a>'
					)
				?>
			</p>
		<?php else : ?>
			<p>
				<?php
					printf(
					// translators: 1: opening a tag, 2: estimate creation url, 3: closing href, 4: closing a tag.
					esc_html__( 'Before you start you will need to %1$s%2$s%3$screate an estimate%4$s.', 'sprout-invoices' ),
					'<a href="',
					esc_url( admin_url( 'post-new.php?post_type=sa_estimate' ) ),
					'">',
					'</a>'
					)
				?>
			</p>
	<?php endif ?>
<?php endif ?>
<label class="si_input_label"><?php esc_html_e( 'Advanced', 'sprout-invoices' ) ?></label>
<p>
	<?php
		printf(
		// translators: 1: opening a tag, 2: template customization support article, 3: closing href, 4: closing a tag.
		esc_html__( 'Customizing templates for estimates, invoices, or any other front-end generated content from Sprout Invoices is easy. Review the %1$s%2$s%3$scustomization documentation%4$s on how to add custom CSS, overriding templates within your (child) theme, and adding custom templates.', 'sprout-invoices' ),
		'<a href="',
		'https://docs.sproutinvoices.com/article/38-customizing-templates',
		'">',
		'</a>'
		)
	?>
</p>

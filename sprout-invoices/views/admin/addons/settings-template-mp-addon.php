<article class="type_addon si-card">
	<div class="section">
		<div class="si-card-title">
			<strong><?php echo wp_kses( $title, wp_kses_allowed_html( 'post' ) ); ?></strong>
		</div>
		<div class="si-card-icon">
			<div class="img_wrap">
				<?php if ( $mp_addon->corp_bundled ) : ?>
					<span class="bundled_addon corp"><?php esc_html_e( 'Exclusive to Corporate License', 'sprout-invoices' ) ?></span>
				<?php elseif ( $mp_addon->biz_bundled ) : ?>
					<span class="bundled_addon biz"><?php esc_html_e( 'Exclusive w/ Business and Corp', 'sprout-invoices' ) ?></span>
				<?php elseif ( $mp_addon->pro_bundled ) : ?>
					<span class="bundled_addon pro"><?php esc_html_e( 'Bundled Free w/ a Pro License', 'sprout-invoices' ) ?></span>
				<?php elseif ( $mp_addon->free_addon ) : ?>
					<span class="bundled_addon free"><?php esc_html_e( 'Free Download!', 'sprout-invoices' ) ?></span>
				<?php endif ?>
				<a href="<?php echo esc_url( si_get_sa_link( $url, 'add-ons' ) )?>" class="si-button" target="_blank"><img src="<?php echo esc_attr( $img ) ?>" /></a>
			</div>
		</div>
		<div class="si-card-footer">
			<div class="addon_description">
				<div class="addon_description">
					<?php echo wp_kses( $description, wp_kses_allowed_html( 'post' ) ); ?>
				</div>
			</div>
		</div>
		<div class="si-card-links">
			<div class="addon_info_link">
				<a href="<?php echo esc_url( si_get_sa_link( $url, 'add-ons' ) ) ?>" class="si-button" target="_blank"><?php esc_html_e( 'Learn More', 'sprout-invoices' ) ?></a>
			</div>
		</div>
	</div>
</article>

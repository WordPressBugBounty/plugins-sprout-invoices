<?php do_action( 'sprout_settings_header' ); ?>

<div id="si_dashboard" class="si_settings sprout_apps_dash wrap about-wrap">

	<img class="header_sa_logo" src="<?php echo esc_attr( SI_RESOURCES ) . 'admin/icons/sproutapps.png' ?>" />

	<h1>
		<?php 
			printf(
				// translators: 1: opening a tag, 2: plugin url, 3: closing href, 4: closing a tag.
				esc_html__( 'Welcome to %1$s%2$s%3$sSprout Apps%4$s', 'sprout-invoices' ),
				'<a href="',
				esc_url( self::PLUGIN_URL ),
				'">',
				'</a>'
			); 
		?>
	</h1>

	<div class="about-text"><?php esc_html_e( 'Our mission is to build a suite of apps to help small businesses and freelancers work more efficiently by reducing the tedious business tasks associated with client management...<em>seriously though</em>, I\'m trying to build something awesome that you will love. Thank you for your support.', 'sprout-invoices' ) ?></div>

	<div id="welcome-panel" class="welcome-panel clearfix">
		<div class="welcome-panel-content">
			<h2><?php esc_html_e( 'Sprout Apps News and Updates', 'sprout-invoices' ) ?></h2>
			<?php
				$maxitems = 0;
				require_once ABSPATH . WPINC . '/feed.php';
				$rss = fetch_feed( 'https://sproutinvoices.com/feed/' ); // FUTURE use feedburner
			if ( ! is_wp_error( $rss ) ) :
				$maxitems = $rss->get_item_quantity( 3 );
				$rss_items = $rss->get_items( 0, $maxitems );
				endif;
			?>
			<div class="rss_widget clearfix">
				<?php if ( $maxitems == 0 ) : ?>
					<p><?php esc_html_e( 'Could not connect to SIserver for updates.', 'sprout-invoices' ); ?></p>
				<?php else : ?>
					<?php foreach ( $rss_items as $item ) :
						$excerpt = sa_get_truncate( strip_tags( $item->get_content() ), 30 );
						?>
						<div>
							<h4><a href="<?php echo esc_url( $item->get_permalink() ); ?>" title="<?php echo esc_html( $item->get_title() ); ?>"><?php echo esc_html( $item->get_title() ); ?></a></h4>
							<span class="rss_date"><?php echo esc_html( $item->get_date( 'j F Y' ) ); ?></span>
							<p><?php esc_html_e( $excerpt, 'sprout-invoices' ); ?></p>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<a class="twitter-timeline" href="https://twitter.com/_sproutapps" data-widget-id="492426361349234688">Tweets by @_sproutapps</a>
			<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
		</div>
	</div>
</div>


<div id="dashboard-widgets-wrap" class="row">
	<?php if ( function_exists( 'wp_dashboard' ) ) :  ?>
		<?php wp_dashboard(); ?>
	<?php else : ?>
		<?php esc_html_e( 'Function wp_dashboard() not available.', 'sprout-invoices' ) ?>
	<?php endif ?>
</div>

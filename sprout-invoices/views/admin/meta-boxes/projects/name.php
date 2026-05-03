<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div id="subject_header" class="clearfix">
	<div id="subject_header_actions" class="clearfix">
		<div id="subject_input_wrap">
			<?php $title = ( $status != 'auto-draft' && get_the_title( $id ) != __('Auto Draft', 'default' ) ) ? get_the_title( $id ) : '' ; // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch -- Reusing WordPress core string translation ?>
			<h2><?php echo esc_html( $title ); ?></h2>
		</div>

		<div id="quick_links">
			<?php do_action( 'project_quick_links', $project ) ?>
		</div>
	</div>
</div>
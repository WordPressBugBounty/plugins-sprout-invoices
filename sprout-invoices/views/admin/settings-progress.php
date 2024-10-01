<?php
$complete = array();
foreach ( $progress as $key => $progress_item ) {
	if ( $progress_item['status'] ) {
		$complete[] = $key;
	}
}
$percentage_complete = count( $complete ) / count( $progress ) * 100;
$percentage_complete = round( $percentage_complete );

?>

<?php if ( defined( 'DOING_AJAX' ) && DOING_AJAX && $percentage_complete >= 100 ) :  ?>
	<img src="<?php echo esc_attr( SI_RESOURCES . 'admin/img/sprout/yipee.png' ) ?>" id="happy_sprout" title="You did it!" width="120" height="auto"/>
<?php else : ?>
	<div id="si_progress_track" class="si_progress_track thickbox  <?php if ( $percentage_complete >= 100 ) { echo 'progress_completed'; } ?>">
		<nav>
			<span class="progress_header"><?php esc_html_e( 'Your Progress', 'sprout-invoices' ) ?></span>
			<div class="progress-content">
				<ol>
					<?php foreach ( $progress as $key => $progress_item ) :  ?>
						<?php
							$status = ( $progress_item['status'] ) ? 'dashicons dashicons-yes' : 'dashicons dashicons-no' ; ?>
						<li class="si_tooltip" aria-label="<?php echo esc_attr( $progress_item['aria-label'] ) ?>">
							<span class="<?php echo esc_html( $status ) ?>"></span>&nbsp;<a href="<?php echo esc_url( $progress_item['link'] ) ?>"><?php echo esc_html( $progress_item['label'] ) ?></a>
						</li>
					<?php endforeach ?>

					<?php if ( $percentage_complete >= 100 ) :  ?>
						<li><img src="<?php echo esc_attr( SI_RESOURCES . 'admin/img/sprout/yipee.png' ) ?>" id="happy_sprout" title="You did it!" width="100" height="auto"/></li>
					<?php endif ?>
				</ol>
			</div>
			<?php if ( $percentage_complete >= 100 ) :  ?>
				<span class="progress_footer si_tooltip" aria-label="<?php esc_html_e( 'You did it!', 'sprout-invoices' ) ?>"><?php esc_html_e( 'Awesome &mdash; 100% Complete!', 'sprout-invoices' ) ?></span>
			<?php else : ?>
				<span class="progress_footer si_tooltip" aria-label="<?php esc_html_e( 'You\'ve got this!', 'sprout-invoices' ) ?>"><span class="si_icon icon-hour-glass"></span>&nbsp;
					<?php
						printf(
						// translators: 1: settings percent complete.
						esc_html__( '%s%% Complete', 'sprout-invoices' ),
						esc_html( $percentage_complete )
						)
					?>
				</span>
			<?php endif ?>
		</nav>
	</div>

<?php endif ?>

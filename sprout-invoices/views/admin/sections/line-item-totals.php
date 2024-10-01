<div id="line_items_totals">

	<?php do_action( 'si_line_item_totals_section_start', $id ); ?>

	<?php if ( ! empty( $totals ) ) : ?>

		<?php foreach ( $totals as $slug => $items_total ) : ?>

			<?php if ( isset( $items_total['admin_hide'] ) && $items_total['admin_hide'] ) : ?>
				<?php continue; ?>
			<?php endif ?>

			<div id="line_<?php echo esc_attr( $slug ); ?>">
				<?php
					$delete_option = ( isset( $items_total['delete_option'] ) ) ? sprintf( '&nbsp;<a href="javascript:void(0)" class="si_delete_fee del_button" data-fee-id="%s" data-doc-id="%s">X</a>', $slug, $id ) : '' ;
					$wp_kses_delete_option = array(
						'a' => array(
							'href'  => array(),
							'class' => array(),
							'data-fee-id' => array(),
							'data-doc-id' => array()
						),
					);
					$wp_kses_items_total = array(
						'span' => array(
							'class' => array(),
							'title' => array(),
						),
					);
				?>
				<?php if ( isset( $items_total['helptip'] ) ) : ?>
					<b title="<?php echo esc_html( $items_total['helptip'] ) ?>" class="helptip"><?php echo wp_kses( $items_total['label'], $wp_kses_items_total ) ?><?php echo wp_kses( $delete_option, $wp_kses_delete_option ) ?></b>
				<?php else : ?>
					<b><?php echo esc_html( $items_total['label'] ) ?><?php echo wp_kses( $delete_option, $wp_kses_delete_option ) ?></b>
				<?php endif ?>
				<?php if ( isset( $items_total['formatted'] ) ) : ?>
					<span class="total"><?php echo wp_kses( $items_total['formatted'], $wp_kses_items_total ) ?></span>
				<?php endif ?>
			</div>

		<?php endforeach ?>
	<?php endif ?>

	<?php do_action( 'si_line_item_totals_section', $id ) ?>

</div>

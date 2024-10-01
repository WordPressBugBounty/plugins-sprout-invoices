<div class="quick_status_update" style="position:relative">
	<?php do_action( 'si_start_status_change_drop', $id ) ?>
	<span id="status_<?php echo(int) $id ?>">
		<span class="status_change" data-dropdown="#status_change_<?php echo (int) $id ?>">
			<?php
				$status_change_span = '<div class="dashicons dashicons-arrow-down"></div>';
				$kses_array = array( 'div' => array( 'class' => array() ) );
			?>
			<?php if ( $status == SI_Invoice::STATUS_PENDING ) : ?>
				<?php 
					printf( '<span class="si_status publish si_tooltip button current_status" title="%s" disabled><span>%s</span>%s</span>',
					esc_html__( 'Pending payment(s)', 'sprout-invoices' ),
					esc_html__( 'Pending Payment(s)', 'sprout-invoices' ),
					wp_kses( $status_change_span, $kses_array )
					); 
				?>
			<?php elseif ( $status == SI_Invoice::STATUS_PAID ) : ?>
				<?php 
					printf( '<span class="si_status complete si_tooltip button current_status" title="%s" disabled><span>%s</span>%s</span>', 
					esc_html__( 'Fully Paid', 'sprout-invoices' ),
					esc_html__( 'Paid', 'sprout-invoices' ),
					wp_kses( $status_change_span, $kses_array )
					); 
				?>
			<?php elseif ( $status == SI_Invoice::STATUS_PARTIAL ) : ?>
				<?php 
					printf( '<span class="si_status publish si_tooltip button current_status" title="%s" disabled><span>%s</span>%s</span>',
					esc_html__( 'Outstanding Balance', 'sprout-invoices' ),
					esc_html__( 'Outstanding Balance', 'sprout-invoices' ),
					wp_kses( $status_change_span, $kses_array )
					); 
				?>
			<?php elseif ( $status == SI_Invoice::STATUS_WO ) : ?>
				<?php 
					printf( '<span class="si_status declined si_tooltip button current_status" title="%s" disabled><span>%s</span>%s</span>',
					esc_html__( 'Written-off', 'sprout-invoices' ),
					esc_html__( 'Written Off', 'sprout-invoices' ),
					wp_kses( $status_change_span, $kses_array )
					);
				?>
			<?php elseif ( $status === SI_Invoice::STATUS_FUTURE ) : ?>
				<?php 
					printf( '<span class="si_status temp si_tooltip button current_status" title="%s" disabled><span>%s</span>%s</span>',
					esc_html__( 'Temp Invoice', 'sprout-invoices' ),
					esc_html__( 'Scheduled', 'sprout-invoices' ),
					wp_kses( $status_change_span, $kses_array )
					);
				?>
			<?php elseif ( $status === SI_Invoice::STATUS_ARCHIVED ) : ?>
				<?php 
					printf( '<span class="si_status temp si_tooltip button current_status" title="%s" disabled><span>%s</span>%s</span>',
					esc_html__( 'Archived Invoice', 'sprout-invoices' ),
					esc_html__( 'Archived', 'sprout-invoices' ),
					wp_kses( $status_change_span, $kses_array )
					);
				?>
			<?php elseif ( apply_filters( 'si_is_invoice_currently_custom_status', false, $id ) ) : ?>
				<?php do_action( 'si_invoice_custom_status_current_label', $id ); ?>
			<?php else : ?>
				<?php 
					printf( '<span class="si_status temp si_tooltip button current_status" title="%s" disabled><span>%s</span>%s</span>',
					esc_html__( 'Temp Invoice', 'sprout-invoices' ),
					esc_html__( 'Temp', 'sprout-invoices' ),
					wp_kses( $status_change_span, $kses_array ) 
					);
				?>
			<?php endif ?>
		</span>
	</span>
	<div id="status_change_<?php echo (int) $id ?>" class="dropdown dropdown-tip dropdown-relative dropdown-anchor-right">
		<ul class="si-dropdown-menu">
			<?php if ( SI_Invoice::STATUS_FUTURE !== $status ) : ?>
				<?php if ( $status != SI_Invoice::STATUS_PENDING ) : ?>
					<?php 
						printf( '<li><a class="doc_status_change pending" title="%s" href="%s" data-id="%s" data-status-change="%s" data-nonce="%s">%s</a></li>',
						esc_html__( 'Mark Pending Payment(s)', 'sprout-invoices' ),
						esc_attr( get_edit_post_link( $id ) ),
						esc_attr( $id ), 
						esc_attr( SI_Invoice::STATUS_PENDING ),
						esc_attr( wp_create_nonce( SI_Controller::NONCE ) ),
						esc_html__( 'Active: Pending Payment(s)', 'sprout-invoices' ) 
						);
					?>
				<?php endif ?>
			<?php endif ?>
			<?php if ( $status != SI_Invoice::STATUS_PARTIAL ) : ?>
				<?php 
					printf( '<li><a class="doc_status_change publish" title="%s" href="%s" data-id="%s" data-status-change="%s" data-nonce="%s">%s</a></li>',
					esc_html__( 'Outstanding Balance.', 'sprout-invoices' ),
					esc_attr( get_edit_post_link( $id ) ),
					esc_attr( $id ), 
					esc_attr( SI_Invoice::STATUS_PARTIAL ),
					esc_attr( wp_create_nonce( SI_Controller::NONCE ) ),
					esc_html__( 'Active: Partial Payment Received', 'sprout-invoices' ) 
					);
				?>
			<?php endif; ?>
			<?php if ( $status != SI_Invoice::STATUS_PAID ) : ?>
				<?php 
					printf( '<li><a class="doc_status_change publish" title="%s" href="%s" data-id="%s" data-status-change="%s" data-nonce="%s">%s</a></li>',
					esc_html__( 'Mark as Paid.', 'sprout-invoices' ),
					esc_attr( get_edit_post_link( $id ) ),
					esc_attr( $id ), 
					esc_attr( SI_Invoice::STATUS_PAID ),
					esc_attr( wp_create_nonce( SI_Controller::NONCE ) ),
					esc_html__( 'Complete: Paid in Full', 'sprout-invoices' ) 
					); 
				?>
			<?php endif; ?>
			<?php if ( ! apply_filters( 'si_is_invoice_currently_custom_status', false, $id ) ) : ?>
				<?php do_action( 'si_invoice_custom_status_current_option', $id ); ?>
			<?php endif; ?>
			<?php if ( $status != SI_Invoice::STATUS_TEMP && $status != 'auto-draft' ) : ?>
			<li><hr/></li>
				<?php 
					printf( '<li class="casper"><a class="doc_status_change temp" title="%s" href="%s" data-id="%s" data-status-change="%s" data-nonce="%s">%s</a></li>',
					esc_html__( 'Make Temporary', 'sprout-invoices' ),
					esc_attr( get_edit_post_link( $id ) ),
					esc_attr( $id ),
					esc_attr( SI_Invoice::STATUS_TEMP ),
					esc_attr( wp_create_nonce( SI_Controller::NONCE ) ),
					esc_html__( 'Temp: Drafted Invoice', 'sprout-invoices' ) 
					); 
				?>
			<?php endif ?>
			<li><hr/></li>
			<?php if ( $status != SI_Invoice::STATUS_ARCHIVED ) : ?>
				<?php 
					printf( '<li><a class="doc_status_change decline" title="%s" href="%s" data-id="%s" data-status-change="%s" data-nonce="%s">%s</a></li>',
					esc_html__( 'Write-off Invoice', 'sprout-invoices' ),
					esc_attr( get_edit_post_link( $id ) ),
					esc_attr( $id ),
					esc_attr( SI_Invoice::STATUS_ARCHIVED ),
					esc_attr( wp_create_nonce( SI_Controller::NONCE ) ),
					esc_html__( 'Archive: No Client Access', 'sprout-invoices' ) ); ?>
			<?php endif ?>
			<?php if ( $status != SI_Invoice::STATUS_WO ) : ?>
				<?php 
					printf( '<li><a class="doc_status_change decline" title="%s" href="%s" data-id="%s" data-status-change="%s" data-nonce="%s">%s</a></li>',
					esc_html__( 'Write-off Invoice', 'sprout-invoices' ),
					esc_attr( get_edit_post_link( $id ) ),
					esc_attr( $id ),
					esc_attr( SI_Invoice::STATUS_WO ),
					esc_attr( wp_create_nonce( SI_Controller::NONCE ) ),
					esc_html__( 'Void: Write-off Invoice', 'sprout-invoices' ) 
					); 
				?>
			<?php endif ?>
			<?php
			if ( current_user_can( 'delete_post', $id ) ) {
				printf( '<li><a class="doc_status_delete delete" title="%s" href="%s">%s</a></li>', esc_html__( 'Delete Invoice', 'sprout-invoices' ), get_delete_post_link( $id, '' ), esc_html__( '<b>Delete:</b> Trash Invoice', 'sprout-invoices' ) );
			} ?>
		</ul>
	</div>
	<?php do_action( 'si_end_status_change_drop', $id ) ?>
</div>
<?php do_action( 'si_status_change_drop_outside', $id ) ?>

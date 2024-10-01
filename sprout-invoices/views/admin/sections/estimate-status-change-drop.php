<div class="quick_status_update" style="position:relative">
	<?php do_action( 'si_start_status_change_drop', $id ) ?>
	<span id="status_<?php echo (int) $id ?>">
		<span class="status_change" data-dropdown="#status_change_<?php echo (int) $id ?>">
			<?php
				$status_change_span = '&nbsp;<div class="dashicons dashicons-arrow-down"></div>';
				$kses_array = array( 'div' => array( 'class' => array() ) );
			?>
			<?php if ( $status == SI_Estimate::STATUS_PENDING ) : ?>
				<?php 
					printf( '<span class="si_status publish si_tooltip button current_status" title="%s" disabled><span>%s</span>%s</span>',
					esc_html__( 'Currently Pending.', 'sprout-invoices' ),
					esc_html__( 'Pending', 'sprout-invoices' ),
					wp_kses( $status_change_span, $kses_array ) 
					); 
				?>
			<?php elseif ( $status == SI_Estimate::STATUS_APPROVED ) : ?>
				<?php 
					printf( '<span class="si_status complete si_tooltip button current_status" title="%s" disabled><span>%s</span>%s</span>',
					esc_html__( 'Currently Approved.','sprout-invoices' ),
					esc_html__( 'Approved', 'sprout-invoices' ),
					wp_kses( $status_change_span, $kses_array )
					);
				?>
			<?php elseif ( $status == SI_Estimate::STATUS_DECLINED ) : ?>
				<?php 
					printf( '<span class="si_status declined si_tooltip button current_status" title="%s" disabled><span>%s</span>%s</span>',
					esc_html__( 'Currently Declined.', 'sprout-invoices' ),
					esc_html__( 'Declined', 'sprout-invoices' ), 
					wp_kses( $status_change_span, $kses_array ) 
					); 
				?>
			<?php elseif ( $status == SI_Estimate::STATUS_REQUEST ) : ?>
				<?php 
					printf( '<span class="si_status draft si_tooltip button current_status" title="%s" disabled><span>%s</span>%s</span>',
					esc_html__( 'New Estimate Request', 'sprout-invoices' ),
					esc_html__( 'Submission', 'sprout-invoices' ),
					wp_kses( $status_change_span, $kses_array )
					);
				?>
			<?php elseif ( $status === SI_Estimate::STATUS_ARCHIVED ) : ?>
				<?php 
					printf( '<span class="si_status draft si_tooltip button current_status" title="%s" disabled><span>%s</span>%s</span>',
					esc_html__( 'Archive Estimate', 'sprout-invoices' ),
					esc_html__( 'Archive', 'sprout-invoices' ),
					wp_kses( $status_change_span, $kses_array )
					);
				?>
			<?php elseif ( apply_filters( 'si_is_estimate_currently_custom_status', false, $id ) ) : ?>
				<?php do_action( 'si_estimate_custom_status_current_label', $id ); ?>
			<?php else : ?>
				<?php 
					printf( '<span class="si_status draft si_tooltip button current_status" title="%s" disabled><span>%s</span>%s</span>', 
					esc_html__( 'Pending Estimate Request.', 'sprout-invoices' ), 
					esc_html__( 'Draft', 'sprout-invoices' ), 
					wp_kses( $status_change_span, $kses_array ) 
					);
				?>
			<?php endif ?>
		</span>
	</span>

	<div id="status_change_<?php echo (int) $id ?>" class="dropdown dropdown-tip dropdown-relative dropdown-anchor-right">
		<ul class="si-dropdown-menu">
			<?php if ( $status != SI_Estimate::STATUS_PENDING ) : ?>
				<?php 
					printf( '<li><a class="doc_status_change pending" title="%s" href="%s" data-id="%s" data-status-change="%s" data-nonce="%s">%s</a></li>',
					esc_html__( 'Mark Pending', 'sprout-invoices' ),
					esc_attr( get_edit_post_link( $id ) ),
					esc_attr( $id ),
					esc_attr( SI_Estimate::STATUS_PENDING ),
					esc_attr( wp_create_nonce( SI_Controller::NONCE ) ),
					esc_html__( 'Pending: Waiting for Review', 'sprout-invoices' )
					);
				?>
			<?php endif ?>
			<?php if ( $status != SI_Estimate::STATUS_APPROVED ) : ?>
				<?php 
					printf( '<li><a class="doc_status_change publish" title="%s" href="%s" data-id="%s" data-status-change="%s" data-nonce="%s">%s</a></li>',
					esc_html__( 'Mark Approved', 'sprout-invoices' ),
					esc_attr( get_edit_post_link( $id ) ),
					esc_attr( $id ),
					esc_attr( SI_Estimate::STATUS_APPROVED ),
					esc_attr( wp_create_nonce( SI_Controller::NONCE ) ),
					esc_html__( 'Complete: Estimate Approved', 'sprout-invoices' )
					); 
				?>
			<?php endif ?>
			<?php if ( ! apply_filters( 'si_is_estimate_currently_custom_status', $id ) ) : ?>
				<?php do_action( 'si_estimate_custom_status_current_option', $id ); ?>
			<?php endif; ?>
			<?php if ( $status != SI_Estimate::STATUS_DECLINED ) : ?>
				<?php 
					printf( '<li><a class="doc_status_change decline" title="%s" href="%s" data-id="%s" data-status-change="%s" data-nonce="%s">%s</a></li>',
					esc_html__( 'Mark Declined', 'sprout-invoices' ),
					esc_attr( get_edit_post_link( $id ) ),
					esc_attr( $id ),
					esc_attr( SI_Estimate::STATUS_DECLINED ),
					esc_attr( wp_create_nonce( SI_Controller::NONCE ) ),
					esc_html__( 'Void: Estimate Declined', 'sprout-invoices' )
					);
				?>
			<?php endif ?>
			<?php if ( ! apply_filters( 'si_is_estimate_currently_custom_status', false, $id ) ) : ?>
				<?php do_action( 'si_estimate_custom_status_current_option', $id ); ?>
			<?php endif; ?>
			<li><hr/></li>
			<?php if ( $status != SI_Invoice::STATUS_ARCHIVED ) : ?>
				<?php 
					printf( '<li><a class="doc_status_change decline" title="%s" href="%s" data-id="%s" data-status-change="%s" data-nonce="%s">%s</a></li>',
					esc_html__( 'Write-off Invoice', 'sprout-invoices' ),
					esc_attr( get_edit_post_link( $id ) ),
					esc_attr( $id ),
					esc_attr( SI_Invoice::STATUS_ARCHIVED ),
					esc_attr( wp_create_nonce( SI_Controller::NONCE ) ),
					esc_html__( 'Archive: No Client Access', 'sprout-invoices' ) 
					);
				?>
			<?php endif ?>
			<?php
			if ( current_user_can( 'delete_post', $id ) ) {
				printf( '<li><a class="doc_status_delete delete" title="%s" href="%s">%s</a></li>', esc_html__( 'Delete Estimate', 'sprout-invoices' ), get_delete_post_link( $id, '' ), esc_html__( 'Delete: Trash Estimate', 'sprout-invoices' ) );
			} ?>
		</ul>
	</div>
	<?php do_action( 'si_end_status_change_drop', $id ) ?>
</div>
<?php do_action( 'si_status_change_drop_outside', $id ) ?>

<p>
	<b><?php esc_html_e( 'Projects', 'sprout-invoices' ); ?></b>
	<?php if ( ! empty( $projects ) ) : ?>
		<dl>
			<?php foreach ( $projects as $project_id ) : ?>
				<?php $project = SI_Project::get_instance( $project_id ); ?>
				<dt><?php printf( '<a href="%s">%s</a>', esc_attr( get_edit_post_link( $project_id ) ), esc_html( get_the_title( $project_id ) ) ); ?></dt>
				<dd>
					<?php if ( $project->get_start_date() && $project->get_end_date() ) : ?>
						<?php printf( '%s&mdash;%s', esc_html( date_i18n( get_option( 'date_format' ), $project->get_start_date() ) ), esc_html( date_i18n( get_option( 'date_format' ), $project->get_end_date() ) ) ); ?>
					<?php elseif ( $project->get_start_date() ) : ?>
						<?php printf( '<b>Start</b>&mdash;%s', esc_html( date_i18n( get_option( 'date_format' ), $project->get_start_date() ) ) ); ?>
					<?php elseif ( $project->get_end_date() ) : ?>
						<?php printf( '<b>End</b>&mdash;%s', esc_html( date_i18n( get_option( 'date_format' ), $project->get_end_date() ) ) ); ?>
					<?php else : ?>
						<?php esc_html_e( 'No start and/or end date set.', 'sprout-invoices' ); ?>
					<?php endif ?>

				</dd>
			<?php endforeach ?>
		</dl>
	<?php else : ?>
		<em><?php esc_html_e( 'No projects', 'sprout-invoices' ); ?></em>
	<?php endif ?>
</p>
<hr/>

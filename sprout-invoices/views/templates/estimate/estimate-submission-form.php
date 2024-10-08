<?php

/**
 * DO NOT EDIT THIS FILE! Instead customize it via a theme override.
 *
 * Any edit will not be saved when this plugin is upgraded. Not upgrading will prevent you from receiving new features,
 * limit our ability to support your site and potentially expose your site to security risk that an upgrade has fixed.
 *
 * https://sproutinvoices.com/support/knowledgebase/sprout-invoices/customizing-templates/
 *
 * You find something that you're not able to customize? We want your experience to be awesome so let support know and we'll be able to help you.
 *
 */ ?>

<form id="si_estimate_submission" method="post" action="" enctype="multipart/form-data">
	<?php sa_form_fields( $fields, 'estimate' ); ?>
	<div class="form-actions">
		<input type="submit" class="btn btn-primary" value="<?php esc_html_e( 'Submit', 'sprout-invoices' ); ?>" />
	</div>
</form>

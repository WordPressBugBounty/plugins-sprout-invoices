<div id="si-page-header" class="si-has-logo">
	<h1><?php esc_html_e( 'Sprout Invoices', 'sprout-invoices' ); ?></h1>
	<div class="page-title-actions">
		<a href="post-new.php?post_type=sa_invoice" class="page-title-action page-title-action-primary">Create Invoice</a>
		<a href="post-new.php?post_type=sa_estimate" class="page-title-action page-title-action-primary">Create Estimate</a>
	</div>
</div>

<?php if ( apply_filters( 'si_show_help_desk', true ) ) : ?>
<script>!function(e,t,n){function a(){var e=t.getElementsByTagName("script")[0],n=t.createElement("script");n.type="text/javascript",n.async=!0,n.src="https://beacon-v2.helpscout.net",e.parentNode.insertBefore(n,e)}if(e.Beacon=n=function(t,n,a){e.Beacon.readyQueue.push({method:t,options:n,data:a})},n.readyQueue=[],"complete"===t.readyState)return a();e.attachEvent?e.attachEvent("onload",a):e.addEventListener("load",a,!1)}(window,document,window.Beacon||function(){});window.Beacon('init', '0857c75f-2142-4344-9c48-ccc5333af6eb')</script>
<?php endif ?>

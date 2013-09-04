<div class="navbar navbar-inverse navbar-fixed-top">
	<div class="container">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="<?= base_url() ?>"><?= $app_title ?></a>
		</div>
		<div class="navbar-collapse collapse">
			<ul class="nav navbar-nav">
				<li <?= set_active( '', $active_segment, 'class="active"' ) ?>><a href="<?= base_url() ?>">Home</a></li>
				<li <?= set_active( 'search', $active_segment, 'class="active"' ) ?>><a href="<?= base_url() ?>search">Search</a></li>
			</ul>
		</div>
	</div>
</div>
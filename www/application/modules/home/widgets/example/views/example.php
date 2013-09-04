<div class="container">
	<div class="row">
		<div class="col-lg-12">
			<hr />
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title"><?= $label ?></h3>
				</div>
				<div class="panel-body">
					<p>This little bit of content is running via the <strong>example widget</strong> in: <pre>/application/modules/home/widgets/example</pre></p>
					<p>Below is an example of displaying dynamic data within the module.</p>
				</div>

				<ul class="list-group">
					<?php foreach ( $data AS $row ) : ?>
						<li class="list-group-item"><?= $row ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</div>
</div>
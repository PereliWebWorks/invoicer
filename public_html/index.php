<?php require_once("helpers/global.php"); ?>
<?php require_once("helpers/require-log-in.php"); ?>
<?php require_once("helpers/global-html-head.php"); ?>

	<div class="row user-info">
		<h2 class="col-xs-12"><?= getCurrentUser()->name ?></h2>
	</div>
	<div class="row client-list">
		<?php foreach (getCurrentUser()->getClients() as $client) : ?>
			<div class="col-xs-12">
				<a href="client.php?c=<?= $client->id; ?>">
					<h4><?= $client->name; ?></h4>
				</a>
				<div><i><?= $client->email; ?></i></div>
			</div>
		<?php endforeach ?>
	</div>
	
<?php require_once("helpers/global-html-foot.php"); ?>

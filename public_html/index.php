<?php require_once("helpers/global.php"); ?>
<?php require_once("helpers/require-log-in.php"); ?>
<?php require_once("helpers/global-html-head.php"); ?>
<?php
	//Get user info
	$query = "SELECT * FROM users WHERE id=:id";
	$stmt = $db->prepare($query);
	$stmt->bindParam(":id", $_SESSION["user_id"]);
	$stmt->execute();
	$user = $stmt->fetch(PDO::FETCH_ASSOC);
	//Get clients
	$q = "SELECT id, name, email FROM clients WHERE user_id=:id";
	$stmt = $db->prepare($q);
	$stmt->bindParam(":id", $_SESSION["user_id"]);
	$stmt->execute();
	$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
	<div class="row user-info">
		<h2 class="col-xs-12"><?= $user["name"] ?></h2>
	</div>
	<div class="row client-list">
		<?php foreach ($clients as $client) : ?>
			<div class="col-xs-12">
				<a href="client.php?c=<?= $client['id']; ?>">
					<h4><?= $client['name']; ?></h4>
				</a>
				<div><i><?= $client['email']; ?></i></div>
			</div>
		<?php endforeach ?>
	</div>
	
<?php require_once("helpers/global-html-foot.php"); ?>

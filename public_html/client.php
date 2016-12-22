<?php
	require_once("helpers/global.php");
	require_once("helpers/require-log-in.php");
	
	if (empty($_GET["c"]))
	{
		setFlash("danger", "You must specify a client.");
		header("Location: index.php");
		die();
	}
	$client_id = $_GET["c"];
	//If the client doesn't belong to the logged in user or doesn't exist, redirect to index
	$client = getClient($client_id);
	if (!$client || $client["user_id"] !== $_SESSION["user_id"])
	{
		setFlash("danger", "That is not a client of yours.");
		header("Location: index.php");
		die();
	}
?>
<?php require_once("helpers/global-html-head.php"); ?>

	<?php print_r($client); ?>
	<div class="row">
		<h1><?= $client["name"]; ?></h1>
		<div><?= $client["email"]; ?></div>
		<div>$<?= ($client["default_rate"] / 100) ?> per hour</div>
	</div>
	
<?php require_once("helpers/global-html-foot.php"); ?>
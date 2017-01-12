<?php require_once("../global.php"); ?>
<?php require_once("../require-log-in.php"); ?>
<?php
	$r = new Response();
	if (empty($_POST["invoice"]["client_id"]))
	{
		$r->message = "Bad client ID.";
		echo $r;
		die();
	}
	$client = Client::find($_POST["invoice"]["client_id"]);
	if (!$client)
	{
		$r->message = "Bad client ID.";
		echo $r;
		die();
	}
	if ($client->user != getCurrentUser())
	{
		$r->message = "Bad client ID.";
		echo $r;
		die();
	}
	$i = new Invoice($_POST["invoice"]);
	$attempt = $i->save();
	echo $attempt;
?>
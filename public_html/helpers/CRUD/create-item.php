<?php require_once("../global.php"); ?>
<?php require_once("../require-log-in.php"); ?>
<?php
	$r = new Response();
	if (empty($_POST["item"]))
	{
		echo $r;
		die();
	}
	$item_info = $_POST["item"];
	unset($_POST["item"]);
	$item_info["duration"] = intval($item_info["duration"]);
	$item = new Item($item_info);
	$attempt = $item->save();
	if (!$attempt->success)
	{
		echo $attempt;
		die();
	}
	$r->success = true;
	$r->message = "Item added.";
	echo $r;
?>
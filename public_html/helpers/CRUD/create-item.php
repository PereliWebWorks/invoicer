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
	
	$item = new Item($item_info);
	$attempt = $item->save();
	echo $attempt;
?>
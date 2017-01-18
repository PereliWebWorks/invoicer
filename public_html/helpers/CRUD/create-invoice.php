<?php require_once("../global.php"); ?>
<?php require_once("../require-log-in.php"); ?>
<?php
	$i = new Invoice($_POST["invoice"]);
	$attempt = $i->save();
	echo $attempt;
?>
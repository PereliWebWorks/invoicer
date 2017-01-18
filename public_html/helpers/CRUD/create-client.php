<?php require_once("../global.php"); ?>
<?php require_once("../require-log-in.php"); ?>
<?php
//Validate required data
	$r = new Response();
	if (empty($_POST["new-client"]))
	{
		$r->message = "Something went wrong. Try again later.";
		echo $r;
		die();
	}
	$new_client_info = $_POST["new-client"];
	unset($_POST["new-client"]);
	$client = new Client($new_client_info);
	$attempt = $client->save();
	echo $attempt;
?>




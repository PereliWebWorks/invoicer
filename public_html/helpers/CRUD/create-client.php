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
	$new_client_info["user_id"] = getCurrentUser()->id;
	$client = new Client($new_client_info);
	$attempt = $client->save();
	//If "rate" isn't numeric, echo an error
	if (!$attempt->success)
	{
		echo $attempt;
		die();
	}
	$_POST["new-invoice"]["client_id"] = $client->id;
	require("create-invoice.php");
	$r->client_id = $client->id;
	$r->success = true;
	$r->message = "Client added.";
	echo $r;
?>




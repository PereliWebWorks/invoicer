<?php require_once("../globals.php"); ?>
<?php require_once("../require-log-in.php"); ?>
<?php 
	$response = array("message"=>"", "success"=>false);
	if (empty($_POST["invoice"]))
	{
		$response["message"] = "There was an error.";
		echo json_encode($response);
		die();
	}
	$invoice = $_POST["invoice"];
	unset($_POST["invoice"]);
	if (empty($invoice["client_id"]))
	{
		$response["message"] = "There was an error.";
		echo json_encode($response);
		die();	
	}
	$client = getClient($invoice["client_id"]);
	//If the client doesn't belong to the current user, die.
	if ($client["user_id"] !== $_SESSION["user_id"])
	{
		$response["message"] = "There was an error.";
		echo json_encode($response);
		die();
	}
	$invoice = getCurrentInvoice($invoice["client_id"]);
	//Update it to pending
	$q = "UPDATE invoices SET status=:status WHERE id=:id";
	$st = $db->prepare($q);
	$status = "1";
	$st->bindParam(":status", $status);
	$st->bindParam(":id", $invoice["id"]);
	$st->execute();
?>
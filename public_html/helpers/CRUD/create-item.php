<?php require_once("../global.php"); ?>
<?php require_once("../require-log-in.php"); ?>
<?php
	$response = array("message"=>"", "success"=>false);
	if (empty($_POST["item"]))
	{
		$response["message"] = "There was an error.";
		echo json_encode($response);
		die();
	}
	$item = $_POST["item"];
	unset($_POST["item"]);
	if (empty($item["description"]) || empty($item["duration"]) || empty($item["invoice_id"]))
	{
		$response["message"] = "Missing required fields.";
		echo json_encode($response);
		die();
	}
	$desc = $item["description"];
	$dur = $item["duration"];
	$i_id = $item["invoice_id"];
	if (!is_numeric($dur))
	{
		$response["message"] = "Duration must be a number.";
		echo json_encode($response);
		die();
	}
	$dur = intval($dur);
	//Add item
	$q = "INSERT INTO items (description, duration, invoice_id) VALUES (:descr, :dur, :i_id)";
	$st = $db->prepare($q);
	$st->bindParam(":descr", $desc);
	$st->bindParam(":dur", $dur);
	$st->bindParam(":i_id", $i_id);
	$st->execute();
	$response["success"] = true;
	$response["message"] = "Item added";
	echo json_encode($response);
?>

<?php
	$q = "INSERT INTO invoices (client_id) VALUES (:client_id)";
	$st = $db->prepare($q);
	$st->bindParam(":client_id", $_POST["new-invoice"]["client_id"]);
	$st->execute();
?>
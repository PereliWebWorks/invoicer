<?php require_once("helpers/global.php"); ?>
<?php require_once("helpers/require-log-in.php"); ?>
<?php require_once("helpers/global-html-head.php"); ?>
<?php
	//Get user info
	$query = "SELECT * FROM users WHERE id=:id";
	$stmt = $db->prepare($query);
	$stmt->bindParam(":id", $_SESSION["user_id"]);
	$stmt->execute();
	$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
	<h2><?= $user["name"] ?></h2>
<?php require_once("helpers/global-html-foot.php"); ?>

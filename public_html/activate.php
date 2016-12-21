<?php require_once("helpers/global.php") ?>
<?php
	//If the url is invalid, redirect to sign up
	if (!isset($_GET["i"]) || !isset($_GET["c"]))
	{
		header("Location: http://" . HOST . "/sign-up.php");
	}
	$id = $_GET["i"];
	$code = $_GET["c"];
	//If the id doesn't exist, send to sign up
	$query = "SELECT activated, activation_code_digest FROM users WHERE id=:id";
	$stmt = $db->prepare($query);
	$stmt->bindParam(":id", $id);
	$stmt->execute();
	if ($stmt->rowCount() !== 1) //If bad id
	{
		header("Location: http://" . HOST . "/sign-up.php");
	}
	//If account is already activated
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	if ($result["activated"] === "1")
	{
		header("Location: http://" . HOST . "/log-in.php");
	}
	$code_digest = $result["activation_code_digest"];
	if (!password_verify($code, $code_digest))
	{
		header("Location: http://" . HOST . "/sign-up.php");
	}
	//Else, we're all good! Set the account to activated.
	$query = "UPDATE users SET activated=1 WHERE id=:id";
	$stmt = $db->prepare($query);
	$stmt->bindParam(":id", $id);
	$stmt->execute();
	logIn($id);
	setFlash("success", "Your account has been activated.");
	header("Location: http://" . HOST . "/index.php");
?>
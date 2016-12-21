<?php require_once("../global.php"); ?>
<?php
	if (!isset($_POST["log-in"]["email"]) || !isset($_POST["log-in"]["password"]))
	{
		echo "Password and email must be set.";
		die();
	}
	$query = "SELECT id, password_digest, activated FROM users WHERE email=:email";
	$stmt = $db->prepare($query);
	$stmt->bindParam(":email", $_POST["log-in"]["email"]);
	$stmt->execute();
	if ($stmt->rowCount() !== 1)//If bad email
	{
		echo "Invalid email address.";
		die();
	}
	$user = $stmt->fetch(PDO::FETCH_ASSOC);
	if (!password_verify($_POST["log-in"]["password"], $user["password_digest"]))
	{
		echo "Password incorrect.";
		die();
	}
	if ($user["activated"] === "0")
	{
		echo "You must activate your account. Check your email for an activation link.";
		die();
	}
	//If we're here, we're good!
	logIn($user["id"]);
	echo "SUCCESS";

?>
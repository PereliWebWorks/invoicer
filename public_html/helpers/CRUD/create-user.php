<?php require_once("../global.php"); ?>
<?php
	if (empty($_POST["new-user"]))
	{
		die();
	}
	$_POST = $_POST["new-user"];
	$_POST["new-user"] = null;

	if (empty($_POST["username"]))
	{
		echo "Name must be present.";
		die();
	}
	if (empty($_POST["email"]))
	{
		echo "Email must be present.";
		die();
	}
	if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL))
	{
		echo "Invalid email.";
		die();
	}
	$query = "SELECT email FROM users WHERE email=:email";
	$stmt = $db->prepare($query);
	$stmt->bindParam(":email", $_POST["email"]);
	$stmt->execute();
	if ($stmt->rowCount() !== 0)
	{
		echo "There is already an account set up with that email address.";
		die();
	}
	if (empty($_POST["password"]) || strlen($_POST["password"]) < 6 )
	{
		echo ($_POST["password"]);
		echo "Invalid password.";
		die();
	}
	$pwd_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);
	$_POST["password"] = null; 
	if (empty($_POST["password-confirmation"]))
	{
		echo "Invalid password confirmation.";
		die();
	}
	if (!password_verify($_POST["password-confirmation"], $pwd_hash))
	{
		echo "Passwords do not match.";
		die();
	}
	$_POST["password-confirmation"] = null;
	//Everything is valid
	$activation_code = md5(rand());
	$activation_code_digest = password_hash($activation_code, PASSWORD_DEFAULT);
	//Insert the new user into the database
	$query = "INSERT INTO users (name, email, password_digest, activation_code_digest) VALUES (:name, :email, :pwd, :ac_digest)";
	$stmt = $db->prepare($query);
	$stmt->bindParam(":name", $_POST["username"]);
	$stmt->bindParam(":email", $_POST["email"]);
	$stmt->bindParam(":pwd", $pwd_hash);
	$stmt->bindParam(":ac_digest", $activation_code_digest);
	$stmt->execute();
	//Send the email
	$query = "SELECT id FROM users WHERE email=:email";
	$stmt = $db->prepare($query);
	$stmt->bindParam(":email", $_POST["email"]);
	$stmt->execute();
	$id = $stmt->fetch(PDO::FETCH_ASSOC)["id"];
	$activation_url = HOST . "/activate.php?i={$id}&c={$activation_code}";
	$to = $_POST["email"];
	$subject = "Thanks for signing up with Invoicer";
	$headers[] = 'MIME-Version: 1.0';
	$headers[] = 'Content-type: text/html; charset=iso-8859-1';
	$headers[] = 'From: Invoicer <noreply@'.HOST.'>';
	$message = "<html><head><title>Invoicer | Account Activation</title></head>"
					. "<body><h2>Thaks for signing up with invoicer</h2>"
						. "<h3><a href='{$activation_url}'>Click here</a> to activate your account!</h3>"
					. "</body></html>";
	$result = mail($to, $subject, $message, implode("\r\n", $headers));
	if ($result)
	{
		echo "success";
	}
	else
	{
		echo "There was an issue sending the activation email. Try again later.";
		$query = "DELETE FROM users WHERE id=:id";
		$stmt = $db->prepare($query);
		$stmt->bindParam(":id", $id);
		$stmt->execute();
	}
?>





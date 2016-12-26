<?php require_once("../global.php"); ?>
<?php
	if (empty($_POST["log-in"]["email"]) || empty($_POST["log-in"]["password"]))
	{
		echo "Password and email must be set.";
		die();
	}
	$user = User::findBy("email", $_POST["log-in"]["email"])[0];
	if (!$user)
	{
		echo "Invalid email address.";
		die();
	}
	if (!password_verify($_POST["log-in"]["password"], $user->password_digest))
	{
		unset($_POST["log-in"]["password"]);
		echo "Password incorrect.";
		die();
	}
	unset($_POST["log-in"]["password"]);
	if (!$user->activated)
	{
		echo "You must activate your account. Check your email for an activation link.";
		die();
	}
	//If we're here, we're good!
	$user->logIn();
	//If 'remember me' is set
	if (!empty($_POST["log-in"]["remember"]))
	{
		$user->remember();
	}
	else
	{
		$user->forget();
	}
	echo "SUCCESS";

?>
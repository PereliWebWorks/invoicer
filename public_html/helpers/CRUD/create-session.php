<?php require_once("../global.php"); ?>
<?php
	$r = new Response();
	if (empty($_POST["log-in"]["email"]) || empty($_POST["log-in"]["password"]))
	{
		$r->message = "Password and email must be set.";
		echo $r;
		die();
	}
	$user = User::findFirstBy("email", $_POST["log-in"]["email"]);
	if (!$user)
	{
		$r->message = "Invalid email address.";
		echo $r;
		die();
	}
	if (!password_verify($_POST["log-in"]["password"], $user->password_digest))
	{
		unset($_POST["log-in"]["password"]);
		$r->message = "Password incorrect.";
		echo $r;
		die();
	}
	unset($_POST["log-in"]["password"]);
	if (!$user->activated)
	{
		$r->message = "You must activate your account. Check your email for an activation link.";
		echo $r;
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
	$r->success = true;
	$r->message = "Success";
	echo $r;

?>
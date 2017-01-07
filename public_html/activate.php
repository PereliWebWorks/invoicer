<?php require_once("helpers/global.php") ?>
<?php
	//If the url is invalid, redirect to sign up
	if (!isset($_GET["i"]) || !isset($_GET["c"]))
	{
		header("Location: http://" . HOST . "/sign-up.php");
		die();
	}
	$id = $_GET["i"];
	$code = $_GET["c"];
	$user = User::find($id);
	//If the id doesn't exist, send to sign up
	if (!$user) //If bad id
	{
		header("Location: http://" . HOST . "/sign-up.php");
		die();
	}
	//If account is already activated
	if ($user->activated)
	{
		header("Location: http://" . HOST . "/log-in.php");
		die();
	}
	if (!password_verify($code, $user->activation_code_digest))
	{
		header("Location: http://" . HOST . "/sign-up.php");
		die();
	}
	//Else, we're all good! Set the account to activated.
	$user->update("activated", true);
	$user->logIn();
	setFlash("success", "Your account has been activated.");
	header("Location: http://" . HOST . "/index.php");
	die();
?>
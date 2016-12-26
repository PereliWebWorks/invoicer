<?php require_once("global.php"); ?>
<?php
	if (!loggedIn())
	{
		if (!currentRememberCookiesValid())
		{
			header("Location: http://" . HOST . "/sign-up.php");
			die();
		}
		User::find($_COOKIE["user_id"])->logIn()->remember();
		setFlash("success", "Welcome back!");
	}
?>
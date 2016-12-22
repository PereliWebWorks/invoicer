<?php require_once("global.php"); ?>
<?php
	if (!loggedIn())
	{
		if (!currentUserIsRemembered())
		{
			header("Location: http://" . HOST . "/sign-up.php");
			die();
		}
		logIn($_COOKIE["user_id"]);
		setFlash("success", "Welcome back!");
	}
?>
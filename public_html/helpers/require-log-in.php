<?php require_once("global.php"); ?>
<?php
	if (!loggedIn())
	{
		header("Location: http://" . HOST . "/sign-up.php");
		die();
	}
?>
<?php require_once("helpers/global.php"); ?>
<?php
	if (!loggedIn())
	{
		header("Location: http://" . HOST . "/sign-up.php");
	}
?>
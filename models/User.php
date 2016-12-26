<?php
	class User extends Model
	{
		const TABLE_NAME = "users";
		static $immutable_fields = array("email");
		function getClients()
		{
			return Client::findBy("user_id", $this->data["id"]);
		}

		function remember()
		{
			$remember_token = md5(rand());
			$remember_digest = password_hash($remember_token, PASSWORD_DEFAULT);
			//Set cookie
			setcookie("remember_token", $remember_token, time() + (86400 * 30), "/");
			setcookie("user_id", $_SESSION["user_id"], time() + (86400 * 30), "/");
		}
	}
?>
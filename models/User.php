<?php
	final class User extends Model
	{
		const TABLE_NAME = "users";
		static $immutable_fields = array("email");
		function logIn()
		{
			$_SESSION["user"] = $this;
			return $this;
		}
		function logOut()
		{
			$this->forget();
			unset($_SESSION["user"]);
			return $this;
		}
		function getClients()
		{
			return Client::findBy("user_id", $this->data["id"]);
		}

		function remember()
		{
			//If this user isn't current user, return false.
			if ($this !== getCurrentUser()){throw new Error("Attempting to remember non-current user.");}
			$remember_token = md5(rand());
			$remember_digest = password_hash($remember_token, PASSWORD_DEFAULT);
			//Set cookie
			setcookie("remember_token", $remember_token, time() + (86400 * 30), "/");
			setcookie("user_id", $this->data['id'], time() + (86400 * 30), "/");
			$this->update("remember_digest", $remember_digest);
			return $this;
		}
		function forget()
		{
			if ($this !== getCurrentUser()){throw new Error("Attempting to remember non-current user.");}
			setcookie("remember_token", "", time() - (86400), "/");
			setcookie("user_id", "", time() - (86400), "/");
			$this->update("remember_digest", null);
			return $this;
		}
	}
?>
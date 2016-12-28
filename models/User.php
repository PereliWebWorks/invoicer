<?php
	final class User extends Model
	{
		const TABLE_NAME = "users";
		protected static $immutable_fields = array("email");
		public static $columns;
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

		public function fieldIsValid($field, $value)
		{
			//If the base validator fails, return false.
			//Base validator will also run each field through this validator.
			if (!parent::fieldIsValid($field, $value)){return false;}
			//If field is set, so must value, and vice versa
			if (!empty($field)) //If field (and value) are not empty, validate them
			{
				switch ($field)
				{
					case "email": //Validate proper form
						if (!filter_var($value, FILTER_VALIDATE_EMAIL))
						{
							echo "Invalid email.";
							return false;
						}
						break;
				}	
			}
			return true;
		}
	}
	User::init();
?>














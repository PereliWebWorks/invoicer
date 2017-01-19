<?php
	final class User extends Model
	{
		const TABLE_NAME = "users";
		protected static $immutable_fields = array("email");
		public static $columns;
		protected static $require_owner_login = array(
				"save" => true,
				"create" => false,
				"update" => true,
			);
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
			$r = new Response();
			//If the base validator fails, return false.
			//Base validator will also run each field through this validator.
			$field_response = parent::fieldIsValid($field, $value);
			if (!$field_response->success){return $field_response;}
			//If field is set, so must value, and vice versa
			if (!empty($field)) //If field (and value) are not empty, validate them
			{
				switch ($field)
				{
					case "email": //Validate proper form
						if (!filter_var($value, FILTER_VALIDATE_EMAIL))
						{
							$r->message = "Invalid email.";
							return $r;
						}
						break;
				}	
			}
			$r->success = true;
			return $r;
		}

		function create()
		{
			$r = new Response();
			$activation_code = md5(rand());
			$this->activation_code_digest = password_hash($activation_code, PASSWORD_DEFAULT);
			if (!isset($this->password) || !isset($this->password_confirmation))
			{
				throw new Error("Password and password confirmation must be set when creating a new user.");
				$r->message = "Password and password confirmation must be set when creating a new user.";
				return $r;
			}
			if ($this->password !== $this->password_confirmation)
			{
				$r->message = "Passwords do not match.";
				return $r;
			}
			$this->password_digest = password_hash($this->password, PASSWORD_DEFAULT);
			unset($this->password);
			unset($this->password_confirmation);
			$r = parent::create();
			if ($r->success && $r->new) //If the user was newly created. send the activation email.
			{
				$id = $this->id;
				$activation_url = HOST . "/activate.php?i={$id}&c={$activation_code}";
				$mail = new PHPMailer();
				$mail->setFrom("noreply@" . HOST, "Invoicer");
				$mail->addAddress($this->email);
				$mail->Subject = "Thanks for signing up with Invoicer";
				$mail->isHTML();
				/*
				$to = $this->email;
				$subject = "Thanks for signing up with Invoicer";
				$headers[] = 'MIME-Version: 1.0';
				$headers[] = 'Content-type: text/html; charset=iso-8859-1';
				$headers[] = 'From: Invoicer <noreply@'.HOST.'>';
				*/
				$message = "<html><head><title>Invoicer | Account Activation</title></head>"
								. "<body><h2>Thaks for signing up with invoicer</h2>"
									. "<h3><a href='{$activation_url}'>Click here</a> to activate your account!</h3>"
								. "</body></html>";
				$altMessage = "Thank you for signing up with invoicer. Click the following link to activate your account. \n";
				$altMessage .= "$activation_url";
				$mail->Body = $message;
				$mail->AltBody = $altMessage;
				$mail_success = $mail->send();
				if ($mail_success)
				{
					$r->message = "You've been added! Check your email for a confirmation link.";
					$r->success = true;
				}
				else
				{
					$r->message = "There was an issue sending the activation email. Try again later.";
					$r->success = false;
					$this->destroy();
				}
			}
			return $r;
		}


		function __get($field)
		{
			switch ($field)
			{
				case "owner":
					return $this;
				default:
					return parent::__get($field);
			}
		}
	}
	User::init();
?>














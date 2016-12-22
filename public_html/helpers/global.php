<?php
	session_start();
	define("HOST", "invoicer.drewpereli.com");

	error_reporting(E_ALL|E_STRICT);
	ini_set('display_errors', 1);
	ini_set('sendmail_from', "noreply@" . HOST);
	
	
	require_once("connectToDB.php");

	function logInViaCookie($id)
	{
		$_SESSION["user_id"] = $id;
		rememberUser();
	}
	function logIn($id)
	{
		$_SESSION["user_id"] = $id;
	}
	function logOut()
	{
		forgetUser();
		$_SESSION["user_id"] = null;
	}
	function loggedIn()
	{
		if (isset($_SESSION["user_id"]))
		{
			return true;
		}
		return false;
	}
	function rememberUser()
	{
		if (!loggedIn()){return false;}
		$remember_token = md5(rand());
		$remember_digest = password_hash($remember_token, PASSWORD_DEFAULT);
		//Set cookie
		setcookie("remember_token", $remember_token, time() + (86400 * 30), "/");
		setcookie("user_id", $_SESSION["user_id"], time() + (86400 * 30), "/");
		//Insert remember token into db
		$db = $GLOBALS["db"];
		$query = "UPDATE users SET remember_digest=:remember_digest WHERE id=:id";
		$st = $db->prepare($query);
		$st->bindParam(":remember_digest", $remember_digest);
		$st->bindParam(":id", $_SESSION["user_id"]);
		$st->execute();
	}
	function forgetUser()
	{
		if (!loggedIn()){return false;}
		setcookie("remember_token", "", time() - (86400), "/");
		setcookie("user_id", "", time() - (86400), "/");
		$query = "UPDATE users SET remember_digest=NULL WHERE id=:id";
		$db = $GLOBALS["db"];
		$st = $db->prepare($query);
		$st->bindParam(":id", $_SESSION["user_id"]);
		$st->execute();
	}
	//When a user goes to the site, this will see if they are remembered. If so it will log them in.
	function currentUserIsRemembered()
	{
		if (empty($_COOKIE["user_id"]) || empty($_COOKIE["remember_token"]))
		{
			return false;
		}
		$id = $_COOKIE["user_id"];
		$token = $_COOKIE["remember_token"];
		$q = "SELECT remember_digest FROM users WHERE id=:id";
		$db = $GLOBALS["db"];
		$st = $db->prepare($q);
		$st->bindParam(":id", $id);
		$st->execute();
		$digest = $st->fetch(PDO::FETCH_ASSOC)["remember_digest"];
		//If the token is invalid
		if (!password_verify($token, $digest))
		{
			return false;
		}
		return true;
	}

	//Returns an array with all the info about the current user
	function currentUser()
	{
		if (!loggedIn()){return false;}
		$db = $GLOBALS['db'];
		$q = "SELECT * FROM users WHERE id=:id";
		$st = $db->prepare($q);
		$st->bindParam(":id", $_SESSION["user_id"]);
		$st->execute();
		return $st->fetch(PDO::FETCH_ASSOC);
	}
	function getClient($id)
	{
		if (!loggedIn()){return false;}
		$db = $GLOBALS['db'];
		$q = "SELECT * FROM clients WHERE id=:id";
		$st = $db->prepare($q);
		$st->bindParam(":id", $id);
		$st->execute();
		if ($st->rowCount() === 0){return false;}
		return ($st->fetch(PDO::FETCH_ASSOC));
	}

	function setFlash($type, $message)
	{
		$_SESSION["flash"][$type] = $message;
	}
	function flash($type)
	{
		echo $_SESSION["flash"][$type];
		unset($_SESSION["flash"][$type]);
	}

	function getIntFromPhone($phone_number)
	{
		$p = preg_replace('/[^0-9]/','',$phone_number);
		$p = intval($p);
		return $p;
	}

	function generateForm($fields, $form_name)
	{
		echo "<form id='{$form_name}_form'>";
		for ($i = 0 ; $i < sizeof($fields) ; $i++)
		{
			$field = $fields[$i];
			$label = $field["label"];
			$type = $field["type"];
			$name = $form_name . "[" . $field["name"] . "]";
			$id = "{$form_name}_{$field['name']}";
			$required_class = isset($field["required"]) && $field["required"] != false ? "required" : "";
			$label = $required_class === "required" ? $field["label"] . " *" : $field["label"];
			$element;
			if ($type === "checkbox")
			{
				$checked = $field["checked"] ? "true" : "false";
				$element = "<div class='form-group'>
								<label for='$id'>$label</label>
								<input type='checkbox' name='$name' id='$id' checked='$checked'>
							</div>";
			}
			elseif ($type === "HOURLY_RATE")
			{
				$element = "<div class='form-group'>
								<label for='$id'>$label</label>
								<div class='input-group'>
									<div class='input-group-addon'>$</div>
									<input type='number' name='$name' id='$id' class='form-control $required_class'>
									<span class='input-group-addon'>per hour</span>
								</div>
							</div>";
			}
			elseif ($type === "STATE_DROPDOWN")
			{
				$element = "<div class='form-group'>"
							. "<label for='$id'>$label</label><br/>";
				$element .= "<select name='{$name}' id='{$id}' class='$required_class'>
								<option value=''></option>
								<option value='AL'>Alabama</option>
								<option value='AK'>Alaska</option>
								<option value='AZ'>Arizona</option>
								<option value='AR'>Arkansas</option>
								<option value='CA'>California</option>
								<option value='CO'>Colorado</option>
								<option value='CT'>Connecticut</option>
								<option value='DE'>Delaware</option>
								<option value='DC'>District Of Columbia</option>
								<option value='FL'>Florida</option>
								<option value='GA'>Georgia</option>
								<option value='HI'>Hawaii</option>
								<option value='ID'>Idaho</option>
								<option value='IL'>Illinois</option>
								<option value='IN'>Indiana</option>
								<option value='IA'>Iowa</option>
								<option value='KS'>Kansas</option>
								<option value='KY'>Kentucky</option>
								<option value='LA'>Louisiana</option>
								<option value='ME'>Maine</option>
								<option value='MD'>Maryland</option>
								<option value='MA'>Massachusetts</option>
								<option value='MI'>Michigan</option>
								<option value='MN'>Minnesota</option>
								<option value='MS'>Mississippi</option>
								<option value='MO'>Missouri</option>
								<option value='MT'>Montana</option>
								<option value='NE'>Nebraska</option>
								<option value='NV'>Nevada</option>
								<option value='NH'>New Hampshire</option>
								<option value='NJ'>New Jersey</option>
								<option value='NM'>New Mexico</option>
								<option value='NY'>New York</option>
								<option value='NC'>North Carolina</option>
								<option value='ND'>North Dakota</option>
								<option value='OH'>Ohio</option>
								<option value='OK'>Oklahoma</option>
								<option value='OR'>Oregon</option>
								<option value='PA'>Pennsylvania</option>
								<option value='RI'>Rhode Island</option>
								<option value='SC'>South Carolina</option>
								<option value='SD'>South Dakota</option>
								<option value='TN'>Tennessee</option>
								<option value='TX'>Texas</option>
								<option value='UT'>Utah</option>
								<option value='VT'>Vermont</option>
								<option value='VA'>Virginia</option>
								<option value='WA'>Washington</option>
								<option value='WV'>West Virginia</option>
								<option value='WI'>Wisconsin</option>
								<option value='WY'>Wyoming</option>
							</select>";	
				$element .= "</div>";
			}
			else
			{
				$element = "<div class='form-group'>"
							. "<label for='$id'>$label</label>"
							. "<input type='$type' name='$name' id='$id' class='form-control {$required_class}'/>"
						. "</div>";
			}

			echo $element;
		}
		echo "<input type='button' value='Submit' id='{$form_name}_submit_btn' name='{$form_name}[submit]' class='btn btn-default' />";
		echo "</form>";
	}
?>
<?php
	define("HOST", "invoicer.drewpereli.com");
?>
<?php
	error_reporting(E_ALL|E_STRICT);
	ini_set('display_errors', 1);
	ini_set('sendmail_from', "noreply@" . HOST);
?>
<?php session_start(); ?>
<?php
	function logIn($id)
	{
		$_SESSION["user_id"] = $id;
	}
	function logOut()
	{
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

	function setFlash($type, $message)
	{
		$_SESSION["flash"][$type] = $message;
	}
	function flash($type)
	{
		echo $_SESSION["flash"][$type];
		$_SESSION["flash"][$type] = null;
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
			if ($type === "MONEY")
			{
				$element = "<div class='form-group'>
								<label for='$name'>$label</label>
								<div class='input-group'>
									<div class='input-group-addon'>$</div>
									<input type='number' name='$name' id='$id'>
								</div>
							</div>";
			}
			elseif ($type === "STATE_DROPDOWN")
			{
				$element = "<div class='form-group'>"
							. "<label for='$name'>$label</label><br/>";
				$element .= "<select name='{$name}' id='{$id}'>
								<option value='null'></option>
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
							. "<label for='$name'>$label</label>"
							. "<input type='$type' name='$name' id='$id' class='form-control {$required_class}'/>"
						. "</div>";
			}

			echo $element;
		}
		echo "<input type='button' value='Submit' id='{$form_name}_submit_btn' name='{$form_name}[submit]' class='btn btn-default' />";
		echo "</form>";
	}
?>
<?php require_once("connectToDB.php"); ?>
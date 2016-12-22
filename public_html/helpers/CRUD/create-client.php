<?php require_once("../global.php"); ?>
<?php require_once("../require-log-in.php"); ?>
<?php
//Validate required data
	if (empty($_POST["new-client"]))
	{
		echo "Something went wrong. Try again later.";
		die();
	}
	$new_client = $_POST["new-client"];
	$_POST["new-client"] = null;
	$required_fields = array("name", "email", "default_rate");
	//Make sure all required fields are present
	foreach ($required_fields as $field)
	{
		if (empty($new_client[$field]))
		{
			echo "You're missing a required field: $field";
			die();
		}
	}
	//Make sure only valid fields are present, and set empty non-required fields to null
	$stmt = $db->prepare("DESCRIBE clients");
	$stmt->execute();
	$table_fields = $stmt->fetchAll(PDO::FETCH_COLUMN);
	foreach ($new_client as $field_name => $value)
	{
		if (!in_array($field_name, $table_fields)) //If there is a field present that is not also a database field
		{
			echo "Invalid field name: $field_name";
			die();
		}
		if (empty($value))//If it's an empty field, we know it's not required so we can unset it
		{
			unset($new_client[$field_name]);
		}
	}
	//If "rate" isn't numeric, echo an error
	if (!is_numeric($new_client["default_rate"]))
	{
		echo "'Rate' must be a number.";
		die();
	}
	$rate = floatval($new_client["default_rate"]);
	$rate *= 100; //Convert to cents/
	$rate = abs($rate); //Conver to positive just in case someone is fucking around.
	$rate = intval($rate);
	$new_client["default_rate"] = $rate;
	//validate email address
	if (!filter_var($new_client["email"], FILTER_VALIDATE_EMAIL))
	{
		echo "Invalid email address.";
		die();
	}
	//************************
	//Input is valid past here
	//************************
	//Reformat phone number if it's set
	if (!empty($new_client["phone"]))
	{
		$new_client["phone"] = getIntFromPhone($new_client["phone"]);
	}
	//Set 'user_id' reference to current logged-in user.
	$new_client["user_id"] = $_SESSION["user_id"];
	//Generate the query
	$query = "INSERT INTO clients ";
	$query_part_1 = "(";
	$query_part_2 = "VALUES (";
	$i = 0;
	foreach ($new_client as $field_name=>$value)
	{
		$appender = $i === (sizeof($new_client) - 1) ? ") " : ", ";
		$query_part_1 .= $field_name . $appender;
		$query_part_2 .= "':$field_name'$appender";
		$i++;
	}
	$query .= $query_part_1 . $query_part_2;
	$stmt = $db->prepare($query);
	//Iterate through the client array again to bind the params
	foreach ($new_client as $field_name=>$value)
	{
		$stmt->bindParam(":{$field_name}", $value);
	}
	$stmt->execute();
	echo "success";
?>




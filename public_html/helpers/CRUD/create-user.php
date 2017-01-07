<?php require_once("../global.php"); ?>
<?php
	$r = new Response();
	if (empty($_POST["new-user"]))
	{
		echo $r;
		die();
	}
	$new_user_info = $_POST["new-user"];
	unset($_POST);
	if (empty($new_user_info['password']))
	{
		echo $r;
		die();
	}
	$new_user_info["password_digest"] = password_hash($new_user_info["password"], PASSWORD_DEFAULT);
	unset($new_user_info["password"]);
	if (!password_verify($new_user_info["password-confirmation"], $new_user_info["password_digest"]))
	{
		echo $r;
		die();
	}
	$activation_code = md5(rand());
	$new_user_info["activation_code_digest"] = password_hash($activation_code, PASSWORD_DEFAULT);
	$new_user = new User($new_user_info);
	$save_response = $new_user->save();
	if (!$save_response->success)
	{
		$response["message"] = $save_response["message"];
		$new_user->destroy();
		echo json_encode($response);
		die();
	}
	//Send the email
	$id = $new_user->id;
	$activation_url = HOST . "/activate.php?i={$id}&c={$activation_code}";
	$to = $new_user->email;
	$subject = "Thanks for signing up with Invoicer";
	$headers[] = 'MIME-Version: 1.0';
	$headers[] = 'Content-type: text/html; charset=iso-8859-1';
	$headers[] = 'From: Invoicer <noreply@'.HOST.'>';
	$message = "<html><head><title>Invoicer | Account Activation</title></head>"
					. "<body><h2>Thaks for signing up with invoicer</h2>"
						. "<h3><a href='{$activation_url}'>Click here</a> to activate your account!</h3>"
					. "</body></html>";
	$result = mail($to, $subject, $message, implode("\r\n", $headers));
	if ($result)
	{
		$response["message"] = "You've been added! Check your email for a confirmation link.";
		$response["success"] = true;
		echo json_encode($response);
	}
	else
	{
		echo "There was an issue sending the activation email. Try again later.";
		$user->destroy();
	}
?>





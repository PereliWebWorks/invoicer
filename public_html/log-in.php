<?php require_once("helpers/global.php"); ?>
<?php
	if (loggedIn())
	{
		headers("Location: http://" . HOST);
	}
?>
<?php require_once("helpers/global-html-head.php"); ?>
<h1>Log In</h1>
<?php
	$form_fields = array(
		array("label"=>"Email", "name"=>"email", "type"=>"text", "required"=>true),
		array("label"=>"Password", "name"=>"password", "type"=>"password", "required"=>true)
		);
	generateForm($form_fields, "log-in");
?>
<div>Don't have an account yet? <a href="sign-up.php">Sign Up!</a></div>
<div id="response" class="hidden message">

<script>
	$("#log-in_submit_btn").click(function(){
		var valid_input = validateRequiredFields("log-in_form");
		var message = valid_input ? "" : "Missing required fields";
		if (valid_input)
		{
			$("#response").removeClass("hidden")
				.removeClass("bg-danger")
				.removeClass("text-danger")
				.addClass("bg-success text-success")
				.html("Processing...");
			var json = $("#log-in_form").serializeJSON();
			$.ajax({
				type: "POST",
				url: "helpers/CRUD/create-session.php",
				data: json
			}).done(function(data)
			{
				data = $.trim(data);
				if (data === "SUCCESS")
				{
					//Redirect to index
					window.location.replace("index.php");
				}
				else
				{
					$("#response").removeClass("hidden")
						.addClass("bg-danger text-danger")
						.html(data);
				}
			});
		}
		else
		{
			$("#response").removeClass("hidden")
				.addClass("bg-danger text-danger")
				.html(message);
		}
	});
</script>
<?php require_once("helpers/global.php"); ?>
<?php require_once("helpers/global-html-head.php"); ?>
	<h1>Sign Up</h1>
	<?php
		$form_fields = array(
				array("label"=>"Name", "name"=>"username", "type"=>"text", "required"=>true),
				array("label"=>"Email", "name"=>"email", "type"=>"email", "required"=>true),
				array("label"=>"Password", "name"=>"password", "type"=>"password", "required"=>true),
				array("label"=>"Confirm Password", "name"=>"password-confirmation", "type"=>"password", "required"=>true)
			);
		generateForm($form_fields, "new-user");
	?>
	<div>Already a user? <a href="log-in.php">Log In</a></div>
	<div id="response" class="hidden message">
	</div>
	<script>
		$("#new-user_submit_btn").click(function(){
			//validate data
			var valid_input = validateRequiredFields("new-user_form");
			var message = valid_input ? "" : "Missing required fields";
			//Make sure the passwords are equal
			if (valid_input && $("#new-user_password").val() !== $("#new-user_password-confirmation").val())
			{
				valid_input = false;
				message = "Passwords don't match";
				$("#new-user_password").parent().addClass("form-error");
				$("#new-user_password-confirmation").parent().addClass("form-error");
			}

			//if valid data, send post request to the create user script
			if (valid_input)
			{
				$("#response").removeClass("hidden")
					.removeClass("bg-danger")
					.removeClass("text-danger")
					.addClass("bg-success text-success")
					.html("Processing...");
				var json = $("#new-user_form").serializeJSON();
				$.ajax({
					type: "POST",
					url: "helpers/CRUD/create-user.php",
					data: json,
				}).done(function(data){
					data = $.trim(data);
					if (data === "success")
					{
						$("#response").html("You've been added! Check your email for a confirmation link.")
					}
					else
					{
						$("#response").removeClass("bg-success text-success")
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
<?require_once("helpers/global-html-foot.php"); ?>
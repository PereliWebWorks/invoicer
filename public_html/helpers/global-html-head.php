<html>
<head>
	<!-- jQuery -->
	<script src="jQuery/jquery.js"></script>
	<!-- jquery ui -->
	<link href="jQuery/jQuery_ui/jquery-ui.min.css" type="text/css" rel="stylesheet" />
	<link href="jQuery/jQuery_ui/jquery-ui.structure.min.css" type="text/css" rel="stylesheet" />
	<link href="jQuery/jQuery_ui/jquery-ui.theme.min.css" type="text/css" rel="stylesheet" />
	<script src="jQuery/jQuery_ui/jquery-ui.min.js"></script>
	<!-- jQuery serialize stuff -->
	<script src="jQuery/jquery.serializejson.js"></script>
	<!-- Bootstrap -->
	<link rel="stylesheet" href="custom_bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" href="custom_bootstrap/css/bootstrap-theme.min.css">
	<script src="custom_bootstrap/js/bootstrap.min.js"></script>
	<!-- Custom global css -->
	<link rel="stylesheet" href="css/style.css" type="text/css" />
	<script>
		function validateRequiredFields(formId)
		{
			var valid = true;
			$("#" + formId + " input.required").each(function(index, element){
				if (!$(element).val()) //If a required input is empty
				{
					valid = false;
					$(element).parent().addClass("form-error");
				}
				else
				{
					$(element).parent().removeClass("form-error");
				}
			});
			return valid;
		}
	</script>
</head>
<body>
	<nav class="navbar navbar-default">
		<div class="container-fluid">
		    <div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="index.php">Invoicer</a>
		    </div>
		    <div class="collapse navbar-collapse" id="navbar">
		    	<?php if (loggedIn()) : ?>
		    		<ul class="nav navbar-nav">
		    			<li><a href="index.php">Profile</a></li>
		    			<li><a href="new-client.php">Add Client</a></li>
		    		</ul>
	    		<?php endif ?>
	    		<ul class="nav navbar-nav navbar-right">
	    			<?php if (loggedIn()) : ?>
	    				<li><a id="log-out-btn" href="#">Log Out</a></li>
	    			<?php else : ?>
	    				<li><a href="sign-up.php">Sign Up</a></li>
	    				<li><a href="log-in.php">Log In</a></li>
	    			<?php endif ?>
	    		</ul>
		    </div>
		</div>
	</nav>
	<script>
		$("#log-out-btn").click(function(){
			$.ajax({
				url: "helpers/CRUD/destroy-session.php"
			}).done(function(){
				window.location.replace("log-in.php");
			});
		});
	</script>

	<div class="container-fluid" id="content">




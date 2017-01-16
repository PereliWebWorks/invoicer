<?php require_once("renderer.php"); ?>
<html>
<?php require_once("head.php"); ?>
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
	<script type="text/javascript">
		$("#log-out-btn").click(function(){
			$.ajax({
				url: "helpers/CRUD/destroy-session.php"
			}).done(function(){
				window.location.replace("log-in.php");
			});
		});
		$( function() {
			$( "[title]" ).tooltip();
		} );
	</script>

	<div class="container-fluid" id="content">
		<?php require_once("helpers/flasher.php"); ?>




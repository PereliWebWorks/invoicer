<?php
	require_once("helpers/global.php");
	require_once("helpers/require-log-in.php");
	
	if (empty($_GET["c"]))
	{
		setFlash("danger", "You must specify a client.");
		header("Location: index.php");
		die();
	}
	$client_id = $_GET["c"];
	//If the client doesn't belong to the logged in user or doesn't exist, redirect to index
	$client = getClient($client_id);
	$user = currentUser();
	if (!$client || $client["user_id"] !== $_SESSION["user_id"])
	{
		setFlash("danger", "That is not a client of yours.");
		header("Location: index.php");
		die();
	}
?>
<?php require_once("helpers/global-html-head.php"); ?>

	<div class="row cient-info">
		<h1 class="col-xs-12"><?= $client["name"]; ?></h1>
		<div class="col-xs-12"><?= $client["email"]; ?></div>
		<div class="col-xs-12">$<?= ($client["default_rate"] / 100) ?> per hour</div>
	</div>
	<hr/>
	<div class="row item_form-container">
		<h3 class="col-xs-12">Add Invoice Item</h3>
		<form id="item_form" class="">
			<div class="form-group col-xs-8">
				<label for="item_description">Description</label>
				<input type="text" name="item[description]" id="item_description" class="form-control required" />
			</div>
			<div class="form-group col-xs-2">
				<label for="item_duration">Duration</label>
				<div class="input-group">
					<input type="number" name="item[duration]" id="item_duration" class="form-control required" />
					<div class="input-group-addon">minutes</div>
				</div>
			</div>
			<div class="form-group col-xs-2">
				<label>&nbsp;</label><br/>
				<div type="" id="item_submit-btn" class="btn btn-default">Add Item</div>
			</div>
		</form>
	</div>
	<hr/>
	<div class="row invoice-container">
		<div class="invoice col-sm-8 col-xs-12 col-sm-offset-2">
			<div class="client-info col-xs-4">
				<div class="col-xs-12"><i>To:</i></div>
				<div class="col-xs-12"><?= $client["name"]; ?></div>
				<div class="col-xs-12"><?= $client["email"]; ?></div>
				<?php if (!empty($client["phone"])) : ?>
					<div class="col-xs-12"><?= $client["phone"]; ?></div>
				<?php endif ?>
			</div>
			<div class="user-info col-xs-4 col-xs-offset-4">
				<div class="col-xs-12"><i>From:</i></div>
				<div class="col-xs-12"><?= $user["name"]; ?></div>
				<div class="col-xs-12"><?= $user["email"]; ?></div>
				<?php if (!empty($user["phone"])) : ?>
					<div class="col-xs-12"><?= $user["phone"]; ?></div>
				<?php endif ?>
			</div>
		</div>
	</div>
	
<?php require_once("helpers/global-html-foot.php"); ?>











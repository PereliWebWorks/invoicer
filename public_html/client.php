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
	if (!$client || $client["user_id"] !== $_SESSION["user_id"])
	{
		setFlash("danger", "That is not a client of yours.");
		header("Location: index.php");
		die();
	}
	$user = currentUser();
	$currentInvoice = getCurrentInvoice($client_id);
	$rate = $client["default_rate"];
	//Get the invoice items
	$items = getInvoiceItems($currentInvoice);
?>
<?php require_once("helpers/global-html-head.php"); ?>
	<script> 
		var current_total_duration = <?= getTotalDuration($currentInvoice); ?>; //Cost in cents
		var current_total_cost = <?= getTotalCost($currentInvoice); ?>; //Duration in minutes
	</script>
	<!-- CLIENT INFO -->
	<div class="row cient-info">
		<h1 class="col-xs-12"><?= $client["name"]; ?></h1>
		<div class="col-xs-12"><?= $client["email"]; ?></div>
		<div class="col-xs-12">$<?= ($client["default_rate"] / 100) ?> per hour</div>
	</div>
	<!-- END CLIENT INFO -->
	<hr/>
	<!-- ITEM FORM -->
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
			<input type="hidden" name="item[invoice_id]" value="<?=$currentInvoice['id'];?>" class="required" />
			<div class="form-group col-xs-2">
				<label>&nbsp;</label><br/>
				<div type="" id="item_submit-btn" class="btn btn-default">Add Item</div>
			</div>
		</form>
		<div id="response" class="hidden message col-xs-6 col-xs-offset-3"></div>
		<script>
			$("#item_submit-btn").click(function(){
				var valid_input = validateRequiredFields("item_form");
				var message = valid_input ? "" : "Missing required fields";
				//Should validate number field
				if (valid_input)
				{
					$("#response").removeClass("hidden")
						.removeClass("bg-danger")
						.removeClass("text-danger")
						.addClass("bg-success text-success")
						.html("Processing...");
					var json = $("#item_form").serializeJSON();
					$.ajax({
						type: "POST",
						url: "helpers/CRUD/create-item.php",
						data: json,
					}).done(function(data){
						data = $.trim(data);
						data = $.parseJSON(data);
						if (data["success"])
						{
							$("#response").html("Item added;")
							//Add invoice item
							var description_string = json.item.description;
							var duration_string = `(${json.item.duration} minutes)`;
							var rate = <?= $rate; ?>;
							var cost_string = "$" + (rate / 100 * json.item.duration / 60).toFixed(2);
							$("#item-table").append(
								$(document.createElement("tr"))
									.html("<td>" + description_string + " " + duration_string + "</td>"
											+ "<td>" + cost_string + "</td>")
							);
							current_total_cost = Number(current_total_cost);
							current_total_duration = Number(current_total_duration);
							current_total_cost += Number(rate * json.item.duration / 60);
							current_total_duration += Number(json.item.duration);
							//Update total cost and total duration
							//first total cost
							$("#total-cost").html((current_total_cost / 100).toFixed(2));
							$("#total-duration").html(current_total_duration);
						}
						else
						{
							$("#response").removeClass("bg-success text-success")
								.addClass("bg-danger text-danger")
								.html(data["message"]);
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
	</div>
	<!-- END ITEM FORM -->
	<hr/>
	<!-- INVOICE -->
	<div class="row invoice-container">
		<div class="invoice col-sm-8 col-xs-12 col-sm-offset-2">
			<div class="col-xs-12 header-container">
				<h1 class="col-xs-12">Invoicer for <?= $client["name"]; ?></h1>
			</div>
			<div class="col-xs-12 id-container">
				<div class="col-xs-12">ID: <?= getInvoiceSlug($currentInvoice); ?></div>
			</div>
			<div class="col-xs-12">&nbsp;</div>
			<div class="client-info col-xs-4">
				<div class="col-xs-12"><i>To:</i></div>
				<div class="col-xs-12"><?= $client["name"]; ?></div>
				<?php if (!empty($client["company"])) : ?>
					<div class="col-xs-12"><?= $client["company"]; ?></div>
				<?php endif ?>
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
			<div class="col-xs-12">&nbsp;</div>
			<div class="col-xs-12 rate-container">
				<div class="col-xs-12">$<?= ($client["default_rate"] / 100); ?> per hour</div>
			</div>
			<div class="col-xs-12">&nbsp;</div>
			<div class="col-xs-10 col-xs-offset-1">
				<table class="col-xs-12 table table-responsive" id="item-table">
					<tr><th>Item</th><th>Cost</th></tr>
					<?php foreach($items as $item) : ?>
						<tr>
							<td>
								<?= $item["description"]; ?> (<?= $item["duration"]; ?> minutes)
							</td>
							<td>
								$
								<?php //Get cost
									$hours = $item["duration"] / 60;
									echo number_format(($rate * $hours / 100), 2); 
								?>
							</td>
						</tr>
					<?php endforeach ?>
				</table>
			</div>
			<div class="col-xs-12 total-container">
				<div class="col-xs-12">
					Total Duration: <span id="total-duration">
										<script>document.write(current_total_duration);</script>
									</span>
									minutes
				</div>
				<h4 class="col-xs-12">
					Total Cost: $<span id="total-duration"><script>document.write((current_total_cost/100).toFixed(2));
															</script>
								</span>
				</h4>
			</div>
		</div>
	</div>
	
<?php require_once("helpers/global-html-foot.php"); ?>











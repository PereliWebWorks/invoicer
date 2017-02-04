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
	$client = Client::find($client_id);
	if (!$client || $client->user != getCurrentUser())
	{
		setFlash("danger", "This is not a client of yours.");
		header("Location: index.php");
		die();
	}
	$user = getCurrentUser();
	$current_invoices = $client->getInvoices("CURRENT");
	$current_invoice;
	if (sizeof($current_invoices) === 0)
	{
		$current_invoice = new Invoice(array("client_id"=>$client_id));
		$current_invoice->create();
	}
	else
	{
		$current_invoice = $current_invoices[0];
	}
	$rate = $client->default_rate;
	//Get the invoice items
	$items = $current_invoice->items;
	$to_do_items = $client->to_do_items;
	$unfnished_items = To_Do_Item::findWhere("client_id={$client->id} AND finished=0");
	$fnished_items = To_Do_Item::findWhere("client_id={$client->id} AND finished=1");
?>
<?php require_once("helpers/global-html-head.php"); ?>
	<!-- CLIENT INFO -->
	<div class="row cient-info">
		<h1 class="col-xs-12"><?= $client->name; ?></h1>
		<div class="col-xs-12"><?= $client->email; ?></div>
		<div class="col-xs-12">$<?= ($client->rate_in_dollars_per_hour) ?> per hour</div>
	</div>
	<!-- END CLIENT INFO -->
	<hr/>
	<h2 id="to-do">To Do</h2>
	<div class="row item_form-container">
		<h3 class="col-xs-12">Add To-Do Items</h3>
		<form id="to_do_item_form">
			<div class="form-group col-xs-10">
				<label for="to_do_item_description">Item</label>
				<input type="text" 
						name="description"
						id="to_do_item_description"
						class="form-control required" />
			</div>
			<input type="hidden" name="client_id" value="<?= $client->id; ?>" />
			<input type="hidden" name="model" value="To_Do_Item" />
			<div class="form-group col-xs-2">
				<label>&nbsp;</label><br/>
				<div id="to_do_item-submit_btn" class="btn btn-default">Add Item</div>
			</div>
		</form>
		<div id="to_do_item_response" class="hidden message col-xs-6 col-xs-offset-3"></div>
			<script type="text/javascript">
				$("#to_do_item-submit_btn").click(function(){
					var valid_input = validateRequiredFields("to_do_item_form");
					var message = valid_input ? "" : "Missing required fields";
					//Should validate number field
					if (valid_input)
					{
						$("#to_do_item_response").removeClass("hidden")
							.removeClass("bg-danger")
							.removeClass("text-danger")
							.addClass("bg-success text-success")
							.html("Processing...");
						var json = $("#to_do_item_form").serializeJSON(); 
						$.ajax({
							type: "POST",
							url: "helpers/CRUD/create.php",
							data: json,
						}).done(function(data){
							data = $.trim(data);
							data = $.parseJSON(data);
							if (data["success"])
							{
								$("#to_do_item_response").html("Item added.");
								window.location.reload();
							}
							else
							{
								$("#to_do_item_response").removeClass("bg-success text-success")
									.addClass("bg-danger text-danger")
									.html(data["message"]);
							}
						});
					}
					else
					{
						$("#to_do_item_response").removeClass("hidden")
							.addClass("bg-danger text-danger")
							.html(message);
					}
				});
			</script>
	</div>
	<div class="to-do-items">
		<h4>Unfinished Items</h4>
		<div class="unfinished-items row">
			<table class="table table-responsive">
				<?php foreach($unfnished_items as $to_do_item) : ?>
					<tr id="to_do_item-<?= $to_do_item->id; ?>">
						<td>
							<?= $to_do_item->description; ?>
						</td>
						<td class="btn btn-default mark-as-finished-btn">
						Mark as finished
						</td>
						<td class="btn btn-danger remove-btn">
						Remove
						</td>
					</tr>
				<?php endforeach ?>
			</table>
		</div>
		<h4>Finished Items</h4>
		<div class="unfinished-items row">
			<table class="table table-responsive">
				<?php foreach($fnished_items as $to_do_item) : ?>
					<tr id="to_do_item-<?= $to_do_item->id; ?>">
						<td>
							<?= $to_do_item->description; ?>
						</td>
						<td class="btn btn-danger remove-btn">
						Remove
						</td>
					</tr>
				<?php endforeach ?>
			</table>
		</div>
	</div>
	<script type="text/javascript">
		$(".to-do-items .mark-as-finished-btn").on("click", function()
			{
				var id = $(this).parent().attr("id").split("-")[1];
				var data = {
					model: "To_Do_Item",
					id: id,
					finished: 1
				};
				$.ajax({
					type: "POST",
					data: data,
					url: "helpers/CRUD/update.php"
				}).done(function(response)
				{
					response = $.trim(response);
					response = $.parseJSON(response);
					if (response.success)
					{
						window.location.reload();
					}
					else
					{
						console.log(response.message);
					}
				});
			});

		$(".to-do-items .remove-btn").on("click", function()
			{
				var id = $(this).parent().attr("id").split("-")[1];
				var data = {
					model: "To_Do_Item",
					id: id,
					finished: 1
				};
				$.ajax({
					type: "POST",
					data: data,
					url: "helpers/CRUD/destroy.php"
				}).done(function(response)
				{
					response = $.trim(response);
					response = $.parseJSON(response);
					if (response.success)
					{
						window.location.reload();
					}
					else
					{
						console.log(response.message);
					}
				});
			});
	</script>
	<!-- ITEM FORM -->
	<div class="row item_form-container">
		<h3 class="col-xs-12">Add Invoice Item</h3>
		<form id="item_form" class="">
			<div class="form-group col-xs-6">
				<label for="item_description">Description</label>
				<input type="text" name="description" id="item_description" class="form-control required" />
			</div>
			<div class="form-group col-xs-2">
				<label for="item_duration">Duration</label>
				<div class="input-group">
					<input type="number" name="duration" id="item_duration" class="form-control" />
					<div class="input-group-addon">minutes</div>
				</div>
			</div>
			<div class="form-group col-xs-2">
				<label for="item_cost">Cost</label>
				<div class="input-group">
					<div class="input-group-addon">$</div>
					<input type="number" name="cost_in_dollars" id="item_cost" class="form-control" 
							title="If left blank, the cost will be calculated using this client's hourly rate."/>
				</div>
			</div>
			
			<input type="hidden" name="invoice_id" value="<?=$current_invoice->id;?>" class="required" />
			<div class="form-group col-xs-2">
				<label>&nbsp;</label><br/>
				<div type="" id="item_submit-btn" class="btn btn-default">Add Item</div>
			</div>
		</form>
		<div id="item_response" class="hidden message col-xs-6 col-xs-offset-3"></div>
		<script type="text/javascript">
			$("#item_submit-btn").click(function(){
				var valid_input = validateRequiredFields("item_form");
				var message = valid_input ? "" : "Missing required fields";
				//Should validate number field
				if (valid_input)
				{
					$("#item_response").removeClass("hidden")
						.removeClass("bg-danger")
						.removeClass("text-danger")
						.addClass("bg-success text-success")
						.html("Processing...");
					var json = $("#item_form").serializeJSON();
					json.model = "Item";
					$.ajax({
						type: "POST",
						url: "helpers/CRUD/create.php",
						data: json,
					}).done(function(data){
						data = $.trim(data);
						data = $.parseJSON(data);
						if (data["success"])
						{
							$("#item_response").html("Item added.");
							window.location.reload(true);
						}
						else
						{
							$("#item_response").removeClass("bg-success text-success")
								.addClass("bg-danger text-danger")
								.html(data["message"]);
						}
					});
				}
				else
				{
					$("#item_response").removeClass("hidden")
						.addClass("bg-danger text-danger")
						.html(message);
				}
			});
		</script>
	</div>
	<!-- END ITEM FORM -->
	<hr/>
	<!--******************
		INVOICES 
	******************-->
	<!-- CURRENT INVOICE -->
	<div class="row">
		<div id="current-invoice" class="col-xs-12 col-sm-8 col-sm-offset-2">

			<!--
			<input type="button" class="btn btn-success col-xs-2" data-action="publish_and_send" value="Publish and Send" />
			<input type="button" class="btn btn-success col-xs-2 col-xs-offset-1" data-action="publish" value="Publish" />
			<input type="button" class="btn btn-success col-xs-2 col-xs-offset-1" 
				data-action="send" 
				data-recipient="client"
				value="Send" />
			<input type="button" class="btn btn-success col-xs-2 col-xs-offset-1" 
				data-action="send" 
				data-recipient="self"
				value="Send to Self" />
			-->
			<div>
				<h4>Publish Invoice:</h4>
				<form class="col-xs-offset-1" id="publish-invoice-form">
					<input type="checkbox" name="mark_as_pending"/> Mark as pending <br/>
					<input type="checkbox" name="email_to_client" /> Email to client <br/>
					<input type="checkbox" name="email_to_self" /> Email to self <br/>
					<input type="hidden" name="invoice_id" value="<?= $current_invoice->id; ?>" />
					<input type="button" id="publish-invoice-btn" class="btn btn-success" value="Publish" />
				</form>
			</div>
			<script>
				$("#publish-invoice-btn").click(function()
					{
						var data = $("#publish-invoice-form").serializeJSON();
						if (Object.keys(data).length === 0)
						{
							return;
						}
						$.ajax({
							type: "POST",
							data: data,
							url: "helpers/publish-invoice.php"
						}).done(function(response){
							response = $.parseJSON(response);
							if (response.success)
							{
								window.location.reload();
							}
						});
					});
			</script>
			<div class="col-xs-1"></div>
			<a href="preview-invoice.php?i=<?= $current_invoice->id; ?>"
				class="col-xs-2 btn btn-default"
				target="_blank">
				Preview Invoice
			</a>

			<div class="hidden message" id="publish-response"></div>
			<div class="col-xs-12">&nbsp;</div>
			<div class="col-xs-12 invoice-container">
			<?php 
				$renderer->prepare_template("invoice");
				$renderer->invoice = $current_invoice; 
				$renderer->render();
			?>
			</div>
		</div>
	</div>
		<!--
		<script type="text/javascript">
			$("#current-invoice .btn").click(function(){
				$("#publish-response").removeClass("bg-danger text-danger")
					.addClass("bg-success text-success")
					.html("Processing...")
				var action = $(this).data("action");
				var firstURL;
				var id = <?= $current_invoice->id; ?>;
				if (action === "publish_and_send")
				{
					var data = 
							{
								model: "Invoice",
								id: id,
								status: 1 
							};
					$.ajax({
						type: "POST",
						url: "helpers/CRUD/update.php",
						data: data
					}).done(function(response)
					{
						response = $.trim(response);
						response = $.parseJSON(response);
						if (response.success)
						{
							$.ajax({
									type: "POST",
									url: "helpers/send-invoice.php",
									data: 
									{
										invoice: {
											id: id
										},
										to: "client"
									}
								}).done(function(response)
								{
									response = $.trim(response);
									response = $.parseJSON(response);
									if (response.success)
									{
										window.location.reload();
									}
									else
									{
										$("#publish-response").removeClass("bg-success text-success")
											.addClass("bg-danger text-danger")
											.html(response.message);
									}
								});
						}
						else
						{
							$("#publish-response").removeClass("bg-success text-success")
								.addClass("bg-danger text-danger")
								.html(response.message);
						}
					});
				}
				else if (action === "publish")
				{
					var data = {
						model: "Invoice",
						id: id,
						status: 1
					}
					$.ajax({
						type: "POST",
						url: "helpers/CRUD/update.php",
						data: data
					}).done(function(response){
						response = $.trim(response);
						response = $.parseJSON(response);
						if (response.success)
						{
							window.location.reload();
						}
						else
						{
							$("#publish-response").removeClass("bg-success text-success")
								.addClass("bg-danger text-danger")
								.html(response.message);
						}
					});
				}
				else if (action === "send")
				{
					$.ajax({
						type: "POST",
						url: "helpers/send-invoice.php",
						data: {
							invoice: {id: <?= $current_invoice->id; ?>},
							to: $(this).data("recipient")
						}
					}).done(function(response)
					{
						response = $.trim(response);
						response = $.parseJSON(response);
						if (response.success)
						{
							window.location.reload();
						}
						else
						{
							$("#publish-response").removeClass("bg-success text-success")
								.addClass("bg-danger text-danger")
								.html(response.message);
						}
					});
				}
			})
		</script>
		-->
		<hr/>
	<!-- END CURRENT INVOICE -->
	<!-- PENDING INVOICES -->
	<div class="row">
		<div class="col-xs-12 col-sm-8 col-sm-offset-2" id="pending-invoices">
			<h3>Pending Invoices</h3>
			<?php foreach($client->getInvoices("PENDING") as $invoice) : ?>
				<div class="col-xs-12">&nbsp;</div>
				<div class="col-xs-12">&nbsp;</div>
				<div class="col-xs-12">
					<div class="btn btn-success col-xs-2" 
							data-invoice-id="<?= $invoice->id; ?>"
							data-action="mark-as-paid">Mark as Paid</div>
					<div class="btn btn-default col-xs-2 col-xs-offset-1" 
							data-invoice-id="<?= $invoice->id; ?>"
							data-action="send">Send to Client</div>
					<div class="btn btn-danger col-xs-2 col-xs-offset-1" 
							data-invoice-id="<?= $invoice->id; ?>"
							data-action="delete">Delete</div>
				</div>
				<div class="col-xs-12">&nbsp;</div>
				<div class="col-xs-12 invoice-container">
				<?php
					$renderer->prepare_template("invoice");
					$renderer->invoice = $invoice; 
					$renderer->render();
				?>
				</div>
				<div class="col-xs-12">&nbsp;</div>
				<div class="col-xs-12">&nbsp;</div>
			<?php endforeach ?>
		</div>
	</div>
	<script type="text/javascript">
	$("#pending-invoices .btn").click(function(){
		var id = $(this).data("invoice-id");
		var action = $(this).data("action");
		var data;
		var url;
		if (action === "mark-as-paid")
		{
			data = {
				model: "Invoice",
				id: id,
				status: 2
			}
			url = "helpers/CRUD/update.php";
		}
		else if (action === "send")
		{
			data = {
				invoice: {
					id: id
				}
			}
			url = "helpers/send-invoice.php";
		}
		else if (action === "delete")
		{
			data = {
				model: "Invoice",
				id: id
			}
			url = "helpers/CRUD/destroy.php";
		}
		else
		{
			console.log("There was an error");
			return false;
		}
		console.log(data);
		$.ajax({
			type: "POST",
			url: url,
			data: data
		}).done(function(response){
			response = $.trim(response);
			response = $.parseJSON(response);
			if (response.success)
			{
				window.location.reload();
			}
			else
			{
				console.log(response.message);
			}
		});
	});
	</script>
	<!-- END PENDING INVOICES -->
	<hr/>
	<!-- PAID INVOICES -->
	<div class="row">
		<div class="col-xs-12 col-sm-8 col-sm-offset-2">
		<h3>Paid Invoices (receipts)</h3>
		<?php foreach($client->getInvoices("PAID") as $invoice) : ?>
			<div class="col-xs-12">&nbsp;</div>
			<div class="col-xs-12">&nbsp;</div>
			<div class="col-xs-12">
				<div class="btn btn-default col-xs-2" id="send-receipt-btn_<?= $invoice->id; ?>">Send to Client</div>
				<div class="btn btn-warning col-xs-2 col-xs-offset-1" id="mark-as-pending-btn_<?= $invoice->id; ?>">Mark as pending</div>
				<div class="btn btn-danger col-xs-2 col-xs-offset-1" id="delete-receipt-btn_<?= $invoice->id; ?>">Delete</div>
			</div>
			<div class="col-xs-12">&nbsp;</div>
			<div class="col-xs-12 invoice-container">
			<?php
				$renderer->prepare_template("invoice");
				$renderer->invoice = $invoice; 
				$renderer->render();
			?>
			</div>
			<div class="col-xs-12">&nbsp;</div>
			<div class="col-xs-12">&nbsp;</div>
		<?php endforeach ?>
		</div>
	</div>
	<!-- END PAID INVOICES -->
	
<?php require_once("helpers/global-html-foot.php"); ?>











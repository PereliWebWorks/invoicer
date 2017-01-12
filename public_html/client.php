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
	$current_invoice = $client->getInvoices("CURRENT")[0];
	$rate = $client->default_rate;
	//Get the invoice items
	$items = $current_invoice->items;
?>
<?php require_once("helpers/global-html-head.php"); ?>
	<!-- CLIENT INFO -->
	<div class="row cient-info">
		<h1 class="col-xs-12"><?= $client->name; ?></h1>
		<div class="col-xs-12"><?= $client->email; ?></div>
		<div class="col-xs-12">$<?= ($client->default_rate / 100) ?> per hour</div>
	</div>
	<!-- END CLIENT INFO -->
	<hr/>
	<!-- ITEM FORM -->
	<div class="row item_form-container">
		<h3 class="col-xs-12">Add Invoice Item</h3>
		<form id="item_form" class="">
			<div class="form-group col-xs-6">
				<label for="item_description">Description</label>
				<input type="text" name="item[description]" id="item_description" class="form-control required" />
			</div>
			<div class="form-group col-xs-2">
				<label for="item_duration">Duration</label>
				<div class="input-group">
					<input type="number" name="item[duration]" id="item_duration" class="form-control" />
					<div class="input-group-addon">minutes</div>
				</div>
			</div>
			<div class="form-group col-xs-2">
				<label for="item_cost">Cost</label>
				<div class="input-group">
					<div class="input-group-addon">$</div>
					<input type="number" name="item[cost]" id="item_cost" class="form-control" 
							title="If left blank, the cost will be calculated using this client's hourly rate."/>
				</div>
			</div>
			
			<input type="hidden" name="item[invoice_id]" value="<?=$current_invoice->id;?>" class="required" />
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
							$("#response").html("Item added.");
							window.location.reload(true);
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
	<!--******************
		INVOICES 
	******************-->
	<!-- CURRENT INVOICE -->
	<div class="row">
		<div class="col-xs-12 col-sm-8 col-sm-offset-2">
			<input type="button" class="btn btn-success col-xs-2" id="publish-btn" value="Publish and Send" />
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
		<script>
			$("#publish-btn").click(function(){
				$("#publish-response").removeClass("bg-danger text-danger")
					.addClass("bg-success text-success")
					.html("Processing...")
				var data = 
						{
							invoice: 
							{
								id: <?= $current_invoice->id; ?>,
								status: 1
							}
						};
				$.ajax({
					type: "POST",
					url: "helpers/CRUD/update-invoice.php",
					data: data
				}).done(function(data)
				{
					data = $.trim(data);
					data = $.parseJSON(data);
					if (data.success)
					{
						$.ajax({
							type: "POST",
							url: "helpers/CRUD/create-invoice.php",
							data: {invoice: {client_id: <?= $client->id; ?>}}
						}).done(function(data)
						{
							window.location.reload();
						});
					}
					else
					{
						$("#publish-response").removeClass("bg-success text-success")
							.addClass("bg-danger text-danger")
							.html(data.message);
					}
				});
			})
		</script>
		<hr/>
	<!-- END CURRENT INVOICE -->
	<!-- PENDING INVOICES -->
	<div class="row">
		<div class="col-xs-12 col-sm-8 col-sm-offset-2">
			<h3>Pending Invoices</h3>
			<?php foreach($client->getInvoices("PENDING") as $invoice) : ?>
				<div class="col-xs-12">&nbsp;</div>
				<div class="col-xs-12">&nbsp;</div>
				<div class="col-xs-12">
					<div class="btn btn-success" id="mark-as-paid-btn_<?= $invoice->id; ?>">Mark as Paid</div>
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
	<!-- END PENDING INVOICES
	
<?php require_once("helpers/global-html-foot.php"); ?>











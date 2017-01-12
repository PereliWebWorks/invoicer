		<?php
			$this->user = getCurrentUser();
			$this->client = $this->invoice->client;
			$this->items = $this->invoice->items;
			$this->rate = $this->client->default_rate;
		?>

		<div class="invoice col-xs-12">
			<div class="col-xs-12 header-container">
				<h1 class="col-xs-12">Invoice for <?= $this->client->name; ?></h1>
			</div>
			<div class="col-xs-12 id-container">
				<div class="col-xs-12">ID: <?= $this->invoice->slug; ?></div>
			</div>
			<div class="col-xs-12">&nbsp;</div>
			<div class="client-info col-xs-4">
				<div class="col-xs-12"><i>To:</i></div>
				<?php
					$renderer_current_client = $this->client;
					$this->prepare_template("contact_info");
					$this->user = $renderer_current_client;
					unset($renderer_current_client);
					$this->render();
				?>
			</div>
			<div class="user-info col-xs-4 col-xs-offset-4">
				<div class="col-xs-12"><i>From:</i></div>
				<?php
					$this->prepare_template("contact_info");
					$this->user = getCurrentUser();
					$this->render();
				?>
			</div>
			<div class="col-xs-12">&nbsp;</div>
			<div class="col-xs-12 rate-container">
				<div class="col-xs-12">$<?= $this->client->rate_in_dollars_per_hour; ?> per hour</div>
			</div>
			<div class="col-xs-12">&nbsp;</div>
			<div class="col-xs-10 col-xs-offset-1">
				<table class="col-xs-12 table table-responsive" id="item-table">
					<tr><th>Item</th><th>Cost</th></tr>
					<?php foreach($this->items as $item) : ?>
						<tr>
							<td>
								<?= $item->description; ?> 
								<?php if (isset($item->duration)) : ?>
									(<?= $item->duration; ?> minutes)
								<?php endif ?>
							</td>
							<td>
								<?php if (isset($item->cost_in_dollars)) : ?>
									$
									<?php //Get cost
										echo number_format($item->cost_in_dollars, 2); 
									?>
								<?php else : ?>
									--
								<?php endif ?>
							</td>
						</tr>
					<?php endforeach ?>
				</table>
			</div>
			<div class="col-xs-12 total-container">
				<div class="col-xs-12">
					Total Duration: <?= $this->invoice->duration; ?> minutes
				</div>
				<h4 class="col-xs-12">
					Total Cost: $<?= number_format($this->invoice->cost_in_dollars, 2); ?>
				</h4>
			</div>
		</div>
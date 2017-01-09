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
				<div class="col-xs-12"><?= $this->client->name; ?></div>
				<?php if (!empty($this->client->company)) : ?>
					<div class="col-xs-12"><?= $this->client->company; ?></div>
				<?php endif ?>
				<div class="col-xs-12"><?= $this->client->email; ?></div>
				<?php if (!empty($this->client->phone)) : ?>
					<div class="col-xs-12"><?= $this->client->phone; ?></div>
				<?php endif ?>
			</div>
			<div class="user-info col-xs-4 col-xs-offset-4">
				<div class="col-xs-12"><i>From:</i></div>
				<div class="col-xs-12"><?= $this->user->name; ?></div>
				<div class="col-xs-12"><?= $this->user->email; ?></div>
				<?php if (!empty($this->user->phone)) : ?>
					<div class="col-xs-12"><?= $this->user->phone; ?></div>
				<?php endif ?>
			</div>
			<div class="col-xs-12">&nbsp;</div>
			<div class="col-xs-12 rate-container">
				<div class="col-xs-12">$<?= ($this->client->rate_in_dollars); ?> per hour</div>
			</div>
			<div class="col-xs-12">&nbsp;</div>
			<div class="col-xs-10 col-xs-offset-1">
				<table class="col-xs-12 table table-responsive" id="item-table">
					<tr><th>Item</th><th>Cost</th></tr>
					<?php foreach($this->items as $item) : ?>
						<tr>
							<td>
								<?= $item->description; ?> (<?= $item->duration; ?> minutes)
							</td>
							<td>
								$
								<?php //Get cost
									echo number_format($item->cost_in_dollars, 2); 
								?>
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
					Total Cost: $<?= number_format($this->invoice->cost, 2); ?>
				</h4>
			</div>
		</div>
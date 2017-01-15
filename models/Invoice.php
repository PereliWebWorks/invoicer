<?php
	final class Invoice extends Model
	{
		const TABLE_NAME = "invoices";
		public static $columns;

		function __get($name)
		{
			switch ($name)
			{
				case "client":
					return Client::find($this->client_id);
				case "user":
					return $this->client->user;
				case "items":
					return Item::findBy("invoice_id", $this->id);
				case "duration":
					$d = 0;
					foreach ($this->items as $item)
					{
						$d += intval($item->duration);
					}
					return $d;
				case "duration_in_hours":
					$d = 0;
					foreach ($this->items as $item)
					{
						$d += $item->duration_in_hours;
					}
					return $d;
				case "cost":
					$c = 0;
					foreach ($this->items as $item)
					{
						$c += intval($item->cost);
					}
					return $c;
				case "cost_in_dollars":
					$c = 0;
					foreach ($this->items as $item)
					{
						$c += $item->cost_in_dollars;
					}
					return $c;
				case "slug":
					$invoice_id = $this->id;
					$client_id = $this->client->id;
					$user_id = $this->client->user->id;
					$slug = "u{$user_id}c{$client_id}";
					$slug .= $this->status === "2" ? "r" : "i";
					$slug .= $invoice_id;
					return $slug;
				default:
					return parent::__get($name);
			}
			
		}
	}
	Invoice::init();
?>
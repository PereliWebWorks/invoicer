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
						$d += intval($item->duration);
					}
					$d /= 60;
					return $d;
				case "cost":
					return $this->duration_in_hours * $this->client->rate_in_dollars;
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
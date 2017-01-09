<?php
	final class Item extends Model
	{
		const TABLE_NAME = "items";
		public static $columns;

		function fieldIsValid($field, $value)
		{
			$r = new Response();
			$r1 = parent::fieldIsValid($field, $value);
			if (!$r1->success){return $r1;}
			switch ($field)
			{
				case "duration":
					if (!is_numeric($value))
					{
						$r->message = "Duration is not numeric.";
						return $r;
					}
			}
			$r->success = true;
			return $r;
		}

		function __get($name)
		{
			switch ($name)
			{
				case "invoice":
					return Invoice::find($this->invoice_id);
				case "duration_in_hours":
					return $this->duration / 60;
				case "cost_in_dollars":
					return $this->duration_in_hours * $this->invoice->client->rate_in_dollars;
				default:
					return parent::__get($name);
			}
		}
	}
	Item::init();
?>
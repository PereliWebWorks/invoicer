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
					if (isset($value) && !is_numeric($value))
					{
						$r->message = "Duration must be either null or numeric.";
						return $r;
					}
				case "cost":
					if (isset($value) && !is_numeric($value))
					{
						$r->message = "Cost must be either null or numeric.";
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
					if (isset($this->duration))
					{
						return $this->duration / 60;
					}
					return null;
				case "cost_in_dollars":
					if (isset($this->cost))
					{
						return $this->cost / 100;
					}
					if (isset($this->duration_in_hours))
					{
						return $this->duration_in_hours * $this->invoice->client->rate_in_dollars_per_hour;
					}
					return null;
				default:
					return parent::__get($name);
			}
		}
	}
	Item::init();
?>
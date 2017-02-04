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
						$r->message = "Duration must be either null or numeric. Instead got $value.";
						return $r;
					}
				case "cost":
					if (isset($value) && !is_numeric($value))
					{
						$r->message = "Cost must be either null or numeric. Instead got $value.";
						return $r;
					}
			}
			$r->success = true;
			return $r;
		}


		function __construct($args)
		{
			if (isset($args["cost_in_dollars"]))
			{
				$args["cost"] = $args["cost_in_dollars"] * 100;
				unset($args["cost_in_dollars"]);
			}
			return parent::__construct($args);
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
				case "plain_english_duration":
					$d = $this->duration;
					$minutes_portion = $d % 60;
					$hours_portion = ($d - $minutes_portion) / 60;
					$string = "";
					if ($hours_portion !== 0)
					{
						$string .= $hours_portion . " hour";
						if ($hours_portion !== 1){$string .= "s";}
						if ($minutes_portion !== 0){$string .= " ";}
					}
					if ($minutes_portion !== 0)
					{
						$string .= $minutes_portion . " minute";
						if ($minutes_portion !== 1){$string .= "s";}
					}
					return $string;
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
				case "user":
					return $this->invoice->user;
				case "owner":
					return $this->user;
				default:
					return parent::__get($name);
			}
		}
	}
	Item::init();
?>
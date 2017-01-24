<?php
	final class Client extends Model
	{
		const TABLE_NAME = "clients";
		public static $columns;
		
		function getInvoices($status="ALL")
		{
			$valid_statuses = array("ALL", "CURRENT", "PENDING", "PAID");
			if (!in_array($status, $valid_statuses))
			{
				throw new Error("Invalid invoice status: $status<br/>");
				return;
			}
			$c = "client_id={$this->id}";
			switch ($status)
			{
				case "CURRENT":
					$c .= " AND status=0";
				break;
				case "PENDING":
					$c .= " AND status=1";
				break;
				case "PAID":
					$c .= " AND status=2";
				break;
			}
			$i = Invoice::findWhere($c);
			return $i;
		}
		
		function fieldIsValid($field, $value)
		{
			$r = new Response();
			$r1 = parent::fieldIsValid($field, $value);
			if (!$r1->success){return $r1;}
			switch ($field)
			{
				case "email":
					if (!filter_var($value, FILTER_VALIDATE_EMAIL))
					{
						$r->message = "Invalid email.";
						return $r;
					}
					break;
				case "rate":
					if (!is_numeric($value))
					{
						$r->message="Rate must be numeric.";
						return $r;
					}
					break;
			}
			$r->success = true;
			return $r;
		}
		
		function save()
		{
			$this->phone = getIntFromPhone($this->phone);
			return parent::save();
		}

		function create()
		{
			$this->phone = getIntFromPhone($this->phone);
			$this->user_id = getCurrentUser()->id;
			$r = parent::create();
			if ($r->success)
			{
				$r->client_id = $this->id;
			}
			return $r;
		}


		function __construct($args)
		{
			if (isset($args["default_rate_in_dollars"]))
			{
				$args["default_rate"] = $args["default_rate_in_dollars"] * 100;
				unset($args["default_rate_in_dollars"]);
			}
			return parent::__construct($args);
		}

		function __get($name)
		{
			switch ($name)
			{
				case "user":
					return User::find($this->user_id);
				case "owner":
					return $this->user;
				case "rate_in_dollars_per_hour":
					return $this->default_rate / 100;
				case "to_do_items":
					return To_Do_Item::findBy("client_id", $this->id);
				default:
					return parent::__get($name);
			}
		}
	}
	Client::init();
?>


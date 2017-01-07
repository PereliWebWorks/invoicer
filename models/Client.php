<?php
	final class Client extends Model
	{
		const TABLE_NAME = "clients";
		public static $columns;
		function getUser()
		{
			return User::find($this->user_id);
		}
		function getInvoices($status="ALL")
		{
			$valid_statuses = array("ALL", "CURRENT", "PENDING", "PAID");
			if (!in_array($status, $valid_statuses))
			{
				throw new Error("Invalid invoice status: $status<br/>");
				return;
			}
			$condition = "client_id={$this->id}";
			switch ($status)
			{
				case "CURRENT":
					$q .= " AND status=0";
				break;
				case "PENDING":
					$q .= " AND status=1";
				break;
				case "PAID":
					$q .= " AND status=2";
				break;
			}
			return Invoice::findWhere($condition);
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
	}
	Client::init();
?>


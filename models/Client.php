<?php
	final class Client extends Model
	{
		const TABLE_NAME = "clients";
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
		
	}
?>
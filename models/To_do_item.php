<?php
	
	final class To_Do_Item extends Model
	{
		const TABLE_NAME = "to_do_items";
		public static $columns;

		function __get($name)
		{
			switch ($name)
			{
				case "client":
					return Client::find($this->client_id);
				case "user":
					return $this->client->user;
				case "owner":
					return $this->user;
				default:
					return parent::__get($name);
			}
		}
	}
	To_Do_Item::init();
?>
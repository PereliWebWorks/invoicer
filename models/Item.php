<?php
	final class Item extends Model
	{
		const TABLE_NAME = "items";

		function __get($name)
		{
			switch ($name)
			{
				case "invoice":
					return Item::find($this->invoice_id);
				default:
					return parent::__get($name);
			}
		}
	}
	Invoice::init();
?>
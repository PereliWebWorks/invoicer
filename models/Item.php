<?php
	class Item extends Model
	{
		const TABLE_NAME = "items";
		function getInvoice()
		{
			Item::find($this->invoice_id);
		}
	}
?>
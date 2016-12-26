<?php
	final class Invoice extends Model
	{
		const TABLE_NAME = "invoices";
		function getClient()
		{
			return Client::find($this->client_id);
		}
		function getItems()
		{
			return Item::findBy("invoice_id", $this->id);
		}
	}
?>
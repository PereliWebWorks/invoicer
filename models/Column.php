<?php
	final class Column
	{
		private $data = array();

		function __construct($args)
		{
			$this->name = $args["Field"];
			$type = explode("(", $args["Type"])[0];
			if ($this->name === "email")
			{
				$this->type = "email";
			}
			else if ($this->name = "id")
			{
				$this->type = "auto_increment";
			}
			else if ($type === "int")
			{
				$this->type = "int";
			}
			else if ($type === "varchar")
			{
				$this->type = "string";
			}
			else if ($type === "tinyint")
			{
				$this->type = "bool";
			}
			else
			{
				throw new Error("Invalid type $type.");
				return false;
			}
			$this->nullAllowed = $args["Null"] === "YES";
			$this->default = $args["Default"];
			$this->size = explode("(", $args["Type"])[1];
		}

		function __set($field, $value)
		{
			$this->data[$field] = $value;
		}

		function __get($field)
		{
			return $this->data[$field];
		}

		function __toString()
		{
			return json_encode($this->data);
		}
	}
?>
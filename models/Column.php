<?php
	final class Column
	{
		private $data = array();

		function __construct($args)
		{
			$this->name = $args["Field"];
			$type = explode("(", $args["Type"])[0];
			$this->size = intval(explode("(", $args["Type"])[1]);
			if ($this->name === "email")
			{
				$this->type = "email";
			}
			else if ($this->name === "id")
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
				if ($this->size > 1)
				{
					$this->type = "int";
				}
				else
				{
					$this->type = "boolean";
				}
			}
			else
			{
				throw new Error("Invalid type $type.");
				return false;
			}
			$this->nullAllowed = ($args["Null"] === "YES");
			$this->hasDefault = strlen($args["Default"]) !== 0;
			if ($this->hasDefault)
			{
				$this->default = $args["Default"];
			}
			if (!empty($args["Key"]))
			{
				$this->key = $args["Key"];
			}
			
		}

		function __set($field, $value)
		{
			$this->data[$field] = $value;
		}

		function __get($field)
		{
			return isset($this->data[$field]) ? $this->data[$field] : null;
		}

		function __toString()
		{
			return json_encode($this->data);
		}
	}
?>
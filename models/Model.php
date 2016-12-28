<?php
	abstract class Model
	{
		protected $data;
		const TABLE_NAME = false;
		protected static $immutable_fields = array();
		public static $columns;
		const DB_NAME = "invoicer";
		//Returns whether the object as it stands is valid to save.
		//The base function will make sure all required fields are present in $this->data.
		/*
		public function isValid($field=null, $value=null)
		{
			//If field is set, so must value, and vice versa
			if (empty($field) && !empty($value))
			{
				throw new Error("If value is set, field must be set as well.");
				return false;
			}
			if (!empty($field)) //If $field is set, validate $value
			{
				//Get the column info
				$column = array_filter(static::$columns, function($c) use ($field){
					return $c["Field"] === $field;
				});
				$column = end($column);
				print_r($column);
				//If it's a required field, make sure $value is set
				if ($column["Null"] === "NO" && !isset($value))
				{
					echo "'Not Null' field isn't set: {$column['Field']}.";
					return false;
				}
				//If it's a unique column, make sure the model is alright
				if (!empty($column["Key"]) && $column["Key"] === "UNI")
				{
					$shouldntExist = static::findWhere("{$column['Field']} = {$value} AND id<>{$this->data['id']}");
					if (sizeof($shouldntExist) > 0)
					{
						return false;
					}
				}
			}		
			else //If we're validating all fields
			{
				//Make sure all required fields are present
				foreach (static::$columns as $column)
				{
					if ($column["Null"] === "NO" && !isset($this->data[$column["Field"]]))
					{
						echo "Missing required column: {$column['Field']}.";
						return false;
					}
					if (isset($this->data[$column["Field"]]))
					{
						if (!static::isValid($column["Field"], data[$column["Field"]]))
						{
							return false;
						}
					}
				}
			}
			return true;
		}
		*/
		public function isValid()
		{
			foreach (static::$columns as $column)
			{
				//If the field is NOT NULL and it is not set
				if ($column["Null"] === "NO" && !isset($this->data[$column["Field"]]))
				{
					//If there isn't a default value for the field, return false.
					//If there is a default, or it's auto-incremented,
					// everything is OK because the default will be set upon save.
					if (!isset($column["Default"]) && $column["Extra"] !== "auto_increment")
					{
						echo "Missing required column: {$column['Field']}.";
						return false;
					}
				}
				if (isset($this->data[$column["Field"]])) //If the data isn't set, we know it's a not null field 
				// and we don't have to validate it
				{
					if (!static::fieldIsValid($column["Field"], $this->data[$column["Field"]]))
					{
						return false;
					}
				}
			}
			return true;
		}
		public function fieldIsValid($field, $value)
		{
			//Get the column info
			$column = array_filter(static::$columns, function($c) use ($field){
				return $c["Field"] === $field;
			});
			$column = end($column);
			//If the column has a key constraint
			if (!empty($column["Key"]))
			{
				//If it's a unique column, make sure the model is alright
				if ($column["Key"] === "UNI")
				{
					$condition = "{$column['Field']} = '{$value}'";
					if (!empty($this->data['id']))
					{
						$condition .= " AND id<>{$this->data['id']}";
					}
					$shouldntExist = static::findWhere($condition);
					if (sizeof($shouldntExist) > 0)
					{
						echo "Uniqueness constraint failed. COLUMN: $field | VALUE: $value. ";
						return false;
					}
				}
				//If it's a foreign key, make sure a corresponding primary key of value $value exists.
				if($column["Key"] === "MUL")
				{
					//Get referenced table and column name
					$t_name = static::TABLE_NAME;
					$c_name = $column["Field"];
					$db_name = self::DB_NAME;
					$info_pdo = new PDO('mysql:host=localhost;dbname=INFORMATION_SCHEMA', DB_USERNAME, DB_PASSWORD);
					$q = "SELECT REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME, TABLE_NAME, CONSTRAINT_NAME " 
						. "FROM KEY_COLUMN_USAGE "
						. "WHERE TABLE_NAME='{$t_name}' "
						. "AND COLUMN_NAME='{$c_name}' "
						. "AND REFERENCED_TABLE_NAME IS NOT NULL "
						. "AND CONSTRAINT_SCHEMA='{$db_name}'";
					$st = $info_pdo->prepare($q);
					$st->execute();
					$reference_info = $st->fetch(PDO::FETCH_ASSOC);
					$r_t_name = $reference_info["REFERENCED_TABLE_NAME"];
					$r_c_name = $reference_info["REFERENCED_COLUMN_NAME"];
					$q = "SELECT $r_c_name FROM $r_t_name WHERE $r_c_name='{$value}'";
					$st = $GLOBALS['db']->prepare($q);
					$st->execute();
					if ($st->rowCount() !== 1)
					{
						echo "Invalid foreign key value.";
						return false;
					}
				}
			}
			return true;
		}

		public static function find($id)
		{
			$table = static::TABLE_NAME;
			$q = "SELECT * FROM {$table} WHERE id=:id";
			$st = $GLOBALS["db"]->prepare($q);
			$st->bindParam(":id", $id);
			$st->execute();
			if ($st->rowCount() === 0)
			{
				return false;
			}
			return new static($st->fetch(PDO::FETCH_ASSOC));
		}
		public static function findBy($field, $value)
		{
			$table = static::TABLE_NAME;
			$q = "SELECT * FROM {$table} WHERE {$field}=:value";
			$st = $GLOBALS["db"]->prepare($q);
			$st->bindParam(":value", $value);
			$st->execute();
			$return_array = array();
			while ($row = $st->fetch(PDO::FETCH_ASSOC))
			{
				array_push($return_array, new static($row));
			}
			return $return_array;
			
		}
		public static function findWhere($condition)
		{
			$table = static::TABLE_NAME;
			$q = "SELECT * FROM {$table} WHERE $condition";
			$st = $GLOBALS["db"]->prepare($q);
			$st->execute();
			$return_array = array();
			while ($row = $st->fetch(PDO::FETCH_ASSOC))
			{
				array_push($return_array, new static($row));
			}
			return $return_array;
		}
		public function save()
		{
			if (!static::isValid()){return false;}
			$table = static::TABLE_NAME;
			if (!empty($this->data['id'])) //If we're saving an existing user.
			{
				$q = "UPDATE $table SET ";
				$i = 0;
				foreach(array_column(static::$columns, "Field") as $field)
				{
					if (in_array($field, static::$immutable_fields))
					{
						continue;
					}
					$q .= "{$field}=:{$field}, ";
				}
				$q = substr($q, 0, -2); //Strip trailing comma and space.
				$q .= " WHERE id=:id";
				try
				{
					$st = $GLOBALS["db"]->prepare($q);
					foreach(array_column(static::$columns, "Field") as $field)
					{
						if (in_array($field, static::$immutable_fields))
						{
							continue;
						}
						$value = &$this->data[$field];
						$st->bindParam(':' . $field, $value);
					}
					$st->bindParam(":id", $this->data['id']);
					$st->execute();
					if ($st->rowCount() === 0)
					{
						echo "Invalid id.";
						return false;
					}
					return $this;
				}
				catch (Exception $e)
				{
					throw new Error($e->getMessage());
					return false;
				}
			}
			else //If we're creating a new user
			{
				$q = "INSERT INTO $table";
				$q_part_1 = "";
				$q_part_2 = "";
				foreach(array_column(static::$columns, "Field") as $field)
				{
					if (in_array($field, static::$immutable_fields))
					{
						continue;
					}
					$q_part_1 .= "{$field}, ";
					$q_part_2 .= ":{$field}, ";
				}
				$q_part_1 = substr($q_part_1, 0, -2); //Strip trailing comma and space.
				$q_part_2 = substr($q_part_2, 0, -2);
				$q = "$q ($q_part_1) VALUES ($q_part_2)";
				echo $q;
			}
		}

		public function update($field, $value)
		{
			if (!static::fieldIsValid($field, $value)){return false;}
			$table = static::TABLE_NAME;
			$q = "UPDATE $table SET $field=:value WHERE id=:id";
			try
			{
				$st = $GLOBALS["db"]->prepare($q);
				$st->bindParam(":value", $value);
				$st->bindParam(":id", $this->data['id']);
				$st->execute();
				$this->data[$field] = $value;
				return $this;
			}
			catch (Exception $e)
			{
				throw new Error($e->getMessage());
				return false;
			}
		}

		static function init()
		{
			$st = $GLOBALS["db"]->prepare("DESCRIBE " . static::TABLE_NAME);
			$st->execute();
			static::$columns = $st->fetchAll(PDO::FETCH_ASSOC);
		}
		/********************
			MAGIC METHODS
		********************/
		function __construct($args = null)
		{
			array_push(static::$immutable_fields, "id");
			$this->data = $args;
		}
		function __set($field, $value)
	    {
	    	if (!in_array($field, static::$immutable_fields))
	    	{
	        	$this->data[$field] = $value;
	    	}
	    }

		function __get($field)
		{
			if (array_key_exists($field, $this->data)) {
	            return $this->data[$field];
	        }
	        throw new Error("Invalid property: $field.");
		}
		function __toString()
		{
			return json_encode($this->data);
		}
	}
?>
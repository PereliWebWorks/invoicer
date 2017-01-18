<?php
	abstract class Model
	{
		protected $data;
		const TABLE_NAME = false;
		protected static $immutable_fields = array();
		protected static $unsettable_fields = array(); //Fields that shouldn't be set at creation
		public static $columns;
		protected static $require_owner_login = true;
		const DB_NAME = "invoicer_db";
		//Returns whether the object as it stands is valid to save.
		//The base-class function will make sure all required fields are present in $this->data.
		public function isValid()
		{
			$r = new Response();
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
						$r->message = "Missing required column: {$column['Field']}.";
						return $r;
					}
				}
				if (isset($this->data[$column["Field"]])) //If the data isn't set, we know it's a not null field 
				// and we don't have to validate it
				{
					$field_response = static::fieldIsValid($column["Field"], $this->data[$column["Field"]]);
					if (!$field_response->success)
					{
						return $field_response;
					}
				}
			}
			$r->success = true;
			return $r;
		}
		public function fieldIsValid($field, $value)
		{
			$r = new Response();
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
						$r->message = "Uniqueness constraint failed. COLUMN: $field | VALUE: $value. ";
						return $r;
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
						$r->message = "Invalid foreign key value.";
						return $r;
					}
				}
			}
			$r->success = true;
			return $r;
		}
		protected function getColumnInfo($fieldName)
		{
			foreach (static::$columns as $column)
			{
				if ($column["Field"] === $fieldName)
				{
					return $column;
				}
			}
			return false;
		}
		//Reloads the object from the database
		protected function reload()
		{
			$this->data = static::find($this->data['id'])->data;
			return $this;
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
		public static function findFirstBy($field, $value)
		{
			$results = static::findBy($field, $value);
			return sizeof($results) > 0 ? $results[0] : false;
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
			$r = new Response();
			if (static::$require_owner_login && $this->user != getCurrentUser())
			{
				$r->message("You don't have permission to alter this item.");
				return $r;
			}
			$validator_response = static::isValid();
			if (!$validator_response->success)
			{
				return $validator_response;
			}
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
						$r->message = "Bad ID.";
						return $r;
					}
					$r->success = true;
					return $r;
				}
				catch (Exception $e)
				{
					throw new Error($e->getMessage());
					$r->message = "Update query error.";
					return $r;
				}
			}
			else //If we're creating a new user
			{
				$q_part_1 = "";
				$q_part_2 = "";
				foreach(array_column(static::$columns, "Field") as $field)
				{
					if (in_array($field, static::$unsettable_fields))
					{
						continue;
					}
					if (!isset($this->data[$field]))
					{
						continue;
					}
					$value = $this->data["$field"];
					//If field type is integer and value is false, continue
					$column = static::getColumnInfo($field);
					$type = explode("(", $column["Type"])[0];
					if ($type === "int" || $type === "tinyint")
					{
						if (!is_numeric($value))
						{
							continue;
						}
					}
					$q_part_1 .= "{$field}, ";
					$q_part_2 .= "'$value', ";
				}
				$q_part_1 = substr($q_part_1, 0, -2); //Strip trailing comma and space.
				$q_part_2 = substr($q_part_2, 0, -2);
				$q = "INSERT INTO $table ($q_part_1) VALUES ($q_part_2)";
				try
				{
					$st = $GLOBALS['db']->prepare($q);
					$st->execute();
					$this->data['id'] = $GLOBALS['db']->lastInsertId();
					$this->reload();
					$r->success = true;
					return $r;
				}
				catch (Exception $e)
				{
					throw new Error($e->getMessage());
					$r->message = "Insert query error.";
					return $r;
				}
			}
		}

		public function update($field, $value)
		{
			$r = new Response();
			if (static::$require_owner_login && $this->user != getCurrentUser())
			{
				$r->message("You don't have permission to alter this item.");
				return $r;
			}
			$r1 = static::fieldIsValid($field, $value);
			if (!$r1->success){return $r1;}
			$table = static::TABLE_NAME;
			$q = "UPDATE $table SET $field=:value WHERE id=:id";
			try
			{
				$st = $GLOBALS["db"]->prepare($q);
				$st->bindParam(":value", $value);
				$st->bindParam(":id", $this->data['id']);
				$st->execute();
				$this->data[$field] = $value;
				$r->success = true;
				$r->message = "Updated.";
				return $r;
			}
			catch (Exception $e)
			{
				throw new Error($e->getMessage());
				return $r;
			}
		}

		public function destroy()
		{
			$r = new Response();
			if (static::$require_owner_login && $this->user != getCurrentUser())
			{
				$r->message("You don't have permission to alter this item.");
				return $r;
			}
			$t = static::TABLE_NAME;
			$q = "DELETE FROM $t WHERE id=:id";
			$st = $GLOBALS['db']->prepare($q);
			$st->bindParam(":id", $this->data['id']);
			$st->execute();
			$r->success = true;
			$r->message = "Object destroyed";
			return $r;
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
			array_push(static::$unsettable_fields, "id");
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
	        return null;
	        //throw new Error("Invalid property: $field.");
		}
		function __isset($field)
		{
			$v = $this->__get($field);
			return isset($v);
		}
		function __toString()
		{
			return json_encode($this->data);
		}
	}
?>
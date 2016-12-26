<?php
	abstract class Model
	{
		protected $data;
		const TABLE_NAME = false;
		static $immutable_fields = array();
		//abstract protected static function getTableName();

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
			$table = static::TABLE_NAME;
			$q = "UPDATE $table SET ";
			$i = 0;
			foreach($this->data as $field=>$value)
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
				foreach($this->data as $field=>&$value)
				{
					if (in_array($field, static::$immutable_fields))
					{
						continue;
					}
					$st->bindParam(':' . $field, $value);
				}
				$st->bindParam(":id", $this->data['id']);
				$st->execute();
				return $this;
			}
			catch (Exception $e)
			{
				echo $e->getMessage();
				return false;
			}
		}

		public function update($field, $value)
		{
			$table = static::TABLE_NAME;
			$q = "UPDATE $table SET $field=:value WHERE id=:id";
			try
			{
				$st = $GLOBALS["db"]->prepare($q);
				$st->bindParam(":value", $value);
				$st->bindParam(":id", $this->data['id']);
				$st->execute();
				return $this;
			}
			catch (Exception $e)
			{
				echo $e['message'];
				return false;
			}
		}
		/********************
			MAGIC METHODS
		********************/
		protected function __construct($args)
		{
			$array = static::$immutable_fields;
			array_push($array, "id");
			static::$immutable_fields = $array;
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
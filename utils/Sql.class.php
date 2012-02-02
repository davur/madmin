<?php

class Sql
{
	function unixTimeToDatetime($time)
	{
		return date('Y-m-d H:i:s', $time);
	}
	function escape($s)
	{
		return mysql_real_escape_string($s);
	}
	function query($sql)
	{
		global $debug;
		if ($debug > 1)
			error_log("Query: " . $sql . "\n"); 

		$result = mysql_query($sql);

		global $borage;
		if ($result === false && isset($borage))
			$borage->displaySQLError($sql);
		
		return $result;
	}
	function fetchValue($sql)
	{
		$row = Sql::fetchRow($sql);
		return $row[0];
	}

	function fetchArray($sql)
	{
		$res = Sql::query($sql);
		return mysql_fetch_array($res);
	}
	function fetchAssoc($sql)
	{
		$res = Sql::query($sql);
		return mysql_fetch_assoc($res);
	}
	function fetchObject($sql) //TODO: extend to include , $class_name, $params)
	{
		$res = Sql::query($sql);
		return mysql_fetch_object($res);
	}
	function fetchRow($sql)
	{
		$res = Sql::query($sql);
		return mysql_fetch_row($res);
	}

	function fetchObjectArray($sql)
	{
		$res = Sql::query($sql);
		$objectArray = array();
		while ($obj = mysql_fetch_object($res))
			$objectArray[] = $obj;
		return $objectArray;
	}
	function fetchRowArray($sql)
	{
		$res = Sql::query($sql);
		$rowArray = array();
		while ($row = mysql_fetch_row($res))
			$rowArray[] = $row;
		return $rowArray;
	}
	function fetchAssocArray($sql)
	{
		$res = Sql::query($sql);
		$assocArray = array();
		while ($assoc = mysql_fetch_assoc($res))
			$assocArray[] = $assoc;
		return $assocArray;
	}

	function values($fields, $emptyToNULL = true)
	{
		if (!is_array($fields))
			return $fields;

		$sqlValues = array();
		foreach($fields as $key => $value)
		{
			if($emptyToNULL && strlen($value)==0)
				$sqlValues[$key] = "NULL";
			else
				$sqlValues[$key] = "'" . Sql::escape($value) . "'";
		}
		return $sqlValues;
	}

	function buildSetSyntax($values)
	{
		$values = Sql::Values($values);

		$assignments = array();
		foreach($values as $column => $value)
		{
			$assignments[] = "$column = $value";
		}

		return implode(', ', $assignments);
	}

	function buildWhereSyntax($values)
	{
		$values = Sql::values($values);

		$conditions = array();
		foreach ($values as $column => $value)
		{
			if ($value == 'NULL')
				$conditions[] = "$column IS NULL";
			else
				$conditions[] = "$column = $value";
		}

		return implode(' AND ', $conditions);
	}

	function update($table, $values = false, $conditions = false)
	{

		if (is_string($table) && $values === false && $conditions === false)
		{
			$sql = $table;
		}
		else
		{
			if (!is_array($values) && is_object($values))
			{
				$values_array = array();
				foreach ($values as $key => $value)
				{
					$values_array[$key] = $values->$key;
				}
				$values = $values_array;
			}

			if ($conditions === false)
			{
				$default_id_column = $table . "_ID";
				if (array_key_exists($default_id_column, $values))
					$conditions = array($default_id_column => $values[$default_id_column]);
			}
			else if (is_string($conditions))
				$conditions = explode("|",$conditions);

			foreach ($conditions as $key => $value)
			{
				if (is_int($key) && array_key_exists($value, $values) && !array_key_exists($value, $conditions))
				{
					$conditions[$value] = $values[$value];
				}
			}

			$set = Sql::buildSetSyntax($values);
			$where = Sql::buildWhereSyntax($conditions);

			$sql = "UPDATE $table SET $set WHERE $where";
		}

		Sql::query($sql);
		return mysql_affected_rows();
	}

	function insert($destination, $fields = null, $useKeysAsColumnNames = true)
	{
		if (is_string($destination) && is_null($fields))
		{
			$sql = $destination;
		}
		else
		{
			$fields = Sql::values($fields);

			$columns = ($useKeysAsColumnNames ? "(" . implode(", ", array_keys($fields)) . ")" : "");
			$values = implode(", ", array_values($fields));	

			$sql = "INSERT INTO $destination $columns VALUES ($values)";
		}

		Sql::query($sql);
		return mysql_insert_id();
	}
































}



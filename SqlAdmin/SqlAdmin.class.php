<?php

class SqlAdmin
{
	private static $links;
	private static $schema;
	private static $referrers;
	private static $db;
	private static $diagramMentions;

	public static function init($db)
	{
		self::$schema = $_SESSION['TABLE_SCHEMA'][$db];

	// 	if (!self::$schema)
		{
			$selected = mysql_select_db('information_schema');


			$sql = "select `TABLE_NAME`, `COLUMN_NAME` from `KEY_COLUMN_USAGE` where `CONSTRAINT_NAME` = 'PRIMARY' and `CONSTRAINT_SCHEMA` = '" . Sql::escape($db) . "'";
			$result = Sql::fetchObjectArray($sql);

			$keys = array();
			foreach ($result as $row)
				$keys[$row->TABLE_NAME] = $row->COLUMN_NAME;

			self::$schema['keys']= $keys;

			$_SESSION['TABLE_SCHEMA'][$db] = self::$schema;


			mysql_select_db($db);

			$links = array();

			$fieldInfo = Sql::fetchObjectArray('select * from fieldinfo where link is not null');

			foreach ($fieldInfo as $field)
			{
				$links[$field->database_name][$field->table_name][$field->column_name] = $field->link;
			}


			self::$links = $links;

		}

		$reverse_links = array();
		foreach(self::$links as $db_name => $tables)
		{
			foreach($tables as $table_name => $columns)
			{
				foreach($columns as $column_name => $link)
				{
					$reverse_links[$db_name][$link][] = array($table_name, $column_name);
				}
			}
		}
		self::$referrers = $reverse_links;

		mysql_select_db($db);

		self::$db = $db;
	}

	public static function display($input)
	{
		$input = (string)$input;

		$input = explode('/', $input);


		$db = array_shift($input);

		$table = array_shift($input);

		$id = array_shift($input);

		if (empty($db))
		{
			$sql = 'show databases';
			$result = Sql::fetchObjectArray($sql);

			foreach ($result as &$row)
			{
				$row->Database = "<a href='?input=$row->Database'>$row->Database</a>";
			}

			self::showTable($result);
		}
		else
		{
			self::init($db);
		}

		if (empty($db))
		{
		}
		else if (empty($table))
		{
			$sql = "show tables";
			$result = Sql::fetchObjectArray($sql);

			$tables = array();
			$tables_column = 'Tables_in_' . $db;
			foreach ($result as &$row)
			{
				$tables[] = array('Table'=>"<a href='?input=$db/".$row->$tables_column."'>".$row->$tables_column."</a>");
			}

			self::showTable($tables);
		}
		else if (empty($id))
		{
			self::showTableResult($db, $table, $where);

		}
		else
		{
			$key = self::$schema['keys'][$table];

			$sql = "select * from `$table` where `$key` = '" . Sql::escape($id) . "'";
			$record = Sql::fetchAssoc($sql);


			echo "<table class='readable details'>";
			foreach($record as $col => $value)
			{
				if ($link = self::$links[$db][$table][$col])
				{
					$value = "<a href='?input=$db/$link/$value'>$value &raquo</a>";
				}

				echo '<tr><th'. ($col == $key ? " class='primary-key'" : "") .">$col</th><td>$value</td></tr>";
			}
			echo '</table>';


			if($child_tables = @self::$referrers[$db][$table])
			{
				foreach($child_tables as $rev)
				{
					$tab = $rev[0];
					$col = $rev[1];

					
					echo "<div class='child-table'>";
					echo '<h3>'.$tab.'</h3><em>('.$col.'='.$record[$key].')</em>';

					self::showTableResult($db, $tab, $col."=".$record[$key]);
					echo '</div>';

		// 			echo "<li><a href='?input=$db/$tab&where=$col=".$record[$key]."'>$tab matching ".$col." = ".$record[$key]."</a></li>";
				}
			}
		}


	}

	public static function diagram($table)
	{
		$db = self::$db;

		if (!self::$diagramMentions)
			self::$diagramMentions = array();

		$first_mention = !array_key_exists($table, self::$diagramMentions);

		if ($first_mention)
			self::$diagramMentions[$table] = true;

?>
		<table>
			<tr>
				<td>
					<div class='erd-table <?php echo ($first_mention ? 'first-mention' : 're-mention') ?>'><a href='index.php?input=<?php echo $db . '/' . $table ?>'><?php echo $table ?></a></div>
				</td>
<?php
		if($first_mention && ($child_tables = @self::$referrers[$db][$table]))
		{
			echo "<td>";
			if (count($child_tables)>1)
				echo "<div class='erd-subtables'>";
			foreach($child_tables as $rev)
			{
				list($tab, $col) = $rev;

				self::diagram($tab);
			}
			if (count($child_tables)>1)
				echo '</div>';
			echo '</td>';
		}
?>
			</tr>
		</table>
<?php

	}



	public static function showTable($result)
	{
		if (!$result || count($result)==0)
		{
			echo "<p>No rows returned</p>";
			return;
		}

		echo "<table class='readable'><tr>";

		$first_row = $result[0];
		$column_keys = array_keys((array)$first_row);

		foreach ($column_keys as $key)
		{
			echo '<th>', $key, '</th>';
		}

		echo '</tr>';

		foreach ($result as $row)
		{
			$row = (array)$row;

			echo '<tr>';
				
			foreach ($column_keys as $key)
			{
				echo '<td>', $row[$key], '</td>';
			}

			echo '</tr>';
		}
			
				
		echo '</table>';
	}

	public static function showTableResult($db, $table, $where = false)
	{
		$key = self::$schema['keys'][$table];

		$sql = 'select * from ' . $table;

		if($where)
		{
			$sql .= ' where '. $where;
		}


		$sql .= " limit 0, 200";
		$result = Sql::fetchAssocArray($sql);

		$rows = array();
		foreach ($result as $row)
		{
			foreach ($row as $col => $value)
			{
				if ($link = self::$links[$db][$table][$col])
				{
					$row[$col] = $value . "<a class='index-open' href='?input=$db/$link/$value'> &raquo</a>";
				}
			}

			if ($key)
				$row = array(''=>"<a href='?input=$db/$table/" . $row[$key]."'>Open</a>")+$row;
			$rows[] = $row;
		}

		self::showTable($rows);
	}
}

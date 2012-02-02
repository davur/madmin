<?

$debug = 2;

require_once('../Sql/Sql.class.php');
require_once('array_functions.php');
require_once('connect_to_db.php');

$db1 = $_REQUEST['db1'];
$t1 = $_REQUEST['t1'];

$db2 = $_REQUEST['db2']
or $db2 = $db1;

$t2 = $_REQUEST['t2']
or $t2 = $t1;

$tid1 = $_REQUEST['id1']
or $tid1 = $t1 . '_ID';

$tid2 = $_REQUEST['id2']
or $tid2 = ($t1 == $t2 ? $tid1 : $t2 . '_ID');

$arr1 = Sql::fetchAssocArray("select * from $db1.$t1 order by $tid1");
$arr2 = Sql::fetchAssocArray("select * from $db2.$t2 order by $tid2");

if (count($arr1))
	$cols1 = array_keys($arr1[0]);
else
	$cols1 = array();

if (count($arr2))
	$cols2 = array_keys($arr2[0]);
else
	$cols2 = array();


$hide_identical = $_REQUEST['hide_identical'] or false;

$cols1only = array_diff($cols1, $cols2);
$cols2only = array_diff($cols2, $cols1);

$cols_both = array_diff($cols1, $cols2only);


$i1 = 0;
$i2 = 0;


echo "<html>";
echo "<head><link href='styles.css?5' rel='stylesheet' type='text/css' /></head>";
	
echo "<body><table>";

echo "<tr class='column_headers'><th>" . implode('</th><th>', $cols_both) . "</th><tr>\n";

while($i1 < count($arr1) || $i2 < count($arr2))
{
	if ($i2 >= count($arr2))
	{
		$status = 'left_only';
	}
	else if ($i1 >= count($arr1))
	{
		$status = 'right_only';
	}
	else 
	{
		$id1 = $arr1[$i1][$tid1];
		$id2 = $arr2[$i2][$tid2];
		
		if ($id1 - $id2 > 0)
		{
			$status = 'right_only';
		}
		else if ($id1 - $id2 < 0)
		{
			$status = 'left_only';
		}
		else
		{
			$status = 'both';
		}
	}


	if ($status == 'left_only')
	{
		$left = array_extract($arr1[$i1++], $cols_both);

		echo "<tr class='$status'>\n<td>\n<div class='left'>" . implode("</div></td>\n<td><div class='left'>", $left) . "</div>\n</td>\n</tr>\n";
	}
	else if ($status == 'right_only')
	{
		$right = array_extract($arr2[$i2++], $cols_both);

		echo "<tr class='$status'>\n<td><div class='right'>" . implode("</div></td>\n<td><div class='right'>", $right) . "</div>\n</td>\n</tr>";
	}
	else
	{	
		$left = array_extract($arr1[$i1++], $cols_both);
		$right = array_extract($arr2[$i2++], $cols_both);

		$left_output = "";
		$right_output = "";

		$row_identical = true;
	
		foreach ($cols_both as $index => $col)
		{
			$field_status = ($left[$col] == $right[$col]) ? 'identical' : 'different';
			$row_identical &= ($field_status == 'identical');

			$left_output .= "<td class='$field_status'><div class='left'>" . $left[$col] . "</div></td>\n";
			$right_output .= "<td class='$field_status'><div class='right'>" . $right[$col] . "</div></td>\n";

		}

		$row_status = ($row_identical ? 'identical' : 'different');

		if (!($row_identical && $hide_identical))
		{
			echo "<tr class='left $row_status'>\n$left_output\n</tr>";
			echo "<tr class='right $row_status'>\n$right_output\n</tr>";
		}
	}

}


echo "</table></body></html>";

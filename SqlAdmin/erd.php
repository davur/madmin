<?php

session_start();

require_once('../Sql/Sql.class.php');
require_once('SqlAdmin.class.php');
require_once('array_functions.php');
require_once('connect_to_db.php');

?>
<html>
	<head>
		<link href='styles.css?5' rel='stylesheet' type='text/css' />
	</html>
</head>
<body>
<div class='erd'>
	<?php

		SqlAdmin::init(@$_REQUEST['db']);
		SqlAdmin::diagram(@$_REQUEST['table']);
?>
</div>
</body></html>

<?
/**
 * Конвертер типа таблиц Базы данных
 */

$db_name = "1c_microtest_utf";
$db_host = "localhost";
$db_user = "admin";
$db_password = "N3tzLNR7uxWSJVtc";

$toEngine = "InnoDB";
//$toEngine = "MyISAM";

$db = mysql_connect($db_host, $db_user, $db_password);
if ($db) {
	mysql_select_db($db_name, $db);

	//$query = "SHOW TABLES FROM `".$db_name."`";
	$query = "SELECT `table_name` FROM INFORMATION_SCHEMA.TABLES WHERE engine <> '".$toEngine."' AND `table_schema` = '".$db_name."'";
	$result = mysql_query($query, $db);
	while ($ar = mysql_fetch_array($result)) {
		$tableName = array_pop($ar);

		$query = "ALTER TABLE `".$tableName."` ENGINE = " . $toEngine;
		mysql_query($query);
	}
}

echo 'DONE!';

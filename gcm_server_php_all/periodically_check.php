<?php
//set_include_path('/var/www/gcm_server_php');
// response json
$json = array();

// get monitor info from db
include_once './db_functions.php';
$db = new DB_Functions();
$monitor_info = $db->getMonitorInfo();
if (mysql_num_rows($monitor_info) <= 0)
{
	return;
}

// include functions
include_once './functions.php';
$functions = new Functions();

$rows = array();
while ($row = mysql_fetch_array($monitor_info))
{
    $rows[] = $row;
//	$functions->check_update($row["os"], $row["gcm_regid"], $row["url"], $row["category"], $row["concerned_index"], $row["target"], $row["sign"], $row["id"]);
}
$functions->check_update_all($rows);

?>

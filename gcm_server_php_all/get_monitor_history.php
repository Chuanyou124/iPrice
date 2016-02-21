<?php

// response json
$json = array();

if (isset($_POST["regId"]) && isset($_POST["os"]))
{
	$gcm_regid = $_POST["regId"];
    $os = $_POST["os"];

	include_once './db_functions.php';
	$db = new DB_Functions();

	$matched_monitor_info = $db->getMatchedMonitorInfoByGcmId($os, $gcm_regid);
	$monitor_info = $db->getMonitorInfoByGcmId($os, $gcm_regid);
	if (mysql_num_rows($monitor_info) <= 0 && mysql_num_rows($matched_monitor_info) <= 0)
	{
		echo "No records";
        return;
	}
    if ($os == 1) { // TODO finish this

        // Create the payload body
        $result = array(
            'info' => array(
                'return_code' => 0,
                'msg' => ''
            ),
            'met_url_thresholds' => array(),
            'un_met_url_thresholds' => array(),
        );

        while ($row = mysql_fetch_array($matched_monitor_info))
        {
            $result['met_url_thresholds'][] = array(
                'id' => $row['id'],
                'red' => $row['red'],
                'url' => $row['url'],
                'threshold' => $row['sign'][0] . ' ' . $row['target']
            );
        }
        while ($row = mysql_fetch_array($monitor_info))
        {
            $result['un_met_url_thresholds'][] = array(
                'url' => $row['url'],
                'threshold' => $row['sign'][0] . ' ' . $row['target']
            );
        }

        // Encode the payload as JSON
        echo json_encode($result);
        return;
    }
	$result = "|#head#|";
	while ($row = mysql_fetch_array($matched_monitor_info))
	{
		$result .= $row["url"] . "|#next#|";
	}
	$result .= "|#unmatched#|";
	while ($row = mysql_fetch_array($monitor_info))
	{
		$result .= $row["url"] . "|#next#|";
	}
	$result .= "|#tail#|";

	echo $result;
}

?>

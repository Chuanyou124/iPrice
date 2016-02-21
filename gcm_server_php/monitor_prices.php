<?php

// response json
$json = array();

/**
 * monitor price for a given product url
 */
if (isset($_POST["os"]) && isset($_POST["priceIndex"]) && isset($_POST["priceUrls"]) && isset($_POST["sign"]) && isset($_POST["bound"]) && isset($_POST["regId"])) {

    $os = $_POST["os"];
    $price_index = $_POST["priceIndex"];
	$price_urls = $_POST["priceUrls"];
	$sign = $_POST["sign"];
	$bound = $_POST["bound"];
    $gcm_regid = $_POST["regId"]; // GCM Registration ID
	$category = 0;
	if ($sign == "==")
	{
		$category = 1;
	}
    $batch_query = array();
    foreach( $price_urls as $price_url ) {
            $batch_query[] = '(' . $os . ', "' . $gcm_regid. '", "' . $price_url . '", ' . $category. ', ' . $price_index. ', "' . $bound. '", "' . $sign . '", NOW())';
    }
    error_log(print_r($batch_query, TRUE));

	include_once './db_functions.php';
    $db = new DB_Functions();
	$dbRM = $db->batchStoreMonitorInfo($batch_query);

	echo $dbRM;
} else {
    echo ("price details missing");
}
?>

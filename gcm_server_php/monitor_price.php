<?php

// response json
$json = array();

/**
 * monitor price for a given product url
 */
if (isset($_POST["os"]) && isset($_POST["priceIndex"]) && isset($_POST["priceUrl"]) && isset($_POST["sign"]) && isset($_POST["bound"]) && isset($_POST["regId"])) {
    $os = $_POST["os"];
    $price_index = $_POST["priceIndex"];
	$price_url = $_POST["priceUrl"];
	$sign = $_POST["sign"];
	$bound = $_POST["bound"];
    $gcm_regid = $_POST["regId"]; // GCM Registration ID
	$category = 0;
	if ($sign == "==")
	{
		$category = 1;
	}

	include_once './db_functions.php';
    $db = new DB_Functions();
	$dbRM = $db->storeMonitorInfo($os, $gcm_regid, $price_url, $category, $price_index, $bound, $sign);

	echo $dbRM;
} else {
    echo "user details missing";
}
?>

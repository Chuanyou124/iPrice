<?php

// response json
$json = array();

if (isset($_POST["regId"]) && isset($_POST["os"]) && isset($_POST["historyId"]))
{
	$gcm_regid = $_POST["regId"];
    $os = $_POST["os"];
    $history_id = $_POST["historyId"];

    // include functions
    include_once './functions.php';
    $functions = new Functions();

    echo $functions->get_updated_badge($os, $gcm_regid, $history_id);
}

?>

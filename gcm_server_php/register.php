<?php

// response json
$json = array();

/**
 * Registering a user device
 * Store reg id in users table
 */
// TODO add os as post request parameter in android development
// do not check name for now as we don't need register or login to facilitate user
if (isset($_POST["os"]) && isset($_POST["regId"])) {
    $os = $_POST["os"]; // operating system
    $name = $_POST["name"];
    $email = $_POST["email"];
    $gcm_regid = $_POST["regId"]; // GCM Registration ID

    // Store user details in db
    include_once './db_functions.php';
    include_once './GCM.php';
    include_once './APNS.php';

    $db = new DB_Functions();
    $res = $db->storeUser($os, $name, $email, $gcm_regid);

    if ($os == 0)
    {
        $gcm = new GCM();
        $registatoin_ids = array($gcm_regid);
        $message = array("product" => "shirt");
        echo $gcm->send_notification($registatoin_ids, $message);
    }

    echo 'registered';
} else {
    echo 'either os or regId is absent for registration';
}

?>

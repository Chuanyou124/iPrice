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
    $gcm_regid = $_POST["regId"]; // GCM Registration ID

    // Store user details in db
    include_once './db_functions.php';
    include_once './GCM.php';
    include_once './APNS.php';

    $db = new DB_Functions();
    $db->storeUser($os, $gcm_regid);
    $email_array = mysql_fetch_array($db->getEmail($os, $gcm_regid));
    $email = array(
        'email' => $email_array['email']
    );

    if ($os == 0)
    {
        $gcm = new GCM();
        $registatoin_ids = array($gcm_regid);
        $message = array("product" => "shirt");
        $gcm->send_notification($registatoin_ids, $message);
    }

    echo json_encode($email);
}

?>

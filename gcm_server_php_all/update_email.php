<?php

// response json
$json = array();

if (isset($_POST["os"]) && isset($_POST["regId"]) && isset($_POST["email"])) {
    $os = $_POST["os"]; // operating system
    $gcm_regid = $_POST["regId"]; // GCM Registration ID
    $email = $_POST["email"]; // GCM Registration ID

    // Store user details in db
    include_once './db_functions.php';
    include_once './GCM.php';
    include_once './APNS.php';

    $db = new DB_Functions();
    $db->updateEmail($os, $gcm_regid, $email);
}

?>

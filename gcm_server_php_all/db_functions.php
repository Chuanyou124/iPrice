<?php

class DB_Functions {

    private $db;
    private $DIR_CGI;

    //put your code here
    // constructor
    function __construct() {
        $DIR_CGI = '/var/www/gcm_server_php';
        include_once "$DIR_CGI/db_connect.php";
        // connecting to database
        $this->db = new DB_Connect();
        $this->db->connect();
    }

    // destructor
    function __destruct() {

    }

    /**
     * Storing new user
     * returns user details
     */
    public function storeUser($os, $gcm_regid) {
        // insert user into database
        $result = mysql_query("INSERT INTO gcm_users(os, gcm_regid, created_at) VALUES('$os', '$gcm_regid', NOW())");

        return $result;
    }

    /**
     * get user email
     * returns user email
     */
    public function getEmail($os, $gcm_regid) {
        $result = mysql_query("SELECT email FROM gcm_users WHERE os = $os AND gcm_regid LIKE '$gcm_regid'");

        return $result;
    }

    /**
     * update user email
     */
    public function updateEmail($os, $gcm_regid, $email) {
        $result = mysql_query("UPDATE gcm_users SET email = '$email' WHERE os = $os AND gcm_regid LIKE '$gcm_regid'");

        return $result;
    }

    /**
     * Getting all users
     */
    public function getAllUsers() {
        $result = mysql_query("select * FROM gcm_users");
        return $result;
    }

    /**
     * batch storing new price monitor info
     */
    public function batchStoreMonitorInfo($batch_query) {

        $result = mysql_query('INSERT INTO monitor_info(os, gcm_regid, email, url, category, concerned_index, target, sign, created_at) VALUES' . implode(',', $batch_query));
        error_log(print_r($result));
        // check for successful store
		if ($result)
		{
			return "Price successfully monitored\nYou will be notified via email once it meets your target";
		}
		else
		{
			return "You have monitored one price before, please re-select your price";
		}
    }


    /**
     * Storing new price monitor info
     */
    public function storeMonitorInfo($os, $gcm_regid, $url, $category, $concerned_index, $target, $sign) {
        // insert monitor info into database
		$exist = mysql_query("SELECT * FROM monitor_info WHERE os = $os AND gcm_regid LIKE '$gcm_regid' AND url LIKE '$url' AND concerned_index = $concerned_index AND sign LIKE '$sign' AND target LIKE '$target'") or die(mysql_error());
		if (mysql_num_rows($exist) > 0)
		{
			return "The price was monitored before; please change to a new one";
		}

        $result = mysql_query("INSERT INTO monitor_info(os, gcm_regid, url, category, concerned_index, target, sign, created_at) VALUES('$os', '$gcm_regid', '$url', '$category', '$concerned_index', '$target', '$sign', NOW())");
        // check for successful store
		if ($result)
		{
			return "Concerned price is being monitored. You will be notified once it matches your condition";
		}
		else
		{
			return "Sorry, failed to save monitor information; please try again later";
		}
    }

     /**
     * Get new price monitor info
     */
    public function getMonitorInfo() {
		$monitor_info = mysql_query("SELECT * FROM monitor_info") or die(mysql_error());
		return $monitor_info;
    }

	 /**
     * Get new price monitor info
     */
    public function getMonitorInfoByGcmId($os, $gcm_regid) {
		$monitor_info = mysql_query("SELECT url, sign, target FROM monitor_info WHERE os = $os AND gcm_regid LIKE '$gcm_regid' ORDER BY created_at DESC");
		return $monitor_info;
    }

	/**
     * delete price monitor info that meets user requirement
     */
    public function delete_monitor_info($os, $gcm_regid, $url, $concerned_index, $sign, $target)
	{
		$db_rm = mysql_query("DELETE FROM monitor_info WHERE os = $os AND gcm_regid LIKE '$gcm_regid' AND url LIKE '$url' AND concerned_index = $concerned_index AND sign LIKE '$sign' AND target LIKE '$target'") or die(mysql_error());
		echo $db_rm;
	}


    /**
     * Storing matched monitor info
     */
    public function storeMatchedMonitorInfo($os, $gcm_regid, $url, $category, $concerned_index, $target, $sign, $matched_price) {
        // insert matched monitor info into database
        $result = mysql_query("INSERT INTO matched_monitor_info(os, gcm_regid, url, category, concerned_index, target, sign, matched_price, created_at) VALUES('$os', '$gcm_regid', '$url', '$category', '$concerned_index', '$target', '$sign', '$matched_price', NOW())");
        // check for successful store
		if ($result) {
			return mysql_insert_id(); // last inserted id
		}
		else {
			return 0;
		}
    }

     /**
     * Get new matched monitor info
     */
    public function getMatchedMonitorInfoByGcmId($os, $gcm_regid) {
		$matched_monitor_info = mysql_query("SELECT id, url, sign, target, red FROM matched_monitor_info WHERE os = $os AND gcm_regid LIKE '$gcm_regid' ORDER BY created_at DESC");
		return $matched_monitor_info;
    }

    // update matched monitor red info
    public function update_matched_red_info($id) {
		$db_update = mysql_query("UPDATE matched_monitor_info SET red = 1 WHERE id = $id");
    }

    // get number of unread matched monitor info
    public function get_badge($os, $gcm_regid) {
		$badge = mysql_query("SELECT COUNT(*) AS badge FROM matched_monitor_info WHERE os = $os AND gcm_regid LIKE '$gcm_regid' AND red = 0");
        return $badge; // cannot directly return the mysql_query, must assign and return
    }

	/**
     * delete matched monitor info
     */
    public function delete_matched_monitor_info($os, $gcm_regid, $url, $concerned_index, $sign, $target, $matched_price)
	{
		$db_rm = mysql_query("DELETE FROM matched_monitor_info WHERE os = $os AND gcm_regid LIKE '$gcm_regid' AND url LIKE '$url' AND concerned_index = $concerned_index AND sign LIKE '$sign' AND target LIKE '$target' AND matched_price LIKE '$matched_price'");
		echo $db_rm;
	}
}

?>

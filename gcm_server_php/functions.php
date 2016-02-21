<?php

class Functions {

    function __construct()
    {
        $DIR_CGI = '/var/www/gcm_server_php';
        include_once "$DIR_CGI/GCM.php";
        include_once "$DIR_CGI/APNS.php";
        include_once "$DIR_CGI/db_functions.php";
        include_once "$DIR_CGI/async_request.php";
        $this->gcm = new GCM();
        $this->apns = new APNS();
        $this->db = new DB_Functions();
        $this->async_req = new AsyncRequest();
    }

    function send_post_request($params) {
        $price_server_url = 'http://65.182.110.118:1100/api/priceinfo';

        // use key 'http' even if you send the request to https://...
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($params),
            ),
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($price_server_url, false, $context);
        var_dump($result);

        return $result;
    }

    function get_price_with_index($product_url, $concerned_index) {
        $params = array('product_url' => $product_url);
        $price_obj = json_decode($this->send_post_request($params), true);;

        $rc = $price_obj["info"]["return_code"];
        if ($rc != 0) {
            return "No such price";
        }

        if ($price_obj["info"]["source"] == "soup") {
            return $price_obj["prices"][0];
        }

        return $price_obj["prices"][$concerned_index];
    }

    function get_badge($os, $gcm_regid) {
        $badge = $this->db->get_badge($os, $gcm_regid);
        if (mysql_num_rows($badge) <= 0) {
            return 0;
        }

        $row = mysql_fetch_array($badge);
        return $row['badge'];
    }

    function get_updated_badge($os, $gcm_regid, $history_id) {
        $this->db->update_matched_red_info($history_id);
        return $this->get_badge($os, $gcm_regid);
    }

    function check_update_all($param_array) {

        $callback = function($response, $param) {
            //print_r($response);
            include_once "/var/www/gcm_server_php/functions.php";
            $functions = new Functions();

            $os = $param['os'];
            $gcm_regid = $param['gcm_regid'];
            $url = $param['url']; // this is product_url
            $category = $param['category'];
            $concerned_index = $param['concerned_index'];
            $target = $param['target'];
            $sign = $param['sign'];
            $id = $param['id'];

            // get exact price
            $registatoin_ids = array($gcm_regid);
            $current_price = "";
            $price_obj = json_decode($response, true);;

            $rc = $price_obj["info"]["return_code"];
            if ($rc != 0) {
               $current_price = "No such price";
            }

            if ($price_obj["info"]["source"] == "soup") {
                $current_price = $price_obj["prices"][0];
            }

            $current_price = $price_obj["prices"][$concerned_index];

            if ($current_price == "No such price" || floatval($current_price) <= 0)  {
                return;
            }

            // prepare the msg to send as push notification if target met
            $message;
            if ($os == 0) {
               $message =  array("price" => ("The price in URL below is " . $sign . " " . $target . " now!\n|#URL#|" . $url . " |#id#|" . $id));
            } else if ($os == 1) {
                $msg_json_obj = array(
                    'prod_url' => $url,
                    'sign' => $sign,
                    'threshold' => $target
                );
                $message = json_encode($msg_json_obj);
            }
            $badge = $functions->get_badge($os, $gcm_regid) + 1; // + 1 is because we will have new unread below

            // compare current price with target and update db & send PN if possible
            if ($sign == "<=")
            {
                if (floatval($current_price) <= floatval($target))
                {
                    $history_id = $functions->db->storeMatchedMonitorInfo($os, $gcm_regid, $url, $category, $concerned_index, $target, $sign, $current_price);
                    $functions->db->delete_monitor_info($os, $gcm_regid, $url, $concerned_index, $sign, $target);
                    if ($os == 0) // android
                    {
                        echo $functions->gcm->send_notification($registatoin_ids, $message);
                    }
                    else if ($os == 1) // iOS
                    {
                        echo $functions->apns->send_notification($gcm_regid, $message, $badge, $history_id);
                    }
                }
            }
            else if ($sign == ">=")
            {
                if (floatval($current_price) >= floatval($target))
                {
                    $history_id = $functions->db->storeMatchedMonitorInfo($os, $gcm_regid, $url, $category, $concerned_index, $target, $sign, $current_price);
                    $functions->db->delete_monitor_info($os, $gcm_regid, $url, $concerned_index, $sign, $target);
                    if ($os == 0) // android
                    {
                        echo $functions->gcm->send_notification($registatoin_ids, $message);
                    }
                    else if ($os == 1) // iOS
                    {
                        echo $functions->apns->send_notification($gcm_regid, $message, $badge, $history_id);
                    }
                }
            }
            else
            {
                if ($current_price == $target)
                {
                    $history_id = $functions->db->storeMatchedMonitorInfo($os, $gcm_regid, $url, $category, $concerned_index, $target, $sign, $current_price);
                    $functions->db->delete_monitor_info($os, $gcm_regid, $url, $concerned_index, $sign, $target);
                    if ($os == 0) // android
                    {
                        echo $functions->gcm->send_notification($registatoin_ids, $message);
                    }
                    else if ($os == 1) // iOS
                    {
                        echo $functions->apns->send_notification($gcm_regid, $message, $badge, $history_id);
                    }
                }
            }
        };

        $this->async_req->process_req('http://65.182.110.118:1100/api/priceinfo', $param_array, $callback);
    }

    function check_update($os, $gcm_regid, $url, $category, $concerned_index, $target, $sign, $id) {
        $registatoin_ids = array($gcm_regid);
        $current_price = "";
        $current_price = $this->get_price_with_index($url, $concerned_index);

        if ($current_price == "No such price" || floatval($current_price) <= 0)  {
            return;
        }

        $message;
        if ($os == 0) {
           $message =  array("price" => ("The price in URL below is " . $sign . " " . $target . " now!\n|#URL#|" . $url . " |#id#|" . $id));
        } else if ($os == 1) {
            $msg_json_obj = array(
                'prod_url' => $url,
                'sign' => $sign,
                'threshold' => $target
            );
            $message = json_encode($msg_json_obj);
        }
        $badge = $this->get_badge($os, $gcm_regid) + 1; // + 1 is because we will have new unread below

        if ($sign == "<=")
        {
            if (floatval($current_price) <= floatval($target))
            {
                $history_id = $this->db->storeMatchedMonitorInfo($os, $gcm_regid, $url, $category, $concerned_index, $target, $sign, $current_price);
                $this->db->delete_monitor_info($os, $gcm_regid, $url, $concerned_index, $sign, $target);
                if ($os == 0) // android
                {
                    echo $this->gcm->send_notification($registatoin_ids, $message);
                }
                else if ($os == 1) // iOS
                {
                    echo $this->apns->send_notification($gcm_regid, $message, $badge, $history_id);
                }
            }
        }
        else if ($sign == ">=")
        {
            if (floatval($current_price) >= floatval($target))
            {
                $history_id = $this->db->storeMatchedMonitorInfo($os, $gcm_regid, $url, $category, $concerned_index, $target, $sign, $current_price);
                $this->db->delete_monitor_info($os, $gcm_regid, $url, $concerned_index, $sign, $target);
                if ($os == 0) // android
                {
                    echo $this->gcm->send_notification($registatoin_ids, $message);
                }
                else if ($os == 1) // iOS
                {
                    echo $this->apns->send_notification($gcm_regid, $message, $badge, $history_id);
                }
            }
        }
        else
        {
            if ($current_price == $target)
            {
                $history_id = $this->db->storeMatchedMonitorInfo($os, $gcm_regid, $url, $category, $concerned_index, $target, $sign, $current_price);
                $this->db->delete_monitor_info($os, $gcm_regid, $url, $concerned_index, $sign, $target);
                if ($os == 0) // android
                {
                    echo $this->gcm->send_notification($registatoin_ids, $message);
                }
                else if ($os == 1) // iOS
                {
                    echo $this->apns->send_notification($gcm_regid, $message, $badge, $history_id);
                }
            }
        }
    }

}

?>

<?php

class AsyncRequest
{
    public function __construct()
    {
        $this->handle = curl_multi_init();
    }

    // this function is sending a series of asynchronous call and fire callback
    // once response comes back. It is not a generic function, and mainly designed
    // for iPrice purpose to get prices for a series of product urls, while the
    // API service url is unique. The arguments are respectively:
    // url: API service url
    // param_array: array of param, each param is a dictionary of monitor_info db row
    // callback: callback function which process response such as sending notification
    public function process_req($url, $param_array, $callback) {
        if (!is_array($param_array) or count($param_array) == 0) {
            return false;
        }

        $param_hash = array();
        foreach($param_array as $k => $v) {
            $nurl = preg_replace('~([^:\/\.]+)~ei', "rawurlencode('\\1')", $url);
            $curl = curl_init($nurl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5); // seconds
            curl_setopt($curl, CURLOPT_TIMEOUT, 15); // each curl handler takes up to 15 seconds

            $data = array('product_url' => $v['url']);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            curl_multi_add_handle($this->handle, $curl);

            print "curl resource id is: " . intval($curl) . "\n";
            // add this row keyed off to curl resource id, so when response comes back, we can identify
            // it belongs to which request and thus pass corresponding param to the callback function
            $param_hash[intval($curl)] = $v;
        }

        // I believe the first do while loop is sending out ALL http request
        $active = null;
        do {
            $mrc = curl_multi_exec($this->handle, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        // This loop is checking is there response back; i.e., if active > 0 and mrc=CURLM_OK
        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($this->handle) != -1) {
                do {
                    // I believe this execution does NOT send out http request,
                    // but just check / modify active and mrc value
                    $mrc = curl_multi_exec($this->handle, $active);
                    print "in while, mrc:$mrc, active:$active, curlm_ok:" . CURLM_OK . "\n";

                    // getting the incoming response and process it with callback
                    if ($state = curl_multi_info_read($this->handle))
                    {
                    //print "in if\n";
                        print "handle state is: " . intval($state["handle"]) . "\n";
                        if (curl_error($state['handle']) == "") {
                            $callback(curl_multi_getcontent($state['handle']), $param_hash[intval($state['handle'])]);
                        }
                        curl_multi_remove_handle($this->handle, $state['handle']);
                        curl_close($state['handle']);
                    }

                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }
    }

    public function __destruct()
    {
        curl_multi_close($this->handle);
    }
}

<?php

class APNS {

    private $passphrase;

    function __construct() {
        $this->passphrase = 'isisi52044';
    }

    /**
     * Sending Push Notification
     */
    public function send_notification($device_token, $message, $badge, $history_id) {
        var_dump($device_token);
        var_dump($message);
        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', 'adhoc-ck.pem');
        stream_context_set_option($ctx, 'ssl', 'passphrase', $this->passphrase);

        // Open a connection to the APNS server
        $fp = stream_socket_client(
            'ssl://gateway.push.apple.com:2195', $err,
            $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

        if (!$fp)
            exit("Failed to connect: $err $errstr" . PHP_EOL);

        echo 'Connected to APNS' . PHP_EOL;

        // Create the payload body
        $body['aps'] = array(
            'alert' => $message,
            'sound' => 'default',
            'badge' => $badge,
            'history_id' => $history_id
        );

        // Encode the payload as JSON
        $payload = json_encode($body);

        // Build the binary notification
        $msg = chr(0) . pack('n', 32) . pack('H*', $device_token) . pack('n', strlen($payload)) . $payload;

        // Send it to the server
        $result = fwrite($fp, $msg, strlen($msg));

        if (!$result) {
            echo 'Message not delivered' . PHP_EOL;
        var_dump('not delivered');
        }
        else {
            echo 'Message successfully delivered' . PHP_EOL;
        var_dump('delivered');
        }

        // Close the connection to the server
        fclose($fp);

    }

}

?>

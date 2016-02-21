<?php

// Put your device token here (without spaces):
$deviceToken = '79e2362a5e7deb1eefbbe6752463435563f3df4b07a8e1b3b628f9195e4abd40'; // Chuanyou Li's dev
//$deviceToken = '7c06aaa5069556e6c140926de65cf34f94a8452f51b75a683b8a24d482867a9d'; // Chuanyou Li's prod
//$deviceToken = '32bee65831e5148d59c7b24301c1a1f98a118331bbdafb95335effc53c7fc7d0'; // Sisi's prod

// Put your private key's passphrase here:
$passphrase = 'isisi52044';

// Put your alert message here:
$msg_json_obj = array(
    'prod_url' => 'http://www1.bloomingdales.com/shop/product/burberry-brit-thayer-shawl-collar-sweater?ID=975710&cm_mmc=EML2-_-fedfil_order_confirmation-_-Body-_-975710',
    'sign' => '<',
    'threshold' => '399'
);
$message = json_encode($msg_json_obj);

////////////////////////////////////////////////////////////////////////////////

$ctx = stream_context_create();
stream_context_set_option($ctx, 'ssl', 'local_cert', 'ck_dev.pem');
stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

// Open a connection to the APNS server
$fp = stream_socket_client(
	'ssl://gateway.sandbox.push.apple.com:2195', $err,
	//'ssl://gateway.push.apple.com:2195', $err,
	$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

if (!$fp)
	exit("Failed to connect: $err $errstr" . PHP_EOL);

echo 'Connected to APNS' . PHP_EOL;

// Create the payload body
$body['aps'] = array(
	'alert' => $message,
	'sound' => 'default',
    'badge' => 1
	);

// Encode the payload as JSON
$payload = json_encode($body);

// Build the binary notification
$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

// Send it to the server
$result = fwrite($fp, $msg, strlen($msg));

if (!$result)
	echo 'Message not delivered' . PHP_EOL;
else
	echo 'Message successfully delivered' . PHP_EOL;

// Close the connection to the server
fclose($fp);

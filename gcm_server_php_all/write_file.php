<?php

$param_array = array(
    1 => array('a' => 200, 'b' => "\n"),
    2 => 'b',
    3 => 'c'
);
file_put_contents('/home/hongyu/release/priceninja/web/price_info/inputx', json_encode($param_array));

/*
$param_array = array(
    array('id' => 1, 'url' => 'www.linkssea.com', 'shit' => 'fuck'),
    array('id' => 2, 'url' => 'www.menpool.com', 'shit' => 'fuck'),
    array('id' => 3, 'url' => 'www.kengdaddy.com', 'shit' => 'fuck')
);

$fp = fopen('/home/hongyu/release/priceninja/web/price_info/inputx', 'w');
foreach ($param_array as $param) {
    $json_str = json_encode(array(
        'id' => $param['id'],
        'product_url' => $param['url']
    ));
    fwrite($fp, $json_str);
    fwrite($fp, "\n");
}
fclose($fp);

exec("/home/hongyu/release/priceninja/bin/python -c 'import sys; sys.path.append(\"/home/hongyu/release/priceninja/web\"); import app; app.get_price_info_batch()'");
        $json_str_prices = file_get_contents('/home/hongyu/release/priceninja/web/price_info/output');
$json_obj = json_decode($json_str_prices, true);
print_r($json_obj);

if (isset($json_obj[3])) {
    print_r($json_obj[3]);
}
 */

?>

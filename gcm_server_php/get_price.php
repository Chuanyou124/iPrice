<?php

// response json
$json = array();

/**
 * get price from given url
 */
if (isset($_POST["priceUrl"])) {
    $price_url = $_POST["priceUrl"];

	$page = file_get_contents($price_url);
	$pattern = '/\$(\d+(\.\d+)?)/';
	preg_match_all($pattern, $page, $title);
	if (count($title) > 0)
	{
		echo implode('|', $title[1]);
	}
	else
	{
		echo "no price found";
	}

} else {
    echo "product url missing";
}
?>

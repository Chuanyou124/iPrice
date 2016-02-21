<?php


    $www_root = 'http://192.241.151.56/gcm_server_php/price_images';
    $dir = '/var/www/gcm_server_php/price_images'; //James, please add your images in this dir
	// and to distinguish each process, each time when you generate a series of images, just put them in
	// a unique dir name. Remember to associate a product URL to each image and put into $product_urls
    $file_display = array('jpg', 'jpeg', 'png', 'gif');
	$product_urls = array('http://www.stuartweitzman.com/products/mystars/?DepartmentId=160&DepartmentGroupId=-1&',
						  'http://www.ysl.com/us/shop-product/women/shoes-ballerinas-classic-dance-ballerina-flat-in-gold-metallic-leather-and-transparent-vinyl_cod44598199hj.html',
						  'http://www.amazon.com/Ascend-P6-Unlocked-smartphone-Thickness/dp/B00DKEX5AU/ref=sr_1_2?ie=UTF8&qid=1388003972&sr=8-2&keywords=huawei+ascend+p6',
						  'http://www.walmart.com/ip/SCEPTRE-X405BV-FHDR-40-LED-Class-1080P-HDTV-with-ultra-slim-metal-brush-bezel-60Hz/27608624');//<-- put your url in this value
	if ( file_exists( $dir ) == false ) {
       echo 'Directory \'', $dir, '\' not found!';
    } else {
       $dir_contents = scandir( $dir );

	   $check_index = 0;
        foreach ( $dir_contents as $file ) {
		   $exploded_file = explode('.', $file );
		   $extension = end($exploded_file);
           $file_type = strtolower($extension);
           if ( ($file !== '.') && ($file !== '..') && (in_array( $file_type, $file_display)) ) {
			  echo '<div class="container">';
              echo '<img src="', $www_root, '/', $file, '" alt="', $file, '"/>';
			  echo '<input type="checkbox" class="checkbox" id="check' . strval($check_index) . '"/><br />';
			  echo '<input type="hidden" name="product_url" id="product_url' . strval($check_index) . '" value="' . $product_urls[$check_index++] . '"/>
					<br/>';
			  echo '</div>';
           }
        }
        echo '<input type="hidden" name="num_imgs" id="num_imgs" value="' . $check_index . '"/><br />';
    }
?>
<html>
  <head>
	<meta name="viewport" content="width=device-width">
	<style>
		img {
			max-width: 100%;
		}
	</style>
</html>

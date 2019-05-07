<?php
/*
Plugin Name: FCF Shortcodes For LearnDash
Plugin URI: https://www.discoverelearninguk.com
Description: Turn Flexible Checkout Fields into shortcodes that can be used in LearnDash certificates and other content areas
Author: Discover eLearning Ltd
Version: 0.1
Author URI: https://www.discoverelearninguk.com
License: GPL2
*/

add_shortcode('user_meta', 'user_meta_shortcode_handler');
function user_meta_shortcode_handler($atts,$content=null){

	$user_id = get_current_user_id();
	$rut = get_user_meta($user_id, 'rut', true);

	return 'RUT '.$rut;

}

add_shortcode('user_id', 'user_id_handler');
function user_id_handler() {

	$user_id = get_current_user_id();
	return $user_id;

}

add_shortcode('user_profile_image', 'user_profile_image_handler');
function user_profile_image_handler() {
	// return '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img style="float: right; " src="?virtual_image=">';
	return '<img style="float: right; " src="?virtual_image=">';
}

add_filter( 'init', function( $template ) {
    if ( isset( $_GET['virtual_image'] ) ) {

		$user_id = get_current_user_id();
		$image = image_generate(get_avatar_url($user_id));

        die;
    }
} );


function image_generate($filename) {
	$image_s = imagecreatefromstring(file_get_contents($filename));
	$width = imagesx($image_s);
	$height = imagesy($image_s);

	$newwidth = 250;
	$newheight = 250;

	$image = imagecreatetruecolor($newwidth, $newheight);
	imagealphablending($image, true);
	imagecopyresampled($image, $image_s, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

	$mask = imagecreatetruecolor($newwidth, $newheight);

	$transparent = imagecolorallocate($mask, 25,0,0);
	imagecolortransparent($mask, $transparent);

	imagefilledellipse($mask, $newwidth/2, $newheight/2, $newwidth, $newheight, $transparent);

	$red = imagecolorallocate($mask, 0, 0, 0);
	imagecopymerge($image, $mask, 0, 0, 0, 0, $newwidth, $newheight, 100);
	imagecolortransparent($image, $red);
	imagefill($image, 0,0,$red);

	header('Content-type: image/png');
	imagepng($image);
	imagepnd($image, 'output.png');
	imagedestroy($image);
}
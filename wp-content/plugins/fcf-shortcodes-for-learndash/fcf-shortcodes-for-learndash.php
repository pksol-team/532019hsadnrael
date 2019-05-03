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
 
	if ( !isset( $atts['user_id'] ) ){
		$user = wp_get_current_user();
		$atts['user_id'] = $user->ID;
	}

	$user = new WP_User($atts['user_id']);
 
	if ( !$user->exists() ) return;
 
	if ( $user->has_prop( $atts['key'] ) ){
			$value = $atts['user_id']; //$user->get( $atts['key'] );
	}
 
	if (!empty( $value )){
		return $atts['pre'] . $value . $atts['post'] ;
	}
 
	return;
}
<?php

if( !defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') )
    exit();

	$options_array = array(
		'wp_custom_login_logo_url', 
		'wp_custom_login_url', 
		'wp_custom_login_title', 
		'wp_custom_login_logo_height', 
		'wp_custom_login_logo_width', 
		'wp_custom_login_logo_fadein',
		'wp_custom_login_logo_fadetime',
		'wp_custom_login_logo_message'
	);
	
foreach ($options_array as $option) {
	delete_option( $option );
}
?>
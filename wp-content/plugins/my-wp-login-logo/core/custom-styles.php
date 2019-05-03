<?php
/* Custom Styling for Login Page */

		$custom_logo_url = get_option('wp_custom_login_logo_url', DC_MyWP_LoginLogo_URL.'images/mylogo.png');
		$custom_logo_height = get_option('wp_custom_login_logo_height','70');
		$custom_logo_width = get_option('wp_custom_login_logo_width','320');
		
		echo '<!-- Customized using My Wordpress Login Logo -->
		<style type="text/css">
		.login h1 a { 
			background-image:url('.$custom_logo_url.') !important; 
			background-size:'.$custom_logo_width.'px '.$custom_logo_height .'px; 
			height:'.$custom_logo_height .'px; width:'.$custom_logo_width.'px;
			margin: 0 auto;
		}

		.login .message {
		display: none;
		}	
		</style>';
?>
<?php
/*
Plugin Name: My Wordpress Login Logo
Plugin URI: http://digitcodes.com
Description: My Wordpress Login Logo lets you to add a custom logo in your wordpress login page instead of the usual wordpress logo.
Version: 2.2
Author: Afsal Rahim
Author URI: http://afsal.me
*/

if ( !defined( 'DC_MyWP_LoginLogo_URL' ) )
	define( 'DC_MyWP_LoginLogo_URL', plugin_dir_url( __FILE__ ) );
if ( !defined( 'DC_MyWP_LoginLogo_PATH' ) )
	define( 'DC_MyWP_LoginLogo_PATH', plugin_dir_path( __FILE__ ) );
	
class DC_MyWP_LoginLogo {

	function __construct() {
		add_action('login_head', array( $this, 'DC_MyWP_login_logo'));
		add_action('login_head', array( $this, 'DC_MyWP_login_fadein'));
		add_action('login_form', array( $this, 'DC_MyWP_login_form_message'));
		add_action('admin_menu', array( $this, 'DC_MyWP_login_logo_actions'));  

		add_filter('login_headerurl', array( $this, 'DC_MyWP_login_url'));
		add_filter("login_headertitle", array( $this, 'DC_MyWP_login_title'));
	}

	function DC_MyWP_login_logo() {
		include_once( DC_MyWP_LoginLogo_PATH . '/core/custom-styles.php' );
	}

	function DC_MyWP_login_url() {
		$custom_login_url = get_option('wp_custom_login_url',home_url());
		return $custom_login_url;
	}

	function DC_MyWP_login_title() {
		$custom_login_title = get_option('wp_custom_login_title',get_bloginfo('description'));
		return $custom_login_title;
	}

	function DC_MyWP_login_fadein() {
		include_once( DC_MyWP_LoginLogo_PATH . '/core/custom-js.php' );
	}
	
	function DC_MyWP_login_form_message() {
		$custom_logo_message = get_option('wp_custom_login_logo_message','');
		if($custom_logo_message != '') {
		echo '<p>'.$custom_logo_message.'</p><br/>';
		}
	}

	function DC_MyWP_login_logo_options() { 
		require( DC_MyWP_LoginLogo_PATH . '/views/dashboard.php' );
	 }

	function DC_MyWP_login_logo_actions() {  
		add_menu_page( 'My Wordpress Login Logo Options' , 'My Wordpress Login Logo', 'manage_options', 'DC_MyWP_login_logo_dashboard', array( $this, 'DC_MyWP_login_logo_options' ), DC_MyWP_LoginLogo_URL.'images/digitcodes-icon.png' ); 
	}
  
}
$MyWordpressLoginLogo = new DC_MyWP_LoginLogo();
?>
<?php
/*
* Plugin Name:         Perfect Audience Retargeting
* Plugin URI:          http://www.perfectaudience.com/wordpress
* Description:         Perfect Audience is the most effective Facebook and web retargeting platform. This plugin integrates your Perfect Audience code across your WordPress site in a few clicks.
* Author:              PerfectAudience
* Author URI:          http://perfectaudience.com
* Version: 			   1.4
*/

class WP_PerfectAudience {

	public function WP_PerfectAudience() {
		$this->init();
	}
	
	// initial point of action
	public function init() {
		add_action('admin_menu', array($this, 'register_page'));
		add_action('admin_init', array($this, 'register_settings'));
		add_action('wp_footer', array($this,'send_to_frontend'));
	}
	
	// register page call
	public function register_page() {
		add_options_page('Perfect Audience', 'Perfect Audience Retargeting', 'manage_options', 'wp_perfectaudience', array($this, 'perfect_page'));
	}
	
	// register settings group
	public function register_settings() {
		register_setting('perfect_setting', 'perfect_setting');
		
		add_settings_section(
			'perfect_settings_section',         // ID used to identify this section and with which to register options
			'Perfect Audience Site ID',                  // Title to be displayed on the administration page
			array($this, 'perfect_settings_callback'), // Callback used to render the description of the section
			'wp_perfectaudience'                           // Page on which to add this section of options
		);
		
		add_settings_field(
			'perfect_site_id',                      // ID used to identify the field throughout the theme
			'Perfect Audience site_id',                           // The label to the left of the option interface element
			array($this, 'perfect_site_id_callback'),   // The name of the function responsible for rendering the option interface
			'wp_perfectaudience',                          // The page on which this option will be displayed
			'perfect_settings_section'         // The name of the section to which this field belongs
		);
				
	}
	
	
	// general settings group callback
	public function perfect_settings_callback() {
		//$out = '<h3>Perfect Audience Retargeting</h3>';
		$out = '';
		echo $out;
	}
	
	// define settings field site_id
	public function perfect_site_id_callback() {
		$val = get_option('perfect_setting', '');
		$val = $val['perfect_site_id'];
		
		echo '<div><input type="text" id="perfect_site_id" name="perfect_setting[perfect_site_id]" value="'.$val.'" /></div>';
	}
	


	
	// call the page template here
	public function perfect_page() {
		include_once 'wp-perfectaudience-admin-tpl.php';
	}

	// print the code based on the parameters
	public function send_to_frontend() {
		$perfect_setting = get_option('perfect_setting', '');
		if(!empty($perfect_setting) && !empty($perfect_setting['perfect_site_id'])) {
			$site_id = $perfect_setting['perfect_site_id'];
			
			include_once 'footer-script-code.php';			
		}
	}
	
}

$wp_perfectaudience = new WP_PerfectAudience();

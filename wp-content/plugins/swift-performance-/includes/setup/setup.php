<?php

class Swift_Performance_Setup {

	public function __construct(){

		ini_set('display_errors', 0);

     		// Set installer directory path
     		if (!defined('SWIFT_PERFORMANCE_SETUP_DIR')){
     			define ('SWIFT_PERFORMANCE_SETUP_DIR', SWIFT_PERFORMANCE_DIR . 'includes/setup/');
     		}

     		// Set installer directory URI
     		if (!defined('SWIFT_PERFORMANCE_SETUP_URI')){
     			define('SWIFT_PERFORMANCE_SETUP_URI', SWIFT_PERFORMANCE_URI . 'includes/setup/');
     		}

     		// Ajax handlers
     		add_action('wp_ajax_swift_performance_setup', array($this, 'ajax_handler'));

		// Include Wizard
		if (isset($_GET['subpage']) && in_array($_GET['subpage'], array('setup', 'deactivate')) && isset($_GET['page']) && $_GET['page'] == SWIFT_PERFORMANCE_SLUG ){
			add_action('admin_init', array($this, 'init'));
		}

	}

	public function init(){
		// Localization
		$this->localize = array(
				'i18n' => array(
					'Upload' => esc_html__('Upload', 'swift-performance'),
					'Modify' => esc_html__('Modify', 'swift-performance'),
					'Please wait...' => esc_html__('Please wait...', 'swift-performance'),
					'Test timed out' => esc_html__('Test timed out', 'swift-performance')
				),
				'ajax_url'		=> admin_url('admin-ajax.php'),
				'nonce'		=> wp_create_nonce('swift-performance-setup'),
				'luv_nonce'		=> wp_create_nonce('luv-framework-fields-ajax'),
				'home_url'		=> home_url()
		);

		// Enqueue Setup Wizard CSS
		wp_enqueue_style('swift-performance', SWIFT_PERFORMANCE_URI. 'css/styles.css', array(), SWIFT_PERFORMANCE_VER);
		wp_enqueue_style('swift-performance-setup', SWIFT_PERFORMANCE_SETUP_URI . 'css/setup.css', array(), SWIFT_PERFORMANCE_VER);
		wp_enqueue_style('luv-framework', LUV_FRAMEWORK_URL . 'assets/css/fields.css');
		wp_enqueue_style('luv-framework-modal', LUV_FRAMEWORK_URL . 'assets/css/modal.css');
		wp_enqueue_style('font-awesome-5', LUV_FRAMEWORK_URL . 'assets/icons/fa5/css/all.min.css');
		wp_enqueue_style('animate-css', SWIFT_PERFORMANCE_SETUP_URI . 'css/animate.css');

		// Enqueue Setup Wizard JS
		wp_enqueue_script('swift-performance-setup', SWIFT_PERFORMANCE_SETUP_URI . 'js/setup.js', array(), SWIFT_PERFORMANCE_VER);
		wp_localize_script('swift-performance-setup', 'swift_performance', $this->localize);
		wp_enqueue_script('luv-framework-modal', LUV_FRAMEWORK_URL . 'assets/js/modal.js', array(), false, true);

		//WP admin styles
		wp_enqueue_style( 'wp-admin' );

		// Plugin checkings
		$active_plugins = get_option('active_plugins');
		$is_woocommerce_active = apply_filters('swift_performance_is_woocommerce_active', in_array('woocommerce/woocommerce.php', $active_plugins));

		// Setup already ran
		update_option('swift-perforomance-initial-setup-wizard',1);

		// Deactivation
		if (isset($_GET['subpage']) && $_GET['subpage'] == 'deactivate'){
			include_once SWIFT_PERFORMANCE_SETUP_DIR . 'templates/deactivate.tpl.php';
		}
		// Setup
		else {
			include_once SWIFT_PERFORMANCE_SETUP_DIR . 'templates/wizard.tpl.php';
		}
		die;
	}

	/**
	 * Ajax Handler
	 */
	public function ajax_handler(){
		global $wpdb;
		if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'swift-performance-setup') && current_user_can('manage_options')){
			wp_send_json(array(
				'result' => 'error',
				'message' => esc_html__('Your session has been expired. Please refresh the page and try again.','swift-performance')
			));
		}

		$next_slide = '';
		$result	= 'success';
		$message	= '';

		if (isset($_POST['setup-action'])){
			switch ($_POST['setup-action']){
				case 'set-purchase-key':
				$response = swift_performance_purchase_key_validate_callback(true, $_POST['key']);

				if ($response === true){
					Swift_Performance::update_option('purchase-key', $_POST['key']);
					$message = esc_html__('Purchase key has been saved.','swift-performance');
				}
				else if (isset($result['warning'])){
					$result	= 'error';
					$message	= $result['warning'];
				}
				else if (isset($result['error'])){
					$result	= 'error';
					$message	= $result['error'];
				}
				break;
				case 'set-cloudflare-api':
					Swift_Performance::update_option('cloudflare-auto-purge', 1);
					Swift_Performance::update_option('cloudflare-email', $_POST['cloudflare-email']);
					Swift_Performance::update_option('cloudflare-api-key', $_POST['cloudflare-api-key']);
					$message = __('Cloudflare API has been set.', 'swift-performance');
				break;
				case 'reset-defaults':
					$options    = Luv_Framework_Fields::$instances['options']['swift_performance_options']->get_defaults();
					$options['purchase-key'] = Swift_Performance::get_option('purchase-key');
					update_option('swift_performance_options', $options);
					Swift_Performance::update_option('merge-styles', 1);
					Swift_Performance::update_option('merge-scripts', 1);
				break;
				case 'set-uninstall-options':
					update_option('swift-performance-deactivation-settings', array(
						'keep-settings' => (isset($_POST['keep-settings']) ? $_POST['keep-settings'] : 0),
						'keep-custom-htaccess' => (isset($_POST['keep-custom-htaccess']) ? $_POST['keep-custom-htaccess'] : 0),
						'keep-warmup-table' => (isset($_POST['keep-warmup-table']) ? $_POST['keep-warmup-table'] : 0),
						'keep-image-optimizer-table' => (isset($_POST['keep-image-optimizer-table']) ? $_POST['keep-image-optimizer-table'] : 0),
						'keep-logs' => (isset($_POST['keep-logs']) ? $_POST['keep-logs'] : 0)
					), false);
				break;
				case 'timeout';
					delete_transient('swift_performance_analyze_multithread');
					$current_process = mt_rand(0,PHP_INT_MAX);
					Swift_Performance::set_transient('swift_performance_timeout_test_pid', $current_process, 600);

					// Try 600 seconds by default
					Swift_Performance::set_time_limit(601, 'timeout_test');

					wp_send_json(array(
						'result'		=> 'success',
						'next_slide'	=> $next_slide,
						'message'		=> $message
					));

					// Flush connection
					Swift_Performance_Tweaks::flush_connection();
					for ($i=0;$i<600;$i+=10){
						$timeout_test_process	= $wpdb->get_var("SELECT option_value FROM {$wpdb->options} WHERE option_name = '_transient_swift_performance_timeout_test_pid'");
						$multithread		= $wpdb->get_var("SELECT option_value FROM {$wpdb->options} WHERE option_name = '_transient_swift_performance_analyze_multithread'");
						if (($timeout_test_process !== false && $timeout_test_process != $current_process) || ($i >= 50 && empty($multithread)) ){
							break;
						}
						update_option('swift_performance_timeout', $i);
						sleep(10);
					}
					delete_transient('swift_performance_analyze_multithread');
				break;
				case 'max-connections':
					Swift_Performance::set_transient('swift_performance_analyze_multithread', 1, 600);
				break;
				case 'api':
					if (Swift_Performance::check_option('purchase-key', '', '!=')){
						$response = swift_performance_purchase_key_validate_callback(true, Swift_Performance::get_option('purchase-key'));

					 	if (isset($result['warning'])){
							$result	= 'error';
							$message	= $result['warning'];
						}
						else if (isset($result['error'])){
							$result	= 'error';
							$message	= $result['error'];
						}
					}
					else {
						$result	= 'warning';
						$message	= __('Purchase key is empty', 'swift-performance');
					}
				break;
				case 'webserver':
					$missing_apache_modules = array();
					$server_software = Swift_Performance::server_software();
					$rewrites = false;
					if ($server_software == 'apache'){
						$rewrites = true;
						// Check modules if server isn't litespeed
						if (preg_match('~apache~i', $_SERVER['SERVER_SOFTWARE']) && function_exists('apache_get_modules')){
							$missing_apache_modules = array_diff(array(
								'mod_expires',
								'mod_deflate',
								'mod_setenvif',
								'mod_headers',
								'mod_filter',
								'mod_rewrite',
							), apache_get_modules());
						}

						if (preg_match('~apache~i', $_SERVER['SERVER_SOFTWARE']) && function_exists('apache_get_modules')){
							if (!in_array('mod_rewrite', apache_get_modules())){
								$rewrites = false;
							}
						}

						// Check htaccess
						$htaccess = ABSPATH . '.htaccess';

						if (!file_exists($htaccess)){
							@touch($htaccess);
							if (!file_exists($htaccess)){
								$rewrites = false;
							}
						}
						else if (!is_writable($htaccess)){
							$rewrites = false;
						}

						$message = sprintf(__('%s detected. ', 'swift-performance'), ucfirst($server_software));

						if ($server_software == 'apache'){
							$message .=  ($rewrites ? __('Rewrites are working. ', 'swift-performance') : __('Rewrites are not working. ', 'swift-performance'));
							if (!empty($missing_apache_modules)){
								$result = 'warning';
								$missing_modules = (count($missing_apache_modules) > 1 ? implode(', ', $missing_apache_modules) : $missing_apache_modules[0]);
								$message .=  sprintf(
										_n(
										'Please enable %s Apache module for better optimization.',
										'Please enable the following Apache modules for better optimization: %s.',
										count($missing_apache_modules),
										'swift-performance')
									, $missing_modules);
							}
						}
						else if ($server_software === 'unkonwn'){
							$message = __('Server software was not detected.', 'swift-performance');
						}

					}

					// Set caching mode
					if ($rewrites){
						Swift_Performance::update_option('caching-mode', 'disk_cache_rewrite');
						try {
							// Generate and write htaccess rules
							$rules = Swift_Performance::build_rewrite_rules();
							Swift_Performance::write_rewrite_rules($rules);
						}
						catch (Exception $e){
							$result	= 'error';
							$message	= $e->get_error_message();
						}
					}
				break;
				case 'loopback':
					// Automated prebuild
					if (self::check_loopback()){
						Swift_Performance::update_option('automated_prebuild_cache', 1);
						Swift_Performance::update_option('optimize-prebuild-only', 1);
						Swift_Performance::update_option('merge-background-only', 0);
						$message = __('Loopback is working', 'swift-perforance');
					}
					else {
						Swift_Performance::update_option('merge-background-only', 1);
						$result = 'warning';
						$message = __('Loopback is disabled.', 'swift-performance');
					}
				break;
				case 'varnish-proxy':
					$cloudflare = false;
					$varnish = false;

					if (self::check_loopback()){
						$response = wp_remote_get(home_url(), array('timeout' => 60, 'sslverify' => false));

						// Extend the max buffer size for large pages
						$max_buffer_size = max(Swift_Performance::get_option('dom-parser-max-buffer'), strlen($response['body']));
						Swift_Performance::update_option('dom-parser-max-buffer', $max_buffer_size);

						$cf		= wp_remote_retrieve_header( $response, 'cf-cache-status' );
						$xv		= wp_remote_retrieve_header( $response, 'x-varnish' );
						$xc		= wp_remote_retrieve_header( $response, 'x-cache' );
						$iscw		= (isset($_SERVER['HTTP_X_APP_USER']) && isset($_SERVER['HTTP_X_APPLICATION']));

						if (!empty($cf)){
							Swift_Performance::update_option('cloudflare-auto-purge',1);
							$message .= __('Cloudflare cache was detected. Please set your API credentials on next screen.', 'swift-performance');
							$next_slide = 'cloudflare';
							$result = 'warning';
						}

						if (!empty($xv) || (!empty($xc) && $iscw)){
							Swift_Performance::update_option('varnish-auto-purge',1);
							Swift_Performance::update_option('optimize-prebuild-only',0);
							Swift_Performance::update_option('merge-background-only',0);
							$message .= __('Varnish was detected', 'swift-performance');
						}

						if (empty($message)){
							$message = __('No Varnish or Cloudflare cache was detected.', 'swift-performance');
						}
					}
					else{
						$result	= 'warning';
						$message	= __('Loopback is disabled, Swift can\'t check Varnish and reverse proxy. If you are using Cloudflare cache please set your API credentials on next screen.', 'swift-performance');
					}
				break;
				case 'server-level-cache':
					if (self::check_loopback()){
						$response = wp_remote_get(home_url(), array('timeout' => 60, 'sslverify' => false));

						$xc		= wp_remote_retrieve_header( $response, 'x-cache' );
						$iscw		= (isset($_SERVER['HTTP_X_APP_USER']) && isset($_SERVER['HTTP_X_APPLICATION']));
						$iswpe	= class_exists("WpeCommon");

						if (!empty($xc) && !$iscw && !$iswpe){
							$result = 'error';
							Swift_Performance::update_option('optimize-prebuild-only',0);
							Swift_Performance::update_option('merge-background-only',0);
							$message .= __('Server level cache was detected. Please disable it to avoid cache conflicts.', 'swift-performance');
						}
						else if($iswpe){
							$message .= __('WPEngine Cache was detected.', 'swift-performance');
						}

						if (empty($message)){
							$message = __('No Server level cache was detected.', 'swift-performance');
						}
					}
					else{
						$result	= 'warning';
						$message	= __('Loopback is disabled, Swift can\'t check Server level caching.', 'swift-performance');
					}
				break;
				case 'php-settings':
					// Safe Mode
					$safe_mode = ini_get('safe_mode');

					// time limit
					$set_time_limit = Swift_Performance::is_function_disabled('set_time_limit');

					// memory
					$memory = 0;
					$memory_limit = ini_get('memory_limit');
					if (preg_match('/^(\d+)(.)$/', $memory_limit, $matches)) {
					    	if (strtoupper($matches[2]) == 'G') {
  					    		$memory = $matches[1] * 1024 * 1024 * 1024; // nnnM -> nnn MB
  					    	}
					    	else if (strtoupper($matches[2]) == 'M') {
					      	$memory = $matches[1] * 1024 * 1024; // nnnM -> nnn MB
					    	}
						else if (strtoupper($matches[2]) == 'K') {
					        	$memory = $matches[1] * 1024; // nnnK -> nnn KB
					    	}
						else{
					        	$memory = $matches[1]; // nnnK -> nnn KB
					    	}
					}

					// Probably we are on limited shared hosting
					if ($safe_mode || $set_time_limit){
						Swift_Performance::update_option('critical-css', 0);
						Swift_Performance::update_option('merge-styles-exclude-3rd-party', 1);
						Swift_Performance::update_option('merge-scripts-exlude-3rd-party', 1);
						if ($safe_mode){
							$message .= __("Safe mode is enabled. ", 'swift-performance');
							$result = 'warning';
						}

						if ($set_time_limit){
							$message .= __("set_time_limit is disabled. ", 'swift-performance');
							$result = 'warning';
						}
					}

					$message .= sprintf(__("Memory: %s. ", 'swift-performance'), $memory_limit);
					// We don't have too much memory, probably the hosting is limited
					if ($memory < 83886080){
						Swift_Performance::update_option('critical-css', 0);
						$message .= __("Low memory environment detected. ", 'swift-performance');
						$result = 'warning';
					}
				break;
				case 'plugins':
					$plugin_conflicts = self::get_plugin_conflicts();
					// Deactivate plugins
					if (!empty($plugin_conflicts['hard'])){
						$network_wide = is_plugin_active_for_network(SWIFT_PERFORMANCE_PLUGIN_BASENAME);
						deactivate_plugins(array_keys($plugin_conflicts['hard']), false, $network_wide);
						$message = sprintf(_n(
							            '%s was deactivated. ',
							            'The following plugins were deactivated: %s. ',
							            count($plugin_conflicts['hard']),
							            'swift-performance'
							        ), implode(', ', $plugin_conflicts['hard']));
					}
					else {
						$message = __('No hard plugin conflict found. ', 'swift-performance');
					}

					// Soft conflicts
					// WP Touch
					if (isset($plugin_conflicts['soft']['wp-touch'])){
						Swift_Performance::update_option('mobile-support', 1);
						$excluded_useragents = (array)Swift_Performance::get_option('exclude-useragents');
						$excluded_useragents[] = '#(Mobile|Android|Silk/|Kindle|BlackBerry|Opera Mini|Opera Mobi)#';
						Swift_Performance::update_option('exclude-useragents', $excluded_useragents);

						$message .= __('WP Touch detected, caching for mobile is disabled. ', 'swift-performance');
					}

					// Autoptimize
					if (isset($plugin_conflicts['soft']['autoptimize'])){
						Swift_Performance::update_option('merge-styles', 0);
						Swift_Performance::update_option('merge-scripts', 0);
						Swift_Performance::update_option('minify-html', 0);

						$message .= __('Autoptimize detected, merge styles/scripts and minify HTML were disabled. ', 'swift-performance');
					}

				break;
				case 'configure-cache':
					$public	= get_post_types(array('publicly_queryable'=>true));
					$excluded	= array_diff(Swift_Performance::get_post_types(), array_merge(array('page'),(array)$public));

					if (!empty($excluded)){
						Swift_Performance::update_option('exclude-post-types', (array)$excluded);
					}

					// Send early success message on FPM
					if (function_exists('fastcgi_finish_request')){
						header('Content-Type: application/json');
						echo json_encode(array(
							'result'		=> $result,
							'next_slide'	=> $next_slide,
							'message'		=> __('Running in background', 'swift-performance')
						));
					}

					// Prepare warmup table
					Swift_Performance::mysql_query("TRUNCATE " . SWIFT_PERFORMANCE_TABLE_PREFIX . 'warmup');
					Swift_Performance::get_prebuild_urls();

					$message = __('Done.', 'swift-performance');
				break;
			}

			wp_send_json(array(
				'result'		=> $result,
				'next_slide'	=> $next_slide,
				'message'		=> $message
			));
		}

	}

	/**
	 * Get known plugin conflicts
	 * @return array
	 */
	public static function get_plugin_conflicts(){
		$plugin_conflicts = array();
		$active_plugins = get_option('active_plugins');
		foreach ($active_plugins as $plugin_file) {
			$source = file_get_contents(WP_PLUGIN_DIR . '/' . $plugin_file);
			// W3TC
			if (preg_match('~Plugin Name: W3 Total Cache~', $source)){
				$plugin_conflicts['hard'][$plugin_file] = 'W3 Total Cache';
			}

			// WP Supercache
			if (preg_match('~Plugin Name: WP Super Cache~', $source)){
				$plugin_conflicts['hard'][$plugin_file] = 'WP Super Cache';
			}

			// WPR
			if (preg_match('~Plugin Name: WP Rocket~', $source)){
				$plugin_conflicts['hard'][$plugin_file] = 'WP Rocket';
			}

			// WP Fastest Cache
			if (preg_match('~Plugin Name: WP Fastest Cache~', $source)){
				$plugin_conflicts['hard'][$plugin_file] = 'WP Fastest Cache';
			}

			// Autoptimize
			if (preg_match('~Plugin Name: Autoptimize~', $source)){
				$plugin_conflicts['soft']['autoptimize'] = true;
			}

			// Autoptimize
			if (preg_match('~Plugin Name: Better WordPress Minify~', $source)){
				$plugin_conflicts['hard'][$plugin_file] = 'Better WordPress Minify';
			}

			// WPtouch
			if (preg_match('~Plugin Name: WPtouch Mobile Plugin~', $source)){
				$plugin_conflicts['soft']['wp-touch'] = true;
			}

		}
		return $plugin_conflicts;
	}

	/**
	 * Check is loopback enabled
	 * @return boolean
	 */
	public static function check_loopback(){
		$response = wp_remote_get(home_url(), array('timeout' => 60, 'sslverify' => false));

		//Handle HTTP errors
		if (is_wp_error($response)) {
			$loopback = false;
		}
		else{
			if ($response['response']['code'] == 200){
				$loopback = true;
			}
			else {
				$loopback = false;
			}
		}

		return $loopback;
	}

}
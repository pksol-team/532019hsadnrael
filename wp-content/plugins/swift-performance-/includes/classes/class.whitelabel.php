<?php

class Swift_Performance_Whitelabel {

      public function __construct(){
            // Define constants
		if (!defined('SWIFT_PERFORMANCE_PLUGIN_NAME') && Swift_Performance::check_option('whitelabel-plugin-name', '', '!=')){
			define('SWIFT_PERFORMANCE_PLUGIN_NAME', Swift_Performance::get_option('whitelabel-plugin-name'));
		}

		if (!defined('SWIFT_PERFORMANCE_SLUG') && Swift_Performance::check_option('whitelabel-plugin-slug', '', '!=')){
			define('SWIFT_PERFORMANCE_SLUG', sanitize_title(Swift_Performance::get_option('whitelabel-plugin-slug')));
		}

		if (!defined('SWIFT_PERFORMANCE_CACHE_BASE_DIR') && Swift_Performance::check_option('whitelabel-cache-basedir', '', '!=')){
			define('SWIFT_PERFORMANCE_CACHE_BASE_DIR', trailingslashit(Swift_Performance::get_option('whitelabel-cache-basedir')));
		}

		if (!defined('SWIFT_PERFORMANCE_TABLE_PREFIX') && Swift_Performance::check_option('whitelabel-table-prefix', '', '!=')){
			define('SWIFT_PERFORMANCE_TABLE_PREFIX', sanitize_title(Swift_Performance::get_option('whitelabel-table-prefix')));
		}

            // Rename plugin before update
            if (isset($_GET['action']) && $_GET['action'] == 'upgrade-plugin' && isset($_GET['plugin']) && $_GET['plugin'] == SWIFT_PERFORMANCE_PLUGIN_BASENAME && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'upgrade-plugin_' . SWIFT_PERFORMANCE_PLUGIN_BASENAME) ){
                  Swift_Performance::set_option('whitelabel-plugin-name', 'Swift Performance');
                  Swift_Performance::update_plugin_header();
            }
      }

}

new Swift_Performance_Whitelabel();

?>

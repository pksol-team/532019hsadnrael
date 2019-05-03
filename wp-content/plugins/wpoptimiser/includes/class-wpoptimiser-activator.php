<?php

/**
 * Fired during plugin activation
 *
 * @link       http://wpoptimiser.com
 * @since      1.0.0
 *
 * @package    WPOptimiser
 * @subpackage WPOptimiser/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    WPOptimiser
 * @subpackage WPOptimiser/includes
 * @author     Your Name <email@example.com>
 */
class WPOptimiser_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

    $args = array( false );
    if (! wp_next_scheduled ( 'wpoptimiser_daily_event', $args)) {
        wp_schedule_event( time(), 'daily', 'wpoptimiser_daily_event', $args );
    }

    // mu-plugins doesn't exist
		if ( !file_exists( WPMU_PLUGIN_DIR ) && is_writable( dirname( WPMU_PLUGIN_DIR ) ) ) {
			wp_mkdir_p( WPMU_PLUGIN_DIR );
		}
		if ( file_exists( WPMU_PLUGIN_DIR ) && is_writable( WPMU_PLUGIN_DIR ) ) {
			file_put_contents(
				WPMU_PLUGIN_DIR . '/wpoptimiser.php',
				'<' . "?php /* Bootstrap the profiler\n* Plugin Name:       WP Optimiser\n* Plugin URI:        http://wpoptimiser.com\n* Description:       Optimise, diagnose & speed-up your site with our powerful site diagnostics & tuning tools such as: auto-detection of theme & plugin load speed, image/database optimization, site health check + more.\n* Version:           1.1.1\n* Author:            Cybertactics\n* Author URI:        http://cybertactics.net/\n*/\n\n@include_once( WP_PLUGIN_DIR . '/wpoptimiser/includes/start-site-profiler.php'); ?" . '>'
			);
		}

	}

}

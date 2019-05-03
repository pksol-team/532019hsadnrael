<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://wpoptimiser.com
 * @since      1.0.0
 *
 * @package    WPOptimiser
 * @subpackage WPOptimiser/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    WPOptimiser
 * @subpackage WPOptimiser/includes
 * @author     Your Name <email@example.com>
 */
class WPOptimiser_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
    wp_clear_scheduled_hook( 'wpoptimiser_daily_event' );

    delete_option( 'wpopti_profiler_errors' );
    delete_option( 'wpopti_profiler_options' );

    delete_transient( 'wpopti_total_ram' );
    delete_transient( 'wpopti_cpu_count' );
    delete_transient( 'wpopti_cpu_core_count' );
    delete_transient( 'wpopti__php_max_upload_size' );

    // Remove mu-plugin
		if ( file_exists( WPMU_PLUGIN_DIR . '/wpoptimiser.php' ) ) {
			if ( is_writable( WPMU_PLUGIN_DIR . '/wpoptimiser.php' ) ) {
				// If we dont have delete permission.  Empty the file out, first, then try to delete it.
				file_put_contents( WPMU_PLUGIN_DIR . '/wpoptimiser.php', '' );
				unlink( WPMU_PLUGIN_DIR . '/wpoptimiser.php' );
			}
		}
	}

}

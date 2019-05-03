<?php
if ( ! defined( 'ABSPATH' ) ) exit();

/**
* LD Notifications Update class
*/
class LD_Notifications_Update
{
	public function __construct()
	{
		add_action( 'init', array( $this, 'update_plugin_cron_shcedule' ) );
	}

	/**
	 * Update plugin cron schedule for each plugin update
	 */
	public function update_plugin_cron_shcedule()
	{
		$saved_version   = get_option( 'ld_notifications_version' );
		$current_version = LEARNDASH_NOTIFICATIONS_VERSION;

		if ( $saved_version === false || version_compare( $saved_version, $current_version, '<' ) ) {
			wp_clear_scheduled_hook( 'learndash_notifications_cron' );
			wp_clear_scheduled_hook( 'learndash_notifications_cron_hourly' );

			if( ! wp_next_scheduled( 'learndash_notifications_cron' ) ) {
				wp_schedule_event( time(), 'every_minute', 'learndash_notifications_cron' );
			}

			if( ! wp_next_scheduled( 'learndash_notifications_cron_hourly' ) ) {
				wp_schedule_event( time(), 'hourly', 'learndash_notifications_cron_hourly' );
			}

			update_option( 'ld_notifications_version', $current_version );
		}
	}
}

new LD_Notifications_Update;
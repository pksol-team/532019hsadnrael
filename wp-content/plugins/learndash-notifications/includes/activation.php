<?php

function learndash_notifications_activate() {
	if( ! wp_next_scheduled( 'learndash_notifications_cron' ) ) {
		wp_schedule_event( time(), 'every_minute', 'learndash_notifications_cron' );
	}

	if( ! wp_next_scheduled( 'learndash_notifications_cron_hourly' ) ) {
		wp_schedule_event( time(), 'hourly', 'learndash_notifications_cron_hourly' );
	}
}

register_activation_hook( LEARNDASH_NOTIFICATIONS_FILE, 'learndash_notifications_activate' );
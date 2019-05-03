<?php

function learndash_notifications_deactivate() {
	wp_clear_scheduled_hook( 'learndash_notifications_cron' );
	wp_clear_scheduled_hook( 'learndash_notifications_cron_hourly' );
}

register_deactivation_hook( LEARNDASH_NOTIFICATIONS_FILE, 'learndash_notifications_deactivate' );
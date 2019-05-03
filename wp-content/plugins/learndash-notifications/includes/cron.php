<?php

function learndash_notifications_cron() {

	$lock_file = WP_CONTENT_DIR . '/uploads/learndash/ld-notifications/process_lock.txt';
	$dirname   = dirname( $lock_file );

	if ( ! is_dir( $dirname ) ) {
		wp_mkdir_p( $dirname );
	}

	$lock_fp   = fopen( $lock_file, 'c+' );

	// Now try to get exclusive lock on the file. 
	if ( ! flock( $lock_fp, LOCK_EX | LOCK_NB ) ) { 
		// If you can't lock then abort because another process is already running
		exit(); 
	}

	// error_log( 'Cron fired by LearnDash Notifications at: ' . date( 'Y-m-d h:i:sa' ) );
	
	learndash_notifications_send_delayed_emails();
	learndash_notifications_delete_delayed_emails();
	learndash_notifications_send_enroll_course_via_group_queue();

	// error_log( 'LearnDash Notifications cron finished at: ' . date( 'Y-m-d h:i:sa' ) );
}

add_action( 'learndash_notifications_cron', 'learndash_notifications_cron' );

function learndash_notifications_cron_hourly() {
	learndash_notifications_not_logged_in();
	learndash_notifications_course_expires();
}

add_action( 'learndash_notifications_cron_hourly', 'learndash_notifications_cron_hourly' );

function learndash_notifications_cron_schedules( $schedules ) {
	$schedules['every_minute'] = array(
		'interval' => 60,
		'display'  => __( 'Every Minute' ),
	);

	return $schedules;
}

add_filter( 'cron_schedules', 'learndash_notifications_cron_schedules' );
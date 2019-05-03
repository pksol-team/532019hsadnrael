<?php
/**
 * Create necessary table
 */
function learndash_notifications_create_db_table() {
	global $wpdb;
	$current_version = '1.1';
	$db_version      = get_option( 'ld_notifications_db_version' );

	if ( $db_version === false || version_compare( $current_version, $db_version, '!=' ) === true ) {
		
		$table_name      = "{$wpdb->prefix}ld_notifications_delayed_emails";
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id int UNSIGNED NOT NULL AUTO_INCREMENT,
			title varchar(80) NOT NULL,
			message text NOT NULL,
			recipient varchar(255) NOT NULL,
			shortcode_data varchar(255),
			sent_on varchar(15) NOT NULL,
			bcc varchar(255),
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
		update_option( 'ld_notifications_db_version', $current_version );
	}

}

add_action( 'admin_init', 'learndash_notifications_create_db_table' );


/**
 * Delete delayed emails when a user is unenrolled from a course
 * 
 * @param  int 		$user_id 	ID of a user
 * @param  int 		$course_id 	ID of a course
 * @param  string 	$user_id 	Course access list
 * @param  bool 	$user_id 	True if user unenrolled|false otherwise
 */
function learndash_notifications_delete_delayed_email_by_unenrolled_course( $user_id, $course_id, $access_list, $remove )
{
	if ( $remove !== true ) {
		return;
	}

	learndash_notifications_delete_delayed_email_by_user_id_course_id( $user_id, $course_id );
}

add_action( 'learndash_update_course_access', 'learndash_notifications_delete_delayed_email_by_unenrolled_course', 10, 4 );


/**
 * Delete delayed emails when a user is deleted
 * 
 * @param  int $user_id ID of a user
 */
function learndash_notifications_delete_delayed_email_by_deleted_user( $user_id ) {
	learndash_notifications_delete_delayed_email_by_user_id( $user_id );
}

add_action( 'deleted_user', 'learndash_notifications_delete_delayed_email_by_deleted_user', 10, 2 );

/**
 * Delete delayed emails in DB by a key available in column 'shortcode_data'
 * 
 * @param  string $key   Key available in shortcode_data column
 * @param  string $value Value being searched to be deleted
 */
function learndash_notifications_delete_delayed_emails_by( $key, $value )
{
	global $wpdb;

	$emails = learndash_notifications_get_all_delayed_emails();

	foreach ( $emails as $email ) {
		$data = maybe_unserialize( $email['shortcode_data'] );

		if ( $data[ $key ] == $value ) {
			$wpdb->delete(
				"{$wpdb->prefix}ld_notifications_delayed_emails",
				array( 'id' => $email['id'] ),
				array( '%d' )
			);
		}
	}
}

/**
 * Delete delayed emails using recipient email address
 * 
 * @param  string $email_address  Email address of recipient
 */
function learndash_notifications_delete_delayed_emails_by_email( $email_address )
{
	global $wpdb;

	$emails = learndash_notifications_get_all_delayed_emails();

	foreach ( $emails as $email ) {
		$recipients = maybe_unserialize( $email['recipient'] );

		if ( in_array( $email_address, $recipients ) ) {
			$wpdb->delete(
				"{$wpdb->prefix}ld_notifications_delayed_emails",
				array( 'id' => $email['id'] ),
				array( '%d' )
			);
		}
	}
}

/**
 * Delete delayed emails using user ID
 * 
 * @param  int $user_id   ID of a user
 */
function learndash_notifications_delete_delayed_email_by_user_id( $user_id )
{
	global $wpdb;

	$emails = learndash_notifications_get_all_delayed_emails();

	foreach ( $emails as $email ) {
		$data = maybe_unserialize( $email['shortcode_data'] );

		if ( $data['user_id'] == $user_id ) {
			$wpdb->delete(
				"{$wpdb->prefix}ld_notifications_delayed_emails",
				array( 'id' => $email['id'] ),
				array( '%d' )
			);
		}
	}
}

/**
 * Delete delayed emails using user ID and course ID
 * 
 * @param  int $user_id   ID of a user
 * @param  int $course_id ID of a course
 */
function learndash_notifications_delete_delayed_email_by_user_id_course_id( $user_id, $course_id )
{
	global $wpdb;

	$emails = learndash_notifications_get_all_delayed_emails();

	foreach ( $emails as $email ) {
		$data = maybe_unserialize( $email['shortcode_data'] );

		if ( $data['user_id'] == $user_id && $data['course_id'] == $course_id ) {
			$wpdb->delete(
				"{$wpdb->prefix}ld_notifications_delayed_emails",
				array( 'id' => $email['id'] ),
				array( '%d' )
			);
		}
	}
}

function learndash_notifications_delete_delayed_emails_by_user_id_lesson_id( $user_id, $lesson_id )
{
	global $wpdb;

	$emails = learndash_notifications_get_all_delayed_emails();

	foreach ( $emails as $email ) {
		$data = maybe_unserialize( $email['shortcode_data'] );

		if ( $data['user_id'] == $user_id && $data['lesson_id'] == $lesson_id ) {
			$wpdb->delete(
				"{$wpdb->prefix}ld_notifications_delayed_emails",
				array( 'id' => $email['id'] ),
				array( '%d' )
			);
		}
	}
}

function learndash_notifications_delete_delayed_email_by_id( $id )
{
	global $wpdb;

	$wpdb->delete(
		"{$wpdb->prefix}ld_notifications_delayed_emails",
		array( 'id' => $id ),
		array( '%d' )
	);
}

/**
 * Get all delayed emails stored in database
 * 
 * @return array All delayed emails existing in database
 */
function learndash_notifications_get_all_delayed_emails() {
	global $wpdb;

	$sql = "SELECT * FROM {$wpdb->prefix}ld_notifications_delayed_emails";

	return $wpdb->get_results( $sql, ARRAY_A );
}

/**
 * Save a delayed notification to delayed email table
 * 
 * @param  object $notification 	Notification WP post object
 * @param  array  $emails 			List of recipient emails
 * @param  int    $sent_on  		UNIX timestamp when the email will be sent
 * @param  array  $shortcode_data  	List of array
 * @param  array  $bcc  			List of email addresses as bcc
 * @return bool               		True if success|false otherwise
 */
function learndash_notifications_save_delayed_email( $notification, $emails, $sent_on, $shortcode_data, $bcc = array() ) {
	$n = $notification;

	return learndash_notifications_insert_delayed_email( $n->post_title, $n->post_content, $emails, $shortcode_data, $sent_on, $bcc );
}

/**
 * Insert an email to delayed email table
 * 
 * @param  string $title 			Title of email
 * @param  string $message 			Content of email
 * @param  array  $message 			Email addresses
 * @param  array  $shortcode_data  	List of array
 * @param  int    $sent_on  		UNIX timestamp when the email will be sent
 * @param  array  $bcc  			List of email addresses as bcc
 * @return bool               		True if success|false otherwise
 */
function learndash_notifications_insert_delayed_email( $title, $message, $recipient, $shortcode_data, $sent_on, $bcc = array() )
{
	global $wpdb;

	$insert = $wpdb->insert(
		"{$wpdb->prefix}ld_notifications_delayed_emails",
		array(
			'title'          => $title,
			'message'        => $message,
			'recipient'      => maybe_serialize( $recipient ),
			'shortcode_data' => maybe_serialize( $shortcode_data ),
			'sent_on'        => $sent_on,
			'bcc'        	 => maybe_serialize( $bcc ),
		),
		array(
			'%s', '%s', '%s', '%s', '%d', '%s'
		)
	);

	if ( $insert !== false ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Delete delayed emails from DB if sent_on timestamp has passed current time
 *
 * Fired in cron.php
 */
function learndash_notifications_delete_delayed_emails()
{
	global $wpdb;
	
	$date   = date( 'Y-m-d H', time() );
	$emails = learndash_notifications_get_all_delayed_emails();

	foreach ( $emails as $e ) {

		$sent_on = date( 'Y-m-d H', $e['sent_on'] );
		
		if ( $sent_on < $date ) {
			$wpdb->delete(
				"{$wpdb->prefix}ld_notifications_delayed_emails",
				array(
					'id' => $e['id'],
				),
				array(
					'%d',
				)
			);
		}
	}
}
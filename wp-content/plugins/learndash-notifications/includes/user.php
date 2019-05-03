<?php
/**
 * Delete LearnDash related user data if LD user data is deleted
 * 
 * @param  int 	$user_id ID of a user
 */
function learndash_notifications_delete_learndash_user_data( $user_id ) {
	delete_user_meta( $user_id, '_ld_notifications_last_login' );

	$args = array(
		'post_type'      => 'sfwd-courses',
		'posts_per_page' => -1,
	);
	$courses = get_posts( $args );

	$groups = learndash_get_groups( $id_only = true );

	foreach ( $courses as $course ) {
		delete_user_meta( $user_id, 'ld_sent_notification_enroll_course_' . $course->ID );

		$lessons = learndash_get_lesson_list( $course->ID );

		foreach ( $groups as $group_id ) {
			delete_user_meta( $user_id, 'ld_sent_notification_enroll_group_course_' . $course->ID . '_' . $group_id );
		}

		foreach ( $lessons as $lesson ) {
			delete_user_meta( $user_id, 'ld_sent_notification_lesson_available_' . $lesson->ID );
		}
	}
}

add_action( 'learndash_delete_user_data', 'learndash_notifications_delete_learndash_user_data' );
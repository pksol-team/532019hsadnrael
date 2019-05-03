<?php
/****************************
 ****** UPDATE FUNCTIONS **** 
 ****************************/

/**
 * Delete sent notification records if user is unenrolled
 *
 * @param int 	$user_id 		ID of user who enroll
 * @param int 	$course_id 		ID of course enrolled into
 * @param array $access_list 	List of users who have access to the course
 * @param bool 	$remove 		True if remove user access from a course | false otherwise
 */
function learndash_notifications_delete_sent_emails_record( $user_id, $course_id, $access_list, $remove ) {
	// Exit if user is not removed from a course
	if ( $remove !== true ) return;

	// delete enrolled course trigger record
	delete_user_meta( $user_id, 'ld_sent_notification_enroll_course_' . $course_id );

	// delete lesson available trigger record
	$lessons = learndash_get_lesson_list( $course_id );
	foreach ( $lessons as $lesson ) {
		delete_user_meta( $user_id, 'ld_sent_notification_lesson_available_' . $lesson->ID );
	}
}

add_action( 'learndash_update_course_access', 'learndash_notifications_delete_sent_emails_record', 10, 4 );

/**
 * Delete delayed emails in DB when user is enrolled from a course
 *
 * @param int 	$user_id 		ID of user who enroll
 * @param int 	$course_id 		ID of course enrolled into
 * @param array $access_list 	List of users who have access to the course
 * @param bool 	$remove 		True if remove user access from a course | false otherwise
 */
function learndash_notifications_delete_delayed_emails_when_unenrolled( $user_id, $course_id, $access_list, $remove )
{
	if ( $remove === true ) {
		learndash_notifications_delete_delayed_email_by_user_id_course_id( $user_id, $course_id );
	}
}

add_action( 'learndash_update_course_access', 'learndash_notifications_delete_delayed_emails_when_unenrolled', 10, 4 );

/**
 * Check for new scheduled notification when a notification is created or updated
 * @param  int 	  $n_id    ID of the post
 * @param  object $post    WP Post object
 * @param  bool   $update  Post is being updated or not
 */
function learndash_notifications_update_lesson_available_notification_notif_hook( $n_id, $post, $update )
{
	if ( $post->post_type != 'ld-notification' ) {
		return;
	}

	$notification_type = get_post_meta( $n_id, '_ld_notifications_trigger', true );
	$delay_days        = (int) get_post_meta( $n_id, '_ld_notifications_delay', true );

	if ( $notification_type != 'lesson_available' ) {
		return;
	}

	// Get courses
	$args = array(
		'post_type'   => 'sfwd-courses',
		'post_status' => 'publish',
		'posts_per_page' => -1,
	);

	$courses = get_posts( $args );

	foreach ( $courses as $c ) {
		// Get course access user list
		$c_meta = get_post_meta( $c->ID, '_sfwd-courses', true );
		$c_meta = maybe_unserialize( $c_meta );

		// Course access list
		$c_access_list = isset( $c_meta['sfwd-courses_course_access_list'] ) ? $c_meta['sfwd-courses_course_access_list'] : array();

		// If course has no access list, continue
		if ( empty( $c_access_list ) ) continue; 

		$c_access_list = explode( ',', trim( $c_access_list ) );

		// Loop through user
		foreach ( $c_access_list as $u_id ) {
			$access_from = get_user_meta( $u_id, 'course_' . $c->ID . '_access_from', true );

			// Add or update the delayed notification emails
			// New notification
			if ( $post->post_modified_gmt == $post->post_date_gmt ) {
				$n_lesson_id = get_post_meta( $n_id, '_ld_notifications_lesson_id', true );

				if ( isset( $n_lesson_id ) && is_numeric( $n_lesson_id ) ) {

					// Save new email
					$n_course_id = learndash_get_course_id( $n_lesson_id );
					$course_access_from = ld_course_access_from( $n_course_id, $u_id);
					$lesson_access_from = ld_lesson_access_from( $n_lesson_id, $u_id );

					if ( ! is_null( $lesson_access_from ) ) {
						// Exit if notification already sent
						$sent = get_user_meta( $u_id, 'ld_sent_notification_lesson_available_' . $n_lesson_id, true );

						if ( $sent == 1 ) {
							continue;
						}

						learndash_notifications_send_notification( $post, $u_id, $c->ID, $n_lesson_id, null, null, null, $lesson_access_from );

						add_user_meta( $u_id, 'ld_sent_notification_lesson_available_' . $n_lesson_id, 1, true );
					}

					// return;
				}
				else
				{
					$lessons = learndash_get_lesson_list( $c->ID );

					foreach ( $lessons as $lesson ) {
						// Save new email
						$course_access_from = ld_course_access_from( $c->ID, $u_id);
						$lesson_access_from = ld_lesson_access_from( $lesson->ID, $u_id );

						if ( ! is_null( $lesson_access_from ) ) {
							// Exit if notification already sent
							$sent = get_user_meta( $u_id, 'ld_sent_notification_lesson_available_' . $lesson->ID, true );

							// if ( $sent == 1 ) {
							// 	continue;
							// }

							learndash_notifications_send_notification( $post, $u_id, $c->ID, $lesson->ID, null, null, null, $lesson_access_from );

							add_user_meta( $u_id, 'ld_sent_notification_lesson_available_' . $lesson->ID, 1, true );
						}
					}
				}
			} else {
				// Get delayed emails
				$emails = learndash_notifications_get_all_delayed_emails();

				// Loop through the emails
				foreach ( $emails as $email ) {
					$data = maybe_unserialize( $email['shortcode_data'] );

					$n_lesson_id = get_post_meta( $n_id, '_ld_notifications_lesson_id', true );

					if ( isset( $n_lesson_id ) && is_numeric( $n_lesson_id ) ) {
						
						if ( isset( $data['user_id'] ) && $data['user_id'] == $u_id && isset( $data['lesson_id'] ) && $data['lesson_id'] == $n_lesson_id ) {
							// Remove the email
							learndash_notifications_delete_delayed_email_by_id( $email['id'] );

							delete_user_meta( $u_id, 'ld_sent_notification_lesson_available_' . $n_lesson_id );
						}

						// Save new email
						$n_course_id = learndash_get_course_id( $n_lesson_id );
						$course_access_from = ld_course_access_from( $n_course_id, $u_id);
						$lesson_access_from = ld_lesson_access_from( $n_lesson_id, $u_id );

						if ( ! is_null( $lesson_access_from ) ) {
							// Exit if notification already sent
							$sent = get_user_meta( $u_id, 'ld_sent_notification_lesson_available_' . $n_lesson_id, true );

							if ( $sent == 1 ) {
								continue;
							}

							learndash_notifications_send_notification( $post, $u_id, $c->ID, $n_lesson_id, null, null, null, $lesson_access_from );

							add_user_meta( $u_id, 'ld_sent_notification_lesson_available_' . $n_lesson_id, 1, true );
						}

						// return;
					}
					else
					{
						$lessons = learndash_get_lesson_list( $c->ID );
					
						foreach ( $lessons as $lesson ) {

							if ( isset( $data['user_id'] ) && $data['user_id'] == $u_id && isset( $data['lesson_id'] ) && $data['lesson_id'] == $lesson->ID ) {
								// Remove the email
								learndash_notifications_delete_delayed_email_by_id( $email['id'] );

								delete_user_meta( $u_id, 'ld_sent_notification_lesson_available_' . $lesson->ID );
							}

							// Save new email
							$course_access_from = ld_course_access_from( $c->ID, $u_id);
							$lesson_access_from = ld_lesson_access_from( $lesson->ID, $u_id );

							if ( ! is_null( $lesson_access_from ) ) {
								// Exit if notification already sent
								$sent = get_user_meta( $u_id, 'ld_sent_notification_lesson_available_' . $lesson->ID, true );

								if ( $sent == 1 ) {
									continue;
								}

								learndash_notifications_send_notification( $post, $u_id, $c->ID, $lesson->ID, null, null, null, $lesson_access_from );

								add_user_meta( $u_id, 'ld_sent_notification_lesson_available_' . $lesson->ID, 1, true );
							}
						}
					}
				}
			}

		}
	}
}

add_action( 'save_post', 'learndash_notifications_update_lesson_available_notification_notif_hook', 99, 3 );

/**
 * Update lesson available notifications when a lesson is created or updated
 * @param  int 	  $lesson_id Lesson ID
 * @param  object $post      Lesson WP Post object
 * @param  bool   $update    Update post or not
 */
function learndash_notifications_update_lesson_available_notification_lesson_hook( $lesson_id, $post, $update )
{
	if ( $post->post_type != 'sfwd-lessons' ) {
		return;
	}

	$course_id = learndash_get_course_id( $lesson_id );

	$c_meta = get_post_meta( $course_id, '_sfwd-courses', true );
	$c_meta = maybe_unserialize( $c_meta );

	// Course access list
	$c_access_list = isset( $c_meta['sfwd-courses_course_access_list'] ) ? $c_meta['sfwd-courses_course_access_list'] : '';

	// If course has no access list, continue
	if ( empty( $c_access_list ) ) return;; 

	$c_access_list = explode( ',', trim( $c_access_list ) );

	// Loop through user
	foreach ( $c_access_list as $user_id ) {
		learndash_notifications_delete_delayed_emails_by_user_id_lesson_id( $user_id, $lesson_id );

		delete_user_meta( $user_id, 'ld_sent_notification_lesson_available_' . $lesson_id );

		$lesson_access_from = ld_lesson_access_from( $lesson_id, $user_id );

		if ( ! is_null( $lesson_access_from ) ) {
			// Exit if notification already sent
			$sent = get_user_meta( $user_id, 'ld_sent_notification_lesson_available_' . $lesson_id, true );
			if ( $sent == 1 ) {
				continue;
			}

			learndash_notifications_send_notifications( 'lesson_available', $user_id, $course_id, $lesson_id, null, null, null, $lesson_access_from );

			add_user_meta( $user_id, 'ld_sent_notification_lesson_available_' . $lesson_id, 1, true );
		}
	}
}

add_action( 'save_post', 'learndash_notifications_update_lesson_available_notification_lesson_hook', 99, 3 );

/**
 * Delete delayed emails stored in DB if Notification, Course, Lesson, etc is deleted
 * 
 * @param  int 	$post_id  WP Post ID
 */
function learndash_notifications_delete_delayed_emails_when_post_deleted( $post_id )
{
	$post      = get_post( $post_id );
	$post_type = $post->post_type;

	if ( $post_type != 'ld-notification' && $post_type != 'sfwd-courses' && $post_type != 'sfwd-lessons' && $post_type != 'sfwd-topic' && $post_type != 'sfwd-quiz' && $post_type != 'sfwd-assignment' ) {
		return;
	}

	switch ( $post_type ) {
		case 'ld-notification':
			learndash_notifications_delete_delayed_emails_by( 'notification_id', $post->ID ); 
			break;
		
		case 'sfwd-courses':
			learndash_notifications_delete_delayed_emails_by( 'course_id', $post->ID );
			break;

		case 'sfwd-lessons':
			learndash_notifications_delete_delayed_emails_by( 'lesson_id', $post->ID );
			break;

		case 'sfwd-topic':
			learndash_notifications_delete_delayed_emails_by( 'topic_id', $post->ID );
			break;

		case 'sfwd-quiz':
			learndash_notifications_delete_delayed_emails_by( 'quiz_id', $post->ID );
			break;

		case 'sfwd-assignment':
			learndash_notifications_delete_delayed_emails_by( 'assignment_id', $post->ID );
			break;		
	}
}

add_action( 'wp_trash_post', 'learndash_notifications_delete_delayed_emails_when_post_deleted', 10, 1 );
add_action( 'before_delete_post', 'learndash_notifications_delete_delayed_emails_when_post_deleted', 10, 1 );

/**
 * Update delayed emails in DB if user details are updated
 * 
 * @since 1.0.8
 */
function learndash_notifications_update_delayed_emails_when_user_updated( $user_id, $old_user_data )
{
	$user      = get_user_by( 'id', $user_id );
	$old_email = $old_user_data->user_email;

	if ( $user->user_email == $old_email ) {
		return;
	}

	// Get all emails first before deleted
	$emails = learndash_notifications_get_all_delayed_emails();

	// Delete all delayed emails with old email as recipient
	learndash_notifications_delete_delayed_emails_by_email( $old_email );

	foreach ( $emails as $email ) {

		$recipient = maybe_unserialize( $email['recipient'] );

		if ( $key = array_search( $old_email, $recipient ) !== false ) {
			$recipient = array_splice( $recipient, $key, 1 );
		}

		// Add new email to recipient array
		$recipient[] = $user->user_email;

		// Insert new delayed email with new recipient
		learndash_notifications_insert_delayed_email( $email['title'], $email['message'], $recipient, maybe_unserialize( $email['shortcode_data'] ), $email['sent_on'], maybe_unserialize( $email['bcc'] ) );
	}
}

add_action( 'profile_update', 'learndash_notifications_update_delayed_emails_when_user_updated', 10, 2 );

/***************************
 *** NOTIFICATION TRIGGER **
 ***************************/

////// GROUP ENROLLMENT ///////
function learndash_notifications_enroll_group( $user_id, $group_id ) {
	learndash_notifications_send_notifications( 'enroll_group', $user_id, $course_id = null, $lesson_id = null, $topic_id = null, $quiz_id = null, $assignment_id = null, $lesson_access_from = null, $question_id = null, $group_id );
}

add_action( 'ld_added_group_access', 'learndash_notifications_enroll_group', 10, 2 );

/**
 * Send learndash notification email when user enrolls into a course
 *
 * @param int 	$user_id 		ID of user who enroll
 * @param int 	$course_id 		ID of course enrolled into
 * @param array $access_list 	List of users who have access to the course
 * @param bool 	$remove 		True if remove user access from a course | false otherwise
 */
function learndash_notifications_enroll_course( $user_id, $course_id, $access_list, $remove ) {
	// Exit if user removed from a course
	if ( $remove === true ) return;

	// Exit if notification already sent
	$sent = get_user_meta( $user_id, 'ld_sent_notification_enroll_course_' . $course_id, true );
	if ( $sent == 1 ) {
		return;
	}

	learndash_notifications_send_notifications( 'enroll_course', $user_id, $course_id );

	add_user_meta( $user_id, 'ld_sent_notification_enroll_course_' . $course_id, 1, true );
}

add_action( 'learndash_update_course_access', 'learndash_notifications_enroll_course', 10, 4 );

/**
 * Queue course enrollment trigger in cron for user added via group
 * 
 * @param  int 	 	$group_id      Post ID of a group
 * @param  array 	$group_leaders Array of post ID of the object
 * @param  array 	$group_users   Array of post ID of the object
 * @param  array 	$group_courses Array of post ID of the object
 */
function learndash_notifications_enroll_course_via_group( $group_id, $group_leaders, $group_users, $group_courses )
{
	$queue = get_option( '_ld_notifications_enroll_group_queue', array() );
	$queue[ $group_id ] = array(
		'users'   => $group_users,
		'courses' => $group_courses,
	);

	update_option( '_ld_notifications_enroll_group_queue', $queue );
}

add_action( 'ld_group_postdata_updated', 'learndash_notifications_enroll_course_via_group', 10, 4 );

/**
 * Remove DB record for sent group course enrollment notifications
 * @param  int 	$user_id  ID of a user
 * @param  int 	$group_id ID of a group
 */
function learndash_notification_delete_sent_group_emails_record_by_user( $user_id, $group_id )
{
	$courses = learndash_group_enrolled_courses( $group_id );
	foreach ( $courses as $course_id ) {
		delete_user_meta( $user_id, 'ld_sent_notification_enroll_course_' . $course_id );
	}
}

add_action( 'ld_removed_group_access', 'learndash_notification_delete_sent_group_emails_record_by_user', 10, 2 );

/**
 * Remove DB record for sent group course enrollment notifications
 * @param  int 	$course_id  ID of a course
 * @param  int 	$group_id 	ID of a group
 */
function learndash_notification_delete_sent_group_emails_record_by_course( $course_id, $group_id )
{	
	$users = learndash_get_groups_user_ids( $group_id );
	foreach ( $users as $user_id ) {
		delete_user_meta( $user_id, 'ld_sent_notification_enroll_course_' . $course_id );
	}
}

add_action( 'ld_removed_course_group_access', 'learndash_notification_delete_sent_group_emails_record_by_course', 10, 2 );

/**
 * Send learndash notification email when user completes a course
 *
 * @param array $data Course data with keys: 'user' (user object), 'course' (post object), 
 *                    'progress' (array)
 */
function learndash_notifications_complete_course( $data ) {
	$course_progress_old = get_user_meta( $data['user']->ID, '_sfwd-course_progress', true );
	$course_id = $data['course']->ID;

	// Exit if user already has completed the course
	if ( isset( $course_progress_old[ $course_id ]['total'] ) && isset( $course_progress_old[ $course_id ]['completed'] ) && $course_progress_old[ $course_id ]['total'] == $course_progress_old[ $course_id ]['completed'] ) {
		return;
	}

	learndash_notifications_send_notifications( 'complete_course', $data['user']->ID, $data['course']->ID );
}
// Let learndash_course_completed_store_time() fired first
add_action( 'learndash_before_course_completed', 'learndash_notifications_complete_course', 15, 1 );

/**
 * Send learndash notification email when user completes a lesson
 *
 * @param array $data Lesson data with array keys: 'user' (int), 'course' (post object), 
 *                    'lesson' (post object), 'progress' (array)
 */
function learndash_notifications_complete_lesson( $data ) {
	// Exit if user already has completed the course
	// if ( learndash_course_completed( $data['user']->ID, $data['course']->ID ) ) {
	// 	return;
	// }

	learndash_notifications_send_notifications( 'complete_lesson', $data['user']->ID, $data['course']->ID, $data['lesson']->ID );
}

add_action( 'learndash_lesson_completed', 'learndash_notifications_complete_lesson', 10, 1 );

/**
 * Send learndash notification email when a scheduled lesson is available to user
 *
 * @param int 	$user_id 		ID of user who enroll
 * @param int 	$course_id 		ID of course enrolled into
 * @param array $access_list 	List of users who have access to the course
 * @param bool 	$remove 		True if remove user access from a course | false otherwise
 */
function learndash_notifications_lesson_available( $user_id, $course_id, $access_list, $remove ) {
	// Exit if user removed from a course
	if ( $remove === true ) return;

	// Exit if user already has completed the course
	if ( learndash_course_completed( $user_id, $course_id ) ) {
		return;
	}

	$lessons = learndash_get_lesson_list( $course_id );
	foreach ( $lessons as $lesson ) {
		$lesson_access_from = ld_lesson_access_from( $lesson->ID, $user_id );
		if ( ! is_null( $lesson_access_from ) ) {
			// Exit if notification already sent
			$sent = get_user_meta( $user_id, 'ld_sent_notification_lesson_available_' . $lesson->ID, true );
			if ( $sent == 1 ) {
				continue;
			}

			learndash_notifications_send_notifications( 'lesson_available', $user_id, $course_id, $lesson->ID, null, null, null, $lesson_access_from );

			add_user_meta( $user_id, 'ld_sent_notification_lesson_available_' . $lesson->ID, 1, true );
		}
	}
}

add_action( 'learndash_update_course_access', 'learndash_notifications_lesson_available', 10, 4 );

/**
 * Send learndash notification email when user completes a topic
 *
 * @param array $data Topic data with array keys: 'user' (int), 'course' (post object), 
 *                    'lesson' (post object), 'topic' (post object), 'progress' (array)
 */
function learndash_notifications_complete_topic( $data ) {
	// Exit if user already has completed the course
	if ( learndash_course_completed( $data['user']->ID, $data['course']->ID ) ) {
		return;
	}

	learndash_notifications_send_notifications( 'complete_topic', $data['user']->ID, $data['course']->ID, $data['lesson']->ID, $data['topic']->ID );
}

add_action( 'learndash_topic_completed', 'learndash_notifications_complete_topic', 10, 1 );


/*************************************
 ********** QUIZ TRIGGERS ************
 *************************************/

/**
 * Send learndash notification email when user passes a quiz
 *
 * @param array 	$quiz_data 		Data of the quiz taken
 * @param object 	$current_user 	Current user WP object who take the quiz
 */
function learndash_notifications_pass_quiz( $quiz_data, $current_user ) {
	// Exit if user already has completed the course
	if ( learndash_course_completed( $current_user->ID, $quiz_data['course']->ID ) ) {
		return;
	}

	if ( $quiz_data['has_graded'] ) {
		foreach ( $quiz_data['graded'] as $id => $essay ) {
			if ( $essay['status'] == 'not_graded' ) {
				return;
			}
		}
	}

	// If user passes the quiz
	if ( $quiz_data['pass'] == 1 ) {
		learndash_notifications_send_notifications( 'pass_quiz', $current_user->ID, $quiz_data['course']->ID, null, null, $quiz_data['quiz']->ID );
	}

}

add_action( 'learndash_quiz_completed', 'learndash_notifications_pass_quiz', 10, 2 );

/**
 * Send learndash notification email when user fail a quiz
 *
 * @param array 	$quiz_data 		Data of the quiz taken
 * @param object 	$current_user 	Current user WP object who take the quiz
 */
function learndash_notifications_fail_quiz( $quiz_data, $current_user ) {
	// Exit if user already has completed the course
	if ( learndash_course_completed( $current_user->ID, $quiz_data['course']->ID ) ) {
		return;
	}

	if ( $quiz_data['has_graded'] ) {
		foreach ( $quiz_data['graded'] as $id => $essay ) {
			if ( $essay['status'] == 'not_graded' ) {
				return;
			}
		}
	}

	// If user fails the quiz
	if ( $quiz_data['pass'] == 0 ) {
		learndash_notifications_send_notifications( 'fail_quiz', $current_user->ID, $quiz_data['course']->ID, null, null, $quiz_data['quiz']->ID );
	}

}

add_action( 'learndash_quiz_completed', 'learndash_notifications_fail_quiz', 10, 2 );

/**
 * Send learndash notification email when user completes a quiz
 *
 * @param array 	$quiz_data 		Data of the quiz taken
 * @param object 	$current_user 	Current user WP object who take the quiz
 */
function learndash_notifications_complete_quiz( $quiz_data, $current_user ) {
	// Exit if user already has completed the course
	if ( learndash_course_completed( $current_user->ID, $quiz_data['course']->ID ) ) {
		return;
	}

	learndash_notifications_send_notifications( 'complete_quiz', $current_user->ID, $quiz_data['course']->ID, null, null, $quiz_data['quiz']->ID );
}

add_action( 'learndash_quiz_completed', 'learndash_notifications_complete_quiz', 10, 2 );

/**
 * Send learndash notification email when essay question is graded
 * 
 * @param  int $quiz_id         	Quiz ID
 * @param  int $question_id     	Question ID
 * @param  object $updated_scoring	Essay object
 * @param  object $essay  			Submitted essay object
 */
function learndash_notifications_essay_graded( $quiz_id, $question_id, $updated_scoring, $essay ) {
	// If essay has been graded
	if ( $essay->post_status == 'graded' ) {
		$user_id   = $essay->post_author;
		$real_quiz_id = learndash_get_quiz_id_by_pro_quiz_id( $quiz_id );		
		$course_id = learndash_get_course_id( $real_quiz_id );
		$lesson_id = learndash_get_lesson_id( $real_quiz_id );

		// Exit if user already has completed the course
		if ( learndash_course_completed( $user_id, $course_id ) ) {
			return;
		}

		learndash_notifications_send_notifications( 'essay_graded', $user_id, $course_id, $lesson_id, $topic_id = null, $real_quiz_id, $assignment_id = null, $lesson_access_from = null, $question_id );

		$users_quiz_data = get_user_meta( $essay->post_author, '_sfwd-quizzes', true );
	
		foreach ( $users_quiz_data as $quiz_key => $quiz_data ) {
			if ( $quiz_id == $quiz_data['pro_quizid'] ) {
				if ( $quiz_data['has_graded'] ) {
					foreach ( $quiz_data['graded'] as $id => $essay ) {
						if ( $essay['status'] == 'not_graded' ) {
							return;
						}
					}
				}
				
				if ( $quiz_data['pass'] == 1 ) {
					learndash_notifications_send_notifications( 'pass_quiz', $user_id, $course_id, null, null, $real_quiz_id );
				} elseif ( $quiz_data['pass'] == 0 ) {
					learndash_notifications_send_notifications( 'fail_quiz', $user_id, $course_id, null, null, $real_quiz_id );
				}
			}
		}

	}


}

add_action( 'learndash_essay_all_quiz_data_updated', 'learndash_notifications_essay_graded', 10, 4 );

/**
 * Send learndash notification email when user upload an assignment
 *
 * @param int 	$assignment_id 		ID of assignment post object
 * @param array $assignment_meta 	Meta data of the assignment
 */
function learndash_notifications_upload_assignment( $assignment_id, $assignment_meta ) {
	// Exit if user already has completed the course
	if ( learndash_course_completed( $assignment_meta['user_id'], $assignment_meta['course_id'] ) ) {
		return;
	}

	learndash_notifications_send_notifications( 'upload_assignment', $assignment_meta['user_id'], $assignment_meta['course_id'], $assignment_meta['lesson_id'], null, null, $assignment_id );
}

add_action( 'learndash_assignment_uploaded', 'learndash_notifications_upload_assignment', 10, 2 );

/**
 * Send learndash notification email when admin approves an assignment
 *
 * @param int $assignment_id ID of assignment post object
 */
function learndash_notifications_approve_assignment( $assignment_id ) {
	$user_id   = get_post_meta( $assignment_id, 'user_id', true );
	$course_id = get_post_meta( $assignment_id, 'course_id', true );
	$lesson_id = get_post_meta( $assignment_id, 'lesson_id', true );

	// Exit if user already has completed the course
	if ( learndash_course_completed( $user_id, $course_id ) ) {
		return;
	}

	learndash_notifications_send_notifications( 'approve_assignment', $user_id, $course_id, $lesson_id, null, null, $assignment_id );
}

add_action( 'learndash_assignment_approved', 'learndash_notifications_approve_assignment', 10, 1 );

/**********************
 *** CRON FUNCTIONS ***
 **********************/

/**
 * Send learndash notification email when user hasn't logged in for X days
 */
function learndash_notifications_not_logged_in() {
	// Fired in cron.php
	$notifications = learndash_notifications_get_notifications( 'not_logged_in' );

	foreach ( $notifications as $n ) {
		$n_days = get_post_meta( $n->ID, '_ld_notifications_not_logged_in_days', true );

		if ( ! ( $n_days > 0 ) ) continue;

		$course_id  = get_post_meta( $n->ID, '_ld_notifications_course_id', true );
		$recipients = learndash_notifications_get_recipients( $n->ID );

		$roles = array();
		foreach ( $recipients as $r ) {
			switch ( $r ) {
				case 'user':
					$roles[] = 'subscriber';
					$roles[] = 'customer';
					break;
				
				case 'group_leader':
					$roles[] = 'group_leader';
					break;

				case 'admin':
					$roles[] = 'administrator';
					break;
			}
		}

		$users = get_users( array(
			'role__in' => $roles,
		) );

		foreach ( $users as $u ) {

			$last_login = get_user_meta( $u->ID, '_ld_notifications_last_login', true );
			
			if ( empty( $last_login ) || ! isset( $last_login ) ) continue;

			if ( isset( $course_id ) && is_numeric( $course_id ) && $course_id > 0 ) {
				
				// Exit if user already has completed the course
				if ( learndash_course_completed( $u->ID, $course_id ) ) {
					continue;
				}

				if ( date( 'Y-m-d H' ) == date( 'Y-m-d H', strtotime( '+' . $n_days . ' days', $last_login ) ) ) {
					learndash_notifications_send_notification( $n, $u->ID, $course_id );
				}

			} else {

				$courses = ld_get_mycourses( $u->ID );

				foreach ( $courses as $course_id ) {
					// Exit if user already has completed the course
					if ( learndash_course_completed( $u->ID, $course_id ) ) {
						continue;
					}

					if ( date( 'Y-m-d H' ) == date( 'Y-m-d H', strtotime( '+' . $n_days . ' days', $last_login ) ) ) {
						learndash_notifications_send_notification( $n, $u->ID, $course_id );
					}
				}
			}
		}
	}
}

/**
 * Set user last login time
 * 
 * @param  string $user_login User's username to log in
 * @param  object $user       WP_User object
 */
function learndash_notifications_set_last_login() {
	if ( ! is_user_logged_in() ) {
		return;
	} 

	$user_id = get_current_user_id();

	update_user_meta( $user_id, '_ld_notifications_last_login', time() );
}

add_action( 'init', 'learndash_notifications_set_last_login', 10, 2 );

/**
 * Send learndash notification email when user's course is about to expire in X days
 */
function learndash_notifications_course_expires() {
	// Fired in cron.php
	// Get all courses
	$args = array(
		'post_type'   => 'sfwd-courses',
		'post_status' => 'publish',
		'posts_per_page' => -1,
	);
	$courses = get_posts( $args );

	// Get all notifications
	$notifications = learndash_notifications_get_notifications( 'course_expires' );

	// Foreach courses
	foreach ( $courses as $c ) {
		$c_meta = get_post_meta( $c->ID, '_sfwd-courses', true );
		$c_meta = maybe_unserialize( $c_meta );

		// If course doesn't has expiration setting, continue
		if ( ( ! isset( $c_meta['sfwd-courses_expire_access'] ) || ( isset( $c_meta['sfwd-courses_expire_access'] ) && $c_meta['sfwd-courses_expire_access'] != 'on' ) ) 
			|| 
			( ! isset( $c_meta['sfwd-courses_expire_access_days'] ) || ( isset( $c_meta['sfwd-courses_expire_access_days'] ) && $c_meta['sfwd-courses_expire_access_days'] == 0 ) ) ) {
			continue;
		}

		// Course access list
		$c_access_list = isset( $c_meta['sfwd-courses_course_access_list'] ) ? $c_meta['sfwd-courses_course_access_list'] : '';

		// If course has no access list, continue
		if ( empty( $c_access_list ) ) continue; 

		$c_access_list = explode( ',', trim( $c_access_list ) );
		$c_access_days = (int) $c_meta['sfwd-courses_expire_access_days'];

		// Foreach users who have access
		foreach ( $c_access_list as $u_id ) {
			$access_from = get_user_meta( $u_id, 'course_' . $c->ID . '_access_from', true );

			// Foreach notifications
			foreach ( $notifications as $n ) {
				$n_days = get_post_meta( $n->ID, '_ld_notifications_course_expires_days', true );
				
				// If users' course access is equal to setting, send notifications
				if ( date( 'Y-m-d H' ) == date( 'Y-m-d H', strtotime( '-' . $n_days . ' days', strtotime( '+' . $c_access_days . ' days', $access_from ) ) ) ) {
					learndash_notifications_send_notification( $n, $u_id, $c->ID );
				}
			}
		}
	}
}

function learndash_notifications_send_delayed_emails() {
	global $wpdb;
	$date = date( 'Y-m-d H', time() );

	$emails = $wpdb->get_results(
		"SELECT * FROM {$wpdb->prefix}ld_notifications_delayed_emails", ARRAY_A
	);

	foreach ( $emails as $e ) {

		$sent_on = date( 'Y-m-d H', $e['sent_on'] );
		
		if ( $sent_on != $date ) {
			continue;
		}

		$e['shortcode_data'] = unserialize( $e['shortcode_data'] );

		global $ld_notifications_shortcode_data;
		$ld_notifications_shortcode_data = array(
			'user_id'       => $e['shortcode_data']['user_id'],
			'course_id'     => $e['shortcode_data']['course_id'],
			'lesson_id'     => $e['shortcode_data']['lesson_id'],
			'topic_id'      => $e['shortcode_data']['topic_id'],
			'assignment_id' => $e['shortcode_data']['assignment_id'],
			'quiz_id'       => $e['shortcode_data']['quiz_id'],
		);

		$e['recipient'] = unserialize( $e['recipient'] );
		$bcc = isset( $e['bcc'] ) ? unserialize( $e['bcc'] ) : array();

		$send = learndash_notifications_send_email( $e['recipient'], $e['title'], $e['message'], $bcc );

		// Delete record after delivery is succesful
		if ( $send === true ) {
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

function learndash_notifications_send_enroll_course_via_group_queue() {
	$queue = get_option( '_ld_notifications_enroll_group_queue', array() );

	foreach ( $queue as $group_id => $data ) {
		foreach ( $data['users'] as $user_id ) {
			foreach ( $data['courses'] as $course_id ) {
				// Exit if notification already sent
				$sent = get_user_meta( $user_id, 'ld_sent_notification_enroll_course_' . $course_id, true );
				if ( $sent == 1 ) {
					continue;
				}

				learndash_notifications_send_notifications( 'enroll_course', $user_id, $course_id );

				add_user_meta( $user_id, 'ld_sent_notification_enroll_course_' . $course_id, 1, true );
			}
		}

		unset( $queue[ $group_id ] );
	}

	update_option( '_ld_notifications_enroll_group_queue', $queue );
}

/**
 * Send notification when a comment is left on an assignment
 * 
 * @param  int 	  $id       Comment ID
 * @param  string $approved 1 = approved|0 = not approved|spam = SPAM
 * @param  array  $data     Comment data
 */
function learndash_notifications_assignment_essay_comment_left( $id, $approved, $data ) {
	if ( $approved != '1' ) return;

	$post = get_post( $data['comment_post_ID'] );
	if ( $post->post_type != 'sfwd-assignment' && $post->post_type != 'sfwd-essays' ) {
		return;	
	}

	if ( $post->post_type == 'sfwd-assignment' ) {
		$title = __( 'Assignment', 'learndash-notifications' );
	} else {
		$title = __( 'Essay', 'learndash-notifications' );
	}

	$comment  = get_comment( $id );
	$user     = get_user_by( 'ID', $data['user_id'] );
	$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	$comment_author_domain = @gethostbyaddr( $comment->comment_author_IP );
	$comment_content = wp_specialchars_decode( $comment->comment_content );
	$wp_email = 'wordpress@' . preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME']));

	$notify_message  = sprintf( __( 'New comment on assignment "%s"' ), $post->post_title ) . "\r\n";
	/* translators: 1: comment author, 2: author IP, 3: author domain */
	$notify_message .= sprintf( __( 'Author: %1$s (IP: %2$s, %3$s)' ), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "\r\n";
	$notify_message .= sprintf( __( 'Email: %s' ), $comment->comment_author_email ) . "\r\n";
	$notify_message .= sprintf( __('Comment: %s' ), "\r\n" . $comment_content ) . "\r\n\r\n";
	$notify_message .= __( 'You can see all comments on this assignment here:' ) . "\r\n";
	$notify_message .= get_permalink( $comment->comment_post_ID ) . "#comments\r\n\r\n";
	$notify_message .= sprintf( __( 'Permalink: %s' ), get_comment_link( $comment ) ) . "\r\n";

	/* translators: 1: title, 2: post title */
	$subject = sprintf( __( '%1$s Comment: "%2$s"'), $title, $post->post_title );

	if ( '' == $comment->comment_author ) {
		$from = "From: \"$blogname\" <$wp_email>";
		if ( '' != $comment->comment_author_email )
			$reply_to = "Reply-To: $comment->comment_author_email";
	} else {
		$from = "From: \"$comment->comment_author\" <$wp_email>";
		if ( '' != $comment->comment_author_email )
			$reply_to = "Reply-To: \"$comment->comment_author_email\" <$comment->comment_author_email>";
	}

	$headers = "$from\n"
		. "Content-Type: text/plain; charset=\"" . get_option( 'blog_charset' ) . "\"\n";

	if ( isset( $reply_to ) ) {
		$headers .= $reply_to . "\n";		
	}

	if ( in_array( 'administrator', $user->roles ) || ( in_array( 'group_leader', $user->roles ) && learndash_is_group_leader_of_user( $user->ID, $post->post_author ) ) ) {
		$recipients = array( 'user' );
		$emails = learndash_notifications_get_recipients_emails( $recipients, $post->post_author );
		if ( ! empty( $emails ) ) {
			wp_mail( $emails, $subject, $notify_message, $headers );
		}
	} else {
		$recipients = array( 'administrator', 'group_leader' );
		$emails = learndash_notifications_get_recipients_emails( $recipients, $post->post_author );
		if ( ! empty( $emails ) ) {
			wp_mail( $emails, $subject, $notify_message, $headers );
		}
	}
}

add_action( 'comment_post', 'learndash_notifications_assignment_essay_comment_left', 10, 3 );

/************************************
 **** HELPERS AND SEND FUNCTIONS ****
 ************************************/

/**
 * Get notifications
 * 
 * @param  string $notification_type Notification trigger type
 * @return Array                     Notifications posts object
 */
function learndash_notifications_get_notifications( $notification_type ) {
	$args = array(
		'meta_key'    => '_ld_notifications_trigger',
		'meta_value'  => $notification_type,
		'post_type'   => 'ld-notification',
		'post_status' => 'publish',
		'posts_per_page' => -1,
	);

	$notifications = get_posts( $args );

	return $notifications;
}

/**
 * Get recipient from notification
 * 
 * @param  int 		$notification_id Post ID of a notification
 * @return array                  	 List of recipient (user, group_leader, administrator)
 */
function learndash_notifications_get_recipients( $notification_id ) {
	$recipients = get_post_meta( $notification_id, '_ld_notifications_recipient', true );
	$recipients = maybe_unserialize( $recipients );

	return $recipients;
}

/**
 * Get recipients emails
 * 
 * @param  array $recipients List of recipients
 * @param  int $user_id    	 ID of a user. Default is null
 * @return array             List of email addresses
 */
function learndash_notifications_get_recipients_emails( $recipients, $user_id = null ) {
	$emails = array();
	foreach ( $recipients as $r ) {

		switch ( $r ) {
			case 'user':
				$user = get_user_by( 'ID', $user_id );
				$emails[] = $user->user_email;

				break;
			
			case 'group_leader':
				$args = array(
					'role' => 'group_leader',
				);
				$users = get_users( $args );

				foreach ( $users as $u ) {
					if ( ! learndash_is_group_leader_of_user( $u->ID, $user_id ) ) continue;

					$emails[] = $u->user_email;
				}

				break;

			case 'admin':
				$args = array(
					'role' => 'administrator',
				);
				$users = get_users( $args );

				foreach ( $users as $u ) {
					$emails[] = $u->user_email;
				}

				break;
		}
	}

	return $emails;
}

/**
 * Send all learndash notifications
 * 
 * @param  string 	$notification_type Notification type/trigger set for the notification
 * @param  int 		$user_id           ID of a user
 * @param  int 		$course_id         ID of a course
 * @param  int 		$lesson_id         ID of a lesson
 * @param  int 		$topic_id          ID of a topic
 * @param  int 		$quiz_id           ID of a quiz
 * @param  int 		$assignment_id     ID of a assignment
 * @param  int 		$lesson_access_from Timestamp (only for 'lesson_available' type)
 */
function learndash_notifications_send_notifications( $notification_type = '', $user_id = null, $course_id = null, $lesson_id = null, $topic_id = null, $quiz_id = null, $assignment_id = null, $lesson_access_from = null, $question_id = null, $group_id = null ) {
	// Get notifications with enroll course type
	$notifications = learndash_notifications_get_notifications( $notification_type );

	foreach ( $notifications as $n ) {
		learndash_notifications_send_notification( $n, $user_id, $course_id, $lesson_id, $topic_id, $quiz_id, $assignment_id, $lesson_access_from, $question_id, $group_id );
	}
}

/**
 * Send one learndash notification
 *
 * @param  object 	$notification 	   Notification WP Post object
 * @param  int 		$user_id           ID of a user
 * @param  int 		$course_id         ID of a course
 * @param  int 		$lesson_id         ID of a lesson
 * @param  int 		$topic_id          ID of a topic
 * @param  int 		$quiz_id           ID of a quiz
 * @param  int 		$assignment_id     ID of a assignment
 * @param  int 		$lesson_access_from Timestamp (only for 'lesson_available' type)
 */
function learndash_notifications_send_notification( $notification, $user_id = null, $course_id = null, $lesson_id = null, $topic_id = null, $quiz_id = null, $assignment_id = null, $lesson_access_from = null, $question_id = null, $group_id = null ) {

	$n = $notification;

	// Exit if group ID setting doesn't match
	$n_group_id = get_post_meta( $n->ID, '_ld_notifications_group_id', true );
	if ( isset( $group_id ) && $group_id != $n_group_id && $n_group_id != 'all' && ! empty( $n_group_id ) ) {
		return;
	}
	// Exit if course ID setting doesn't match
	$n_course_id = get_post_meta( $n->ID, '_ld_notifications_course_id', true );
	if ( isset( $course_id ) && $course_id != $n_course_id && $n_course_id != 'all' && ! empty( $n_course_id ) ) {
		return;
	}
	// Exit if lesson ID setting doesn't match
	$n_lesson_id = get_post_meta( $n->ID, '_ld_notifications_lesson_id', true );
	if ( isset( $lesson_id ) && $lesson_id != $n_lesson_id && $n_lesson_id != 'all' && ! empty( $n_lesson_id ) ) {
		return;
	}
	// Exit if topic ID setting doesn't match
	$n_topic_id = get_post_meta( $n->ID, '_ld_notifications_topic_id', true );
	if ( isset( $topic_id ) && $topic_id != $n_topic_id && $n_topic_id != 'all' && ! empty( $n_topic_id ) ) {
		return;
	}
	// Exit if quiz ID setting doesn't match
	$n_quiz_id = get_post_meta( $n->ID, '_ld_notifications_quiz_id', true );
	if ( isset( $quiz_id ) && $quiz_id != $n_quiz_id && $n_quiz_id != 'all' && ! empty( $n_quiz_id ) ) {
		return;
	}

	// Get recipient
	$recipients = learndash_notifications_get_recipients( $n->ID );

	// If notification doesn't have recipient, exit
	if ( empty( $recipients ) ) return;

	// Get recipients emails
	$emails = learndash_notifications_get_recipients_emails( $recipients, $user_id );

	$bcc = get_post_meta( $n->ID, '_ld_notifications_bcc', true );
	$bcc = array_map( 'trim', explode( ',', $bcc ) );

	global $ld_notifications_shortcode_data;
	$ld_notifications_shortcode_data = array(
		'user_id'       => $user_id,
		'course_id'     => $course_id,
		'lesson_id'     => $lesson_id,
		'topic_id'      => $topic_id,
		'assignment_id' => $assignment_id,
		'quiz_id'       => $quiz_id,
		'question_id'   => $question_id,
		'notification_id' => $n->ID,
		'group_id'      => $group_id,
	);

	$shortcode_data = $ld_notifications_shortcode_data;

	// Set to delayed emails if $n has delay option
	$delay = (int) get_post_meta( $n->ID, '_ld_notifications_delay', true );
	if ( is_int( $delay ) && $delay > 0 && ! isset( $lesson_access_from ) ) {
		$sent_on = strtotime( '+' . $delay . ' days', time() );
		learndash_notifications_save_delayed_email( $n, $emails, $sent_on, $shortcode_data, $bcc );
	} elseif ( isset( $lesson_access_from ) && $lesson_access_from > time() ) {
		if ( is_int( $delay ) && $delay > 0 ) {
			$sent_on = strtotime( '+' . $delay . ' days', $lesson_access_from );
		} else {
			$sent_on = $lesson_access_from;
		}
		learndash_notifications_save_delayed_email( $n, $emails, $sent_on, $shortcode_data, $bcc );
	} else {
		learndash_notifications_send_email( $emails, $n->post_title, $n->post_content, $bcc );
	}
}

/**
 * Send learndash notification email
 *
 * @param array 	$emails 		List of email addresses
 * @param string 	$title 			Title of message
 * @param string 	$content 		Content of message
 * @param array 	$bcc 			List of email address as BCC
 * @return bool 					True if mail sent|false otherwise
 */
function learndash_notifications_send_email( $emails, $title, $content, $bcc = array() ) {
	$content = wpautop( do_shortcode( $content ) );
	$title   = do_shortcode( $title );

	$emails = array_merge( $emails, $bcc );

	// Send email to each address separately to prevent recipient
	// knowing other recipient's email addresses
	foreach ( $emails as $email ) {
		// Continue if $email is blank
		$email = trim( $email );
		if ( empty( $email ) ) {
			continue;
		}

		// Change mail content type to HTML
		add_filter( 'wp_mail_content_type', 'learndash_notifications_set_html_mail_content_type' );

		$send = wp_mail( $email, $title, $content );

		// Reset mail content type back to plain
		remove_filter( 'wp_mail_content_type', 'learndash_notifications_set_html_mail_content_type' );
	}

	return true;
}

function learndash_notifications_set_html_mail_content_type() {
	return 'text/html';
}

/**
 * TEST FUNCTION
 */
// function learndash_notifications_send_email( $emails, $title, $content ) {
// 	// Change mail content type to HTML
// 	add_filter( 'wp_mail_content_type', 'learndash_notifications_set_html_mail_content_type' );

// 	$content = wpautop( do_shortcode( $content ) );

// 	$send =	wp_mail( $emails, $title, $content );

// 	// Reset mail content type back to plain
// 	remove_filter( 'wp_mail_content_type', 'learndash_notifications_set_html_mail_content_type' );

// 	return $send;
// }

// function test_phpmailer_init( $phpmailer )
// {
//     echo '<pre>';
//         var_dump( $phpmailer );
//     echo '</pre>';
//     return $phpmailer;
// }
// add_action( 'phpmailer_init', 'test_phpmailer_init' );
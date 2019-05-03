<?php

/**
 * Register shortcode
 */
function learndash_notifications_register_shortcode() {
	add_shortcode( 'ld_notifications', 'learndash_notifications_shortcode_init' );
}

add_action( 'init', 'learndash_notifications_register_shortcode', 1 );

/**
 * ld_notifications shortcode callback
 * 
 * @param  array  $atts    Shortcode attributes
 * @param  string $content Shortcode content
 * @return string          Shortcode final result
 */
function learndash_notifications_shortcode_init( $atts, $content = '' ) {
	// Get global variable set in learndash_notifications_send_notifications()
	global $ld_notifications_shortcode_data;
	$data = $ld_notifications_shortcode_data;

	$shortcode = 'ld_notifications';
	$atts = shortcode_atts( array(
		'field' => '',
		'show'  => '',
	), $atts, $shortcode );

	if ( empty( $data ) || empty( $atts['field'] ) || empty( $atts['show'] ) ) {
		return '';
	}

	$show = strtolower( $atts['show'] );

	if ( $atts['field'] == 'user' ) {
		$u = get_user_by( 'id', $data['user_id'] );

		switch ( $show ) {
			case 'username':
				$result = $u->user_login;
				break;

			case 'email':
				$result = $u->user_email;
				break;

			case 'display_name':
				$result = $u->display_name;
				break;

			case 'first_name':
				$result = $u->first_name;
				break;

			case 'last_name':
				$result = $u->last_name;
				break;

			default:
				$result = get_user_meta( $data['user_id'], $show, true );
				break;
		}
	}

	if ( $atts['field'] == 'group' ) {
		$group = get_post( $data['group_id'] );

		switch ( $show ) {
			case 'title':
				$result = $group->post_title;
				break;
		}
	}

	if ( $atts['field'] == 'course' ) {
		$course  = get_post( $data['course_id'] );

		switch ( $show ) {
			case 'title':
				$result = $course->post_title;
				break;

			case 'url':
				$result = get_permalink( $data['course_id'] );
				break;

			case 'completed_on':
				$atts = shortcode_atts( array(
					'format' => 'F j, Y, g:i a',
				), $atts, $shortcode );

				$completed_on = get_user_meta( $data['user_id'], 'course_completed_' . $data['course_id'], true );

				if ( empty( $completed_on ) ) {
					return '-';
				}

				date_default_timezone_set( get_option( 'timezone_string' ) );
				$result = date_i18n( $atts['format'], $completed_on );
				break;

			case 'cumulative_score':
			case 'cumulative_points':
			case 'cumulative_total_points':
			case 'cumulative_percentage':
			case 'cumulative_timespent':
			case 'cumulative_count':
				$field    = str_replace( 'cumulative_', '', $show );
				$quizdata = get_user_meta( $data['user_id'], '_sfwd-quizzes', true );	
				global $wpdb;

				$quizzes = $wpdb->get_col( $wpdb->prepare( 'SELECT post_id FROM ' . $wpdb->postmeta . " WHERE meta_key = 'course_id' AND meta_value = '%d'", $data['course_id'] ) );
				
				if ( empty( $quizzes ) ) {
					$result = 0;
					break;
				}

				$scores = array();

				if ( ( !empty( $quizdata ) ) && ( is_array( $quizdata ) ) ) {
					foreach ( $quizdata as $data ) {
						if ( in_array( $data['quiz'], $quizzes ) ) {
							if ( empty( $scores[ $data['quiz'] ] ) || $scores[ $data['quiz'] ] < $data[ $field ] ) {
								$scores[ $data['quiz'] ] = $data[ $field ];
							}
						}
					}
				}

				if ( empty( $scores ) || ! count( $scores ) ) {
					$result = 0;
					break;
				}

				$sum = 0;

				foreach ( $scores as $score ) {
					$sum += $score;
				}

				$return = number_format( $sum / count( $scores ), 2 );

				if ( $field == 'timespent' ) {
					$result = learndash_seconds_to_time( $return );
				} else {
					$result = $return;
				}
				break;

			case 'aggregate_percentage':
			case 'aggregate_score':
			case 'aggregate_points':
			case 'aggregate_total_points':
			case 'aggregate_timespent':
			case 'aggregate_count':
				$field    = substr_replace( $show, '', 0, 10 );
				$quizdata = get_user_meta( $data['user_id'], '_sfwd-quizzes', true );
				global $wpdb;

				$quizzes = $wpdb->get_col( $wpdb->prepare( 'SELECT post_id FROM ' . $wpdb->postmeta . " WHERE meta_key = 'course_id' AND meta_value = '%d'", $data['course_id'] ) );
				
				if ( empty( $quizzes ) ) {
					$result = 0;
					break;
				}

				$scores = array();
				
				if ( ( ! empty( $quizdata ) ) && ( is_array( $quizdata ) ) ) {
					foreach ( $quizdata as $data ) {
						if ( in_array( $data['quiz'], $quizzes ) ) {
							if ( empty( $scores[ $data['quiz'] ] ) || $scores[ $data['quiz'] ] < $data[ $field ] ) {
								$scores[ $data['quiz'] ] = $data[ $field ];
							}
						}
					}
				}

				if ( empty( $scores ) || ! count( $scores ) ) {
					$result = 0;
					break;
				}

				$sum = 0;

				foreach ( $scores as $score ) {
					$sum += $score;
				}

				$return = number_format( $sum, 2 );

				if ( $field == 'timespent' ) {
					$result = learndash_seconds_to_time( $return );
				} else {
					$result = $return;
				}

				break;

		} // End switch( $show )
	} // End if $atts['field'] == course

	if ( $atts['field'] == 'lesson' ) {
		$lesson = get_post( $data['lesson_id'] );

		switch ( $atts['show'] ) {
			case 'title':
				$result = $lesson->post_title;
				break;

			case 'url':
				$result = get_permalink( $data['lesson_id'] );
				break;
		}
		
	} // End if $atts['field'] == lesson

	if ( $atts['field'] == 'topic' ) {
		$topic = get_post( $data['topic_id'] );

		switch ( $atts['show'] ) {
			case 'title':
				$result = $topic->post_title;
				break;

			case 'url':
				$result = get_permalink( $data['topic_id'] );
				break;
		}
	} // End if $atts['field'] == topic

	if ( $atts['field'] == 'quiz' ) {
		if ( empty( $data['user_id'] ) ) {
			$data['user_id'] = get_current_user_id();
		}

		if ( empty( $data['quiz_id'] ) || empty( $data['user_id'] ) || empty( $show ) ) {
			$result = '';
		}

		$quizinfo = get_user_meta( $data['user_id'], '_sfwd-quizzes', true );

		foreach ( $quizinfo as $quiz_i ) {

			if ( isset( $quiz_i['time'] ) && $quiz_i['time'] == $time && $quiz_i['quiz'] == $data['quiz_id'] ) {
				$selected_quizinfo = $quiz_i;
				break;
			}

			if ( $quiz_i['quiz'] == $data['quiz_id'] ) {
				$selected_quizinfo2 = $quiz_i;
			}
		}

		$selected_quizinfo = empty( $selected_quizinfo ) ? $selected_quizinfo2 : $selected_quizinfo;

		switch ( $show ) {
			case 'url':
				$selected_quizinfo['url'] = get_permalink( $data['quiz_id'] );
				break;

			case 'timestamp':
				$atts = shortcode_atts( array(
					'format' => 'Y-m-d H:i:s',
				), $atts, $shortcode );

				date_default_timezone_set( get_option( 'timezone_string' ) );
				$selected_quizinfo['timestamp'] = date_i18n( $atts['format'], $selected_quizinfo['time'] );
				break;

			case 'percentage':		
				if ( empty( $selected_quizinfo['percentage'] ) ) {
					$selected_quizinfo['percentage'] = empty( $selected_quizinfo['count'] ) ? 0 : $selected_quizinfo['score'] * 100 / $selected_quizinfo['count'];
				}

				break;

			case 'pass':
				$selected_quizinfo['pass'] = ! empty( $selected_quizinfo['pass'] ) ? __( 'Yes', 'learndash' ) : __( 'No', 'learndash' );
				break;

			case 'quiz_title':
				$quiz_post = get_post( $data['quiz_id'] );

				if ( ! empty( $quiz_post->post_title) ) {
					$selected_quizinfo['quiz_title'] = $quiz_post->post_title;
				}

				break;

			case 'course_title':
				$course_id = learndash_get_setting( $data['quiz_id'], 'course' );
				$course    = get_post( $course_id );

				if ( ! empty( $course->post_title) ) {
					$selected_quizinfo['course_title'] = $course->post_title;
				}

				break;

			case 'timespent':
				$selected_quizinfo['timespent'] = isset( $selected_quizinfo['timespent'] ) ? learndash_seconds_to_time( $selected_quizinfo['timespent'] ) : '';
				break;

		}

		if ( isset( $selected_quizinfo[ $show ] ) ) {
			$result = $selected_quizinfo[ $show ];
		} else {
			$result = '';
		}
		
	} // End if $atts['field'] == quiz

	if ( $atts['field'] == 'essay' ) {
		if ( ! isset( $data['user_id'] ) || ! isset( $data['question_id'] ) ) {
			return;
		}

		$questionMapper = new WpProQuiz_Model_QuestionMapper();
		$question       = $questionMapper->fetchById( intval( $data['question_id'] ) );

		switch ( $atts['show'] ) {
			case 'points_earned':
				$question_data = get_user_meta( $data['user_id'], '_sfwd-quizzes', true );
		
				foreach ( $question_data as $q ) {
					if ( ! is_null( $q['graded'] ) ) {
						foreach ( $q['graded'] as $question_id => $q_data ) {
							if ( $question_id == $data['question_id'] ) {
								$result = $q_data['points_awarded'];
								continue 2;
							}
						}
					}
				}
				break;
			
			case 'points_total':
				$result = $question->getPoints();
				break;
		}
	} // End essay field

	if ( $atts['field'] == 'assignment' ) {
		$assignment = get_post( $data['assignment_id'] );

		switch ( $atts['show'] ) {
			case 'title':
				$result = $assignment->post_title;
				break;

			case 'file_name':
				$result = get_post_meta( $data['assignment_id'], 'file_name', true );
				break;

			case 'file_link':
				$result = get_post_meta( $data['assignment_id'], 'file_link', true );
				break;

			case 'lesson_title':
				$result = get_post_meta( $data['assignment_id'], 'lesson_title', true );
				break;

			case 'lesson_type':
				$result = get_post_meta( $data['assignment_id'], 'lesson_type', true );
				break;
		}
	} // End if $atts['field'] == assignment

	unset( $ld_notifications_shortcode_data );

	return $result;
}

function learndash_notifications_usermeta_shortcode( $user_id, $attr )
{
	if ( $attr['use'] == 'notifications' ) {
		global $ld_notifications_shortcode_data;
		$data = $ld_notifications_shortcode_data;

		$user_id = $data['user_id'];

		unset( $ld_notifications_shortcode_data );
	}

	return $user_id;
}

add_filter( 'learndash_usermeta_userid', 'learndash_notifications_usermeta_shortcode_user_id', 10, 2 );
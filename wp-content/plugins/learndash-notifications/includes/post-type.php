<?php
/**
* Registers a new post type
* @uses $wp_post_types Inserts new post type object into the list
*
* @param string  Post type key, must not exceed 20 characters
* @param array|string  See optional args description above.
* @return object|WP_Error the registered post type object, or an error object
*/
function learndash_notifications_register_post_type() {
	$labels = array(
		'name'                => __( 'Notifications', 'learndash-notifications' ),
		'singular_name'       => __( 'Notification', 'learndash-notifications' ),
		'add_new'             => _x( 'Add New Notification', 'learndash-notifications', 'learndash-notifications' ),
		'add_new_item'        => __( 'Add New Notification', 'learndash-notifications' ),
		'edit_item'           => __( 'Edit Notification', 'learndash-notifications' ),
		'new_item'            => __( 'New Notification', 'learndash-notifications' ),
		'view_item'           => __( 'View Notification', 'learndash-notifications' ),
		'search_items'        => __( 'Search Notifications', 'learndash-notifications' ),
		'not_found'           => __( 'No Notification found', 'learndash-notifications' ),
		'not_found_in_trash'  => __( 'No Notification found in Trash', 'learndash-notifications' ),
		'parent_item_colon'   => __( 'Parent Notification:', 'learndash-notifications' ),
		'menu_name'           => __( 'Notifications', 'learndash-notifications' ),
	);

	$args = array(
		'labels'              => $labels,
		'hierarchical'        => false,
		'description'         => __( 'Notifications created for LearnDash.', 'learndash-notifications' ),
		'taxonomies'          => array(),
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => false,
		'show_in_admin_bar'   => false,
		'menu_icon'           => null,
		'show_in_nav_menus'   => false,
		'publicly_queryable'  => true,
		'exclude_from_search' => true,
		'has_archive'         => false,
		'query_var'           => true,
		'can_export'          => true,
		'rewrite'             => true,
		'capability_type'     => 'post',
		'capabilities' 		  => array(),
		'map_meta_cap'		  => true,
		'supports'            => array(
			'title', 'editor',
		)
	);

	register_post_type( 'ld-notification', $args );
}

add_action( 'init', 'learndash_notifications_register_post_type' );

/**
 * Change notification updated messages
 * @param  array  $messages Existing messages
 * @return array            New updated messages
 */
function learndash_notifications_post_updated_messages( $messages ) {
	$messages['ld-notification'] = array(
		0 => '', // Unused. Messages start at index 1.
		1 => __( 'Notification updated.' ),
		2 => __( 'Custom field updated.' ),
		3 => __( 'Custom field deleted.' ),
		4 => __( 'Notification updated.' ),
		/* translators: %s: date and time of the revision */
		5 => isset($_GET['revision']) ? sprintf( __( 'Notification restored to revision from %s.' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => __( 'Notification published.' ),
		7 => __( 'Notification saved.' ),
		8 => __( 'Notification submitted.' ),
		9 => '',
		10 => __( 'Notification draft updated.' ),
	);

	return $messages;
}

add_filter( 'post_updated_messages', 'learndash_notifications_post_updated_messages' );

/**
 * Set or unset columns used in the post type
 * 
 * @param  array $columns Array of available columns
 * @return array          Array of new modified columns
 */
function learndash_notifications_post_type_columns( $columns ) {
	unset( $columns['date'] );
	$columns = array_merge( $columns, 
		array(
			'course'  => LearnDash_Custom_Label::get_label( 'course' ),
			'lesson'  => LearnDash_Custom_Label::get_label( 'lesson' ),
			'topic'   => LearnDash_Custom_Label::get_label( 'topic' ),
			'quiz'    => LearnDash_Custom_Label::get_label( 'quiz' ),
			'trigger' => __( 'Trigger', 'learndash-notifications' ),
		)
	);
	return $columns;
}

add_filter( 'manage_ld-notification_posts_columns', 'learndash_notifications_post_type_columns' );

/**
 * Add custom columns to notifications post screen
 * 
 * @param  string $column  Column name
 * @param  int 	  $post_id Id of a post
 */
function learndash_notifications_post_type_custom_columns( $column, $post_id ) {
	switch ( $column ) {
		case 'course':
			$course_id = get_post_meta( $post_id, '_ld_notifications_course_id', true );
			$trigger = get_post_meta( $post_id, '_ld_notifications_trigger', true );

			// if ( ! in_array( $trigger, array( 'enroll_course', 'complete_course', 'course_expires', 'course_enrollment' ) ) ) {
			// 	echo '-';
			// 	break;
			// }

			if ( $course_id == 'all' ) {
				_e( 'All Courses', 'learndash-notifications' );
			} elseif ( is_numeric( $course_id ) ) {
				$course = get_post( $course_id );
				echo $course->post_title;
			} elseif ( empty( $course_id ) ) {
				echo '-';
			}
			break;

		case 'lesson':
			$lesson_id = get_post_meta( $post_id, '_ld_notifications_lesson_id', true );
			$trigger = get_post_meta( $post_id, '_ld_notifications_trigger', true );

			// if ( ! in_array( $trigger, array( 'complete_lesson' ) ) ) {
			// 	echo '-';
			// 	break;
			// }

			if ( $lesson_id == 'all' ) {
				_e( 'All lessons', 'learndash-notifications' );
			} elseif ( is_numeric( $lesson_id ) ) {
				$lesson = get_post( $lesson_id );
				echo $lesson->post_title;
			} elseif ( empty( $lesson_id ) ) {
				echo '-';
			}
			break;

		case 'topic':
			$topic_id = get_post_meta( $post_id, '_ld_notifications_topic_id', true );
			$trigger = get_post_meta( $post_id, '_ld_notifications_trigger', true );

			// if ( ! in_array( $trigger, array( 'complete_topic' ) ) ) {
			// 	echo '-';
			// 	break;
			// }

			if ( $topic_id == 'all' ) {
				_e( 'All topics', 'learndash-notifications' );
			} elseif ( is_numeric( $topic_id ) ) {
				$topic = get_post( $topic_id );
				echo $topic->post_title;
			} elseif ( empty( $topic_id ) ) {
				echo '-';
			}
			break;

		case 'quiz':
			$quiz_id = get_post_meta( $post_id, '_ld_notifications_quiz_id', true );
			$trigger = get_post_meta( $post_id, '_ld_notifications_trigger', true );

			// if ( ! in_array( $trigger, array( 'pass_quiz', 'fail_quiz', 'complete_quiz' ) ) ) {
			// 	echo '-';
			// 	break;
			// }

			if ( $quiz_id == 'all' ) {
				_e( 'All quizzes', 'learndash-notifications' );
			} elseif ( is_numeric( $quiz_id ) ) {
				$quiz = get_post( $quiz_id );
				echo $quiz->post_title;
			} elseif ( empty( $quiz_id ) ) {
				echo '-';
			}
			break;

		case 'trigger':
			$settings = learndash_notifications_get_meta_box_settings();
			$triggers = $settings['trigger']['value'];
			$trigger = get_post_meta( $post_id, '_ld_notifications_trigger', true );
			echo $settings['trigger']['value'][ $trigger ];
			break;
	}
}

add_filter( 'manage_ld-notification_posts_custom_column', 'learndash_notifications_post_type_custom_columns', 10, 2 );

function learndash_notifications_hide_months_dropdown() {
	global $typenow;
	if ( $typenow != 'ld-notification' ) return;

	add_filter( 'months_dropdown_results', '__return_empty_array' );
}

add_action( 'admin_init', 'learndash_notifications_hide_months_dropdown' );

/**
 * Output filter dropdown on admin page
 * 
 * @param  string $post_type Current post type name
 */
function learndash_notifications_admin_posts_filter( $post_type ) {
	if ( $post_type != 'ld-notification' ) return;

	$filters = array();
	
	$filters['courses'] = get_posts( 'post_type=sfwd-courses&posts_per_page=-1&orderby=title&order=ASC' );

	$filters['lessons'] = get_posts( 'post_type=sfwd-lessons&posts_per_page=-1&orderby=title&order=ASC' );
	
	$filters['topics']  = get_posts( 'post_type=sfwd-topic&posts_per_page=-1&orderby=title&order=ASC' );
	
	$filters['quizzes'] = get_posts( 'post_type=sfwd-quiz&posts_per_page=-1&orderby=title&order=ASC' );
	
	$filters_key = array();
	$filters_key['courses'] = 'course';
	$filters_key['lessons'] = 'lesson';
	$filters_key['topics']  = 'topic';
	$filters_key['quizzes'] = 'quiz';

	foreach ( $filters as $key => $filter ) {
		echo '<select name="' . $filters_key[ $key ] . '_id" id="' . $filters_key[ $key ] . '_id" class="postform notifications-filter">';
		echo '<option value="">' . sprintf( __( 'Select %s', 'learndash-notifications' ), LearnDash_Custom_Label::get_label( $filters_key[ $key ] ) ) . '</option>';
		echo '<option value="all"' . ( isset( $_GET[ $filters_key[ $key ] . '_id'] ) && $_GET[ $filters_key[ $key ] . '_id'] == 'all' ? ' selected="selected"' : '' ) . '>' . sprintf( __( 'All %s', 'learndash-notifications' ), LearnDash_Custom_Label::get_label( $key ) ) . '</option>';

		foreach ( $filter as $post ) {
			echo '<option value="' . $post->ID . '"' . ( isset( $_GET[ $filters_key[ $key ] . '_id'] ) && $_GET[ $filters_key[ $key ] . '_id'] == $post->ID ? ' selected="selected"' : '' ) . '>' . $post->post_title .'</option>';
		}

		echo '</select>';
	}

	// Output triggers filter dropdown
	$triggers = learndash_notifications_get_meta_box_settings();
	$triggers = $triggers['trigger']['value'];
	unset( $triggers[0] );

	echo '<select name="trigger_id" id="trigger_id" class="postform notifications-filter">';
	echo '<option value="">' . __( 'Select Trigger', 'learndash-notifications' ) . '</option>';

	foreach ( $triggers as $k => $t ) {
		echo '<option value="' . $k . '"' . ( isset( $_GET['trigger_id'] ) && $_GET['trigger_id'] == $k ? ' selected="selected"' : '' ) . '>' . $t .'</option>';
	}

	echo '</select>';
}

add_action( 'restrict_manage_posts', 'learndash_notifications_admin_posts_filter', 10, 2 );

/**
 * Filter notificatin CPT posts
 * 
 * @param object $query WP query object
 */
function learndash_notifications_admin_post_filter_query( $query ) {
	global $pagenow;
	$filters  = array( 'course', 'lesson', 'topic', 'quiz' );
	$q_vars  = $query->query_vars;

	foreach ( $filters as $f ) {
		if ( is_admin() AND $pagenow == 'edit.php' AND ! empty( $_GET[ $f . '_id' ] ) AND ( $query->query['post_type'] == 'ld-notification' ) ) {

			$query->query_vars['meta_query'][] = array(
				'key'   => '_ld_notifications_'. $f . '_id',
				'value'	=> $_GET[ $f . '_id' ],
			);
		}
	}

	if ( is_admin() AND $pagenow == 'edit.php' AND ! empty( $_GET[ 'trigger_id' ] ) AND ( $query->query['post_type'] == 'ld-notification' ) ) {

		$query->query_vars['meta_query'][] = array(
			'key'   => '_ld_notifications_trigger',
			'value'	=> $_GET['trigger_id'],
		);
	}
}

add_filter( 'parse_query', 'learndash_notifications_admin_post_filter_query', 10, 2 );
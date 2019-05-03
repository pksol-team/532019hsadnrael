<?php
/**
 * Add meta box function
 */
function learndash_notifications_add_meta_boxes() {
	add_meta_box( 'ld-notifications-meta-box', __( 'Notification Settings', 'learndash-notifications' ), 'learndash_notifications_meta_box', 'ld-notification', 'advanced', 'high' );
	
	add_meta_box( 'ld-notifications-shortcodes-meta-box', __( 'Available Shortcodes', 'learndash-notifications' ), 'learndash_notifications_shortcodes_meta_box', 'ld-notification', 'advanced', 'low' );
}

/**
 * Output meta box HTML
 * 
 * @param  array $args Args array passed from add_meta_box function
 */
function learndash_notifications_meta_box( $args ) {
	$settings = learndash_notifications_get_meta_box_settings();

	echo '<div class="sfwd sfwd_options ld_notifications_metabox_settings">';
	wp_nonce_field( 'learndash_notifications_meta_box', 'learndash_notifications_nonce' );

	foreach ( $settings as $key => $setting ) {
		$function = "learndash_notifications_meta_box_output";

		if ( function_exists( $function ) ) {
			echo $function( $key, $setting );
		}
	}

	echo '</div>';
}

/**
 * Output availble shortcodes meta box HTML
 */
function learndash_notifications_shortcodes_meta_box() {
	?>

	<div class="shortcodes-instruction no-instruction 0"> <?php _e( 'Select an email trigger on the notification settings above to see available shortcodes.', 'learndash-notifications' ); ?></div>

	<?php $instructions = learndash_notifications_get_shortcodes_instructions(); ?>
	<?php foreach ( $instructions as $class => $instruction ) : ?>

		<div class="shortcodes-instruction <?php echo $class; ?>">
			<div class="header-text">
				<?php _e( 'Here are the available shortcodes for the email trigger selected:', 'learndash-notifications' ); ?>
				
			</div>

			<?php foreach ( $instruction as $shortcode => $label ) : ?>

				<div class="shortcode-wrapper">
					<span class="shortcode"><?php echo $shortcode; ?></span> : <span class="label"><?php echo $label; ?></span>
				</div>

			<?php endforeach; ?>

			<?php if ( $class == 'complete_course' ) : ?>
				
				<div class="additional-help-text <?php echo $class; ?>" style="display:none;">
					<?php _e( '<em>Cumulative</em> is average for all quizzes of the course.', 'learndash-notifications' ); ?>
					<br />
					<?php _e( '<em>Aggregate</em> is sum for all quizzes of the course.', 'learndash-notifications' ); ?>
				</div>

			<?php endif; ?>
		</div>

	<?php endforeach; ?>

	<?php
}

add_action( 'add_meta_boxes', 'learndash_notifications_add_meta_boxes', 1, 2 );

/**
 * Get LearnDash Notifications metabox settings
 * 
 * @return array Metabox settings
 */
function learndash_notifications_get_meta_box_settings() {
	$groups = get_posts( 'post_type=groups&posts_per_page=-1&orderby=title&order=ASC' );
	$groups_array = array();
	foreach ( $groups as $g ) {
		$groups_array[ $g->ID ] = $g->post_title;	
	}

	$courses = get_posts( 'post_type=sfwd-courses&posts_per_page=-1&orderby=title&order=ASC' );
	$courses_array = array();
	foreach ( $courses as $c ) {
		$courses_array[ $c->ID ] = $c->post_title;
	}

	$lessons = get_posts( 'post_type=sfwd-lessons&posts_per_page=-1&orderby=title&order=ASC' );
	$lessons_array = array();
	foreach ( $lessons as $l ) {
		$lessons_array[ $l->ID ] = $l->post_title;
	}

	$topics = get_posts( 'post_type=sfwd-topic&posts_per_page=-1&orderby=title&order=ASC' );
	$topics_array = array();
	foreach ( $topics as $t ) {
		$topics_array[ $t->ID ] = $t->post_title;
	}

	$quizzes = get_posts( 'post_type=sfwd-quiz&posts_per_page=-1&orderby=title&order=ASC' );
	$quizzes_array = array();
	foreach ( $quizzes as $q ) {
		$quizzes_array[ $q->ID ] = $q->post_title;
	}

	$settings = array(
		'trigger' => array(
			'type' => 'dropdown',
			'title' => __( 'Email trigger', 'learndash-notifications' ),
			'help_text' => __( 'When this email will be sent.', 'learndash-notifications' ),
			'disabled' => 1,
			'hide' => 0,
			'value' => array(
				0 => __( '-- Select an Email Trigger --', 'learndash-notifications' ),
				'enroll_group' => __( 'User enrolls into a group', 'learndash-notifications' ),
				'enroll_course' => __( 'User enrolls into a course', 'learndash-notifications' ),
				'complete_course' => __( 'User completes a course', 'learndash-notifications' ),
				'complete_lesson' => __( 'User completes a lesson', 'learndash-notifications' ),
				'lesson_available' => __( 'A scheduled lesson is available to user', 'learndash-notifications' ),
				'complete_topic' => __( 'User completes a topic', 'learndash-notifications' ),
				'pass_quiz' => __( 'User passes a quiz', 'learndash-notifications' ),
				'fail_quiz' => __( 'User fails a quiz', 'learndash-notifications' ),
				'complete_quiz' => __( 'User completes a quiz', 'learndash-notifications' ),
				'upload_assignment' => __( 'An Assignment is uploaded', 'learndash-notifications' ),
				'approve_assignment' => __( 'An Assignment is approved', 'learndash-notifications' ),
				'not_logged_in' => __( 'User hasn\'t logged in for "X" days', 'learndash-notifications' ),
				'course_expires' => __( '"X" days before course expires', 'learndash-notifications' ),
				'essay_graded' => __( 'Essay question has been put into graded status', 'learndash-notifications' ),
				// 'course_enrollment' => __( '"X" days after course enrollment', 'learndash-notifications' ),
			)
		),
		'group_id' => array(
			'type' => 'dropdown',
			'title' => __( 'Group', 'learndash-notifications' ),
			'help_text' => __( 'Group that the notification is assigned to.', 'learndash-notifications' ),
			'hide' => 1,
			'disabled' => 1,
			'parent' => array( 'enroll_group' ),
			'value' => array(
				'' => __( '-- Select Group --', 'learndash-notifications' ),
				'all' => __( 'All Groups', 'learndash-notifications' )
			) + $groups_array
		),
		'course_id' => array(
			'type' => 'dropdown',
			'title' => __( 'Course', 'learndash-notifications' ),
			'help_text' => __( 'Course that the notification is assigned to.', 'learndash-notifications' ),
			'hide' => 1,
			'disabled' => 1,
			'class' => 'parent_field',
			'parent' => array( 'enroll_course', 'complete_course', 'course_expires', 'not_logged_in', 'complete_lesson', 'lesson_available', 'complete_topic', 'complete_quiz', 'pass_quiz', 'fail_quiz' ),
			'value' => array(
				'' => __( '-- Select Course --', 'learndash-notifications' ),
				'all' => __( 'All Courses', 'learndash-notifications' )
			) + $courses_array
		),
		'lesson_id' => array(
			'type' => 'dropdown',
			'title' => __( 'Lesson', 'learndash-notifications' ),
			'help_text' => __( 'Lesson that the notification is assigned to.', 'learndash-notifications' ),
			'hide' => 1,
			'disabled' => 1,
			'class' => 'parent_field child_field',
			'parent' => array( 'complete_lesson', 'lesson_available', 'complete_topic', 'complete_quiz', 'pass_quiz', 'fail_quiz' ),
			'value' => array(
				'' => __( '-- Select Lesson --', 'learndash-notifications' ),
				'all' => __( 'All Lessons', 'learndash-notifications' )
			) + $lessons_array,
		),
		'topic_id' => array(
			'type' => 'dropdown',
			'title' => __( 'Topic', 'learndash-notifications' ),
			'help_text' => __( 'Topic that the notification is assigned to.', 'learndash-notifications' ),
			'hide' => 1,
			'disabled' => 1,
			'class' => 'parent_field child_field',
			'parent' => array( 'complete_topic', 'complete_quiz', 'pass_quiz', 'fail_quiz' ),
			'value' => array(
				'' => __( '-- Select Topic --', 'learndash-notifications' ),
				'all' => __( 'All Topics', 'learndash-notifications' )
			) + $topics_array,
		),
		'quiz_id' => array(
			'type' => 'dropdown',
			'title' => __( 'Quiz', 'learndash-notifications' ),
			'help_text' => __( 'Quiz that the notification is assigned to.', 'learndash-notifications' ),
			'hide' => 1,
			'disabled' => 1,
			'class' => 'child_field',
			'parent' => array( 'pass_quiz', 'fail_quiz', 'complete_quiz' ),
			'value' => array(
				'' => __( '-- Select Quiz --', 'learndash-notifications' ),
				'all' => __( 'All Quizzes', 'learndash-notifications' )
			) + $quizzes_array,
		),
		'not_logged_in_days' => array(
			'type' => 'text',
			'title' => __( 'After how many days?', 'learndash-notifications' ),
			'help_text' => __( 'Setting associated with the email trigger setting above.', 'learndash-notifications' ),
			'label' => __( 'day(s)', 'learndash-notifications' ),
			'hide' => 1,
			'hide_delay' => 1,
			'size' => 2,
			'parent' => 'not_logged_in',
		),
		'course_expires_days' => array(
			'type' => 'text',
			'title' => __( 'Before how many days?', 'learndash-notifications' ),
			'help_text' => __( 'Setting associated with the email trigger setting above.', 'learndash-notifications' ),
			'label' => __( 'day(s)', 'learndash-notifications' ),
			'hide' => 1,
			'hide_delay' => 1,
			'size' => 2,
			'parent' => 'course_expires',
		),
		/*'course_enrollment_days' => array(
			'type' => 'text',
			'title' => __( 'After how many days?', 'learndash-notifications' ),
			'help_text' => __( 'Setting associated with the email trigger setting above.', 'learndash-notifications' ),
			'label' => __( 'day(s)', 'learndash-notifications' ),
			'hide' => 1,
			'hide_delay' => 1,
			'size' => 2,
			'parent' => 'course_enrollment',
		),*/
		'recipient' => array(
			'type' => 'checkbox',
			'title' => __( 'Recipient', 'learndash-notifications' ),
			'help_text' => __( 'Recipient of this email.', 'learndash-notifications' ),
			'hide' => 0,
			'hide_on' => array(),
			'value' => array(
				'user' => __( 'User', 'learndash-notifications' ),
				'group_leader' => __( 'Group Leader', 'learndash-notifications' ),
				'admin' => __( 'Admin', 'learndash-notifications' ),
			)
		),
		'bcc' => array(
			'type' => 'text',
			'title' => __( 'Additional Recipient', 'learndash-notifications' ),
			'help_text' => __( 'Additional email addresses (separated by comma) that will also get this notification', 'learndash-notifications' ),
			'hide' => 0,
			'size' => 50,

		),
		'delay' => array(
			'type' => 'text',
			'title' => __( 'Delay', 'learndash-notifications' ),
			'help_text' => __( 'How many days this email is delayed after the trigger occurs (default is 0).', 'learndash-notifications' ),
			'label' => __( 'day(s)', 'learndash-notifications' ),
			'hide' => 0,
			'default' => 0,
			'size' => 2,
		),
	);

	return $settings;
}

/**
 * Output meta box input HTML
 * 
 * @param  array  $args Args array of metabox setting
 * @return string       String of HTML output
 */
function learndash_notifications_meta_box_output( $key, $args ) {
	$screen = get_current_screen();

	$hide    = $args['hide'] == 1 ? 'display: none;' : '';
	$hide_on = '';
	if ( isset( $args['hide_on'] ) ) {
		foreach ( $args['hide_on'] as $class ) {
			$hide_on .= ' hide_on hide_on_' . $class . ' ';
		}
	}

	$parent_class = '';
	if ( isset( $args['parent'] ) && ! is_array( $args['parent'] ) ) {
		$parent_class = $args['parent'] ? $args['parent'] . ' child-input ' : '';	
	} elseif ( isset( $args['parent'] ) && is_array( $args['parent'] ) ) {
		$parent_class = '';
		foreach ( $args['parent'] as $parent_class_name ) {
			$parent_class .= $parent_class_name . ' ';
		}
		$parent_class .= ' child-input';
	}

	$class = '';
	if ( isset( $args['class'] ) && ! is_array( $args['class'] ) ) {
		$class = $args['class'] ? $args['class'] . ' ' : ' ';	
	} elseif ( isset( $args['class'] ) && is_array( $args['class'] ) ) {
		$class = '';
		foreach ( $args['class'] as $class_name ) {
			$class .= $class_name . ' ';
		}
	}

	if ( $screen->post_type == 'ld-notification' && $screen->action != 'add' && isset( $args['disabled'] ) && $args['disabled'] == '1' ) {
		$disabled = 'disabled="disabled" ';
	} else {
		$disabled = '';
	}

	$input_name   = '_ld_notifications_' . $key;
	$id           = get_the_ID();
	$key_value    = get_post_meta( $id, $input_name, true );
	$key_value    = maybe_unserialize( $key_value );
	$default 	  = isset( $args['default'] ) ? $args['default'] : '';
	$hide_delay   = isset( $args['hide_delay'] ) && $args['hide_delay'] == 1 ? 'hide-delay' : '';

	$hide_empty_select = $screen->post_type == 'ld-notification' && $screen->action != 'add' && empty( $key_value ) && $args['type'] == 'dropdown' && strpos( $input_name, '_id' ) !== false ? 'hide-empty-select' : '';

	ob_start();
	?>

	<div class="sfwd_input <?php echo $parent_class; ?> <?php echo $class; ?> <?php echo $hide_delay; ?> <?php echo $hide_on ?> <?php echo $hide_empty_select; ?>" id="<?php echo $key; ?>" style="<?php echo $hide; ?>">
		<span class="sfwd_option_label">
			<a class="sfwd_help_text_link" style="cursor:pointer;" title="<?php _e( 'Click for Help!', 'learndash-notifications' ); ?>" onclick="toggleVisibility( 'learndash-notifications_<?php echo $key; ?>_tip' );"><img src="<?php echo LEARNDASH_LMS_PLUGIN_URL . 'assets/images/question.png' ?>"><label class="sfwd_label textinput"><?php echo $args['title'] ?></label></a>
		</span>
		<span class="sfwd_option_input">
			<div class="sfwd_option_div">
				
				<?php if ( $args['type'] == 'dropdown') : ?>
					
				<select name="<?php echo $input_name; ?>" <?php echo $disabled; ?>>

					<?php foreach ( $args['value'] as $value => $title ) : ?>

					<?php $selected = (string) $value === (string) $key_value ? 'selected="selected"' : '';  ?>
						
					<option value="<?php echo $value; ?>" <?php echo $selected; ?>><?php echo $title; ?></option>

					<?php endforeach; ?>

				</select>

				<?php endif; // Endif type == 'dropdown' ?>

				<?php if ( $args['type'] == 'text') : ?>

				<?php $value = ! empty( $key_value ) ? $key_value : $default; ?>

				<input type="text" size="<?php echo $args['size']; ?>" name="<?php echo $input_name; ?>" value="<?php echo $value; ?>" style="width: initial;" <?php echo $disabled; ?>>

				<?php if ( isset( $args['label'] ) ) : ?>
					<label><?php echo $args['label']; ?></label>
				<?php endif; ?>
					
				<?php endif; // Endif type == 'text' ?>

				<?php if ( $args['type'] == 'checkbox') : ?>

				<?php $cb_input_name = is_array( $args['value'] ) ? $input_name . '[]' : $input_name;  ?>

				<?php if ( is_array( $args['value'] ) && count( $args['value'] ) > 1 ) : ?>
				
				<?php foreach ( $args['value'] as $value => $label ) : ?>
				<?php $key_value = empty( $key_value ) ? array() : $key_value; ?>

				<?php $checked = in_array( $value, $key_value ) ? 'checked="checked"' : ''; ?>

				<input type="checkbox" name="<?php echo $cb_input_name; ?>" id="<?php echo $value ?>" value="<?php echo $value; ?>" <?php echo $checked; ?> <?php echo $disabled; ?>>
				<label for="<?php echo $value ?>"><?php echo $label; ?></label><br />

				<?php endforeach; ?>

				<?php else: ?>

				<?php $checked = $value == $key_value ? 'checked="checked"' : ''; ?>

				<input type="checkbox" name="<?php echo $cb_input_name; ?>" id="<?php echo $value ?>" value="<?php echo $value; ?>" <?php echo $checked; ?> <?php echo $disabled; ?>>
				<label for="<?php echo $value ?>"><?php echo $label; ?></label><br />

				<?php endif; // End if $args['value'] is array check ?>

				<?php endif; // End if $args['type'] is checkbox check ?>

			</div>
			<div class="sfwd_help_text_div" style="display: none;" id="learndash-notifications_<?php echo $key; ?>_tip">
				<label class="sfwd_help_text"><?php echo $args['help_text']; ?></label>
			</div>
		</span>
		<p style="clear:left"></p>
	</div>

	<?php
	return ob_get_clean();
}

/**********************
 *** AJAX FUNCTIONS ***
 **********************/

/**
 * Get children post list for meta box
 */
function learndash_notifications_get_children_list()
{
	if ( ! wp_verify_nonce( $_POST['nonce'], 'ld_notifications_nonce' ) ) {
		wp_die();
	}

	if ( ! current_user_can( LEARNDASH_ADMIN_CAPABILITY_CHECK ) ) {
		wp_die();
	}

	$course_id 	 = sanitize_text_field( $_POST['course_id'] );
	$parent_id   = sanitize_text_field( $_POST['parent_id'] );
	$parent_type = sanitize_text_field( $_POST['parent_type'] );
	$step_type   = '';

	switch ( $parent_type ) {
		case 'course':
			$step_type = 'sfwd-lessons';
			break;
		
		case 'lesson':
			$step_type = 'sfwd-topic';
			break;

		case 'topic':
			$step_type = 'sfwd-quiz';
			break;
	}

	if ( ! empty( $step_type ) ) {
		$return = array();

		if ( $parent_id == 'all' ) {

			if ( $parent_type == 'course' ) {
				$lessons = get_posts( 'post_type=sfwd-lessons&posts_per_page=-1&orderby=title&order=ASC' );

				foreach ( $lessons as $l ) {
					$return[ $l->ID ] = $l->post_title;
				}
			}

			if ( $parent_type == 'lesson' && 'all' == $course_id ) {
				$topics = get_posts( 'post_type=sfwd-topic&posts_per_page=-1&orderby=title&order=ASC' );
				
				foreach ( $topics as $t ) {
					$return[ $t->ID ] = $t->post_title;
				}
			} elseif ( $parent_type == 'lesson' && is_numeric( $course_id ) ) {
				$children = learndash_course_get_steps_by_type( $course_id, 'sfwd-topic' );

				foreach ( $children as $child_id ) {
					$post = get_post( $child_id );
					$return[ $child_id ] = $post->post_title;
				}
			}

			if ( $parent_type == 'topic' && 'all' == $course_id ) {
				$quizzes = get_posts( 'post_type=sfwd-quiz&posts_per_page=-1&orderby=title&order=ASC' );
				
				foreach ( $quizzes as $q ) {
					$return[ $q->ID ] = $q->post_title;
				}
			} elseif ( $parent_type == 'topic' && is_numeric( $course_id ) ) {
				$children = learndash_course_get_steps_by_type( $course_id, 'sfwd-quiz' );

				foreach ( $children as $child_id ) {
					$post = get_post( $child_id );
					$return[ $child_id ] = $post->post_title;
				}
			}

		} else {

			$children = learndash_course_get_children_of_step( $course_id, $parent_id, $step_type );

			foreach ( $children as $child_id ) {
				$post = get_post( $child_id );
				$return[ $child_id ] = $post->post_title;
			}
		}

		echo json_encode( $return );
	}

	wp_die();
}

add_action( 'wp_ajax_ld_notifications_get_children_list', 'learndash_notifications_get_children_list' );

/**
 * Save notifications meta box value
 * 
 * @param  int $post_id ID of post created/updated
 */
function learndash_notifications_save_meta_box( $notification_id ) {

	$notification = get_post( $notification_id );

	if ( ! isset( $_POST['learndash_notifications_nonce'] ) ) {
		return;
	}
	
	if ( $notification->post_type != 'ld-notification' || ! check_admin_referer( 'learndash_notifications_meta_box', 'learndash_notifications_nonce' ) ) {
		return;
	}

	// Update recipient post meta if all unchecked
	if ( ! isset( $_POST['_ld_notifications_recipient'] ) ) {
		update_post_meta( $notification_id, '_ld_notifications_recipient', array() );
	}

	foreach ( $_POST as $key => $value ) {
		if ( strpos( $key, '_ld_notifications' ) === false ) continue;
		if ( is_array( $value ) ) {
			$value = array_map( 'sanitize_text_field', $value );
		} else {
			$value = sanitize_text_field( $value );
		}
		update_post_meta( $notification_id, $key, $value );
	}

	if ( in_array( $_POST['_ld_notifications_trigger'], array( 'enroll_course', 'complete_course', 'course_expires' ) ) ) {
		update_post_meta( $notification_id, '_ld_notifications_lesson_id', '' );
		update_post_meta( $notification_id, '_ld_notifications_topic_id', '' );
		update_post_meta( $notification_id, '_ld_notifications_quiz_id', '' );

	} elseif ( in_array( $_POST['_ld_notifications_trigger'], array( 'complete_lesson', 'lesson_available' ) ) ) {
		$course_id =  (int) $_POST['_ld_notifications_course_id'];
		update_post_meta( $notification_id, '_ld_notifications_course_id', $course_id );
		update_post_meta( $notification_id, '_ld_notifications_topic_id', '' );
		update_post_meta( $notification_id, '_ld_notifications_quiz_id', '' );

	} elseif ( in_array( $_POST['_ld_notifications_trigger'], array( 'complete_topic' ) ) ) {
		$course_id =  (int) $_POST['_ld_notifications_course_id'];
		$lesson_id =  (int) $_POST['_ld_notifications_lesson_id'];
		update_post_meta( $notification_id, '_ld_notifications_course_id', $course_id );
		update_post_meta( $notification_id, '_ld_notifications_lesson_id', $lesson_id );
		update_post_meta( $notification_id, '_ld_notifications_quiz_id', '' );

	} elseif ( in_array( $_POST['_ld_notifications_trigger'], array( 'pass_quiz', 'fail_quiz', 'complete_quiz' ) ) ) {
		$course_id =  (int) $_POST['_ld_notifications_course_id'];
		$lesson_id =  (int) $_POST['_ld_notifications_lesson_id'];
		$topic_id  =  (int) $_POST['_ld_notifications_topic_id'];
		update_post_meta( $notification_id, '_ld_notifications_course_id', $course_id );
		update_post_meta( $notification_id, '_ld_notifications_lesson_id', $lesson_id );
		update_post_meta( $notification_id, '_ld_notifications_topic_id', $topic_id );

	}
}

add_action( 'save_post', 'learndash_notifications_save_meta_box' );

function learndash_notifications_get_shortcodes_instructions(){
	$user_shortcode = array(
		'[ld_notifications field="user" show="username"]' => __( 'Display user\'s username.', 'learndash-notifications' ),
		'[ld_notifications field="user" show="email"]' => __( 'Display user\'s email.', 'learndash-notifications' ),
		'[ld_notifications field="user" show="display_name"]' => __( 'Display user\'s display name.', 'learndash-notifications' ),
		'[ld_notifications field="user" show="first_name"]' => __( 'Display user\'s first name.', 'learndash-notifications' ),
		'[ld_notifications field="user" show="last_name"]' => __( 'Display user\'s last name.', 'learndash-notifications' ),
	);

	$group_basic_shortcode = array(
		'[ld_notifications field="group" show="title"]' => __( 'Display group title.', 'learndash-notifications' ),
	);

	$course_basic_shortcode = array(
		'[ld_notifications field="course" show="title"]' => __( 'Display course title.', 'learndash-notifications' ),
		'[ld_notifications field="course" show="url"]' => __( 'Display course URL.', 'learndash-notifications' ),
	);

	$course_advanced_shortcode = array(
		'[ld_notifications field="course" show="completed_on" format=""]' => __( 'Display time when course is completed. <a href="http://php.net/manual/en/function.date.php" target="_blank">Click here</a> to see format options.', 'learndash-notifications' ),
		'[ld_notifications field="course" show="cumulative_score"]' => __( 'Display average points scored across all quizzes on the course.', 'learndash-notifications' ),
		'[ld_notifications field="course" show="cumulative_points"]' => __( 'Display average points got across all quizzes on the course.', 'learndash-notifications' ),
		'[ld_notifications field="course" show="cumulative_total_points"]' => __( 'Display average total points got across all quizzes on the course.', 'learndash-notifications' ),
		'[ld_notifications field="course" show="cumulative_percentage"]' => __( 'Display average correct answers percentage across all quizzes on the course.', 'learndash-notifications' ),
		'[ld_notifications field="course" show="cumulative_timespent"]' => __( 'Display average time spent across all quizzes on the course.', 'learndash-notifications' ),
		'[ld_notifications field="course" show="cumulative_count"]' => __( 'Display average correct answer counted across all quizzes on the course.', 'learndash-notifications' ),
		'[ld_notifications field="course" show="aggregate_percentage"]' => __( 'Display sum of correct answers percentage across all quizzes on the course.', 'learndash-notifications' ),
		'[ld_notifications field="course" show="aggregate_score"]' => __( 'Display sum of points scored across all quizzes on the course.', 'learndash-notifications' ),
		'[ld_notifications field="course" show="aggregate_points"]' => __( 'Display sum of points got across all quizzes on the course.', 'learndash-notifications' ),
		'[ld_notifications field="course" show="aggregate_total_points"]' => __( 'Display sum of total points got across all quizzes on the course.', 'learndash-notifications' ),
		'[ld_notifications field="course" show="aggregate_timespent"]' => __( 'Display sum of time spent across all quizzes on the course.', 'learndash-notifications' ),
		'[ld_notifications field="course" show="aggregate_count"]' => __( 'Display sum of correct answer counted across all quizzes on the course.', 'learndash-notifications' ),
	);

	$lesson_shortcode = array(
		'[ld_notifications field="lesson" show="title"]' => __( 'Display lesson title.', 'learndash-notifications' ),
		'[ld_notifications field="lesson" show="url"]' => __( 'Display lesson URL.', 'learndash-notifications' ),
	);

	$topic_shortcode = array(
		'[ld_notifications field="topic" show="title"]' => __( 'Display topic title.', 'learndash-notifications' ),
		'[ld_notifications field="topic" show="url"]' => __( 'Display topic URL.', 'learndash-notifications' ),
	);

	$quiz_shortcode = array(
		'[ld_notifications field="quiz" show="url"]' => __( 'Display quiz URL.', 'learndash-notifications' ),
		'[ld_notifications field="quiz" show="timestamp" format=""]' => __( 'Display time when the quiz is taken. <a href="http://php.net/manual/en/function.date.php" target="_blank">Click here</a> to see format options.', 'learndash-notifications' ),
		'[ld_notifications field="quiz" show="percentage"]' => __( 'Display correct percentage of the quiz.', 'learndash-notifications' ),
		'[ld_notifications field="quiz" show="pass"]' => __( 'Display whether the user passes the quiz or not. Display "Yes" or "No".', 'learndash-notifications' ),
		'[ld_notifications field="quiz" show="quiz_title"]' => __( 'Display quiz title.', 'learndash-notifications' ),
		'[ld_notifications field="quiz" show="course_title"]' => __( 'Display course title that quiz belongs to.', 'learndash-notifications' ),
		'[ld_notifications field="quiz" show="timespent"]' => __( 'Display how long is taken to complete the quiz.', 'learndash-notifications' ),
	);

	$essay_shortcode = array(
		'[ld_notifications field="essay" show="points_earned"]' => __( 'Display total points earned.', 'learndash-notifications' ),
		'[ld_notifications field="essay" show="points_total"]' => __( 'Display total points possible for the essay.', 'learndash-notifications' ),
	);

	$assignment_shortcode = array(
		'[ld_notifications field="assignment" show="title"]' => __( 'Display assignment title.', 'learndash-notifications' ),
		'[ld_notifications field="assignment" show="file_name"]' => __( 'Display assignment file name.', 'learndash-notifications' ),
		'[ld_notifications field="assignment" show="file_link"]' => __( 'Display assignment file link.', 'learndash-notifications' ),
		'[ld_notifications field="assignment" show="lesson_title"]' => __( 'Display lesson title that the assignment belongs to.', 'learndash-notifications' ),
		'[ld_notifications field="assignment" show="lesson_type"]' => __( 'Display lesson type that the assignment belongs to.', 'learndash-notifications' ),
	);

	$instructions = array(
		'enroll_group' => array_merge( $user_shortcode, $group_basic_shortcode ),
		'enroll_course' => array_merge( $user_shortcode, $course_basic_shortcode ),
		'complete_course' => array_merge( $user_shortcode, $course_basic_shortcode, $course_advanced_shortcode ),
		'complete_lesson' => array_merge( $user_shortcode, $course_basic_shortcode, $lesson_shortcode ),
		'lesson_available' => array_merge( $user_shortcode, $course_basic_shortcode, $lesson_shortcode ),
		'complete_topic' => array_merge( $user_shortcode, $course_basic_shortcode, $lesson_shortcode, $topic_shortcode ),
		'pass_quiz' => array_merge( $user_shortcode, $quiz_shortcode ),
		'fail_quiz' => array_merge( $user_shortcode, $quiz_shortcode ),
		'complete_quiz' => array_merge( $user_shortcode, $quiz_shortcode ),
		'upload_assignment' => array_merge( $user_shortcode, $assignment_shortcode ),
		'approve_assignment' => array_merge( $user_shortcode, $assignment_shortcode ),
		'not_logged_in' => array_merge( $user_shortcode, $course_basic_shortcode ),
		'course_expires' => array_merge( $user_shortcode, $course_basic_shortcode ),
		'course_enrollment' => array_merge( $user_shortcode, $course_basic_shortcode ),
		'essay_graded' => array_merge( $user_shortcode, $course_basic_shortcode, $lesson_shortcode, $essay_shortcode ),
	);

	return $instructions;
}
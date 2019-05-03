jQuery( document ).ready( function( $ ) {
	
var LD_Notifications = {
	init: function() {
		this.toggle_child_input();
		this.toggle_shortcode_instruction();
		this.submit_disabled_fields();
		this.ajax_get_children_list();
	},

	toggle_child_input: function() {
		if ( $( '.ld_notifications_metabox_settings' ).length > 0 ) {
			$( 'select[name="_ld_notifications_trigger"]' ).change( function( e ) {
				LD_Notifications.update_select_values();

				var option_class = $( this ).val();

				$( '.sfwd_input.' + option_class ).show();
				$( '.sfwd_input.child-input' ).not( '.' + option_class ).hide();
				$( '.sfwd_input.hide_on_' + option_class ).hide();

				if ( $.inArray( option_class, [ 'not_logged_in', 'course_expires', 'course_enrollment' ] ) != -1 ) {
					$( '.sfwd_input#delay' ).hide();
				} else {
					$( '.sfwd_input#delay' ).show();
				}
			});

			$( window ).load( function( e ) {
				LD_Notifications.update_select_values_onload();

				var option_class = $( 'select[name="_ld_notifications_trigger"]' ).val();

				$( '.sfwd_input.' + option_class ).show();
				$( '.sfwd_input.child-input' ).not( '.' + option_class ).hide();
				$( '.sfwd_input.hide-empty-select' ).hide();

				if ( $.inArray( option_class, [ 'not_logged_in', 'course_expires', 'course_enrollment' ] ) != -1 ) {
					$( '.sfwd_input#delay' ).hide();
				} else {
					$( '.sfwd_input#delay' ).show();
				}
			});
		}
	},

	toggle_shortcode_instruction: function() {
		if ( $( '.shortcodes-instruction' ).length > 0 ) {
			$( 'select[name="_ld_notifications_trigger"]' ).change( function( e ) {
				var option_class = $( this ).val();

				$( '.shortcodes-instruction.' + option_class ).show();
				$( '.shortcodes-instruction' ).not( '.' + option_class ).hide();

				if ( option_class == 'complete_course' ) {
					$( '.additional-help-text.complete_course' ).show();
				}
			});

			$( window ).load( function( e ) {
				var option_class = $( 'select[name="_ld_notifications_trigger"]' ).val();
				$( '.shortcodes-instruction.' + option_class ).show();

				if ( option_class == 'complete_course' ) {
					$( '.additional-help-text.complete_course' ).show();
				}
			});
		}
	},

	submit_disabled_fields: function() {
		$( 'form' ).on( 'submit', function() {
			$( this ).find( ':input' ).prop( 'disabled', false );
		});
	},

	ajax_get_children_list: function() {
		$( '.parent_field select' ).change( function( e ) {

			var el = $( this );
			var parent_type = '';
			var val  = $( this ).val();
			var name = $( this ).attr( 'name' );
			
			switch ( name ) {
				case '_ld_notifications_course_id':
					parent_type = 'course';
					break;
				case '_ld_notifications_lesson_id':
					parent_type = 'lesson';
					break;
				case '_ld_notifications_topic_id':
					parent_type = 'topic';
					break;
			}

			var course_id = $( 'select[name="_ld_notifications_course_id"]' ).val();

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'ld_notifications_get_children_list',
					course_id: course_id,
					parent_type: parent_type,
					parent_id: val,
					nonce: LD_Notifications_String.nonce,
				},
			})
			.done( function( data, textStatus, jqXHR ) {
				var response = data;
				response = JSON.parse( response );
				console.log( response );

				if ( el.attr( 'name' ).indexOf( 'course' ) != '-1' ) {
					$( 'select[name="_ld_notifications_topic_id"]' ).html(
						'<option>' + LD_Notifications_String.select_lesson_first + '</option>'
					);

					$( 'select[name="_ld_notifications_quiz_id"]' ).html(
						'<option>' + LD_Notifications_String.select_topic_first + '</option>'
					);

					$( 'select[name="_ld_notifications_lesson_id"]' ).html( 
						'<option>' + LD_Notifications_String.select_lesson + '</option>' +
						'<option value="all">' + LD_Notifications_String.all_lessons + '</option>'
					);

					$.each( response, function( i, val ) {
						$( 'select[name="_ld_notifications_lesson_id"]' ).append( '<option value="' + i + '">' + val + '</option>' );
					});
				}

				if ( el.attr( 'name' ).indexOf( 'lesson' ) != '-1' ) {
					$( 'select[name="_ld_notifications_quiz_id"]' ).html(
						'<option>' + LD_Notifications_String.select_topic_first + '</option>'
					);

					$( 'select[name="_ld_notifications_topic_id"]' ).html( 
						'<option>' + LD_Notifications_String.select_topic + '</option>' +
						'<option value="all">' + LD_Notifications_String.all_topics + '</option>'
					);

					$.each( response, function( i, val ) {
						$( 'select[name="_ld_notifications_topic_id"]' ).append( '<option value="' + i + '">' + val + '</option>' );
					});
				}

				if ( el.attr( 'name' ).indexOf( 'topic' ) != '-1' ) {
					$( 'select[name="_ld_notifications_quiz_id"]' ).html( 
						'<option>' + LD_Notifications_String.select_quiz + '</option>' +
						'<option value="all">' + LD_Notifications_String.all_quizzes + '</option>' 
					);

					$.each( response, function( i, val ) {
						$( 'select[name="_ld_notifications_quiz_id"]' ).append( '<option value="' + i + '">' + val + '</option>' );
					});
				}
			});
			
		});
	},

	update_select_values: function() {
		$( 'select[name="_ld_notifications_lesson_id"]' ).html( 
			'<option>' + LD_Notifications_String.select_course_first + '</option>'
		);
		
		$( 'select[name="_ld_notifications_topic_id"]' ).html(
			'<option>' + LD_Notifications_String.select_lesson_first + '</option>'
		);

		$( 'select[name="_ld_notifications_quiz_id"]' ).html(
			'<option>' + LD_Notifications_String.select_topic_first + '</option>'
		);
	},

	update_select_values_onload: function() {
		if ( $( 'select[name="_ld_notifications_course_id"]' ).val() === '' ) {
			$( 'select[name="_ld_notifications_course_id"]' ).prop( 'selectedIndex', 0 );
		}

		if ( $( 'select[name="_ld_notifications_lesson_id"]' ).val() === '' ) {
			$( 'select[name="_ld_notifications_lesson_id"]' ).html( 
				'<option>' + LD_Notifications_String.select_course_first + '</option>'
			);
		}
		
		if ( $( 'select[name="_ld_notifications_topic_id"]' ).val() === '' ) {
			$( 'select[name="_ld_notifications_topic_id"]' ).html(
				'<option>' + LD_Notifications_String.select_lesson_first + '</option>'
			);
		}

		if ( $( 'select[name="_ld_notifications_quiz_id"]' ).val() === '' ) {
			$( 'select[name="_ld_notifications_quiz_id"]' ).html(
				'<option>' + LD_Notifications_String.select_topic_first + '</option>'
			);
		}
	}
};

LD_Notifications.init();

} );
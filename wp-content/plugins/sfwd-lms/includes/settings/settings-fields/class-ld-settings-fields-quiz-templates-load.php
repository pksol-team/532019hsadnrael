<?php
/**
 * LearnDash Settings field Quiz Load Templates.
 *
 * @package LearnDash
 * @subpackage Settings
 */

if ( ( class_exists( 'LearnDash_Settings_Fields' ) ) && ( ! class_exists( 'LearnDash_Settings_Fields_Quiz_Templates_Load' ) ) ) {
	/**
	 * Class to create the settings field.
	 */
	class LearnDash_Settings_Fields_Quiz_Templates_Load extends LearnDash_Settings_Fields {

		/**
		 * Public constructor for class
		 */
		public function __construct() {
			$this->field_type = 'quiz-templates-load';

			parent::__construct();
		}

		/**
		 * Function to crete the settiings field.
		 *
		 * @since 2.4
		 *
		 * @param array $field_args An array of field arguments used to process the ouput.
		 * @return void
		 */
		public function create_section_field( $field_args = array() ) {
			$field_args = apply_filters( 'learndash_settings_field', $field_args );

			$html = apply_filters( 'learndash_settings_field_html_before', '', $field_args );

			if ( ( isset( $field_args['value'] ) ) && ( ! empty( $field_args['value'] ) ) ) {
				$template_loaded_id = absint( $field_args['value'] ) ;
			} else {
				$template_loaded_id = 0;
			}

			$select_template_options = array();
			$template_type           = '';
			if ( isset( $field_args['template_type'] ) ) {
				$template_type = $field_args['template_type'];
			} else {
				global $post_type;
				if ( learndash_get_post_type_slug( 'quiz' ) === $post_type ) {
					$template_type = WpProQuiz_Model_Template::TEMPLATE_TYPE_QUIZ;
				} elseif ( learndash_get_post_type_slug( 'question' ) === $post_type ) {
					$template_type = WpProQuiz_Model_Template::TEMPLATE_TYPE_QUESTION;
				}
			}
			if ( ( isset( $template_type ) ) && ( '' !== $template_type ) ) {
				$template_mapper = new WpProQuiz_Model_TemplateMapper();
				$templates       = $template_mapper->fetchAll( $template_type, false );
				if ( ! empty( $templates ) ) {
					foreach ( $templates as $template ) {
						$select_template_options[ absint( $template->getTemplateId() ) ] = esc_html( $template->getName() );
					}
				}
			}

			$html .= '<span class="ld-select">';
			$html .= '<select class="learndash-section-field-select" data-ld-select2="1" name="templateLoadId">';

			if ( ( isset( $_GET['post'] ) ) && ( ! empty( $_GET['post'] ) ) && ( isset( $_GET['templateLoadId'] ) ) && ( ! empty( $_GET['templateLoadId'] ) ) ) {
				$template_url = remove_query_arg( 'templateLoadId' );
				$html        .= '<option value="' . $template_url . '">' . sprintf(
					// translators: Quiz Title.
					esc_html_x( 'Revert: %s', 'placeholder: Quiz Title', 'learndash' ),
					get_the_title( $_GET['post'] )
				) . '</option>';
			} else {
				if ( ( defined( 'LEARNDASH_SELECT2_LIB' ) ) && ( true === apply_filters( 'learndash_select2_lib', LEARNDASH_SELECT2_LIB ) ) ) {
					$html .= '<option value="-1">' . 'Search or select a template…' . '</option>';
				} else {
					$html .= '<option value="">' . esc_html__( 'Select a Template to load', 'learndash' ) . '</option>';
				}
			}

			if ( ! empty( $select_template_options ) ) {
				foreach ( $select_template_options as $template_id => $template_name ) {
					if ( $template_id > 0 ) {
						$template_url = add_query_arg( 'templateLoadId', $template_id );
					} else {
						$template_url = $template_id;
					}
					$html .= '<option ' . selected( $template_loaded_id, $template_id ) . ' value="' . $template_url . '">' . $template_name . '</option>';
				}
			}

			$html .= '</select>';
			$html .= '</span><br />';
			$html .= '<input type="submit" name="templateLoad" value="' . esc_html__( 'load template', 'learndash' ) . '" class="button-primary"></p>';

			$html = apply_filters( 'learndash_settings_field_html_after', $html, $field_args );

			echo $html;
		}

		/**
		 * Default validation function. Should be overriden in Field subclass.
		 *
		 * @since 2.4
		 *
		 * @param mixed  $val Value to validate.
		 * @param string $key Key of value being validated.
		 * @param array  $args Array of field args.
		 *
		 * @return mixed $val validated value.
		 */
		public function validate_section_field( $val, $key, $args = array() ) {
			if ( ( isset( $args['field']['type'] ) ) && ( $args['field']['type'] === $this->field_type ) ) {
				if ( ! empty( $val ) ) {
					$val = wp_check_invalid_utf8( $val );
					if ( ! empty( $val ) ) {
						$val = sanitize_post_field( 'post_content', $val, 0, 'db' );
					}
				}

				return $val;
			}

			return false;
		}
	}
}
add_action(
	'learndash_settings_sections_fields_init',
	function() {
		LearnDash_Settings_Fields_Quiz_Templates_Load::add_field_instance( 'quiz-templates-load' );
	}
);

<?php
/**
 * LearnDash Settings Section for LD30 Theme Colors Metabox.
 *
 * @package LearnDash
 * @subpackage Settings
 */

if ( ( class_exists( 'LearnDash_Theme_Settings_Section' ) ) && ( ! class_exists( 'LearnDash_Settings_Theme_LD30' ) ) ) {
	/**
	 * Class to create the settings section.
	 */
	class LearnDash_Settings_Theme_LD30 extends LearnDash_Theme_Settings_Section {

		/**
		 * Protected constructor for class
		 */
		protected function __construct() {

			// The page ID (different than the screen ID).
			$this->settings_page_id = 'learndash_lms_settings';

			// This is the 'option_name' key used in the wp_options table.
			$this->setting_option_key = 'learndash_settings_theme_ld30';

			// This is the HTML form field prefix used.
			$this->setting_field_prefix = 'learndash_settings_theme_ld30';

			// Used within the Settings API to uniquely identify this section.
			$this->settings_section_key = 'settings_theme_ld30';

			// Section label/header.
			$this->settings_section_label = esc_html__( 'Theme LearnDash 3.0 Options', 'learndash' );

			// Set Associated Theme ID
			$this->settings_theme_key = 'ld30';

			parent::__construct();
		}

		/**
		 * Initialize the metabox settings values.
		 */
		public function load_settings_values() {
			parent::load_settings_values();

			if ( ! isset( $this->setting_option_values['login_logo'] ) ) {
				$this->setting_option_values['login_logo'] = 0;
			}

			if ( ! isset( $this->setting_option_values['focus_mode_enabled'] ) ) {
				$this->setting_option_values['focus_mode_enabled'] = 0;
			}

			if ( ! isset( $this->setting_option_values['login_mode_enabled'] ) ) {
				$this->setting_option_values['login_mode_enabled'] = 0;
			}

			if ( ( ! isset( $this->setting_option_values['color_primary'] ) ) || ( empty( $this->setting_option_values['color_primary'] ) ) ) {
				$this->setting_option_values['color_primary'] = '';
			}

			if ( ( ! isset( $this->setting_option_values['color_secondary'] ) ) || ( empty( $this->setting_option_values['color_secondary'] ) ) ) {
				$this->setting_option_values['color_secondary'] = '';
			}

			if ( ( ! isset( $this->setting_option_values['color_tertiary'] ) ) || ( empty( $this->setting_option_values['color_tertiary'] ) ) ) {
				$this->setting_option_values['color_tertiary'] = '';
			}
			/*
			if ( ( ! isset( $this->setting_option_values['color_4'] ) ) || ( empty( $this->setting_option_values['color_4'] ) ) ) {
				$this->setting_option_values['color_4'] = '#35e8d0';
			} */
		}

		/**
		 * Initialize the metabox settings fields.
		 */
		public function load_settings_fields() {
			$this->setting_option_fields = array(

				'color_primary'      => array(
					'name'      => 'color_primary',
					'type'      => 'colorpicker',
					'label'     => esc_html__( 'Accent Color', 'learndash' ),
					'help_text' => esc_html__( 'Main color used throughout the theme (buttons, action items, and highlights).', 'learndash' ),
					'value'     => $this->setting_option_values['color_primary'],
				),
				'color_secondary'    => array(
					'name'      => 'color_secondary',
					'type'      => 'colorpicker',
					'label'     => esc_html__( 'Progress Color', 'learndash' ),
					'help_text' => esc_html__( 'Color used for all successful progress-related items (completed items, certificates, and progress bars).', 'learndash' ),
					'value'     => $this->setting_option_values['color_secondary'],
				),
				'color_tertiary'     => array(
					'name'      => 'color_tertiary',
					'type'      => 'colorpicker',
					'label'     => esc_html__( 'Notifications, Warnings, etc...', 'learndash' ),
					'help_text' => esc_html__( 'This color is used when there are warning, important messages.', 'learndash' ),
					'value'     => $this->setting_option_values['color_tertiary'],
				),

				'focus_mode_enabled' => array(
					'name'      => 'focus_mode_enabled',
					'type'      => 'checkbox-switch',
					'label'     => esc_html__( 'Focus Mode', 'learndash' ),
					'help_text' => sprintf(
						// translators: placeholder: courses.
						esc_html_x( 'Provide a distraction-free course experience allowing users to focus on the content. This applies to ALL %s.', 'placeholder: courses.', 'learndash' ),
						learndash_get_custom_label_lower( 'courses' )
					),
					'value'     => $this->setting_option_values['focus_mode_enabled'],
					'options'   => array(
						''    => '',
						'yes' => sprintf(
							// translators: placeholder: course.
							esc_html_x( 'Distraction-free %s experience', 'placeholder: course', 'learndash' ),
							learndash_get_custom_label_lower( 'course' )
						),
					),
				),
				'login_mode_enabled' => array(
					'name'      => 'login_mode_enabled',
					'type'      => 'checkbox-switch',
					'label'     => esc_html__( 'Login & Registration', 'learndash' ),
					'help_text' => esc_html__( 'When active the LearnDash templates will be used for user login and registration pages.', 'learndash' ),
					'value'     => $this->setting_option_values['login_mode_enabled'],
					'options'   => array(
						''    => esc_html__( 'Default registration used', 'learndash' ),
						'yes' => sprintf(
							// translators: placeholder: courses.
							esc_html_x( 'Customized registration enabled for LearnDash %s', 'placeholder: courses', 'learndash' ),
							learndash_get_custom_label_lower( 'courses' )
						),
					),
				),
				'login_logo'         => array(
					'name'              => 'login_logo',
					'type'              => 'media-upload',
					'label'             => esc_html__( 'Logo Upload', 'learndash' ),
					'help_text'         => esc_html__( 'This logo will appear in the Focus Mode and LearnDash Login form when enabled. Optional.', 'learndash' ),
					'value'             => $this->setting_option_values['login_logo'],
					'validate_callback' => array( $this, 'validate_section_field_media_upload' ),
					'validate_args'     => array(
						'allow_empty' => 1,
					),
				),
			);

			$this->setting_option_fields = apply_filters( 'learndash_settings_fields', $this->setting_option_fields, $this->settings_section_key );

			parent::load_settings_fields();
		}

		/**
		 * Validate settings field.
		 *
		 * @param string $val Value to be validated.
		 * @param string $key settings fields key.
		 * @param array  $args Settings field args array.
		 *
		 * @return integer $val.
		 */
		public function validate_section_field_media_upload( $val, $key, $args = array() ) {
			// Get the digits only.
			$val = absint( $val );
			if ( ( isset( $args['field']['validate_args']['allow_empty'] ) ) && ( true == $args['field']['validate_args']['allow_empty'] ) && ( empty( $val ) ) ) {
				$val = '';
			}
			return $val;
		}

	}
}

add_action(
	'learndash_settings_sections_init',
	function() {
		//if ( learndash_is_active_theme( 'ld30' ) ) {
		LearnDash_Settings_Theme_LD30::add_section_instance();
		//}
	}
);

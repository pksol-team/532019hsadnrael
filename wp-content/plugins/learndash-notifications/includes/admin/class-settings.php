<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit();

/**
* LearnDash_Notifications_Settings class
*
* This class is responsible for managing plugin settings.
*
* @since 1.0
*/
class LearnDash_Notifications_Settings {

	/**
	 * Plugin options
	 *
	 * @since 1.0
	 * @var array
	 */
	protected $options;

	/**
	 * Class __construct function
	 *
	 * @since 1.0
	 */
	public function __construct() {

		$this->options = get_option( 'learndash_notifications_settings', array() );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		// add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_filter( 'learndash_submenu', array( $this, 'add_submenu' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu') );
		add_filter( 'learndash_admin_tabs', array( $this, 'admin_tabs' ), 10, 1 );
		add_filter( 'learndash_admin_tabs_on_page', array( $this, 'admin_tabs_on_page' ), 10, 3 );
	}

	/**
	 * Enqueue admin scripts and styles
	 */
	public function enqueue_scripts() {
		global $post_type;

		if ( ! is_admin() || 'ld-notification' != $post_type ) {
			return;
		}

		wp_enqueue_script( 'learndash_notifications_admin_scripts', LEARNDASH_NOTIFICATIONS_PLUGIN_URL . 'assets/js/admin-scripts.js', array( 'jquery' ), LEARNDASH_NOTIFICATIONS_VERSION, false );

		wp_enqueue_style( 'learndash_notifications_admin_styles', LEARNDASH_NOTIFICATIONS_PLUGIN_URL . 'assets/css/admin-styles.css', array(), LEARNDASH_NOTIFICATIONS_VERSION );

		wp_enqueue_style( 'learndash_style', plugins_url() . '/sfwd-lms/assets/css/style.css' );

		wp_enqueue_style( 'sfwd-module-style', plugins_url() . '/sfwd-lms/assets/css/sfwd_module.css' );

		wp_enqueue_script( 'sfwd-module-script', plugins_url() . '/sfwd-lms/assets/js/sfwd_module.js', array( 'jquery' ) );

		wp_localize_script( 'learndash_notifications_admin_scripts', 'LD_Notifications_String', array(
			'nonce' => wp_create_nonce( 'ld_notifications_nonce' ),
			'select_lesson' => __( '-- Select Lesson --', 'learndash-notifications' ),
			'select_topic'  => __( '-- Select Topic --', 'learndash-notifications' ),
			'select_quiz'   => __( '-- Select Quiz --', 'learndash-notifications' ),
			'select_course_first' => __( '-- Select Course First --', 'learndash-notifications' ),
			'select_lesson_first' => __( '-- Select Lesson First --', 'learndash-notifications' ),
			'select_topic_first'  => __( '-- Select Topic First --', 'learndash-notifications' ),
			'select_quiz_first'   => __( '-- Select Quiz First --', 'learndash-notifications' ),
			'all_lessons' => __( 'All Lessons', 'learndash-notifications' ),
			'all_topics'  => __( 'All Topics', 'learndash-notifications' ),
			'all_quizzes'   => __( 'All Quizzes', 'learndash-notifications' ),
		) );
		wp_localize_script( 'sfwd-module-script', 'sfwd_data', array() );
	}

	/**
	 * Register plugin settings, add initial values and define settings section and fields
	 */
	public function register_settings() {
		register_setting( 'learndash_notifications_settings_group', 'learndash_notifications_settings', array( $this, 'sanitize_settings' ) );

		$options = array(
			
		);

		add_option( 'learndash_notifications_settings', $options );

		add_settings_section( 'learndash_notifications_settings', __return_null(), __return_empty_array(), 'learndash-notifications-settings' );

		$settings = $this->get_notifications_settings();
	}

	/**
	 * Sanitize setting inputs
	 * @param  array $inputs Non-sanitized inputs
	 * @return array         Sanitized inputs
	 */
	public function sanitize_settings( $inputs ) {

		foreach ( $inputs as $key => $input ) {
			$inputs[ $key ] = sanitize_text_field( $input );
		}

		return $inputs;
	}

	/**
	 * Add submenu in LearnDash menu
	 * @param array $submenu Existing submenu
	 * @return array New submenus
	 */
	public function add_submenu( $submenu ) {
		$notification_menu = array( array(
			'name' => _x( 'Notifications', 'Menu Label', 'learndash-notifications' ),
			'cap'  => 'manage_options',
			'link' => 'edit.php?post_type=ld-notification',
		) );

		array_splice( $submenu, 6, 0, $notification_menu );
		return $submenu;
	}

	/**
	 * Add submenu page for settings page
	 */
	public function admin_menu() {
		add_submenu_page( 'edit.php?post_type=ld-notification', __( 'Notifications', 'learndash-notifications' ), __( 'Notifications','learndash-notifications'), 'manage_options', 'edit.php?post_type=ld-notification', __return_empty_array() );

		// add_submenu_page( 'learndash-lms-non-existant', __( 'Notifications', 'learndash-notifications' ), __( 'Notifications', 'learndash-notifications' ), 'manage_options', 'post-new.php?post_type=ld-notification', __return_empty_array() );
	}

	/**
	 * Add admin tabs for settings page
	 * @param  array $tabs Original tabs
	 * @return array       New modified tabs
	 */
	public function admin_tabs( $tabs ) {
		$tabs['edit-ld-notification'] = array(
			'link'      => 'edit.php?post_type=ld-notification',
			'name'      => __( 'Notifications', 'learndash-notifications' ),
			'id'        => 'edit-ld-notification',
			'menu_link' => 'edit.php?post_type=ld-notification',
		);

		$tabs['ld-notification'] = array(
			'link'      => 'post-new.php?post_type=ld-notification',
			'name'      => __( 'Add New', 'learndash-notifications' ),
			'id'        => 'ld-notification',
			'menu_link' => 'edit.php?post_type=ld-notification',
		);

		return $tabs;
	}

	/**
	 * Display active tab on settings page
	 * @param  array $admin_tabs_on_page Original active tabs
	 * @param  array $admin_tabs         Available admin tabs
	 * @param  int 	 $current_page_id    ID of current page
	 * @return array                     Currenct active tabs
	 */
	public function admin_tabs_on_page( $admin_tabs_on_page, $admin_tabs, $current_page_id ) {
		$tabs = array( 'ld-notification', 'edit-ld-notification' );

		// Add to new tab
		$admin_tabs_on_page['ld-notification'] = $tabs;
		$admin_tabs_on_page['edit-ld-notification'] = $tabs;

		return $admin_tabs_on_page;
	}

	/**
	 * Output settings page
	 */
	public function notifications_settings_page() {
		if ( ! current_user_can( 'manage_options' )	) {
			wp_die( __( 'Cheatin huh?', 'learndash-notifications' ) );
		}

		?>
		
		<?php
	}
	

	/**
	 * Define settings fields
	 * @return array Settings of the plugin
	 */
	public function get_notifications_settings() {
		
		$settings = array(
			
		);

		return apply_filters( 'learndash_notifications_settings', $settings );
	}

	/**
	 * Callback function for text type settings
	 * @param  array $args Arguments of the settings
	 */
	public function text_callback( $args ) {
		$html  = '<input type="text" name="learndash_notifications_settings[' . $args['id'] . ']" value="' . $args['value'] . '" class="regular-text">';

		echo $html;
	}

	/**
	 * Callback function for checkbox type settings
	 * @param  array $args Arguments of the settings
	 */
	public function checkbox_callback( $args ) {
		$html = '<input type="checkbox" name="learndash_notifications_settings[' . $args['id'] .']" value="1" ' . checked( $this->options[ $args['id'] ], 1, false ) . '>';

		echo $html;
	}
}

new LearnDash_Notifications_Settings();
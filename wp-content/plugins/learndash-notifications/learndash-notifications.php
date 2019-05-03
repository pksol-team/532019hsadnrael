<?php
/**
 * Plugin Name: LearnDash LMS - Notifications
 * Plugin URI: 
 * Description:	Create and send notification emails to the users. 
 * Version: 1.2.0
 * Author: LearnDash
 * Author URI: http://www.learndash.com/
 * Text Domain: learndash-notifications
 * Domain Path: languages
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

// Check if class name already exists
if ( ! class_exists( 'LearnDash_Notifications' ) ) :

/**
* Main class
*
* @since 1.0
*/
final class LearnDash_Notifications {
	
	/**
	 * The one and only true LearnDash_Notifications instance
	 *
	 * @since 1.0
	 * @access private
	 * @var object $instance
	 */
	private static $instance;

	/**
	 * Instantiate the main class
	 *
	 * This function instantiates the class, initialize all functions and return the object.
	 * 
	 * @since 1.0
	 * @return object The one and only true LearnDash_Notifications instance.
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) && ( ! self::$instance instanceof LearnDash_Notifications ) ) {

			self::$instance = new LearnDash_Notifications();
			self::$instance->setup_constants();
			
			add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );

			self::$instance->includes();
		}

		return self::$instance;
	}	

	/**
	 * Function for setting up constants
	 *
	 * This function is used to set up constants used throughout the plugin.
	 *
	 * @since 1.0
	 */
	public function setup_constants() {

		// Plugin version
		if ( ! defined( 'LEARNDASH_NOTIFICATIONS_VERSION' ) ) {
			define( 'LEARNDASH_NOTIFICATIONS_VERSION', '1.2.0' );
		}

		// Plugin file
		if ( ! defined( 'LEARNDASH_NOTIFICATIONS_FILE' ) ) {
			define( 'LEARNDASH_NOTIFICATIONS_FILE', __FILE__ );
		}		

		// Plugin folder path
		if ( ! defined( 'LEARNDASH_NOTIFICATIONS_PLUGIN_PATH' ) ) {
			define( 'LEARNDASH_NOTIFICATIONS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
		}

		// Plugin folder URL
		if ( ! defined( 'LEARNDASH_NOTIFICATIONS_PLUGIN_URL' ) ) {
			define( 'LEARNDASH_NOTIFICATIONS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}
	}

	/**
	 * Load text domain used for translation
	 *
	 * This function loads mo and po files used to translate text strings used throughout the 
	 * plugin.
	 *
	 * @since 1.0
	 */
	public function load_textdomain() {

		// Set filter for plugin language directory
		$lang_dir = dirname( plugin_basename( LEARNDASH_NOTIFICATIONS_FILE ) ) . '/languages/';
		$lang_dir = apply_filters( 'learndash_notifications_languages_directory', $lang_dir );

		// Load plugin translation file
		load_plugin_textdomain( 'learndash-notifications', false, $lang_dir );
	}

	/**
	 * Includes all necessary PHP files
	 *
	 * This function is responsible for including all necessary PHP files.
	 *
	 * @since  0.1
	 */
	public function includes() {		
		if ( is_admin() ) {
			include LEARNDASH_NOTIFICATIONS_PLUGIN_PATH . '/includes/admin/class-settings.php';
		}

		include LEARNDASH_NOTIFICATIONS_PLUGIN_PATH . '/includes/activation.php';
		include LEARNDASH_NOTIFICATIONS_PLUGIN_PATH . '/includes/cron.php';
		include LEARNDASH_NOTIFICATIONS_PLUGIN_PATH . '/includes/deactivation.php';
		include LEARNDASH_NOTIFICATIONS_PLUGIN_PATH . '/includes/database.php';
		include LEARNDASH_NOTIFICATIONS_PLUGIN_PATH . '/includes/meta-box.php';
		include LEARNDASH_NOTIFICATIONS_PLUGIN_PATH . '/includes/notification.php';
		include LEARNDASH_NOTIFICATIONS_PLUGIN_PATH . '/includes/post-type.php';
		include LEARNDASH_NOTIFICATIONS_PLUGIN_PATH . '/includes/shortcode.php';
		include LEARNDASH_NOTIFICATIONS_PLUGIN_PATH . '/includes/update.php';
		include LEARNDASH_NOTIFICATIONS_PLUGIN_PATH . '/includes/user.php';
	}
}

endif; // End if class exists check

/**
 * The main function for returning instance
 *
 * @since 1.0
 * @return object The one and only true instance.
 */
function learndash_notifications() {
	return LearnDash_Notifications::instance();
}

// Run plugin
learndash_notifications();
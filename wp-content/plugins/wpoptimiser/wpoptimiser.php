<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://wpoptimiser.com
 * @since             1.0.0
 * @package           WPOptimiser
 *
 * @wordpress-plugin
 * Plugin Name:       WP Optimiser
 * Plugin URI:        http://wpoptimiser.com
 * Description:       Optimise, diagnose & speed-up your site with our powerful site diagnostics & tuning tools such as: auto-detection of theme & plugin load speed, image/database optimization, site health check + more.
 * Version:           1.1.3
 * Author:            Cybertactics
 * Author URI:        http://cybertactics.net/
 * Text Domain:       wpoptimiser
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define('WPOPTI_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
define('WPOPTI_PLUGIN_FILE', __FILE__ );

/**
 * This value represent the current version of the layout of the database tables. This value
 * needs to be increased every time you change something in your database layout.
 * @var integer
 */
global $wp_optimiser_db_version;
$wp_optimiser_db_version = '1.0';

/**
 * If the global WP SCRIPT_DEBUG is defined then use the uncompressed versions of JS & CSS Files
 * otherwise the minimised version are used
 * @var integer
 */
global $wp_optimiser_asset_suffix;
$wp_optimiser_asset_suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

/**
 * Holds the list of page hooks created by adding the plugins menu options.
 *
 * @var integer
 */
global $wp_optimiser_page_hooks;
$wp_optimiser_page_hooks = array();

/**
 * Launch the site profiler if its been enables
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/start-site-profiler.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wpoptimiser-activator.php
 */
function activate_wpoptimiser() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpoptimiser-activator.php';
	WPOptimiser_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wpoptimiser-deactivator.php
 */
function deactivate_wpoptimiser() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpoptimiser-deactivator.php';
	WPOptimiser_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wpoptimiser' );
register_deactivation_hook( __FILE__, 'deactivate_wpoptimiser' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wpoptimiser.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wpoptimiser() {

	$plugin = new WPOptimiser();
	$plugin->run();

}
run_wpoptimiser();

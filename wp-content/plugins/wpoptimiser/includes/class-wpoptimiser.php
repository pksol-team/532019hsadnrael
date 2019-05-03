<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://wpoptimiser.com
 * @since      1.0.0
 *
 * @package    WPOptimiser
 * @subpackage WPOptimiser/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    WPOptimiser
 * @subpackage WPOptimiser/includes
 * @author     Your Name <email@example.com>
 */
class WPOptimiser {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      WPOptimiser_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'wpoptimiser';
		$this->version = '1.1.3';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_metabox_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - WPOptimiser_Loader. Orchestrates the hooks of the plugin.
	 * - WPOptimiser_i18n. Defines internationalization functionality.
	 * - WPOptimiser_Admin. Defines all hooks for the admin area.
	 * - WPOptimiser_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wpoptimiser-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wpoptimiser-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wpoptimiser-settings.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wpoptimiser-admin.php';

		/**
 		 * The class responsible for defining all actions relating to metaboxes.
 		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wpoptimiser-admin-metaboxes.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wpoptimiser-public.php';

		/**
		 * The class responsible for dealing with licensing the plugin
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/DigitalGuard_IT_Licensing_2_2.php';

		/**
		 * The class responsible for dealing with auto updates for the plugin
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/DigitalGuard_IT_Updater_1_0.php';

		/**
		 * The TinyPng API access classes
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wpoptimiser-tiny-compress-client.php';

    /**
     * The Database optimization class
     */
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wpoptimiser-db-optimizer.php';

		$this->loader = new WPOptimiser_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the WPOptimiser_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new WPOptimiser_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new WPOptimiser_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_menu' );
    $this->loader->add_action( 'wp_ajax_update_bulk_compress_stats', $plugin_admin, 'update_bulk_compress_stats' );
		$this->loader->add_filter( 'wp_generate_attachment_metadata', $plugin_admin, 'compress_image_on_upload', 10, 2);
    $this->loader->add_action( 'wp_ajax_process_bulk_image_optimization', $plugin_admin, 'process_bulk_image_optimization' );
    $this->loader->add_action( 'wp_ajax_process_db_optimization', $plugin_admin, 'process_db_optimization' );
    $this->loader->add_action( 'wp_ajax_WPOPTI_start_profiling', $plugin_admin, 'start_site_profiler' );
    $this->loader->add_action( 'wp_ajax_WPOPTI_stop_profiling', $plugin_admin, 'stop_site_profiler' );
    $this->loader->add_action( 'wp_ajax_process_image_optimization', $plugin_admin, 'process_image_optimization' );
    $this->loader->add_action( 'manage_media_custom_column', $plugin_admin, 'render_media_column', 10, 2);
    $this->loader->add_filter( 'manage_media_columns',  $plugin_admin, 'add_media_columns' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new WPOptimiser_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_head', $plugin_public, 'setup_lazy_load_filters', 9999 );
    $this->loader->add_action( 'wpoptimiser_daily_event', $plugin_public, 'do_site_check_cron', 10);

	}

  /**
	 * Register all of the hooks related to metaboxes
	 *
	 * @since 		1.0.0
	 * @access 		private
	 */
	private function define_metabox_hooks() {

		$plugin_metaboxes = new WPOptimiser_Admin_Metaboxes( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'add_meta_boxes', $plugin_metaboxes, 'add_metaboxes' );
		$this->loader->add_action( 'save_post', $plugin_metaboxes, 'validate_meta', 10, 2 );

	} // define_metabox_hooks()

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    WPOptimiser_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}

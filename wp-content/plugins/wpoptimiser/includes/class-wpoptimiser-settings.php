<?php

/**
 * The class to handle the settings for the plugin
 *
 *
 * @link       http://wpoptimiser.com
 * @since      1.0.0
 *
 * @package    WPOptimiser
 * @subpackage WPOptimiser/includes
 */

/**
 * The plugin settings class.
 *
 * This is used to deal with the settings used by the plugin, store and retrieves
 * the settings providing default where none currently set.
 *
 * @since      1.0.0
 * @package    WPOptimiser
 * @subpackage WPOptimiser/includes
 * @author     Your Name <email@example.com>
 */
class WPOptimiser_Settings {

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
	public function __construct( $plugin_name, $version ) {

			$this->plugin_name = $plugin_name;
			$this->version = $version;
	}

	/**
	 * Returns the pluign general settings
	 *
	 * @since    1.0.0
	 */
	public function get_settings() {

		$gen_settings = get_option($this->plugin_name. '-options');

		// Set defaults if needed
    if(!isset($gen_settings['retain_interval'])) $gen_settings['retain_interval'] = '0';
	  if(!isset($gen_settings['active_site_check'])) $gen_settings['active_site_check'] = 'N';

		return $gen_settings;
	}

  /**
   * Sets the general settings
   *
   * @since    1.0.0
   */
  public function set_settings($gen_settings) {
		update_option($this->plugin_name. '-options', $gen_settings);
  }

	/**
	 * Returns the lazy load Settings
	 *
	 * @since    1.0.0
	 */
	public function get_lazyload_settings() {
		$lazyload_settings = get_option($this->plugin_name. '-lazyload-options');

		if(!isset($lazyload_settings['active'])) $lazyload_settings['active'] = 'N';

		return $lazyload_settings;
	}

	/**
		* Sets the lazy load settings into the Wordpress options
		*
		* @since    1.0.0
		*/
	public function set_lazyload_settings($lazyload_settings) {
		update_option($this->plugin_name. '-lazyload-options', $lazyload_settings );
	}

	/**
	 * Returns the optimize images settings
	 *
	 * @since    1.0.0
	 */
	public function get_optimizeimgs_settings() {
		$optimizeimgs_settings = get_option($this->plugin_name. '-optimizeimgs-options');

		if(!isset($optimizeimgs_settings['active'])) $optimizeimgs_settings['active'] = 'Y';
		if(!isset($optimizeimgs_settings['tinypngkey'])) $optimizeimgs_settings['tinypngkey'] = '';
		if(!isset($optimizeimgs_settings['compimg'])) $optimizeimgs_settings['compimg'] = array();
		if(!isset($optimizeimgs_settings['compimg'][0])) $optimizeimgs_settings['compimg'][0] = 'N';

		return $optimizeimgs_settings;
	}

	/**
		* Sets the optimize images settings into the Wordpress options
		*
		* @since    1.0.0
		*/
	public function set_optimizeimgs_settings($optimizeimgs_settings) {
		update_option($this->plugin_name. '-optimizeimgs-options', $optimizeimgs_settings );
	}

}

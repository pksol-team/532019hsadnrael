<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://wpoptimiser.com
 * @since      1.0.0
 *
 * @package    WPOptimiser
 * @subpackage WPOptimiser/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WPOptimiser
 * @subpackage WPOptimiser/public
 * @author     Your Name <email@example.com>
 */
class WPOptimiser_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * An instance of the settings handler class
	 * @since    1.0.0
	 * @access   private
	 * @var      class     $settings    The current instance of the settings handler.
	 */
	private $settings;

	/**
	 * The general settings
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $settings    The current settings of this plugin.
	 */
	private $gen_settings;

	/**
	 * The lazy load settings
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $lazyload_settings    The current lazy load settings of this plugin.
	 */
	private $lazyload_settings;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->settings = new WPOptimiser_Settings( $plugin_name, $version );
		$this->gen_settings = $this->settings->get_settings();
		$this->lazyload_settings = $this->settings->get_lazyload_settings();
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		global $wp_optimiser_asset_suffix;
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in WPOptimiser_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WPOptimiser_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		// wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wpoptimiser-public' . $wp_optimiser_asset_suffix . '.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		global $wp_optimiser_asset_suffix;
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in WPOptimiser_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WPOptimiser_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		// wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wpoptimiser-public' . $wp_optimiser_asset_suffix . '.js', array( 'jquery' ), $this->version, false );

		// if( $this->lazyload_settings['active'] == 'Y' ) {
			wp_enqueue_script( $this->plugin_name . '-jquery-lazy', plugin_dir_url( __FILE__ ) . 'js/jquery.lazy' . $wp_optimiser_asset_suffix . '.js', array( 'jquery' ), $this->version, false );
      if(function_exists('wp_add_inline_script'))
			   wp_add_inline_script( $this->plugin_name . '-jquery-lazy', 'jQuery(document).ready(function(){jQuery("img[data-wpopti-lazyimg-src]").lazy({ attribute: "data-wpopti-lazyimg-src"});});' );
		// }
	}

	/**
	 * If lazy load active then setup the filters to enable the plugin to modify the content accordingly
	 *
	 * @since    1.0.0
	 */
	public function setup_lazy_load_filters() {

		// if( $this->lazyload_settings['active'] == 'N' )
		// 	return;

		add_filter( 'the_content', array($this, 'add_image_placeholders'), 99 ); // Run as late as possible so other content filters have run
		add_filter( 'post_thumbnail_html', array($this, 'add_image_placeholders'), 20);
		add_filter( 'get_avatar', array($this, 'add_image_placeholders'), 20 );
		add_filter( 'widget_text', array($this, 'add_image_placeholders'), 20 );
		add_filter( 'get_header_image_tag', array($this, 'header_image_markup'), 20, 3);
	}

	/**
	 * Scans the content for images to convert to lazy loading
	 *
	 * @since    1.0.0
	 */
	public function add_image_placeholders($content) {

		// Don't process for feeds, previews
		if( is_feed() )
			return $content;

    $lazy_load = false;
		if( $this->lazyload_settings['active'] == 'Y' ) $lazy_load = true;
    if(is_single() || is_page()) {
      $postMeta = get_post_meta(get_the_ID(), 'wpoptimiser-lazy-load', true);
      if($postMeta == 'Y') $lazy_load = true;
		  if($postMeta == 'N') $lazy_load = false;
    }

    if(!$lazy_load) return $content;

		// Don't lazy-load if the content has already been run through previously
		if ( false !== strpos( $content, 'data-wpopti-lazyimg-src' ) )
			return $content;

		// This is a pretty simple regex, but it works
		$content = preg_replace_callback( '#<(img)([^>]+?)(>(.*?)</\\1>|[\/]?>)#si', array( $this, 'process_image' ), $content );

		return $content;

	}

	public function process_image( $matches ) {

		// $placeholder_image = apply_filters( 'lazyload_images_placeholder_image', self::get_url( 'images/1x1.trans.gif' ) );

		$old_attributes_str = $matches[2];
		$old_attributes = wp_kses_hair( $old_attributes_str, wp_allowed_protocols() );

		if ( !isset( $old_attributes['src'] ) ) {
			return $matches[0];
		}

		$image_src = $old_attributes['src']['value'];

		// Remove src and lazy-src since we manually add them
		$new_attributes = $old_attributes;
		unset( $new_attributes['src'], $new_attributes['data-wpopti-lazyimg-src'] );

		// Deal with srcsets and sizes
		if ( isset( $old_attributes['srcset'] ) ) {
			$new_attributes['data-srcset'] = $old_attributes['srcset'];
			unset($new_attributes['srcset']);
		}
		if ( isset( $old_attributes['sizes'] ) ) {
			$new_attributes['data-sizes'] = $old_attributes['sizes'];
			unset($new_attributes['sizes']);
		}

		$new_attributes_str = $this->build_attributes_string( $new_attributes );

		// echo sprintf( '<pre><img data-wpopti-lazyimg-src="%1$s" %2$s><noscript>%3$s</noscript></pre>', $image_src, $new_attributes_str, $matches[0] );
		// die();
		return sprintf( '<img data-wpopti-lazyimg-src="%1$s" %2$s><noscript>%3$s</noscript>',  $image_src, $new_attributes_str, $matches[0] );
	}

	private function build_attributes_string( $attributes ) {
		$string = array();
		foreach ( $attributes as $name => $attribute ) {
			$value = $attribute['value'];
			if ( '' === $value ) {
				$string[] = sprintf( '%s', $name );
			} else {
				$string[] = sprintf( '%s="%s"', $name, esc_attr( $value ) );
			}
		}
		return implode( ' ', $string );
	}

	function header_image_markup($html, $header, $attr) {

		$html = preg_replace_callback( '#<(img)([^>]+?)(>(.*?)</\\1>|[\/]?>)#si', array( $this, 'process_image' ), $html );
		return $html;
	}

  function do_site_check_cron() {

    if($this->gen_settings['active_site_check'] != 'Y')
      return;

    include_once WPOPTI_PLUGIN_DIR . 'includes/site-check.php';

    $prevStats = get_option('wpoptimiser_last_heatlh_check', false);
    $siteCheck = new WPOptimiser_Site_Check();
    $serverStats = $siteCheck->check();

    if($prevStats === false)
      $prevStats = $serverStats;

    // Check For Alerts
    $alerts = array();
    // Has IP address Changed
    if($prevStats['user_ip'] != $serverStats['user_ip']) {
      $alerts[] = 'YOUR SERVER IP: Your Servers IP Address Has Changed: Previous IP was '.$prevStats['user_ip'].' current IP is '.$serverStats['user_ip'];
    }
    // Shell exec disabled
    if(!$serverStats['shell_exec_enabled']) {
      $alerts[] = 'PHP SHELL EXEC FUNCTION: PHP \'shell_exec\' is not enabled, this will restrict the operation of many plugins including WP Optimiser. Speak to your host to get the \'shell_exec\' function enabled. As a result some checks cannot be determined.';
    }

    $prevUptime = number_format_i18n( ( $prevStats['uptime']/60/60/24 ) );
    $uptime = number_format_i18n( ( $serverStats['uptime']/60/60/24 ) );
    if($uptime < $prevUptime) {
      $alerts[] = 'CURRENT SERVER UPTIME: Your servers uptime is currently '.$uptime.' '._n( 'day', 'days', $uptime).' and has not been up very long... if this is regular contact your host to resolve the issue.';
    }

    if($serverStats['ram_usage_pos'] > 81) {
      $alerts[] = 'REAL TIME MEMORY USAGE: Total System Memory = '.$serverStats['total_ram'].' / '.$serverStats['free_ram'].' = '.$serverStats['ram_usage_pos'].'% Usage - You are close to your servers memory Limit - You should consider upgrading your RAM (speak to your host)';
    }

    if($serverStats['memory_usage_pos'] > 81) {
      $alerts[] = 'REAL TIME PHP MEMORY USAGE: Total Available PHP Memory = '.$serverStats['memory_usage_total'].'M / '.$serverStats['memory_usage_MB'].'M = '.$serverStats['memory_usage_pos'].'% Usage- You are close to your sites PHP memory Limit - Your should increase your PHP Memory Limit setting. (speak to your host)';
    }

    if($serverStats['cpu_load'] > 81) {
      $alerts[] = 'REAL TIME CPU LOAD: '. $serverStats['cpu_count'].' CPU\'s / '.$serverStats['cpu_core_count'].' Cores - '.$serverStats['cpu_load'].'% Usage- You are close to your servers Load Limit - You should consider upgrading CPU & number of available cores (speak to your host)';
    }

    if($serverStats['disk_free_pos'] > 81) {
      $alerts[] = 'ACTUAL DISK USAGE: Total Storage = '.$siteCheck->format_filesize_kB($serverStats['disk_total_space'] / 1024).' / '.$siteCheck->format_filesize_kB($serverStats['disk_used_space'] / 1024).' - '.$serverStats['disk_free_pos'].'% Usage - You are close to your servers storage capacity - You should consider Increasing your Disk Size (speak to your host)';
    }

    if(max($serverStats['ping_times']) > 99) {
      $alerts[] = 'HOST CONNECTIVITY SPEED CHECK: Europe: bbc.co.uk - '.$serverStats['ping_times'][0].'ms, America: foxnews.com - '.$serverStats['ping_times'][1].'ms, Middle East: www.aljazeera.com - '.$serverStats['ping_times'][2].'ms, Asia: www.channelnewsasia.com - '.$serverStats['ping_times'][3].'ms - With 100ms+ ping times we strongly recommend you change host to one with a better peering agreements & move your site closer to your target audience.';
    }

    // Send email
    if(count($alerts) > 0) {
      $email = get_option('admin_email');
      $subject = "[".get_bloginfo('name')."] WP Optimiser Site Health Check Alert";
      $message = '<p style="text-align: justify;"><span style="font-size: larger; font-family: Arial;">WP Optimiser has discovered the following alerts during its daily Site Health Check monitoring:</span></p>';
      foreach ($alerts as $alert) {
        $message.= '<p style="text-align: justify;"><span style="font-size: larger;font-family: Arial;">'.$alert.'</span></p>';
      }
      $message.= '<p style="text-align: justify;"><span style="font-size: larger;font-family: Arial;">Please log into your sites admin area and access the WP Optimiser Site Health Check page for more information.</span></p>';
  		$message = wpautop( $message );
  		$headers = array('Content-Type: text/html; charset=UTF-8');

   		@wp_mail($email, $subject, $message, $headers);
    }

    // Update the last check options
    update_option('wpoptimiser_last_heatlh_check', $serverStats);
  }
}

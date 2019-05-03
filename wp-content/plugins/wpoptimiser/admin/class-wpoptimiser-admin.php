<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://wpoptimiser.com
 * @since      1.0.0
 *
 * @package    WPOptimiser
 * @subpackage WPOptimiser/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WPOptimiser
 * @subpackage WPOptimiser/admin
 * @author     Your Name <email@example.com>
 */
class WPOptimiser_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The licencing system that handles product licensing
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      DigitalGuard_IT_Licensing_2_2   $licensing   Licensing System.
	 */
	private $licensing;

	/**
	 * The plugin auto updater system that handles plugin updates
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      DigitalGuard_IT_Updater_1_0   $autoupdate   Auto Updater System.
	 */
	private $autoupdate;

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
	 * @var      string    $gen_settings    The current settings of this plugin.
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
	 * The optimize image settings
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $optimizeimgs_settings    The current optimize image settings of this plugin.
	 */
	private $optimizeimgs_settings;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;

    $this->licensing = new DigitalGuard_IT_Licensing_2_2('8JDWOPBAWBCWM', 'wpoptimiser', 'WP Optimiser');
    $this->licensing->update();
		$this->autoupdate = new DigitalGuard_IT_Updater_1_0('5234538F29CBE', WPOPTI_PLUGIN_FILE, 'wpoptimiser', 'plugin-update');

		$this->settings = new WPOptimiser_Settings( $plugin_name, $version );
    $this->gen_settings = $this->settings->get_settings();
		$this->lazyload_settings = $this->settings->get_lazyload_settings();
		$this->optimizeimgs_settings = $this->settings->get_optimizeimgs_settings();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles($hook) {
		global $wp_optimiser_asset_suffix, $wp_optimiser_page_hooks;
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
    if($hook == 'upload.php')
      wp_enqueue_style( $this->plugin_name . 'media-library', plugin_dir_url( __FILE__ ) . 'css/wpoptimiser-media-library' . $wp_optimiser_asset_suffix . '.css', array(), $this->version, 'all' );

    if(!in_array($hook, $wp_optimiser_page_hooks) )
        return;

    wp_enqueue_style( $this->plugin_name . '-easypiechart', plugin_dir_url( __FILE__ ) . 'css/jquery-easypiechart' . $wp_optimiser_asset_suffix . '.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wpoptimiser-admin' . $wp_optimiser_asset_suffix . '.css', array(), $this->version, 'all' );


	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts($hook) {
		global $wp_optimiser_asset_suffix, $wp_optimiser_page_hooks;
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
    if($hook == 'upload.php') {
      wp_enqueue_script( $this->plugin_name . '-media-library', plugin_dir_url( __FILE__ ) .'js/wpoptimiser-media-library' . $wp_optimiser_asset_suffix . '.js', array( 'jquery' ), $this->version, false );
    	wp_localize_script( $this->plugin_name . '-media-library', 'WPOptimiser_Admin', array('nonce' => wp_create_nonce( 'wpoptimiser-nonce' ) ));
    }

    if(!in_array($hook, $wp_optimiser_page_hooks) )
      return;

    wp_enqueue_script( $this->plugin_name . '-easypiechart', plugin_dir_url( __FILE__ ) . 'js/jquery.easypiechart' . $wp_optimiser_asset_suffix . '.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name . '-flot', plugin_dir_url( __FILE__ ) . 'js/jquery.flot' . $wp_optimiser_asset_suffix . '.js', array( 'jquery-ui-core' ) );
		wp_enqueue_script( $this->plugin_name . '-flot-pie', plugin_dir_url( __FILE__ ) . 'js/jquery.flot.pie' . $wp_optimiser_asset_suffix . '.js', array(  $this->plugin_name . '-flot' ) );
    wp_enqueue_script( $this->plugin_name . '-flot-tooltip', plugin_dir_url( __FILE__ ) . 'js/jquery.flot.tooltip' . $wp_optimiser_asset_suffix . '.js', array(  $this->plugin_name . '-flot-pie' ) );
    wp_enqueue_script( $this->plugin_name . '-flot-resize', plugin_dir_url( __FILE__ ) . 'js/jquery.flot.resize' . $wp_optimiser_asset_suffix . '.js', array(  $this->plugin_name . '-flot' ) );

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wpoptimiser-admin' . $wp_optimiser_asset_suffix . '.js', array( 'jquery' ), $this->version, false );

		wp_localize_script( $this->plugin_name, 'WPOptimiser_Admin', array(
			'nonce' => wp_create_nonce( 'wpoptimiser-nonce' ),
      'imgurl' => plugin_dir_url( __FILE__ ) ));
	}

	/**
	 * Adds a page links to a menu
	 *
	 * @link 		https://codex.wordpress.org/Administration_Menus
	 * @since 		1.0.0
	 * @return 		void
	 */
	public function add_menu() {
    global $wp_optimiser_page_hooks;

		$wp_optimiser_page_hooks[] = add_menu_page(__("WP Optimiser - Settings"), __("WP Optimiser"), "manage_options", $this->plugin_name . '-settings', array($this, "page_settings"), plugin_dir_url( __FILE__ ) . '/images/icon.png');

		$wp_optimiser_page_hooks[] = add_submenu_page($this->plugin_name . '-settings', __("Settings"), __("Settings"), "manage_options", $this->plugin_name . '-settings', array($this, "page_settings"));

    if($this->licensing->is_licensed()) {
  		$wp_optimiser_page_hooks[] = add_submenu_page($this->plugin_name . '-settings', __("Lazy Load Images"), __("Lazy Load Images"), "manage_options", $this->plugin_name . '-lazyload-setup', array($this, "page_lazyload_setup"));

  		$wp_optimiser_page_hooks[] = add_submenu_page($this->plugin_name . '-settings', __("Optimize Images"), __("Optimize Images"), "manage_options", $this->plugin_name . '-optimizeimgs-setup', array($this, "page_optimizeimgs_setup"));

      $wp_optimiser_page_hooks[] = add_submenu_page($this->plugin_name . '-settings', __("Bulk Optimize Images"), __("Bulk Optimize Images"), "manage_options", $this->plugin_name . '-bulk-optimizeimgs', array($this, "page_bulk_optimize_imgs"));

      $wp_optimiser_page_hooks[] = add_submenu_page($this->plugin_name . '-settings', __("Optimize Database"), __("Optimize Database"), "manage_options", $this->plugin_name . '-optimizedb-setup', array($this, "page_optimizedb_setup"));

      $wp_optimiser_page_hooks[] = add_submenu_page($this->plugin_name . '-settings', __("Site Health Check"), __("Site Health Check"), "manage_options", $this->plugin_name . '-site-check', array($this, "page_site_check"));

      $wp_optimiser_page_hooks[] = add_submenu_page($this->plugin_name . '-settings', __("Site Speed Profiler"), __("Site Speed Profiler"), "manage_options", $this->plugin_name . '-site-profiler', array($this, "page_site_profiler"));

    }

    do_action($this->plugin_name  . '_admin_menu');
	} // add_menu()

  /**
	 * The Settings Page
	 *
	 * @since 		1.0.0
	 * @return 		void
	 */
	public function page_settings() {
    global $wpdb;

		$errors = "";
    $refresh = false;

		$action = empty( $_REQUEST['action'] ) ? '' : $_REQUEST['action'];
		if($action == 'wpoptimiser_save_settings') {

      $values = isset($_REQUEST['wpoptimiser-settings-options']) ? $_REQUEST['wpoptimiser-settings-options'] : array();

      $current_key = $this->licensing->get_license_key();
      $license_key = isset($values['license_key']) ? trim($values['license_key']) : "";

      // If license key entered
      if(!empty($license_key)) {
        if($current_key != $license_key) {
          if(!empty($current_key)) $this->licensing->revoke_license($current_key);
          $this->licensing->validate_license($license_key);
          $refresh = true;
        }
      }
      else if(empty($license_key) && !empty($current_key)) {
        $this->licensing->revoke_license($current_key);
        $refresh = true;
      }
      else if(empty($license_key) && empty($current_key)) {
        $this->licensing->clear_error();
      }


      unset($values['license_key']);

			$this->gen_settings = wp_parse_args( $values, $this->gen_settings );

			$this->settings->set_settings($this->gen_settings);
		}

		$this->gen_settings = $this->settings->get_settings();

    if($refresh) {
      echo '<script>location.reload();</script>';
    	exit;
    }

		include( plugin_dir_path( __FILE__ ) . 'partials/wpoptimiser-admin-page-settings.php' );
  }

	/**
	 * The Lazy Load Images Setup Page
	 *
	 * @since 		1.0.0
	 * @return 		void
	 */
	public function page_lazyload_setup() {
		global $wpdb;

		$errors = "";
    $message = "";

		$action = empty( $_REQUEST['action'] ) ? '' : $_REQUEST['action'];
		if($action == 'wpoptimiser_save_lazyload_setup') {

			 $values = isset($_REQUEST['wpoptimiser-lazyload-options']) ? $_REQUEST['wpoptimiser-lazyload-options'] : array();

			 $values['active'] = isset($values['active']) ? $values['active'] : "N";

			 $this->lazyload_settings = wp_parse_args( $values, $this->lazyload_settings );

			 $this->settings->set_lazyload_settings($this->lazyload_settings);

       $message = "<div class='updated'>Your changes have been saved.</div>";
		}

		$this->lazyload_settings = $this->settings->get_lazyload_settings();

		include( plugin_dir_path( __FILE__ ) . 'partials/wpoptimiser-admin-page-lazy-load.php' );
	}

	/**
	 * The Optimize images Setup Page
	 *
	 * @since 		1.0.0
	 * @return 		void
	 */
	public function page_optimizeimgs_setup() {
		global $wpdb;

		$errors = "";
    $message = "";

		$action = empty( $_REQUEST['action'] ) ? '' : $_REQUEST['action'];
		if($action == 'wpoptimiser_save_optimizeimgs_setup') {

			 $values = isset($_REQUEST['wpoptimiser-optimizeimgs-options']) ? $_REQUEST['wpoptimiser-optimizeimgs-options'] : array();

			 $values['active'] = isset($values['active']) ? $values['active'] : "N";
       $values['compimg'] = isset($values['compimg']) ? $values['compimg'] : array();

       if(empty($values['tinypngkey']))  $values['active'] = 'N';

       $message = "<div class='updated'>Your changes have been saved.</div>";

			 $this->optimizeimgs_settings = wp_parse_args( $values, $this->optimizeimgs_settings );

			 $this->settings->set_optimizeimgs_settings($this->optimizeimgs_settings);
		}

		$this->optimizeimgs_settings = $this->settings->get_optimizeimgs_settings();

		$compressor = Tiny_Compress::create($this->optimizeimgs_settings['tinypngkey'], array($this, 'tiny_compress_callback' ) );
    $compressor->get_status();

		include( plugin_dir_path( __FILE__ ) . 'partials/wpoptimiser-admin-page-optimize-imgs.php' );
	}

	/**
	 * The TinyPng API callback method, used to set any account limit errors
	 *
	 * @since 		1.0.0
	 * @return 		void
	 */
	public function tiny_compress_callback( $compressor ) {
		if ( ! is_null( $count = $compressor->get_compression_count() ) ) {
			update_option( $this->plugin_name. '-optimizeimgs-compcount', $count );
		}
		if ( $compressor->limit_reached() ) {
			$link = '<a href="https://tinypng.com/developers" target="_blank">' .
				esc_html( 'TinyPNG API account' ) . '</a>';

			$message = sprintf(	esc_html(	'You have reached your limit of %s compressions this month.' ),	$count) .
				sprintf( esc_html( 'Upgrade your %s if you like to compress more images.'	), $link);

			update_option( $this->plugin_name. '-optimizeimgs-acclimit', $message);

		} else {
			delete_option( $this->plugin_name. '-optimizeimgs-acclimit');
		}
	}

	/**
	 * When an image is uploaded then we need to see if it needs compressing
	 *
	 * @since 		1.0.0
	 * @return 		void
	 */
		public function compress_image_on_upload( $metadata, $attachment_id ) {

			if($this->optimizeimgs_settings['active'] == 'N') return $metadata;

			if ( ! empty( $metadata ) ) {

				$compressor = Tiny_Compress::create($this->optimizeimgs_settings['tinypngkey'], array($this, 'tiny_compress_callback' ) );
				$tiny_image = new Tiny_Image($compressor, $this->optimizeimgs_settings, $attachment_id, $metadata );
				$result = $tiny_image->compress( );
				return $tiny_image->get_wp_metadata();
			} else {
				return $metadata;
			}
	}

  /**
	 * Compress the specified image
	 *
	 * @since 		1.0.0
	 * @return 		void
	 */
   public function compress_image( $id, $selectedSizes, $toProcess ) {

    $metadata = wp_get_attachment_metadata( $id );
    if ( ! is_array( $metadata ) ) {
      $message = 'Could not find metadata of media file.';
      echo json_encode( array( 'error' => $message ) );
      exit;
    }

    $compressor = Tiny_Compress::create($this->optimizeimgs_settings['tinypngkey'], array($this, 'tiny_compress_callback' ) );
    $tiny_image = new WPOptimiser_Tiny_Image($compressor, $selectedSizes, $id, $metadata );
    $result = $tiny_image->compress($toProcess);
    wp_update_attachment_metadata( $id, $tiny_image->get_wp_metadata() );

    if(!is_null($tiny_image->get_latest_error()))
      return $tiny_image->get_latest_error();

    return $result;
  }
  /**
   * Ajax callback for updating the Bulk Optimization Stats
   *
   * @since 		1.0.0
   * @return 		void
   */
  public function update_bulk_compress_stats() {
		if ( !check_ajax_referer( 'wpoptimiser-nonce', '_nonce', false ) ) {
			exit();
		}

    $message = 'Welcome';

    $selectedSizes = array();
    parse_str($_REQUEST['sizes'], $selectedSizes);
    $compressor = Tiny_Compress::create($this->optimizeimgs_settings['tinypngkey'] );
    $stats = Tiny_Image::get_optimization_statistics($compressor, $selectedSizes['wpoptimiser-bulkoptimizeimgs-options']);
    $compcount = get_option($this->plugin_name. '-optimizeimgs-compcount');
    $compcount = $compcount == false ? 0 : $compcount;
    $estimated_costs = Tiny_Compress::estimate_cost($stats['available-unoptimized-sizes'], $compcount);

    $stats['available_unoptimized_sizes'] = number_format($stats['available-unoptimized-sizes'], 0);
    $stats['estimated_costs'] = '$ ' . number_format( $estimated_costs, 2 );

    unset($stats['available-unoptimized-sizes']);
    unset($stats['uploaded-images']);
    unset($stats['optimized-image-sizes']);
    unset($stats['optimized-library-size']);
    unset($stats['unoptimized-library-size']);
    unset($stats['available-for-optimization']);
    unset($stats['display-percentage']);

    echo json_encode( $stats );
    exit();

  }

  /**
   * Ajax callback for updating the Bulk Optimization Stats
   *
   * @since 		1.0.0
   * @return 		void
   */
  public function page_bulk_optimize_imgs() {

    $this->optimizeimgs_settings = $this->settings->get_optimizeimgs_settings();

    $compressor = Tiny_Compress::create($this->optimizeimgs_settings['tinypngkey'], array($this, 'tiny_compress_callback' ) );
    $compressor->get_status();

    include( plugin_dir_path( __FILE__ ) . 'partials/wpoptimiser-admin-page-bulk-optimize.php' );
  }

  /**
   * Ajax callback for processing the Bulk Image Optimization Stats
   *
   * @since 		1.0.0
   * @return 		void
   */
  function process_bulk_image_optimization() {

    if ( !check_ajax_referer( 'wpoptimiser-nonce', '_nonce', false ) ) {
			exit();
		}

		$args = array('step' => 'done', 'page' => 'wpoptimiser-bulk-optimizeimgs');
		$redirect_url = add_query_arg( $args, admin_url('admin.php') );

    $form = array();
  	parse_str( $_POST['form'], $form );

  	$step  = absint( $_REQUEST['step'] );
    $total = absint( $form['wpoptimiser-bulkoptimizeimgs-options']['count'] );
    $perBatch = round(sqrt ($total), 0);

    $toProcess = ( ($step * $perBatch) - $total < 0 ) ? $perBatch : absint(($step-1) * $perBatch - $total);
    $percentage = ( ($step * $perBatch) > $total) ? 100 : ( ($step * $perBatch) / $total) * 100;

    $compressor = Tiny_Compress::create($this->optimizeimgs_settings['tinypngkey'] );
    $stats = Tiny_Image::get_optimization_statistics($compressor, $form['wpoptimiser-bulkoptimizeimgs-options']);

    if ( count( $stats['available-for-optimization']) <= 0 ) {
  		echo json_encode( array( 'step' => 'done', 'url' => $redirect_url, 'status ' => 'No Unoptimized Images' ) ); exit;
    }

    $i = 0;
    $processed = 0;
    while($processed < $toProcess && $i < count($stats['available-for-optimization'])) {
      $return = $this->compress_image($stats['available-for-optimization'][$i], $form['wpoptimiser-bulkoptimizeimgs-options'], $toProcess);
      if(!is_array($return)) {
  		    echo json_encode( array( 'step' => 'error', 'message' => $return ) ); exit;
      }
      // See How Many
      $i++;
      $processed+= $return['success'];
    }

  	if( ($step * $perBatch) < $total) {
  		$step++;
  		echo json_encode( array( 'step' => $step, 'percentage' => $percentage, 'toProcess ' => $toProcess, 'processed ' => $processed  ) ); exit;

  	} else {
  		echo json_encode( array( 'step' => 'done', 'url' => $redirect_url, 'toProcess ' => $toProcess, 'processed ' => $processed ) ); exit;
  	}
  }

  /**
   * Ajax callback for processing the Image Optimization via the media library
   *
   * @since 		1.1.1
   * @return 		void
   */
  function process_image_optimization() {

    if ( !check_ajax_referer( 'wpoptimiser-nonce', '_nonce', false ) ) {
			exit();
		}

    if ( ! current_user_can( 'upload_files' ) ) {
			$message = "You don't have permission to upload files.";
			echo $message;
			exit();
		}

		if ( empty( $_POST['id'] ) ) {
			$message = 'Not a valid media file.';
			echo $message;
			exit();
		}

		$id = intval( $_POST['id'] );
		$metadata = wp_get_attachment_metadata( $id );
		if ( ! is_array( $metadata ) ) {
			$message = 'Could not find metadata of media file.';
			echo $message;
			exit;
		}

		$compressor = Tiny_Compress::create($this->optimizeimgs_settings['tinypngkey'], array($this, 'tiny_compress_callback' ) );
		$tiny_image = new Tiny_Image($compressor, $this->optimizeimgs_settings, $id, $metadata );
		$result = $tiny_image->compress( );

		wp_update_attachment_metadata( $id, $tiny_image->get_wp_metadata() );

		echo $this->render_compress_details( $tiny_image );

		exit();
	}


  /**
   * The Optimize database Setup Page
   *
   * @since 		1.0.0
   * @return 		void
   */
  public function page_optimizedb_setup() {
    global $wpdb;

    $errors = "";

    $action = empty( $_REQUEST['action'] ) ? '' : $_REQUEST['action'];
    if($action == 'wpoptimiser_save_optimizedb_setup') {

    }

    $db_optimizer = new WPOptimiser_DB_Optimizer();
    $db_optimizer->retain_interval = $this->gen_settings['retain_interval'];
    $post_revisions_count = $db_optimizer->get_post_revision_stats();
    $post_auto_trash_count = $db_optimizer->get_auto_save_trash_stats();
    $post_comments_count = $db_optimizer->get_comments_stats();
    $orphaned_meta_data = $db_optimizer->get_orphaned_meta_data_stats();

    include( plugin_dir_path( __FILE__ ) . 'partials/wpoptimiser-admin-page-optimize-db.php' );
  }

  /**
   * Ajax callback for processing the Database Optimization actions
   *
   * @since 		1.0.0
   * @return 		void
   */
  function process_db_optimization() {
    global $wpdb;

    if ( !check_ajax_referer( 'wpoptimiser-nonce', '_nonce', false ) ) {
			exit();
		}

    $dbAction = empty( $_REQUEST['dbaction'] ) ? 'error' : $_REQUEST['dbaction'];
    $message = '';

    $db_optimizer = new WPOptimiser_DB_Optimizer();
    $db_optimizer->retain_interval = $this->gen_settings['retain_interval'];

    switch ($dbAction ) {
      case 'db-optimize':
        $result = $db_optimizer->optimize_tables();
        if($result) {
          $result = 'done';
          $db_optimizer->update();
          $replace = '.infobox';
          $replaceHTML = '<div class="infobox infobox-blue2 small">';
          $replaceHTML.= '<div class="infobox-progress">';
          $replaceHTML.= '<div class="db-efficiency" data-percent="'.$db_optimizer->efficiency_perc.'"></div>';
          $replaceHTML.= '</div>';
          $replaceHTML.= '<div class="infobox-data">';
          $replaceHTML.= '<div class="infobox-text">Size: '.$db_optimizer->format_size($db_optimizer->total_size).'</div>';
          $replaceHTML.= '<div class="infobox-text">Overhead: '.$db_optimizer->format_size($db_optimizer->total_gain).'</div>';
          $replaceHTML.= '<div class="infobox-text">Efficiency: '.number_format_i18n($db_optimizer->efficiency_perc,2).'%</div>';
          $replaceHTML.= '</div>';
          $replaceHTML.= '</div>';
        }
        else {
          $result = 'error';
          $message = $wpdb->last_result;
        }
        break;

      case 'db-post-revs':
        $result = $db_optimizer->optimize_post_revisions();
        if($result) {
          $result = 'done';
          $replace = '.infobox';
          $replaceHTML = '<div class="infobox infobox-blue">';
          $replaceHTML.= '<div class="infobox-icon">';
          $replaceHTML.= '<i class="ace-icon dashicons dashicons-slides"></i>';
          $replaceHTML.= '</div>';
          $replaceHTML.= '<div class="infobox-data">';
          $replaceHTML.= '<span class="infobox-text">Old Revisions: 0</span>';
          $replaceHTML.= '</div>';
          $replaceHTML.= '</div>';
        }
        else {
          $result = 'error';
          $message = $wpdb->last_result;
        }
        break;

      case 'db-post-optimize':
        $result = $db_optimizer->optimize_auto_save_trash_stats();
        if($result) {
          $result = 'done';
          $replace = '.infobox';
          $replaceHTML = '<div class="infobox infobox-blue2  small">';
          $replaceHTML.= '<div class="infobox-icon">';
          $replaceHTML.= '<i class="ace-icon dashicons dashicons-trash"></i>';
          $replaceHTML.= '</div>';
          $replaceHTML.= '<div class="infobox-data">';
          $replaceHTML.= '<div class="infobox-text">Auto Saves Posts: 0</div>';
          $replaceHTML.= '<div class="infobox-text">Trash Posts: 0</div>';
          $replaceHTML.= '</div>';
          $replaceHTML.= '</div>';
        }
        else {
          $result = 'error';
          $message = $wpdb->last_result;
        }
        break;

        case 'db-post-comments':
          $result = $db_optimizer->optimize_comments_stats();
          if($result) {
            $result = 'done';
            $replace = '.infobox';
            $replaceHTML = '<div class="infobox infobox-blue2  small">';
            $replaceHTML.= '<div class="infobox-icon">';
            $replaceHTML.= '<i class="ace-icon dashicons dashicons-welcome-comments"></i>';
            $replaceHTML.= '</div>';
            $replaceHTML.= '<div class="infobox-data">';
            $replaceHTML.= '<div class="infobox-text">Unapproved: 0</div>';
            $replaceHTML.= '<div class="infobox-text">Trash: 0</div>';
            $replaceHTML.= '<div class="infobox-text">Spam: 0</div>';
            $replaceHTML.= '</div>';
            $replaceHTML.= '</div>';
          }
          else {
            $result = 'error';
            $message = $wpdb->last_result;
          }
          break;

          case 'db-meta-data':
            $result = $db_optimizer->optimize_orphaned_meta_data();
            if($result) {
              $result = 'done';
              $replace = '.infobox';
              $replaceHTML = '<div class="infobox infobox-blue2  small">';
              $replaceHTML.= '<div class="infobox-icon">';
              $replaceHTML.= '<i class="ace-icon dashicons dashicons-nametag"></i>';
              $replaceHTML.= '</div>';
              $replaceHTML.= '<div class="infobox-data">';
              $replaceHTML.= '<div class="infobox-text">Post Meta Data: 0</div>';
              $replaceHTML.= '<div class="infobox-text">Comments Meta Data: 0</div>';
              $replaceHTML.= '</div>';
              $replaceHTML.= '</div>';
            }
            else {
              $result = 'error';
              $message = $wpdb->last_result;
            }
            break;

          default:
            $result = 'error';
            $message = 'Ajax Function called with incorrect parameteres';
            $replace = '';
            $replaceHTML = '';
            break;
      }

    // $download_url = add_query_arg( $args, admin_url('admin.php') );
    $response = array( 'result' => $result, 'dbaction' => $dbAction, 'replace' => $replace, 'html' => $replaceHTML, 'message' => $message);

    echo json_encode( $response ); exit;

    wp_die();
  }

	/**
	 * The Site Health Check Setup Page
	 *
	 * @since 		1.0.0
	 * @return 		void
	 */
	public function page_site_check() {
		global $wpdb;

		$errors = "";
    $message = "";

		$action = empty( $_REQUEST['action'] ) ? '' : $_REQUEST['action'];
		if($action == 'wpoptimiser_site_check_setup') {

			 $values = isset($_REQUEST['wpoptimiser-site-check-options']) ? $_REQUEST['wpoptimiser-site-check-options'] : array();

			 $values['active_site_check'] = isset($values['active_site_check']) ? $values['active_site_check'] : "N";

			 $this->gen_settings = wp_parse_args( $values, $this->gen_settings );

       $message = "<div class='updated'>Your changes have been saved.</div>";

			 $this->settings->set_settings($this->gen_settings);
		}


		include( plugin_dir_path( __FILE__ ) . 'partials/wpoptimiser-admin-page-site-check.php' );
	}

  /**
   * The Site Profiler Setup Page
   *
   * @since 		1.0.0
   * @return 		void
   */
  public function page_site_profiler() {
    global $wpdb;

    $errors = "";
    $updates = "";

    // Generate a random list of pages to scan
		$pages = array( get_home_url() ); // Home page

		// Search for a word from the blog description
		$words = array_merge( explode( ' ', get_bloginfo( 'name' ) ), explode( ' ', get_bloginfo( 'description' ) ) );
		$pages[] = home_url( '?s=' . $words[ mt_rand( 0, count( $words ) - 1 ) ] );

		// Get 4 random tags
		add_filter( 'get_terms_orderby', array($this, 'get_terms_orderby_filter'));
		$terms = get_terms(array('taxonomy' => 'post_tag','number' => 4));

		foreach ( (array) $terms as $term ) {
			$pages[] = get_term_link( $term );
		}

		// Get 4 random categories
		$cats = get_terms(array('taxonomy' => 'category', 'number' => 4));
		foreach ( (array) $cats as $cat ) {
			$pages[] = get_term_link( $cat );
		}
		remove_filter( 'get_terms_orderby', array($this, 'get_terms_orderby_filter') );

		// Get the latest 4 posts
		$tmp = preg_split( '/\s+/', wp_get_archives(array('type' => 'postbypost', 'limit' => 4, 'echo' => false) ) );
		if ( !empty( $tmp ) ) {
			foreach ( $tmp as $page ) {
				if ( preg_match( "/href='([^']+)'/", $page, $matches ) ) {
					$pages[] = $matches[1];
				}
			}
		}

		// Scan some admin pages, too
		$pages[] = admin_url();
		$pages[] = admin_url('edit.php');
		$pages[] = admin_url('plugins.php');

		// Fix SSL
		if ( true === force_ssl_admin() ) {
			foreach ( $pages as $k => $v ) {
				$pages[$k] = str_replace( 'http://', 'https://', $v );
			}
		}

    if ( strtok(phpversion(),'.') < 7) {
      include( plugin_dir_path( dirname( __FILE__ ) )  . 'includes/site-profiler-scan-parser.php');
      include( plugin_dir_path( __FILE__ ) . 'partials/wpoptimiser-admin-page-site-profiler.php' );
    }
    else {
      include( plugin_dir_path( __FILE__ ) . 'partials/wpoptimiser-admin-page-no-site-profiler.php' );
    }
  }

  function get_terms_orderby_filter() {

    return 'rand()';
  }

  /**
   * Ajax callback for starting the site profiler
   *
   * @since 		1.0.0
   * @return 		void
   */
  function start_site_profiler() {
    global $wpdb;

    if ( !check_ajax_referer( 'wpoptimiser-nonce', 'nonce', false ) ) {
      exit();
    }

		delete_option( 'wpopti_profiler_errors' );
		$opts = get_option( 'wpopti_profiler_options' );
		if( empty( $opts ) || !is_array( $opts ) ) {
			$opts = array();
		}

    // Count the number of active plugins
		$active_plugins = count( get_mu_plugins() );
		foreach ( get_plugins() as $plugin => $junk ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_plugins++;
			}
		}

		$opts['profiling_enabled'] = array(
			'ip'                   => stripslashes( $_REQUEST['ip'] ),
			'disable_opcode_cache' => false,
			'scan_time'            => time(),
      'log'                  => true,
      'active_plugins'       => $active_plugins,
		);
		update_option( 'wpopti_profiler_options', $opts );

    wp_die(1);
  }

  /**
   * Adds column to media library
   *
   * @since 		1.1.1
   * @
   */
	public function add_media_columns( $columns ) {
    if($this->licensing->is_licensed()) {
		    $columns[ 'wp_optimiser_images' ] = 'WP Optimiser';
    }
		return $columns;
	}

	public function render_media_column( $column, $id ) {
    if(!$this->licensing->is_licensed()) return;
		if ( 'wp_optimiser_images'  === $column ) {
      //$compressor = Tiny_Compress::create($this->optimizeimgs_settings['tinypngkey'], array($this, 'tiny_compress_callback' ) );
			$tiny_image = new Tiny_Image(null,  $this->optimizeimgs_settings, $id );
			if ( $tiny_image->file_type_allowed() ) {
				echo '<div class="wpotimiser-container">';
				$this->render_compress_details( $tiny_image );
				echo '</div>';
			}
		}
	}

  private function render_compress_details( $tiny_image ) {
		$in_progress = $tiny_image->filter_image_sizes( 'in_progress' );
		if ( count( $in_progress ) > 0 ) {
			include( plugin_dir_path( __FILE__ ) . 'partials/media-library-image-processing.php' );
		} else {
			include( plugin_dir_path( __FILE__ ) . 'partials/media-library-image-details.php' );
		}
	}

  /**
   * Ajax callback for stopping the site profiler
   *
   * @since 		1.0.0
   * @return 		void
   */
  function stop_site_profiler() {
    global $wpdb;

    if ( !check_ajax_referer( 'wpoptimiser-nonce', 'nonce', false ) ) {
      exit();
    }

    delete_option( 'wpopti_profiler_errors' );
    delete_option( 'wpopti_profiler_options' );

    wp_die(1);
  }

}

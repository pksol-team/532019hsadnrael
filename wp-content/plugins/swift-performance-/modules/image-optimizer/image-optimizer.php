<?php
class Swift_Performance_Image_Optimizer {

	public $api_url;

	public $api_key;

	public $upload_dir;

	public $original_size = 0;

	public $compressed_size = 0;

	public $localize = array();

	/**
	 * Create Image Optimizer Object
	 */
	public function __construct(){
		do_action('swift_performance_image_optimizer_before_init');

		$this->upload_dir = wp_upload_dir();

		$this->db_version = '1.0';

		if (!defined('SWIFT_PERFORMANCE_SITE_ROOT_DIR')){
			define('SWIFT_PERFORMANCE_SITE_ROOT_DIR', trailingslashit(dirname(WP_CONTENT_DIR)));
		}

		// Constants
		if (!defined('SWIFT_PERFORMANCE_IMAGE_TABLE')){
			global $wpdb;
			if (defined('SWIFT_PERFORMANCE_TABLE_PREFIX')){
				define('SWIFT_PERFORMANCE_IMAGE_TABLE', SWIFT_PERFORMANCE_TABLE_PREFIX . 'image_optimizer');
			}
			else {
				define('SWIFT_PERFORMANCE_IMAGE_TABLE', $wpdb->prefix . 'swift_image_optimizer');
			}
		}

		if (!defined('SWIFT_PERFORMANCE_IMAGE_OPTIMIZER_DIR')){
			define ('SWIFT_PERFORMANCE_IMAGE_OPTIMIZER_DIR', SWIFT_PERFORMANCE_DIR . 'modules/image-optimizer/');
		}

		if (!defined('SWIFT_PERFORMANCE_IMAGE_OPTIMIZER_URI')){
			define ('SWIFT_PERFORMANCE_IMAGE_OPTIMIZER_URI', SWIFT_PERFORMANCE_URI . 'modules/image-optimizer/');
		}

		if (!defined('SWIFT_PERFORMANCE_IMAGE_OPTIMIZER_DB_VER')){
			define ('SWIFT_PERFORMANCE_IMAGE_OPTIMIZER_DB_VER', '1.0');
		}


		// Init optimizer
		add_action('init', array($this, 'init'),11);

		// Optimize uploaded images
		add_action('wp_handle_upload', array($this, 'handle_upload'), 10);
		add_action('image_make_intermediate_size', array($this, 'handle_upload'), 10);

		// Create hook for scheduled optimization
		add_action('swift_performance_process_optimize_image_queue', array($this, 'process_queue'));

		// Create hook for load images
		add_action('swift_performance_load_images', array('Swift_Performance_Image_Optimizer', 'load_images'));

		// Remove backed up original images on delete file
		add_filter('wp_delete_file', array($this, 'remove_original_on_delete'));

		do_action('swift_performance_image_optimizer_init');
	}


	/**
	 * Set API URL, purchase code, create admin menu
	 */
	public function init(){
		if (is_admin() || (defined('DOING_CRON') && DOING_CRON)){
			// Set API URL
			$this->api_url = 'http://io.swteplugins.com/swift-optimizer/';

			// Set purchase key
			$this->api_key = Swift_Performance::get_option('purchase-key');

			// Localization
			$this->localize = array(
				'ajax_url'	=> admin_url('admin-ajax.php'),
				'nonce'	=> wp_create_nonce('swift-optimize-images'),
				'i18n'	=> array(
					'Preparing...' => esc_html__('Preparing...', 'swift-performance'),
					'Done' => esc_html__('Done', 'swift-performance'),
					'Restart' => esc_html__('Restart', 'swift-performance'),
					'Running' => esc_html__('Running', 'swift-performance'),
					'Progress:' => esc_html__('Progress:', 'swift-performance'),
					'Compression ratio:' => esc_html__('Compression ratio:', 'swift-performance'),
					'All of your images are already optimized' => esc_html__('All of your images are already optimized', 'swift-performance'),
				)
			);

			// Enqueue assets
			add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));

			// Ajax handler
			if (!empty($this->api_key)){
				add_action('wp_ajax_swift_performance_image_optimizer', array($this, 'ajax_handler'));
				add_action('wp_ajax_swift_performance_restore_original_image', array($this, 'ajax_handler'));
			}
		}
	}


	/**
	 * Ajax handler
	 */
	public function ajax_handler(){
		global $wpdb;
		if (!current_user_can('manage_options') || !isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'swift-optimize-images')){
			return;
		}
		$action = isset($_POST['swift_performance_action']) ? $_POST['swift_performance_action'] : '';
		$images = array('count' => 0);

		switch ($action){
			case 'load_images':
				self::load_images();
				wp_send_json(1);
				break;
			case 'clear_queue':
				$wpdb->query("UPDATE " . SWIFT_PERFORMANCE_IMAGE_TABLE . " SET status = 0 WHERE status IN (2,3)");
				Swift_Performance::set_transient('swift_performance_image_optimizer_pid', 'stop', 600);
				wp_send_json(1);
				break;
			case 'quickview':
				$image 		= $wpdb->get_row($wpdb->prepare("SELECT * FROM " . SWIFT_PERFORMANCE_IMAGE_TABLE . " WHERE hash = %s", $_REQUEST['hash']), ARRAY_A);
				if (file_exists(SWIFT_PERFORMANCE_SITE_ROOT_DIR . $image['file'])){
					$basename		= basename($image['file']);
					$image_src		= Swift_Performance::home_url() . $image['file'];
					$original_src	= (file_exists(SWIFT_PERFORMANCE_SITE_ROOT_DIR . $image['file'] . '.swift-original') ? Swift_Performance::home_url() . $image['file'] . '.swift-original' : '');
					$original_size	= Swift_Performance_Image_Optimizer::formatted_size($image['original']);
					$dimensions		= $image['width'] . '&times;' . $image['height'];
					$quality		= $image['quality'];
					$optimized_size	= ($image['status'] == 1 ? Swift_Performance_Image_Optimizer::formatted_size(filesize(SWIFT_PERFORMANCE_SITE_ROOT_DIR . $image['file'])) : '');
					$cache_buster	= md5_file(SWIFT_PERFORMANCE_SITE_ROOT_DIR . $image['file']);
					include_once SWIFT_PERFORMANCE_IMAGE_OPTIMIZER_DIR . 'templates/modal.tpl.php';
				}
				die;
				break;
			case 'optimize_bulk':
				$settings = json_encode(array(
					'jpeg-quality' 		=> $_REQUEST['jpeg-quality'],
					'png-quality' 		=> $_REQUEST['png-quality'],
					'keep-original-images'	=> $_REQUEST['keep-original-images'],
					'maximum-image-width'	=> ($_REQUEST['resize-large-images'] == 1 ? max(1, $_REQUEST['maximum-image-width']) : '')
				));

				$images = isset($_POST['images']) ? $_POST['images'] : array();
				$hash_in = array();
				foreach ((array)$images as $image){
					$hash_in[] = '"'.esc_sql($image).'"';
				}
				if (!empty($hash_in)){
					$wpdb->query($wpdb->prepare("UPDATE " . SWIFT_PERFORMANCE_IMAGE_TABLE . " SET status = 2, settings = %s WHERE hash IN (" . implode(',', $hash_in) . ") AND status != -1", $settings));
				}
				else {
					$wpdb->query($wpdb->prepare("UPDATE " . SWIFT_PERFORMANCE_IMAGE_TABLE . " SET status = 2, settings = %s WHERE status != -1", $settings));
				}
				$this->schedule();
				wp_send_json(array(
					array('target' => '.column-status span', 'action' => 'hide'),
					array('target' => '.column-status .queued', 'action' => 'show')
				));
				break;
			case 'restore_bulk':
			case 'remove_bulk':
				$images = isset($_POST['images']) ? $_POST['images'] : array();
				if (empty($images)){
					$images = $wpdb->get_col("SELECT hash FROM " . SWIFT_PERFORMANCE_IMAGE_TABLE);
				}
				foreach ((array)$images as $hash){
					if ($action == 'restore_bulk'){
						$this->restore_original($hash);
					}
					if ($action == 'remove_bulk'){
						$this->remove_original($hash);
					}
				}
				wp_send_json(1);
				break;
			case 'restore_single':
				$hash	= isset($_POST['hash']) ? $_POST['hash'] : '';

				$this->restore_original($hash);
				wp_send_json(array(
					array('target' => '.single-restore', 'action' => 'hide'),
					array('target' => '.remove-original', 'action' => 'hide'),
					array('target' => '.single-optimize', 'action' => 'show'),
					array('target' => '.column-status span', 'action' => 'hide'),
					array('target' => '.column-status .not-optimized', 'action' => 'show')
				));
				break;
			case 'remove_original':
				$hash	= isset($_POST['hash']) ? $_POST['hash'] : '';

				$this->remove_original($hash);
				wp_send_json(array(
					array('target' => '.single-restore', 'action' => 'hide'),
					array('target' => '.remove-original', 'action' => 'hide'),
				));
				break;
			case 'image_stat':
				$images = $wpdb->get_results("SELECT hash, file, type, width, height, original, quality, status FROM " . SWIFT_PERFORMANCE_IMAGE_TABLE, ARRAY_A);
				$stat = array(
					'total'           => count($images),
					'optimized'       => 0,
					'original_size'   => 0,
					'current_size'    => 0,
					'queued'          => 0,
				);

				foreach ($images as $image){
					if (!file_exists(SWIFT_PERFORMANCE_SITE_ROOT_DIR . $image['file'])){
						$wpdb->query($wpdb->prepare("DELETE FROM " . SWIFT_PERFORMANCE_IMAGE_TABLE . " WHERE file = %s", $image['file']));
						continue;
					}

					// Get size
					$size = filesize(SWIFT_PERFORMANCE_SITE_ROOT_DIR . $image['file']);

					// Statistics
					$stat['optimized']      += ($image['status'] == 1 ? 1 : 0);
					$stat['queued']   	+= (in_array($image['status'], array(2,3)) ? 1 : 0);
					$stat['original_size']  += (int)$image['original'];
					$stat['current_size']   += (int)$size;
				}

				$stat['formatted_original_size']	= Swift_Performance_Image_Optimizer::formatted_size($stat['original_size']);
				$stat['formatted_current_size']	= Swift_Performance_Image_Optimizer::formatted_size($stat['current_size']);
				$stat['formatted_save']			= Swift_Performance_Image_Optimizer::formatted_size(max(0, ($stat['original_size'] - $stat['current_size'])));
				wp_send_json($stat);
				break;
		}
	}

	/**
	 * Call API
	 * @param string $function
	 * @param array $args
	 */
	public function api($args = array()){
		$response = wp_remote_post (
			$this->api_url, array(
					'timeout' => 300,
					'sslverify' => false,
					'user-agent' => 'SwiftPerformance',
					'headers' => array (
							'X-ENVATO-PURCHASE-KEY' => trim ($this->api_key)
					),
					'body' => array (
							'site' => Swift_Performance::home_url(),
							'args' => $args
					)
			)
		);

		if (is_wp_error($response)){
			Swift_Performance::log('API connection error: ' . $response->get_error_message(), 1);
		}
		else if ($response['response']['code'] == 403){
			Swift_Performance::log('API connection error: IP address is blocked');
			return array('status' => 'error');
		}
		else{
			return json_decode($response['body'], true);
		}
	}

	/**
	 * Process image optimizer queue
	 */
	public function process_queue(){
		global $wpdb;

		$retry = false;

		// Set current process id
		$current_process = mt_rand(0,PHP_INT_MAX);
		Swift_Performance::set_transient('swift_performance_image_optimizer_pid', $current_process, 600);
		Swift_Performance::log('Process image optimizer queue ('.$current_process.') start', 9);

		// Get images
		$images = $wpdb->get_results("SELECT hash, file, type, quality, status, settings FROM " . SWIFT_PERFORMANCE_IMAGE_TABLE . " WHERE status IN (2,3) ", ARRAY_A);

		// Prevent timeout, process should run again.
		if (!empty($images)){
			$this->schedule(60);
			Swift_Performance::log('Process image optimizer queue rescheduled, ' . count($images) . ' images left', 9);
		}

		// Process
		foreach ((array)$images as $image){
			// Force single thread
			$process = $wpdb->get_var("SELECT option_value FROM {$wpdb->options} WHERE option_name = '_transient_swift_performance_image_optimizer_pid'");
			if ($process !== false && $process != $current_process){
				Swift_Performance::log('Process image optimizer queue ('.$current_process.') stop', 9);
				return;
			}

			$hash		= $image['hash'];
			$settings	= json_decode($image['settings'], true);

			// Remove file if not exists
			if (!file_exists(SWIFT_PERFORMANCE_SITE_ROOT_DIR . $image['file'])){
				$wpdb->query($wpdb->prepare("DELETE FROM " . SWIFT_PERFORMANCE_IMAGE_TABLE . " WHERE file = %s", $image['file']));
				continue;
			}

			// Skip file if it isn't writable
			if (!is_writable(SWIFT_PERFORMANCE_SITE_ROOT_DIR . $image['file'])){
				$wpdb->query($wpdb->prepare("UPDATE " . SWIFT_PERFORMANCE_IMAGE_TABLE . " SET status = 0 WHERE hash = %s LIMIT 1", $hash));
				continue;
			}

			// Resize
			if (!empty($settings['maximum-image-width'])){
				self::resize($hash, SWIFT_PERFORMANCE_SITE_ROOT_DIR . $image['file'], $settings['maximum-image-width'], $settings['keep-original-images']);
			}

			// Maximum file size
			if (filesize(SWIFT_PERFORMANCE_SITE_ROOT_DIR . $image['file']) > 10*1024*1024){
				Swift_Performance::log(SWIFT_PERFORMANCE_SITE_ROOT_DIR . $image['file'] . ' not optimized. File exceeded maximum size.', 6);
				$wpdb->query($wpdb->prepare("UPDATE " . SWIFT_PERFORMANCE_IMAGE_TABLE . " SET status = 0 WHERE hash = %s LIMIT 1", $hash));
				continue;
			}

			// Skip if quality already worse than excepted and original not exists
			$type = ($image['type'] == 3 ? 'png' : 'jpeg');
			if (!empty($image['quality']) && $settings[$type . '-quality'] > $image['quality']){
				// Re optimize if original is exists
				if (file_exists(SWIFT_PERFORMANCE_SITE_ROOT_DIR . $image['file'] . '.swift-original')){
					$this->restore_original($image['hash']);
				}
				else {
					// Already optimized
					$wpdb->query($wpdb->prepare("UPDATE " . SWIFT_PERFORMANCE_IMAGE_TABLE . " SET status = 1 WHERE hash = %s LIMIT 1", $hash));
					continue;
				}
			}
			else if (!empty($image['quality']) && $settings[$type . '-quality'] == $image['quality']){
				// Already optimized
				$wpdb->query($wpdb->prepare("UPDATE " . SWIFT_PERFORMANCE_IMAGE_TABLE . " SET status = 1 WHERE hash = %s LIMIT 1", $hash));
				continue;
			}


			// Optimize
			$upload = ($image['status'] == 2 ? true : false);
			try {
				$response = $this->process(SWIFT_PERFORMANCE_SITE_ROOT_DIR . $image['file'], $settings[$type . '-quality'], $settings['keep-original-images'], $upload);
			}
			catch (Exception $e) {
				Swift_Performance::log('Image Optimizer exception occurred: '. $e->getMessage(), 1);
				//Skip the file
				$wpdb->query($wpdb->prepare("UPDATE " . SWIFT_PERFORMANCE_IMAGE_TABLE . " SET status = 0 WHERE hash = %s LIMIT 1", $hash));
				continue;
			}

			// Handle response
			switch ($response['status']) {
				// Try later if API is busy
				case 'busy':
					Swift_Performance::log('Image optimization API is busy. Wait ' . esc_attr($response['should_wait']) .'s...', 9);
					$this->schedule($response['should_wait']);
					return;
					break;
				// Log API error
				case 'error':
					Swift_Performance::log('API error: ' . $response['message'], 6);
					$wpdb->query($wpdb->prepare("UPDATE " . SWIFT_PERFORMANCE_IMAGE_TABLE . " SET status = 0 WHERE hash = %s LIMIT 1", $hash));
					break;
				// Image queued, we should check results later (status=3)
				case 'queued':
					Swift_Performance::log('Image optimization queued. Wait 120s...', 9);
					$wpdb->query($wpdb->prepare("UPDATE " . SWIFT_PERFORMANCE_IMAGE_TABLE . " SET status = 3 WHERE hash = %s LIMIT 1", $hash));
					$retry = $response['should_wait'];
					break;
				// Something went wrong, maybe next time
				case 'failed':
					$wpdb->query($wpdb->prepare("UPDATE " . SWIFT_PERFORMANCE_IMAGE_TABLE . " SET status = 0 WHERE hash = %s LIMIT 1", $hash));
					break;
				// Optimized file expired on server
				case 'gone':
					Swift_Performance::log('Optimized image was expired on server. Wait 120s and try again.', 9);
					$wpdb->query($wpdb->prepare("UPDATE " . SWIFT_PERFORMANCE_IMAGE_TABLE . " SET status = 2 WHERE hash = %s LIMIT 1", $hash));
					$retry = 120;
					break;
				// YAY
				case 'success':
					$wpdb->query($wpdb->prepare("UPDATE " . SWIFT_PERFORMANCE_IMAGE_TABLE . " SET quality = %d, status = 1 WHERE hash = %s LIMIT 1", $response['quality'], $hash));
					break;
			}
		}

		// Retry again later
		if ($retry !== false){
			Swift_Performance::log('Process image optimizer queue rescheduled', 9);
			$this->schedule($retry);
		}
		Swift_Performance::log('Process image optimizer queue ('.$current_process.') finished', 9);
	}

	/**
	 * Compress the image using API
	 * @param string $file file path
	 */
	public function process($file, $quality, $keep_original, $upload){
		// Increase time limit
		Swift_Performance::set_time_limit(120, 'image_optimizer_process');

		if (!function_exists('WP_Filesystem')){
			require_once(ABSPATH . 'wp-admin/includes/file.php');
		}
		global $wp_filesystem, $wpdb;
		WP_Filesystem();

		// Set variables
		$data	= $wp_filesystem->get_contents($file);

		// Build request
		$request = array(
				'quality'	=> $quality,
				'hash'	=> md5($data)
		);

		if ($upload){
			$request['data'] = $data;
		}

		// Compress file
		$response = $this->api($request);

		if ($response['status'] == 'success'){
			$new_image = base64_decode($response['source']);

			// Create temporary file for checkings
			$test_img = $this->upload_dir['basedir'] . '/test-image_' . mt_rand(0,PHP_INT_MAX);
			$wp_filesystem->put_contents($test_img, $new_image);

			// Check the resized image
			@$check = getimagesize($test_img);

			// If image seems ok overwrite the original image
			if ($check !== false && isset($check[0]) && $check[0] > 0){
				if (filesize($file) > filesize($test_img)){
					if ($keep_original == 1){
						$wp_filesystem->copy($file, $file . '.swift-original');
					}

					$wp_filesystem->put_contents($file, $new_image);
					Swift_Performance::log('Image optimized. File: ' . $file, 9);

					$image_url = str_replace(SWIFT_PERFORMANCE_SITE_ROOT_DIR, Swift_Performance::home_url(), $file);

					// Clear Cloudflare
	                        if(Swift_Performance::check_option('cloudflare-auto-purge',1) && Swift_Performance::check_option('cloudflare-email', '', '!=') && Swift_Performance::check_option('cloudflare-api-key', '', '!=')){
	                              Swift_Performance_Cache::purge_cloudflare_zones($image_url);
	                        }

					// Clear varnish
					if(Swift_Performance::check_option('varnish-auto-purge',1)){
						Swift_Performance_Cache::purge_varnish_url($image_url, true);
					}
				}
				else {
					Swift_Performance::log('Image couldn\'t be optimized: image already optimized. File: ' . $file . ' original: ' . filesize($file) . ' compressed: ' . filesize($test_img) , 9);
				}
			}
			else {
				if ($check === false){
					$response['status'] = 'failed';
					Swift_Performance::log('Image optimized failed. File: ' . $file, 9);
				}
				else if (empty($check[0])){
					$response['status'] = 'failed';
					Swift_Performance::log('Image optimized failed: image corrupted. File: ' . $file, 9);
				}
			}

			// Remove temporary file
			$wp_filesystem->delete($test_img);
		}

		return $response;

	}

	/**
	 * Restore original image
	 * @param string $hash
	 */
	public function restore_original($hash){
		global $wpdb;
		$file = $wpdb->get_var($wpdb->prepare("SELECT file FROM " . SWIFT_PERFORMANCE_IMAGE_TABLE . " WHERE hash = %s", $hash));

		if (file_exists(SWIFT_PERFORMANCE_SITE_ROOT_DIR . $file . '.swift-original')){
			@rename(SWIFT_PERFORMANCE_SITE_ROOT_DIR . $file . '.swift-original', SWIFT_PERFORMANCE_SITE_ROOT_DIR . $file);
			$wpdb->query("UPDATE " . SWIFT_PERFORMANCE_IMAGE_TABLE . " SET quality = '', status = 0 WHERE hash = '{$hash}' LIMIT 1");
		}
	}

	/**
	 * Remove original image
	 * @param string $hash
	 */
	public function remove_original($hash){
		global $wpdb;
		$file = $wpdb->get_var($wpdb->prepare("SELECT file FROM " . SWIFT_PERFORMANCE_IMAGE_TABLE . " WHERE hash = %s", $hash));

		if (file_exists(SWIFT_PERFORMANCE_SITE_ROOT_DIR . $file . '.swift-original')){
			@unlink(SWIFT_PERFORMANCE_SITE_ROOT_DIR . $file . '.swift-original');
		}
	}

	/**
	 * Regenerate thumbnails
	 * @param int $id
	 */
	public function regenerate_thumbnails($id){
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, get_attached_file($id) ) );
		Swift_Performance::log('Regenerate thumbnails ID: ' . $id, 9);
	}

	/**
	 * Optimize images on upload
	 * @param string|array $file
	 * @return $mixed
	 */
	public function handle_upload($upload){
		global $wpdb;
		self::db_install();

		$file		= (is_array($upload) ? $upload['file'] : $upload );
		$status	= (!empty($this->api_key) && Swift_Performance::check_option('optimize-uploaded-images', 1) ? 2 : 0);

		$image = str_replace(SWIFT_PERFORMANCE_SITE_ROOT_DIR, '', $file);
		@list($width, $height, $type,,) = getimagesize($file);
		$original = filesize($file);

		// Register only valid images
		if (empty($width)){
			return $upload;
		}

		if (isset($_REQUEST['swift_performance_action'])){
			$settings = json_encode(array(
				'jpeg-quality' 		=> $_REQUEST['jpeg-quality'],
				'png-quality' 		=> $_REQUEST['png-quality'],
				'keep-original-images'	=> $_REQUEST['keep-original-images'],
				'maximum-image-width'	=> ($_REQUEST['resize-large-images'] == 1 ? max(1, $_REQUEST['maximum-image-width']) : '')
			));
		}
		else {
			$settings = json_encode(array(
				'jpeg-quality'		=> Swift_Performance::get_option('jpeg-quality'),
				'png-quality'		=> Swift_Performance::get_option('png-quality'),
				'keep-original-images'	=> Swift_Performance::get_option('keep-original-images'),
				'maximum-image-width'	=> (Swift_Performance::check_option('resize-large-images', 1) ? max(1, Swift_Performance::get_option('maximum-image-width')) : '')
			));
		}

		$values = '("'.md5($image).'", "' . esc_sql($image) . '", "'.(int)$width.'", "'.(int)$height.'", "'.esc_sql($type).'", "'.(int)$original.'", \''.$settings.'\', '.(int)$status.')';
		$wpdb->query("INSERT IGNORE INTO " . SWIFT_PERFORMANCE_IMAGE_TABLE . " (hash, file, width, height, type, original, settings, status) VALUES " . $values);

		if ($status == 2){
			$this->schedule();
			Swift_Performance::log('Image Optimize scheduled: ' . $file, 9);
		}
		return $upload;
	}

	/**
	 * Remove backed up original files
	 * @param string $file
	 */
	public function remove_original_on_delete($file){
		global $wpdb;
		self::db_install();

		if (file_exists($file . 'swift-original')){
			@unlink($file . 'swift-original');
		}

		$wpdb->query($wpdb->prepare("DELETE FROM " . SWIFT_PERFORMANCE_IMAGE_TABLE . " WHERE file = %s", str_replace(SWIFT_PERFORMANCE_SITE_ROOT_DIR, '', $file)));

		return $file;
	}

	/**
	 * Resize image
	 * @param string $hash
	 * @param string $file
	 * @param int $max_width
	 */
	public static function resize($hash, $file, $max_width, $keep_original){
		@list($width,$height,,,) = getimagesize($file);
		if ($width < $max_width){
			Swift_Performance::log('Image resize skipped (width:'.$width.' < '.$max_width.')' . $file, 9);
			// already smaller we don't need to do anything
			return;
		}

		$image = wp_get_image_editor($file);

		if (!is_wp_error($image)) {
			global $wp_filesystem, $wpdb;
			if ($keep_original == 1){
				if (!function_exists('WP_Filesystem')){
					require_once(ABSPATH . 'wp-admin/includes/file.php');
				}
				WP_Filesystem();
				$wp_filesystem->copy($file, $file . '.swift-original');
			}

		    	$image->resize($max_width, null, false);
		    	$image->save($file);

			// Get new sizes
			@list($width, $height, $type,,) = getimagesize($file);

			// Update meta data for media library elements
			$upload_dir = wp_upload_dir();
			if (strpos($file, $upload_dir['basedir']) !== false){

				$id = Swift_Performance::get_image_id(str_replace(trailingslashit($upload_dir['basedir']), '', $file));
				$meta = wp_get_attachment_metadata($id);
				$meta['width'] = $width;
				$meta['height'] = $height;
				wp_update_attachment_metadata($id, $meta);
			}

			$wpdb->query($wpdb->prepare("UPDATE " . SWIFT_PERFORMANCE_IMAGE_TABLE . " SET width = %d, height = %d WHERE hash = %s", $width, $height, $hash));
			Swift_Performance::log('Image resized: ' . $file, 9);
		}
		else {
			Swift_Performance::log('Image resize failed ' . $file . ' Error:' . $image->get_error_message(), 1);
		}
	}

	/**
	 * Schedule process queue (if it wasn't schedule before)
	 * @param int $delay
	 */
	public function schedule($delay = 0){
		wp_schedule_single_event(time() + $delay, 'swift_performance_process_optimize_image_queue');
	}

	/**
	 * Reschedule process queue if stucked
	 */
	public function should_reschedule(){
		global $wpdb;
		$images = $wpdb->get_results("SELECT hash, file, type, quality, status, settings FROM " . SWIFT_PERFORMANCE_IMAGE_TABLE . " WHERE status IN (2,3) ", ARRAY_A);
		if (count($images) > 0){
			$process = get_transient('swift_performance_image_optimizer_pid');
			if ($process === false && !wp_next_scheduled('swift_performance_process_optimize_image_queue')){
				$this->schedule();
			}
		}
	}

	/**
	 * Create menu
	 */
	public function admin_menu(){
		add_submenu_page( 'upload.php', 'Image Optimizer', 'Image Optimizer','manage_options', 'swift-performance-optimize-images', array($this, 'dashboard'));
	}


	/**
	 * Display dashboard
	 */
	public function dashboard() {
		self::db_install();
		$this->should_reschedule();
		include_once 'templates/dashboard.tpl.php';
	}

	/**
	 * Enqueue assets
	 */
	public function enqueue_assets(){
		global $pagenow;
		if ( $pagenow == 'upload.php' || ($pagenow == 'post.php' && isset($_GET['action']) && $_GET['action'] == 'edit') || ($pagenow == 'tools.php' && isset($_GET['subpage']) && $_GET['subpage'] == 'image-optimizer')){
			wp_enqueue_script('swift-performance-image-optimizer', SWIFT_PERFORMANCE_IMAGE_OPTIMIZER_URI . 'js/optimizer.js', array('jquery'), SWIFT_PERFORMANCE_VER, true );
			wp_localize_script('swift-performance-image-optimizer', 'swift_performance_image_optimizer', $this->localize);

			wp_enqueue_style('swift-performance-image-optimizer', SWIFT_PERFORMANCE_IMAGE_OPTIMIZER_URI . 'css/admin.css',  array(), SWIFT_PERFORMANCE_VER);
			wp_enqueue_style('media-views');
		}
	}

	/**
	 * Collect and load images into image_optimizer table
	 */
	public static function load_images(){
		global $wpdb;
		$images = array();

		// Run only one thread
		if (get_transient('swift_image_optimizer_load_images') !== false){
			return;
		}

		// Extend timeout
		$timeout = Swift_Performance::set_time_limit(300, 'image_optimizer_load_images');
		set_transient('swift_image_optimizer_load_images', true, $timeout);

		if (is_multisite()){
			$upload_dir = wp_upload_dir();
			$content_dir = $upload_dir['basedir'];
		}
		else {
			$content_dir = WP_CONTENT_DIR;
		}

		$Directory = new RecursiveDirectoryIterator($content_dir);
		$Iterator = new RecursiveIteratorIterator($Directory);
		$Regex = new RegexIterator($Iterator, '/\.(jpe?g|png)$/i', RecursiveRegexIterator::GET_MATCH);
		$index = 0;

		$max_allowed_packet		= $wpdb->get_row("SHOW VARIABLES LIKE 'max_allowed_packet'", ARRAY_A);
		$max_allowed_packet_size	= (isset($max_allowed_packet['Value']) && !empty($max_allowed_packet['Value']) ? $max_allowed_packet['Value']*0.9 : 1024*970);

		foreach(apply_filters('swift-performance-image-optimizer-files', $Regex) as $filename=>$file){
			$image = str_replace(SWIFT_PERFORMANCE_SITE_ROOT_DIR, '', $filename);
			@list($width, $height, $type,,) = getimagesize($filename);
			$original = filesize($filename);

			// Build blocks
			$values = '("'.md5($image).'", "' . esc_sql($image) . '", "'.(int)$width.'", "'.(int)$height.'", "'.esc_sql($type).'", "'.(int)$original.'" ),';
			if (!isset($images[$index])){
				$images[$index] = '';
			}
			// Next block
			if(strlen($images[$index] . $values) > max($max_allowed_packet_size, 1024*970)){
				$index++;
			}
			$images[$index] .= $values;
		}
		foreach ($images as $value) {
			$value = trim($value, ',') . ';';
			Swift_Performance::mysql_query("INSERT IGNORE INTO " . SWIFT_PERFORMANCE_IMAGE_TABLE . " (hash, file, width, height, type, original) VALUES " . $value);
		}

		delete_transient('swift_image_optimizer_load_images');
	}

	/**
	 * Format size
	 * @param int $size (bytes)
	 * @return string
	 */
	public static function formatted_size($size){
		if ($size > 1024*1024*1024){
                  return number_format($size/1024/1024/1024, 2, '.', ' ') . 'Gb';
            }
            else if ($size > 1024*1024){
                  return number_format($size/1024/1024, 2, '.', ' ') . 'Mb';
            }
            elseif ($size > 1024){
                  return number_format($size/1024, 2, '.', ' ') . 'Kb';
            }
            else {
                  return number_format($size, 0, '.', ' ') . ' bytes';
            }
      }

	/**
	 * Create DB for the plugin
	 */
	public static function db_install(){
		global $wpdb;

		$table_name = SWIFT_PERFORMANCE_IMAGE_TABLE;
		$sql = "CREATE TABLE {$table_name} (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`hash` varchar(32) NOT NULL,
			`file` varchar(500) NOT NULL,
			`width` int(5) NOT NULL,
			`height` int(5) NOT NULL,
			`type` int(2) NOT NULL,
			`original` int(10) NOT NULL,
			`quality` varchar(3) NOT NULL,
			`settings` varchar(200) NOT NULL,
			`status` int(1) NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY hash (hash)
		);";

		$current_db_version = get_option(SWIFT_PERFORMANCE_TABLE_PREFIX . 'image_optimizer_db_version');
		if (empty($current_db_version)){
			Swift_Performance::mysql_query($sql);
			update_option( SWIFT_PERFORMANCE_TABLE_PREFIX . "image_optimizer_db_version", SWIFT_PERFORMANCE_IMAGE_OPTIMIZER_DB_VER );
		}
		else if ($current_db_version !== SWIFT_PERFORMANCE_IMAGE_OPTIMIZER_DB_VER){
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );

			if (!wp_next_scheduled('swift_performance_load_images')){
				wp_schedule_single_event(time(), 'swift_performance_load_images');
			}
			update_option( SWIFT_PERFORMANCE_TABLE_PREFIX . "image_optimizer_db_version", SWIFT_PERFORMANCE_IMAGE_OPTIMIZER_DB_VER );
		}
	}

	/**
	 * Clean tables, scheduled hooks and options, on deactivation
	 */
	public static function uninstall(){
		global $wpdb;

		$settings = get_option('swift-performance-deactivation-settings');
		if (!isset($settings['keep-image-optimizer-table']) || empty($settings['keep-image-optimizer-table'])){
			if (!defined('SWIFT_PERFORMANCE_IMAGE_TABLE')){
				if (defined('SWIFT_PERFORMANCE_TABLE_PREFIX')){
					define('SWIFT_PERFORMANCE_IMAGE_TABLE', $wpdb->prefix . 'swift_performance_image_optimizer');
				}
				else {
					define('SWIFT_PERFORMANCE_IMAGE_TABLE', $wpdb->prefix . 'swift_image_optimizer');
				}
			}

			// Multisite
			if (is_multisite()){
				if (defined('SWIFT_PERFORMANCE_TABLE_PREFIX')){
					$wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . 'swift_performance_image_optimizer');
				}
				else {
					$wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . 'swift_image_optimizer');
				}
			}
			else {
				$wpdb->query("DROP TABLE IF EXISTS " . SWIFT_PERFORMANCE_IMAGE_TABLE);
			}

			delete_option(SWIFT_PERFORMANCE_TABLE_PREFIX . "image_optimizer_db_version");
		}

		wp_clear_scheduled_hook('swift_performance_process_optimize_image_queue');
	}

}
return new Swift_Performance_Image_Optimizer();
?>

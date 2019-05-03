<?php
class Swift_Performance_Cache {

      // Current content
      public $buffer;

      // Current cache path (relative to cache folder)
      public $path;

      // Is dynamic or static page
      public $is_dynamic_cache = false;

      // No cache mode
      public $no_cache = false;

      // Run time disable caching
      public $disabled_cache = false;

      // Contain Headers
      public $headers = array();

      public function __construct(){
            do_action('swift_performance_cache_before_init');

            $device = (Swift_Performance::check_option('mobile-support', 1) && Swift_Performance::is_mobile() ? 'mobile' : 'desktop');

            // Prefix
            $prefix = apply_filters('swift_performance_cache_folder_prefix', '');

            // Force cache to check js errors
            if (isset($_GET['force-cache'])){
                  $_COOKIE = $_REQUEST = $_POST = $_GET = array();
                  Swift_Performance::set_option('optimize-prebuild-only',0);
                  Swift_Performance::set_option('merge-background-only',0);

                  // Check timeout and disable time consuming features if timeout is not extendable
                  $timeout = get_option('swift_performance_timeout');
                  if (empty($timeout)){
                        Swift_Performance::update_option('critical-css', 0);
                        Swift_Performance::update_option('merge-styles-exclude-3rd-party', 1);
                        Swift_Performance::update_option('merge-scripts-exlude-3rd-party', 1);
                  }
                  else {
                        Swift_Performance::set_option('critical-css',0);
                  }

                  add_filter('swift_performance_is_cacheable', '__return_true');
                  add_action('wp_head', function(){
                        echo '<script data-dont-merge>window.addEventListener("error", function(){if(parent){parent.postMessage("report-js-error", "*");}});</script>';
                  },6);
            }

            // Logged in path
            if (!isset($_GET['force-cached']) && isset($_COOKIE[LOGGED_IN_COOKIE]) && Swift_Performance::check_option('shared-logged-in-cache', '1', '!=')){
                  @list($user_login,,,) = explode('|', $_COOKIE[LOGGED_IN_COOKIE]);
                  $hash = md5($user_login . NONCE_SALT);

                  $this->path = trailingslashit(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) . '/' . $device . '/authenticated/' . $hash . '/');
            }
            // Not logged in path
            else {
                  $this->path = trailingslashit(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) . '/' . (!empty($prefix) ? '@prefix/' . trailingslashit(sanitize_title($prefix)) : '') . $device . '/unauthenticated/');
            }

            // Case insensitive caching
            if (Swift_Performance::check_option('cache-case-insensitive', 1)){
                  $this->path = strtolower($this->path);
            }

            // Disable caching if shared logged in cache is set
            add_action('init', function(){
                  if (is_user_logged_in() && Swift_Performance::check_option('shared-logged-in-cache', '1') && !is_admin()){
                        define('SWIFT_PERFORMANCE_DISABLE_CACHE', true);
                  }
            }, PHP_INT_MAX);

            // Clear cache on post save
            add_action('save_post', array('Swift_Performance_Cache', 'clear_post_cache'));
            add_action('delete_post', array('Swift_Performance_Cache', 'clear_post_cache'));
            add_action('wp_trash_post', array('Swift_Performance_Cache', 'clear_post_cache'));
            add_action('pre_post_update', array('Swift_Performance_Cache', 'clear_post_cache'));
            add_action('delete_attachment', array('Swift_Performance_Cache', 'clear_post_cache'));
            add_action('woocommerce_product_object_updated_props', array('Swift_Performance_Cache', 'clear_post_cache'));
            add_action('woocommerce_product_set_stock', array('Swift_Performance_Cache', 'clear_post_cache'));
            add_action('woocommerce_variation_set_stock', array('Swift_Performance_Cache', 'clear_post_cache'));
            add_action('fl_builder_after_save_layout', array('Swift_Performance_Cache', 'clear_post_cache'));

            // Clear pagecache after publish/update post
            add_action('save_post', array('Swift_Performance_Cache', 'clear_cache_after_post'));
            add_action('delete_post', array('Swift_Performance_Cache', 'clear_cache_after_post'));
            add_action('wp_trash_post', array('Swift_Performance_Cache', 'clear_cache_after_post'));
            add_action('delete_attachment', array('Swift_Performance_Cache', 'clear_cache_after_post'));
            add_action('woocommerce_product_object_updated_props', array('Swift_Performance_Cache', 'clear_cache_after_post'));
            add_action('woocommerce_product_set_stock', array('Swift_Performance_Cache', 'clear_cache_after_post'));
            add_action('woocommerce_variation_set_stock', array('Swift_Performance_Cache', 'clear_cache_after_post'));
            add_action('fl_builder_after_save_layout', array('Swift_Performance_Cache', 'clear_cache_after_post'));

            // Elementor AJAX update
            add_action('elementor/ajax/register_actions', function(){
                  Swift_Performance_Cache::clear_post_cache($_REQUEST['editor_post_id']);
            });

            // Scheduled posts
            add_action('transition_post_status', function($new, $old = '', $post = NULL){
                  if (!empty($post)){
                        Swift_Performance_Cache::clear_post_cache($post->ID);
                        Swift_Performance_Cache::clear_cache_after_post();
                  }
            });

            // Clear all cache actions
            foreach (array('after_switch_theme','customize_save_after','update_option_permalink_structure','update_option_tag_base','update_option_category_base','wp_update_nav_menu', 'update_option_sidebars_widgets') as $action){
                  add_action($action, array('Swift_Performance_Cache', 'clear_all_cache'));
            }

            // Clear cache on plugin/update actions
            add_action('activated_plugin', function($plugin){
                  if ($plugin !== basename(SWIFT_PERFORMANCE_DIR) . '/performance.php'){
                        $action     = esc_html__('A plugin has been activated. Page cache should be cleared.', 'swift-performance');
                        ob_start();
                        include SWIFT_PERFORMANCE_DIR . 'templates/clear-cache-notice.php';
                        Swift_Performance::add_notice(ob_get_clean(), 'warning', 'plugin/update-action');
                  }
            });
            add_action('deactivated_plugin', function($plugin){
                  if ($plugin !== basename(SWIFT_PERFORMANCE_DIR) . '/performance.php'){
                        $action     = esc_html__('A plugin has been activated. Page cache should be cleared.', 'swift-performance');
                        ob_start();
                        include SWIFT_PERFORMANCE_DIR . 'templates/clear-cache-notice.php';
                        Swift_Performance::add_notice(ob_get_clean(), 'warning', 'plugin/update-action');
                  }
            });
            add_action('upgrader_process_complete', function(){
                  $action     = esc_html__('Updater process has been finished. Page cache probably should be cleared.', 'swift-performance');
                  ob_start();
                  include SWIFT_PERFORMANCE_DIR . 'templates/clear-cache-notice.php';
                  Swift_Performance::add_notice(ob_get_clean(), 'warning', 'plugin/update-action');
            });

            // Clear cache after comment approved
            add_action('wp_set_comment_status', function($comment_id, $status){
                  if (in_array($status, array('approve', 'hold'))){
                       $comment = get_comment( $comment_id );
                       Swift_Performance_Cache::clear_post_cache($comment->comment_post_ID);
                  }
            }, 10, 2);

            // Clear intelligent cache based on request (Legacy)
            if (Swift_Performance::check_option('cache-expiry-mode', 'intelligent') && Swift_Performance::check_option('resource-saving-mode', 1)){
                  add_action('shutdown', array($this, 'clear_intelligent_cache'));
            }

            // Clear user cache on delete user
            add_action( 'delete_user', array('Swift_Performance_Cache', 'clear_user_cache'));

            // Discover mode
            add_action('shutdown', array($this, 'discover'));

            // Bypass Avatar
            if (Swift_Performance::check_option('gravatar-cache', 1)){
                  add_filter('get_avatar_url', array($this, 'bypass_gravatar'));
            }

            // Cache WooCommerce empty minicart
            if (Swift_Performance::check_option('cache-empty-minicart', 1) && isset($_GET['wc-ajax']) && $_GET['wc-ajax'] == 'get_refreshed_fragments' && (!isset($_COOKIE['woocommerce_cart_hash']) || empty($_COOKIE['woocommerce_cart_hash'])) && (!isset($_COOKIE['woocommerce_items_in_cart']) || empty($_COOKIE['woocommerce_items_in_cart']))){
                  Swift_Performance::set_option('optimize-prebuild-only', 0);
                  Swift_Performance::set_option('merge-background-only', 0);
                  add_filter('swift_performance_is_cacheable_dynamic', '__return_true');

                  $timestamp = gmdate("D, d M Y H:i:s") . " GMT";
                  header("Expires: $timestamp");
                  header("Last-Modified: $timestamp");
                  header("Pragma: no-cache");
                  header("Cache-Control: no-cache, must-revalidate");
            }

            // Clear post cache on user action
            add_action('init', array('Swift_Performance_Cache', 'clear_on_user_action'));

            // Force no cache mode
            if (isset($_GET['swift-no-cache'])){
                  $this->no_cache = true;
                  unset($_GET['swift-no-cache']);
                  unset($_REQUEST['swift-no-cache']);
                  $_SERVER['REQUEST_URI'] = preg_replace('~(&|\?)swift-no-cache=(\d+)~','',$_SERVER['REQUEST_URI']);
            }

            // Developer mode
            if (Swift_Performance::is_developer_mode_active()){
                  foreach (array('swift_performance_is_cacheable', 'swift_performance_is_cacheable_dynamic', 'swift_performance_is_cacheable_ajax') as $filter){
                        add_filter($filter, '__return_false', 100);
                  }
            }

            // Load cache
            if (!$this->no_cache){
                  $this->load_cache();
            }

            // Set cache
            if (self::is_cacheable() || self::is_cacheable_dynamic()){
                  $this->is_dynamic_cache = (self::is_cacheable_dynamic() ? true : false);

                  ob_start(array($this, 'build_cache'));
                  add_action('shutdown', array($this, 'set_cache'), apply_filters('swift_performance_set_cache_hook_priority',PHP_INT_MAX));
            }
            else if (self::is_cacheable_ajax()){
				ob_start(array($this, 'build_cache'));
				add_action('shutdown', array($this, 'set_ajax_cache'), apply_filters('swift_performance_set_cache_hook_priority',PHP_INT_MAX));
            }

            // Print intelligent cache js in head
            if (Swift_Performance::check_option('cache-expiry-mode', 'intelligent') && (self::is_cacheable() || self::is_cacheable_dynamic())){
                  add_action('wp_head', array('Swift_Performance_Cache', 'intelligent_cache_xhr'), PHP_INT_MAX);
            }

            // Keep original headers
            if (Swift_Performance::check_option('keep-original-headers',1)){
                  add_filter('wp_headers', function($headers){
                        $this->headers = apply_filters('swift_performance_kept_headers', headers_list());
                  }, PHP_INT_MAX);
            }

            do_action('swift_performance_cache_init');
      }

      /**
       * Catch the content
       */
      public function build_cache($buffer){
            $this->buffer = $buffer;

            if (Swift_Performance::check_option('cache-expiry-mode', 'intelligent') && $this->no_cache){
                  $this->check_integrity(self::avoid_mixed_content($this->buffer));
            }

            return $buffer;
      }


      /**
       * Save current page to cache
       */
      public function set_cache(){
            global $wpdb;

            // Return on CLI
            if (defined('WP_CLI') && WP_CLI){
                  return;
            }

            do_action('swift_performance_before_set_cache', $this->buffer);

            // Don't write cache if there isn't free thread for optimizing assets
            if ($this->disabled_cache || (defined('SWIFT_PERFORMANCE_DISABLE_CACHE') && SWIFT_PERFORMANCE_DISABLE_CACHE)){
                  return;
            }

            // We have wp_query, so we re-check is it cacheable
            if (!self::is_cacheable() && !self::is_cacheable_dynamic()){
                  return;
            }

            // Don't write cache for common request if background assets merging enabled
            if (Swift_Performance::check_option('merge-background-only', 1) && !isset($_SERVER['HTTP_X_MERGE_ASSETS'])){
                  return;
            }

            // Remove background loading from cache
            if (Swift_Performance::check_option('merge-background-only', 1) && Swift_Performance::check_option('cache-expiry-mode', array('timebased', 'actionbased'), 'IN') ){
                  $this->buffer = str_replace("<script data-dont-merge>var xhr = new XMLHttpRequest();xhr.open('GET', document.location.href);xhr.setRequestHeader('X-merge-assets', 'true');xhr.send(null);</script>", '', $this->buffer);
            }

            $this->buffer = self::avoid_mixed_content($this->buffer);

            // Fix output buffer conflict
            if (strpos($this->buffer, '<!--SWIFT_PERFORMACE_OB_CONFLICT-->') !== false){
                  $this->buffer = $GLOBALS['swift_performance']->modules['asset-manager']->asset_manager_callback($this->buffer);
            }

            // Appcache
            $device = Swift_Performance::is_mobile() ? '-mobile' : '-desktop';
            if (Swift_Performance::check_option('appcache'.$device, 1)){
                  $this->buffer = preg_replace('~<html~', '<html manifest="'.Swift_Performance::home_url().SWIFT_PERFORMANCE_SLUG.'.appcache"', $this->buffer);
            }


            // Set charset
            if (!Swift_Performance::is_amp($this->buffer) && apply_filters('swift_performance_character_encoding_meta', true)){
                  // Remove charset meta if exists
                  $this->buffer = preg_replace('~<meta charset([^>]+)>~', '', $this->buffer);

                  // Append charset to the top
                  $this->buffer = preg_replace('~<head([^>]*)?>~',"<head$1>\n<meta charset=\"".get_bloginfo('charset')."\">", $this->buffer, 1);
            }

            // Ajaxify
            $ajaxify = apply_filters('swift_performance_ajaxify', Swift_Performance::get_option('ajaxify'));
            if (!empty($ajaxify)){
                  ob_start();
                  include SWIFT_PERFORMANCE_DIR . 'modules/cache/ajaxify.php';
                  $ajaxify_html = ob_get_clean();
                  $this->buffer = str_replace('</body>',"{$ajaxify_html}\n</body>", $this->buffer);
            }

            if (Swift_Performance::check_option('whitelabel', 1, '!=')){
                  $this->buffer = str_replace('</body>',"<!--Cached with ".SWIFT_PERFORMANCE_PLUGIN_NAME."-->\n</body>", $this->buffer);
            }

            $this->buffer = apply_filters('swift_performance_buffer', $this->buffer);

            $content_type = '';
            foreach (headers_list() as $header) {
                  if (preg_match('~Content-type:\s?([a-z]*)/([a-z]*)~i', $header, $matches)){
                        $content_type = $matches[1].'/'.$matches[2];
                  }
            }

            // Disk cache
            if (Swift_Performance::check_option('caching-mode', array('disk_cache_rewrite', 'disk_cache_php'), 'IN')){
                  // Set cached file basename
                  if (Swift_Performance::is_404()){
                        $basename = '404.html';
                        $type = '404';
                  }
                  else if ($content_type == 'text/xml' || Swift_Performance::is_feed()){
                        $basename = 'index.xml';
                        $type = 'xml';
                  }
                  else if ($content_type == 'application/json' || (defined('REST_REQUEST') && REST_REQUEST)){
                        $basename = 'index.json';
                        $type = 'json';
                  }
                  else {
                        $basename = 'index.html';
                        $type = 'html';
                  }

                  // Dynamic cache
                  if ($this->is_dynamic_cache){
                        set_transient('swift_performance_dynamic_' . Swift_Performance::get_unique_id(), array('time' => time(), 'basename' => $basename, 'content' => $this->buffer), 3600);
                        Swift_Performance::log('Dynamic cache (db) ' . $_SERVER['REQUEST_URI'] . serialize($_REQUEST), 9);
                  }
                  // General cache
                  else {
                        self::write_file($this->path . $basename, $this->buffer, true);
                        if (Swift_Performance::check_option('enable-gzip', 1) && function_exists('gzencode')){
                              self::write_file($this->path . $basename . '.gz', gzencode($this->buffer), true);
                        }

                        if (Swift_Performance::check_option('keep-original-headers', 1)){
                              foreach ((array)$this->headers as $header) {
                                    preg_match('~([^:]+):\s(.*)~', $header, $matches);
                                    $headers[$matches[1]] = $matches[2];
                              }

                              // Remove content encoding, cookies and Swift Performance status
                              unset($headers['Content-Encoding']);
                              unset($headers[SWIFT_PERFORMANCE_SLUG]);
                              unset($headers['Set-Cookie']);

                              self::write_file($this->path . 'header.json', json_encode($headers), true);
                              if (Swift_Performance::server_software() == 'apache'){
                                    $header_htaccess = '';
                                    foreach ((array)$headers as $key => $value) {
                                          $header_htaccess .= "Header add {$key} \"$value\"\n";
                                    }
                                    if (!empty($header_htaccess)){
                                          $header_htaccess = "<ifModule mod_headers.c>\n{$header_htaccess}</iFmodule>";
                                          self::write_file($this->path . '.htaccess', $header_htaccess, true);
                                    }
                              }
                        }

                        // Update warmup table
                        $id = Swift_Performance::get_warmup_id($_SERVER['HTTP_HOST'] . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
                        $priority = Swift_Performance::get_default_warmup_priority();
                        $url = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                        $timestamp = time();

                        Swift_Performance::mysql_query("INSERT IGNORE INTO " . SWIFT_PERFORMANCE_TABLE_PREFIX . "warmup (id, url, timestamp, type, priority) VALUES ('".Swift_Performance::get_warmup_id($url)."', '".esc_url($url)."', '".$timestamp."', '".$type."', ".(int)$priority.") ON DUPLICATE KEY UPDATE timestamp = '{$timestamp}', type = '{$type}'");

                        Swift_Performance::log('General cache (file) ' . $this->path . $basename, 9);
                  }
            }
            // Memcached
            else if(Swift_Performance::check_option('caching-mode', 'memcached_php')){
                  // Set cached file basename
                  if (Swift_Performance::is_404()){
                        $basename = '404.html';
                        $type = '404';
                  }
                  else if ($content_type == 'text/xml' || Swift_Performance::is_feed()){
                        $basename = 'index.xml';
                        $type = 'xml';
                  }
                  else if ($content_type == 'application/json' || (defined('REST_REQUEST') && REST_REQUEST)){
                        $basename = 'index.json';
                        $type = 'json';
                  }
                  else {
                        $basename = 'index.html';
                        $type = 'html';
                  }

                  // Dynamic cache
                  if ($this->is_dynamic_cache){
                        Swift_Performance_Cache::memcached_set(Swift_Performance::get_unique_id(), array('time' => time(), 'basename' => $basename, 'content' => $this->buffer));
                        Swift_Performance::log('Dynamic cache (memcached) ' . $_SERVER['REQUEST_URI'] . serialize($_REQUEST), 9);
                  }
                  // General cache
                  else {
                        Swift_Performance_Cache::memcached_set($this->path . $basename, array('time' => time(), 'content' => $this->buffer));
                        if (Swift_Performance::check_option('enable-gzip', 1) && function_exists('gzencode')){
                              Swift_Performance_Cache::memcached_set($this->path . $basename . '.gz', array('time' => time(), 'content' => gzencode($this->buffer)));
                        }

                        // Update warmup table
                        $id = Swift_Performance::get_warmup_id($_SERVER['HTTP_HOST'] . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
                        $priority = Swift_Performance::get_default_warmup_priority();
                        $url = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                        $timestamp = time();

                        Swift_Performance::mysql_query("INSERT IGNORE INTO " . SWIFT_PERFORMANCE_TABLE_PREFIX . "warmup (id, url, timestamp, type, priority) VALUES ('".Swift_Performance::get_warmup_id($url)."', '".esc_url($url)."', '".$timestamp."', '".$type."', ".(int)$priority.") ON DUPLICATE KEY UPDATE timestamp = '{$timestamp}', type = '{$type}'");

                        Swift_Performance::log('General cache (memcached) ' . $this->path . $basename, 9);
                  }
            }
      }

      /**
       * Set AJAX object transient
       */
      public function set_ajax_cache(){
            // Set AJAX object expiry
            $expiry = Swift_Performance::get_option('ajax-cache-expiry-time');

            // Store AJAX object in db if we are using disk cache
            if (Swift_Performance::check_option('caching-mode', array('disk_cache_rewrite', 'disk_cache_php'), 'IN')){
                  set_transient('swift_performance_ajax_' . Swift_Performance::get_unique_id(), array('time' => time(), 'content' => $this->buffer), $expiry);
                  Swift_Performance::log('Ajax cache (db) ' . $_SERVER['REQUEST_URI'] . serialize($_REQUEST), 9);
            }
            // Memcached
            else {
                  Swift_Performance_Cache::memcached_set(Swift_Performance::get_unique_id(), array('time' => time(), 'content' => $this->buffer));
                  Swift_Performance::log('Ajax cache (memcached) ' . $_SERVER['REQUEST_URI'] . serialize($_REQUEST), 9);
            }

		return $this->buffer;
      }



      /**
       * Load cached file and stop if file exists
       */
      public function load_cache(){
            // Return on CLI
            if (defined('WP_CLI') && WP_CLI){
                  return;
            }

            // Set default content type
            $content_type  = 'text/html';

            // Add Swift Performance header
            if (!Swift_Performance::is_admin() && Swift_Performance::check_option('whitelabel', 1, '!=')){
                  header(SWIFT_PERFORMANCE_SLUG . ': MISS');
            }

            // Serve cached dynamic & AJAX requests
            if (self::is_cacheable_dynamic() || self::is_cacheable_ajax()){
                  if (Swift_Performance::check_option('caching-mode', 'memcached_php')){
                        $cached_request = Swift_Performance_Cache::memcached_get(Swift_Performance::get_unique_id());
                  }
                  else {
                        if (self::is_cacheable_dynamic()){
                              $cached_request = get_transient('swift_performance_dynamic_' . Swift_Performance::get_unique_id());
                        }
                        else {
                              $cached_request = get_transient('swift_performance_ajax_' . Swift_Performance::get_unique_id());
                        }
                  }

                  if ($cached_request !== false){
                        if (Swift_Performance::check_option('whitelabel', 1, '!=')){
                              header(SWIFT_PERFORMANCE_SLUG . ': HIT');
                        }

                        if (isset($cached_request['basename'])){
                              switch ($cached_request['basename']) {
                                    case 'index.xml':
                                          $content_type = 'text/xml';
                                          break;
                                    case 'index.json':
                                          $content_type = 'text/json';
                                          break;
                              }
                        }

                        $content                = $cached_request['content'];
                        $last_modified_time     = $cached_request['time'];
                        $etag                   = md5($content);

                        // HTTP2 server push
                        if (Swift_Performance::check_option('server-push',1)){
                              self::server_push_headers($content);
                        }

                        // Send headers
                        if (Swift_Performance::check_option('304-header', 1) && (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified_time || @trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag)){
                              header("HTTP/1.1 304 Not Modified");
                        }

                        if (Swift_Performance::check_option('proxy-cache',1)){
                              header('Cache-Control: "s-maxage=' . Swift_Performance::get_option('proxy-cache-maxage') . ', max-age=0, public, must-revalidate"');
                        }
                        header("Last-Modified: ".gmdate("D, d M Y H:i:s", $last_modified_time)." GMT");
                        header("Etag: $etag");
                        header("Content-type: $content_type");
                        echo $content;

                        die;
                  }
            }

            // Skip cache if SWIFT_PERFORMANCE_DISABLE_CACHE is defined
            if (defined('SWIFT_PERFORMANCE_DISABLE_CACHE')){
                  return;
            }

            // Skip if requested page isn't cacheable
            if (!self::is_cacheable() && !isset($_GET['force-cached'])){
                  return;
            }

            // Disk cache
            $is_disk_cache    = (strpos(Swift_Performance::get_option('caching-mode'), 'disk_cache') !== false);
            $is_cached        = false;
            $is_404           = false;
            if (file_exists(SWIFT_PERFORMANCE_CACHE_DIR . $this->path . 'index.html')){
                  $path       = SWIFT_PERFORMANCE_CACHE_DIR . $this->path . 'index.html';
                  $is_cached  = true;
            }
            else if (file_exists(SWIFT_PERFORMANCE_CACHE_DIR . $this->path . 'index.xml')){
                  $path       = SWIFT_PERFORMANCE_CACHE_DIR . $this->path . 'index.xml';
                  $is_cached  = true;
                  $content_type  = 'text/xml';
            }
            else if (file_exists(SWIFT_PERFORMANCE_CACHE_DIR . $this->path . 'index.json')){
                  $path       = SWIFT_PERFORMANCE_CACHE_DIR . $this->path . 'index.json';
                  $is_cached  = true;
                  $content_type  = 'application/json';
            }
            else if (file_exists(SWIFT_PERFORMANCE_CACHE_DIR . $this->path . '404.html')){
                  $path       = SWIFT_PERFORMANCE_CACHE_DIR . $this->path . '404.html';
                  $is_cached  = true;
                  $is_404     = true;
            }
            if ($is_disk_cache && $is_cached && filesize($path) > 0){
                  if (Swift_Performance::check_option('whitelabel', 1, '!=')){
                        header(SWIFT_PERFORMANCE_SLUG . ': HIT');
                  }

                  $last_modified_time = filemtime($path);
                  $etag = md5_file($path);

                  if (!$is_404 && Swift_Performance::check_option('304-header', 1) && (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified_time || @trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag)){
                        status_header(304);
                  }
                  else if ($is_404){
                        status_header(404);
                  }

                  // HTTP2 server push
                  if (Swift_Performance::check_option('server-push',1)){
                        self::server_push_headers(file_get_contents($path));
                  }

                  if (Swift_Performance::check_option('proxy-cache',1)){
                        header('Cache-Control: "s-maxage=' . Swift_Performance::get_option('proxy-cache-maxage') . ', max-age=0, public, must-revalidate"');
                  }
                  header("Last-Modified: ".gmdate("D, d M Y H:i:s", $last_modified_time)." GMT");
                  header("Etag: $etag");
                  header("Content-type: $content_type");

                  if (Swift_Performance::check_option('keep-original-headers',1) && file_exists(SWIFT_PERFORMANCE_CACHE_DIR . $this->path . 'header.json')){
                        $headers = json_decode(file_get_contents(SWIFT_PERFORMANCE_CACHE_DIR . $this->path . 'header.json'), true);
                        foreach ((array)$headers as $key => $header){
                              header("{$key}: {$header}");
                        }
                  }

                  if ( file_exists($path . '.gz') && isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos( $_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip' ) !== false ) {
                        header('Content-Encoding: gzip');
                        readfile($path . '.gz');
                  }
                  else{
                        readfile($path);
                  }
                  exit;
            }
            // Memcache
            else if (Swift_Performance::check_option('caching-mode', 'memcached_php')){
                  if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos( $_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip' ) !== false ) {
                        $cached_request = Swift_Performance_Cache::memcached_get($this->path . 'index.html.gz');
                        if (empty($cached_request)) {
                              $cached_request = Swift_Performance_Cache::memcached_get($this->path . 'index.xml.gz');
                              $content_type  = 'text/xml';
                        }
                        if (empty($cached_request)) {
                              $cached_request = Swift_Performance_Cache::memcached_get($this->path . 'index.json.gz');
                              $content_type  = 'application/json';
                        }
                        if (empty($cached_request)) {
                              $cached_request = Swift_Performance_Cache::memcached_get($this->path . '404.html.gz');
                        }
                  }
                  else {
                        $cached_request = Swift_Performance_Cache::memcached_get($this->path . 'index.html');
                        if (empty($cached_request)) {
                              $cached_request = Swift_Performance_Cache::memcached_get($this->path . 'index.xml');
                              $content_type  = 'text/xml';
                        }
                        if (empty($cached_request)) {
                              $cached_request = Swift_Performance_Cache::memcached_get($this->path . 'index.json');
                              $content_type  = 'application/json';
                        }
                        if (empty($cached_request)) {
                              $cached_request = Swift_Performance_Cache::memcached_get($this->path . '404.html');
                        }
                  }

                  if ($cached_request !== false){
                        if (Swift_Performance::check_option('whitelabel', 1, '!=')){
                              header(SWIFT_PERFORMANCE_SLUG . ': HIT');
                        }

                        $content = $cached_request['content'];
                        if (!empty($content)) {
                              $last_modified_time     = $cached_request['time'];
                              $etag                   = md5($content);

                              // Send headers
                              if (Swift_Performance::check_option('304-header', 1) && (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified_time || @trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag)){
                                    header("HTTP/1.1 304 Not Modified");
                              }

                              // HTTP2 server push
                              if (Swift_Performance::check_option('server-push',1)){
                                    self::server_push_headers($content);
                              }

                              if (Swift_Performance::check_option('proxy-cache',1)){
                                    header('Cache-Control: "s-maxage=' . Swift_Performance::get_option('proxy-cache-maxage') . ', max-age=0, public, must-revalidate"');
                              }
                              header("Last-Modified: ".gmdate("D, d M Y H:i:s", $last_modified_time)." GMT");
                              header("Etag: $etag");
                              header("Content-type: $content_type");
                              header("Content-Encoding: none");
                              header('Connection: close');

                              if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos( $_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip' ) !== false ) {
                                    header('Content-Encoding: gzip');
                              }

                              echo $content;

                              exit;
                        }
                  }
            }
      }

      /**
       * Check is dynamic content and cache identical, return dynamic content if not.
       * @return string
       */
      public function check_integrity($buffer){
            // Cache is identical
            if (self::is_identical()){
                  header('X-Cache-Status: identical');
                  die;
            }

            if (Swift_Performance::check_option('whitelabel', 1, '!=')){
                  $buffer = str_replace('</body>', "<!--Cached with ".SWIFT_PERFORMANCE_PLUGIN_NAME."-->\n</body>", $buffer);
            }

            $checksum = false;
            // Disk cache
            if(strpos(Swift_Performance::get_option('caching-mode'), 'disk_cache') !== false){
                  if (self::is_cacheable()){
                        if (!file_exists(SWIFT_PERFORMANCE_CACHE_DIR . $this->path . 'index.html')){
                              header('X-Cache-Status: miss');
                              return;
                        }

                        $clean_cached = self::clean_buffer(file_get_contents(SWIFT_PERFORMANCE_CACHE_DIR . $this->path . 'index.html'));
                        $clean_buffer = self::clean_buffer($buffer);

                        $checksum = (md5($clean_buffer) != md5($clean_cached));
                  }
                  else if (self::is_cacheable_dynamic()){
                        $cached     = get_transient('swift_performance_dynamic_' . Swift_Performance::get_unique_id());
                        $clean_cached = preg_replace_callback('~([0-9abcdef]{10})~',array('Swift_Performance_Cache', 'clean_nonce_buffer'), $cached['content']);
                        $clean_buffer = preg_replace_callback('~([0-9abcdef]{10})~',array('Swift_Performance_Cache', 'clean_nonce_buffer'), $buffer);
                        $checksum = (md5($clean_buffer) != md5($clean_cached));
                  }

                  if ($checksum){
                        header("Last-Modified: ".gmdate("D, d M Y H:i:s", time())." GMT");
                        header("Etag: " . md5($buffer));
                        header('X-Cache-Status: changed');
                        return;
                  }
            }
            // Memcached
            else {
                  if (self::is_cacheable()){
                        $cached_request = Swift_Performance_Cache::memcached_get($this->path . 'index.html');
                        if (empty($cached_request)){
                              header('X-Cache-Status: miss');
                              return;
                        }

                        $clean_cached = self::clean_buffer($cached_request['content']);
                        $clean_buffer = self::clean_buffer($buffer);

                        $checksum = (md5($clean_buffer) != md5($clean_cached));
                  }
                  else if (self::is_cacheable_dynamic()){
                        $cached     = Swift_Performance_Cache::memcached_get(Swift_Performance::get_unique_id());
                        $clean_cached = preg_replace_callback('~([0-9abcdef]{10})~',array('Swift_Performance_Cache', 'clean_nonce_buffer'), $cached['content']);
                        $clean_buffer = preg_replace_callback('~([0-9abcdef]{10})~',array('Swift_Performance_Cache', 'clean_nonce_buffer'), $buffer);
                        $checksum = (md5($clean_buffer) != md5($clean_cached));
                  }

                  if ($checksum){
                        header("Last-Modified: ".gmdate("D, d M Y H:i:s", time())." GMT");
                        header("Etag: " . md5($buffer));
                        header('X-Cache-Status: changed');
                        return;
                  }
            }

            header('X-Cache-Status: not-modified');

      }

      /**
       * Clear intelligent cache if request is POST or query string isn't empty
       */
      public function clear_intelligent_cache(){
            // Exceptions

            // Swift check cache
            if ($this->no_cache){
                  return;
            }

            // Don't clear on AJAX request
            if (defined('DOING_AJAX')){
                  return;
            }

            // Don't clear cache on login/register
            if (@in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ))){
                  return;
            }

            // Remove empty elements
            $_GET = array_filter($_GET);
            $_POST = array_filter($_POST);

            // Clear transients if necessary
            if (!empty($_POST) || !empty($_GET)){
                  self::clear_transients('identical');
            }
            Swift_Performance::log('Clear Intelligent Cache', 9);
      }

      /**
       * Insert discovered links into warmup table
       */
      public function discover(){
            // Save URL for warmup if it doesn't exists yet
            if (Swift_Performance::check_option('discover-warmup', 1) && (Swift_Performance_Cache::is_cacheable() || Swift_Performance_Cache::is_cacheable_dynamic()) && !Swift_Performance::is_404()){
                  global $wpdb;
                  $table_name = SWIFT_PERFORMANCE_TABLE_PREFIX . 'warmup';
                  $priority = Swift_Performance::get_default_warmup_priority();
                  $url = trim(Swift_Performance::home_url(), '/') . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

                  if (Swift_Performance::check_option('cache-case-insensitive',1)){
                        $url = strtolower($url);
                  }

                  $result = $wpdb->query("INSERT IGNORE INTO {$table_name} (id, url, priority, menu_item) VALUES ('".Swift_Performance::get_warmup_id($url)."', '".esc_url($url)."', ".(int)$priority.", '0')");

                  // Prebuild
                  if ($result == 1 && Swift_Performance::check_option('automated_prebuild_cache', 1)){
                        Swift_Performance::log('New page discovered: ' . esc_url($url), 9);
                        wp_schedule_single_event(time(), 'swift_performance_prebuild_page_cache', array(array($url)));
                  }
            }
      }

      /**
       * Bypass Gravatar
       * @param string url
       * @return string
       */
      public function bypass_gravatar($url){
            // Bypass gravatar only
            if (!preg_match('~gravatar.com$~', parse_url($url, PHP_URL_HOST))){
                  return $url;
            }

            // Serve gravatar if it is exists and not expired
            $filename = apply_filters('swift_gravatar_cache_filename', md5($url));
            $gravatar = SWIFT_PERFORMANCE_CACHE_DIR . 'garvatar-cache/' . $filename;

            if (file_exists($gravatar) && filemtime($gravatar) + (int)Swift_Performance::get_option('gravatar-cache-expiry') > time()){
                  return SWIFT_PERFORMANCE_CACHE_URL . 'garvatar-cache/' . $filename;
            }

            // We are here, so gravatar is not exists, or expired. Check is the gravatar-cache folder exists at all
            if (!file_exists(SWIFT_PERFORMANCE_CACHE_DIR . 'garvatar-cache')){
                  // No it isn't exists, so we try to create it
                  if (!is_writable(SWIFT_PERFORMANCE_CACHE_DIR . 'garvatar-cache')){
                        @mkdir(SWIFT_PERFORMANCE_CACHE_DIR . 'garvatar-cache', 0777, true);
                  }
                  else {
                        // No luck, no cache
                        return $url;
                  }
            }

            // Gravatar isn't exists, or expired, and gravatar-cache folder is exists
            if (!file_exists($gravatar) || is_writable($gravatar)){
                  // Download the image
                  $response = wp_safe_remote_get( $url, array( 'timeout' => 300, 'stream' => true, 'filename' => $gravatar ) );

                  // Something went wrong :-/
                  if (is_wp_error($response)) {
                        @unlink($gravatar);
                        Swift_Performance::log('Gravatar bypass failed: ' . $response->get_error_message(), 1);
                        return $url;
                  }
                  else {
                        list($width, $height) = getimagesize($gravatar);
                        if (empty($width)){
                              @file_put_contents($gravatar, base64_decode('/9j/4AAQSkZJRgABAQAAAQABAAD//gA7Q1JFQVRPUjogZ2QtanBlZyB2MS4wICh1c2luZyBJSkcgSlBFRyB2NjIpLCBxdWFsaXR5ID0gOTAK/9sAQwADAgIDAgIDAwMDBAMDBAUIBQUEBAUKBwcGCAwKDAwLCgsLDQ4SEA0OEQ4LCxAWEBETFBUVFQwPFxgWFBgSFBUU/9sAQwEDBAQFBAUJBQUJFA0LDRQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQU/8AAEQgAIAAgAwEiAAIRAQMRAf/EAB8AAAEFAQEBAQEBAAAAAAAAAAABAgMEBQYHCAkKC//EALUQAAIBAwMCBAMFBQQEAAABfQECAwAEEQUSITFBBhNRYQcicRQygZGhCCNCscEVUtHwJDNicoIJChYXGBkaJSYnKCkqNDU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6g4SFhoeIiYqSk5SVlpeYmZqio6Slpqeoqaqys7S1tre4ubrCw8TFxsfIycrS09TV1tfY2drh4uPk5ebn6Onq8fLz9PX29/j5+v/EAB8BAAMBAQEBAQEBAQEAAAAAAAABAgMEBQYHCAkKC//EALURAAIBAgQEAwQHBQQEAAECdwABAgMRBAUhMQYSQVEHYXETIjKBCBRCkaGxwQkjM1LwFWJy0QoWJDThJfEXGBkaJicoKSo1Njc4OTpDREVGR0hJSlNUVVZXWFlaY2RlZmdoaWpzdHV2d3h5eoKDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uLj5OXm5+jp6vLz9PX29/j5+v/aAAwDAQACEQMRAD8A+uK0tH8O6lr7sthaPcbfvMMBR9ScCs2voDwXaQWfhbTFgACvAsjEd2YZY/maAPEdY8OaloDKL+0eAN91shlP0IyKza+gfGVpBeeF9TS4AKLA8gJ7MoyD+Yr5+oAfFE88qRxoXkchVVRkknoBXufgPQ9R0HRlt7+4WQH5kgAz5Oeo3d/881wnwj0lL3XJ7yRQwtIxsB7M2QD+QavYKAOa8d6JqOvaM1vYXCx/xPCRgy45A3dv6+teGSxPBK8ciFJEJVlYYII6g19MV4/8XNJSy1yC8jUKLuM7wO7LgE/kVoA//9k='));
                        }
                        // Yay!
                        return SWIFT_PERFORMANCE_CACHE_URL . 'garvatar-cache/' . apply_filters('swift_gravatar_cache_filename', md5($url));
                  }
            }

            // No cache
            return $url;
      }

      /**
       * Clean buffer to be able to compare integrity for logged in users
       * @param string buffer
       * @return string
       */
      public static function clean_buffer($buffer){
            $buffer = preg_replace('~swift-no-cache(=|%3D)(\d*)~', '', $buffer);
            $buffer = preg_replace_callback('~([0-9abcdef]{10})~',array('Swift_Performance_Cache', 'clean_nonce_buffer'), $buffer);
            return $buffer;
      }

      /**
       * Remove valid nonces from the cache/buffer to be able to compare integrity for logged in users
       * @param array $matches
       * @return string
       */
      public static function clean_nonce_buffer($matches){
            if (wp_verify_nonce($matches[0])){
                  return '';
            }
            return $matches[0];
      }

      /**
       * Print cache check XHR request in head
       */
      public static function intelligent_cache_xhr(){
            echo "\n<script data-dont-merge='1'>setTimeout(function(){ function fire(){ window.removeEventListener('touchstart',fire); window.removeEventListener('scroll',fire); document.removeEventListener('mousemove',fire); var request = new XMLHttpRequest(); request.open('GET', document.location.href + (document.location.href.match(/\?/) ? '&' : '?') + 'swift-no-cache=' + parseInt(Math.random() * 100000000), true); request.onreadystatechange = function() { if (request.readyState === 4 && request.getResponseHeader('X-Cache-Status') == 'changed' && '1' != '".Swift_Performance::get_option(' disable - instant - reload ')."') { document.open(); document.write(request.responseText.replace('<html', '<html data-swift-performance-refreshed')); document.close(); } }; if (document.querySelector('[data-swift-performance-refreshed]') == null) { request.send(); }; document.getElementsByTagName('html')[0].addEventListener('click', function() { request.abort() }, false); document.getElementsByTagName('html')[0].addEventListener('keypress', function() { request.abort() }, false); } window.addEventListener('load', function() { window.addEventListener('touchstart',fire); window.addEventListener('scroll',fire); document.addEventListener('mousemove',fire); }); },10); </script>";
      }

      /**
       * Check known user actions when we should clear post cache
       */
      public static function clear_on_user_action(){
            // Comment
            if (isset($_POST['comment_post_ID']) && !empty($_POST['comment_post_ID'])){
                  self::clear_post_cache($_POST['comment_post_ID']);
                  Swift_Performance::log('Clear post cache triggered (new comment) ', 9);
            }

            // bbPress comment
            else if (isset($_POST['bbp_reply_content']) && isset($_POST['bbp_topic_id']) && !empty($_POST['bbp_topic_id'])){
                  self::clear_post_cache($_POST['bbp_topic_id']);
                  Swift_Performance::log('Clear post cache triggered (new bbPress comment) ', 9);
            }

            // Buddypress
            else if (function_exists('bp_get_root_domain') && isset($_POST['action']) && $_POST['action'] == 'post_update'){
                  $bb_permalink = '';
                  if (isset($_POST['object']) && $_POST['object'] == 'group'){
                        if (isset($_POST['item_id']) && !empty($_POST['item_id'])){
                              $group = groups_get_group( array( 'group_id' => $_POST['item_id'] ) );
                              $bb_permalink = trailingslashit( bp_get_root_domain() . '/' . bp_get_groups_root_slug() . '/' . $group->slug . '/' );
                        }
                  }
                  else if ((!isset($_POST['object']) || empty($_POST['object'])) && (!isset($_POST['item_id']) || empty($_POST['item_id']))){
                        $bb_permalink = str_replace(home_url(), '', bp_get_root_domain() .'/'. bp_get_root_slug());
                  }

                  if (!empty($bb_permalink)){
                        if (Swift_Performance::check_option('caching-mode', array('disk_cache_rewrite', 'disk_cache_php'), 'IN')){
                              // Disk cache
                              self::recursive_rmdir($bb_permalink);
                              Swift_Performance::log('Clear permalink cache (disk) triggered (buddypress activity) ', 9);
                        }
                        else {
                              // Memcached
                              $memcached = self::get_memcache_instance();
                              $keys = $memcached->getAllKeys();
                              foreach($keys as $item) {
                                  if(preg_match('~^swift-performance' . str_replace(home_url(), '', $bb_permalink) .'~', $item)) {
                                      $memcached->delete($item);
                                  }
                              }
                              Swift_Performance::log('Clear permalink cache (memcached) triggered (buddypress activity) ', 9);
                        }

                        // Update warmup table
                        $id = Swift_Performance::get_warmup_id($bb_permalink);
                        Swift_Performance::mysql_query("UPDATE " . SWIFT_PERFORMANCE_TABLE_PREFIX . "warmup SET timestamp = 0, type = '' WHERE id = '{$id}' LIMIT 1");

                        // Cloudflare
                        if(Swift_Performance::check_option('cloudflare-auto-purge',1) && Swift_Performance::check_option('cloudflare-email', '', '!=') && Swift_Performance::check_option('cloudflare-api-key', '', '!=')){
                              self::purge_cloudflare_zones($bb_permalink);
                        }

                        // Varnish
                        if(Swift_Performance::check_option('varnish-auto-purge',1)){
                              self::purge_varnish_url($bb_permalink);
                        }

                        // Prebuild cache
                        if (Swift_Performance::check_option('automated_prebuild_cache',1)){
                              wp_remote_get($bb_permalink);
                              Swift_Performance::log('Prebuild cache for buddypress activity: ' . $bb_permalink, 9);
                        }
                  }
            }
      }

      /**
       * Clear all cache
       */
      public static function clear_all_cache(){
            do_action('swift_performance_before_clear_all_cache');

            // Delete prebuild booster
            delete_option('swift_performance_prebuild_booster');

            // Clear cache
            if (Swift_Performance::check_option('caching-mode', array('disk_cache_rewrite', 'disk_cache_php'), 'IN')){
                  // Disk cache
                  self::recursive_rmdir();
                  Swift_Performance::log('Clear all cache (disk)', 9);
            }
            else {
                  // Memcached
                  $memcached = self::get_memcache_instance();
                  $memcached->flush();
                  Swift_Performance::log('Clear all cache (memcached)', 9);
            }

            // Cloudflare
            if(Swift_Performance::check_option('cloudflare-auto-purge',1) && Swift_Performance::check_option('cloudflare-email', '', '!=') && Swift_Performance::check_option('cloudflare-api-key', '', '!=')){
                  self::purge_cloudflare_zones();
            }

            // Varnish
            if(Swift_Performance::check_option('varnish-auto-purge',1)){
                  self::purge_varnish_url(Swift_Performance::home_url(), true);
            }

            Swift_Performance_Third_Party::clear_cache();

            // Prebuild cache
            Swift_Performance::stop_prebuild();
            if (Swift_Performance::check_option('automated_prebuild_cache',1)){
                  wp_schedule_single_event(time(), 'swift_performance_prebuild_cache');
                  Swift_Performance::log('Prebuild cache scheduled', 9);
            }

            // Clear object cache
            self::clear_transients();

            // MaxCDN
            if (Swift_Performance::check_option('enable-cdn', 1) && Swift_Performance::check_option('maxcdn-alias', '','!=') && Swift_Performance::check_option('maxcdn-key', '','!=') && Swift_Performance::check_option('maxcdn-secret', '','!=')){
                  Swift_Performance_CDN_Manager::purge_cdn();
            }

            // Update warmup table
            Swift_Performance::mysql_query("UPDATE " . SWIFT_PERFORMANCE_TABLE_PREFIX . "warmup SET timestamp = 0, type = ''");

            do_action('swift_performance_after_clear_all_cache');
      }

      /**
       * Clear expired cache
       */
      public static function clear_expired(){
            global $wpdb;
            do_action('swift_performance_before_clear_expired_cache');

            $timestamp  = time() - Swift_Performance::get_option('cache-expiry-time');
            $expired    = $wpdb->get_col("SELECT url FROM " . SWIFT_PERFORMANCE_TABLE_PREFIX . "warmup WHERE timestamp <= '{$timestamp}'");
            foreach ($expired as $permalink) {
                  self::clear_permalink_cache($permalink);
            }

            do_action('swift_performance_after_clear_expired_cache');
      }

      /**
       * Clear cache based on permalink
       * @param string $permalink
       */
      public static function clear_permalink_cache($permalink){
            do_action('swift_performance_before_clear_permalink_cache', $permalink);

            // Try to get the page/post id
            $maybe_id = url_to_postid($permalink);
            if (!empty($maybe_id)){
                  self::clear_post_cache($maybe_id);
                  return;
            }

            // Cloudflare
            if(Swift_Performance::check_option('cloudflare-auto-purge',1) && Swift_Performance::check_option('cloudflare-email', '', '!=') && Swift_Performance::check_option('cloudflare-api-key', '', '!=')){
                  self::purge_cloudflare_zones($permalink);
            }

            // Clear permalink
            self::clear_single_cached_item($permalink);

            // Prebuild cache
            if (Swift_Performance::check_option('automated_prebuild_cache',1)){
                  wp_schedule_single_event(time(), 'swift_performance_prebuild_page_cache', array($permalink));
            }

            do_action('swift_performance_after_clear_permalink_cache', $permalink);
      }

      /**
       * Delete cached post on save
       * @param int $post_id
       */
      public static function clear_post_cache($post_id){
            do_action('swift_performance_before_clear_post_cache', $post_id);

            // Don't clear cache on autosave
            if (defined('DOING_AUTOSAVE')){
                  return;
            }

            // WooCommerce product variation
            if (get_post_type($post_id) == 'product_variation'){
                  $parent_id  = apply_filters('swift_peformance_get_product_variation_parent', wp_get_post_parent_id($post_id), $post_id);
                  $post_id    = (!empty($parent_id) ? $parent_id : $post_id);
                  $maybe_comments_feed = false;
            }

            $permalink = get_permalink($post_id);

            if (!Swift_Performance_Cache::is_object_cacheable($permalink, $post_id)){
                  return;
            }

            $permalinks = array();

            // Permalink
            $permalinks[] = $permalink;

            // Comments feed
            if (Swift_Performance::check_option('cache-feed',1)){
                  @$permalinks[] = get_post_comments_feed_link($post_id);
            }

            // AMP
            if (function_exists('amp_get_permalink')) {
                  $amp_url = amp_get_permalink($post_id);
                  if (!empty($amp_url)){
                        $permalinks[] = $amp_url;
                  }
		}

            // REST API
            if (Swift_Performance::check_option('cache-rest',1)){
                  $rest_url = self::get_rest_url($post_id);
                  if (!empty($rest_url)){
                        $permalinks[] = $rest_url;
                  }
            }

            // Archive URLs
            if (Swift_Performance::check_option('cache-archive',1)){
                  @$archive_urls = self::get_archive_urls($post_id);
                  if (!empty($archive_urls)){
                        $permalinks = array_merge($permalinks, $archive_urls);
                  }
            }

            if (Swift_Performance::check_option('cache-terms',1)){
                  @$term_urls = self::get_term_urls($post_id);
                  if (!empty($term_urls)){
                        $permalinks = array_merge($permalinks, $term_urls);
                  }
            }

            // Author
            if (Swift_Performance::check_option('cache-author',1)){
                  @$author_urls = self::get_author_urls($post_id);
                  if (!empty($author_urls)){
                        $permalinks = array_merge($permalinks, $author_urls);
                  }
            }

            // Feeds
            if (Swift_Performance::check_option('cache-feed',1)){
                  $permalinks[] = get_bloginfo_rss('rdf_url');
                  $permalinks[] = get_bloginfo_rss('rss_url');
                  $permalinks[] = get_bloginfo_rss('rss2_url');
                  $permalinks[] = get_bloginfo_rss('atom_url');
                  $permalinks[] = get_bloginfo_rss('comments_rss2_url');
            }


            // Cloudflare
            if(Swift_Performance::check_option('cloudflare-auto-purge',1) && Swift_Performance::check_option('cloudflare-email', '', '!=') && Swift_Performance::check_option('cloudflare-api-key', '', '!=')){
                  self::purge_cloudflare_zones($permalinks);
            }

            // WP Engine
            if (method_exists('WpeCommon', 'purge_varnish_cache')){
                  WpeCommon::purge_varnish_cache($post_id);
            }

            // Clear cached permalinks
            foreach ($permalinks as $permalink) {
                  self::clear_single_cached_item($permalink);
            }

            // Prebuild cache
            if (Swift_Performance::check_option('automated_prebuild_cache',1)){
                  wp_schedule_single_event(time(), 'swift_performance_prebuild_page_cache', array($permalinks));
            }

            do_action('swift_performance_after_clear_post_cache', $post_id);
      }

      /**
       * Clear page cache after publish/update post
       */
      public static function clear_cache_after_post(){
            //By post id
            $pages = array_filter((array)Swift_Performance::get_option('clear-page-cache-after-post'));
            if (!empty($pages)){
                  foreach ($pages as $page){
                        self::clear_post_cache($page);
                  }
            }

            // By permalink
            $permalinks = array_filter((array)Swift_Performance::get_option('clear-permalink-cache-after-post'));
            if (!empty($permalinks)){
                  foreach ($permalinks as $permalink){
                        self::clear_permalink_cache($permalink);
                  }
            }
      }

      /**
       * Clear single item from cache
       * @param string $permalink
       * @param int $post_id
       */
      public static function clear_single_cached_item($permalink, $post_id = 0){
            do_action('swift_performance_before_clear_single_cached_item', $permalink, $post_id);
            global $wp_rewrite, $wpdb;

            if (empty($permalink)){
                  return;
            }

            if (Swift_Performance::check_option('caching-mode', array('disk_cache_rewrite', 'disk_cache_php'), 'IN')){
                  $base_path = trailingslashit(parse_url($permalink, PHP_URL_PATH));
                  $css_dir = $js_dir = '';
                  $desktop_cache_path     = $base_path . 'desktop/';
                  $mobile_cache_path      = $base_path . 'mobile/';
                  $subcache_path          = $base_path . '@prefix/';
                  $paginate_cache_path    = $base_path . trailingslashit($wp_rewrite->pagination_base);

                  if (Swift_Performance::check_option('separate-css', 1)){
                        $css_dir = apply_filters('swift_performance_css_dir', trailingslashit(trim(parse_url($permalink, PHP_URL_PATH),'/')) . 'css');
                  }
                  if (Swift_Performance::check_option('separate-js', 1)){
                        $js_dir = apply_filters('swift_performance_css_dir', trailingslashit(trim(parse_url($permalink, PHP_URL_PATH),'/')) . 'js');
                  }

                  self::recursive_rmdir($desktop_cache_path);
                  self::recursive_rmdir($mobile_cache_path);
                  self::recursive_rmdir($subcache_path);
                  self::recursive_rmdir($paginate_cache_path);

                  if (!empty($css_dir)){
                        self::recursive_rmdir($css_dir);
                  }

                  if (!empty($js_dir)){
                        self::recursive_rmdir($js_dir);
                  }

                  Swift_Performance::log('Clear single cached item (disk) ID: ' . $post_id . ', permalink: ' . $permalink, 9);
            }
            else {
                  // Memcached
                  $path = trailingslashit(parse_url($permalink, PHP_URL_PATH) . '/(desktop|mobile|'.preg_quote($wp_rewrite->pagination_base).')/');
                  $memcached = self::get_memcache_instance();
                  $keys = $memcached->getAllKeys();
                  foreach($keys as $item) {
                      if(preg_match('~^swift-performance_' . $path .'~', $item)) {
                          $memcached->delete($item);
                          Swift_Performance::log('Clear single cached item (memcached) ID: ' . $post_id . ', permalink: ' . $permalink, 9);
                      }
                  }
            }

            // Varnish
            if(Swift_Performance::check_option('varnish-auto-purge',1)){
                  self::purge_varnish_url($permalink);
            }

            // Dynamic cache
            $url_path = hash('crc32', parse_url($permalink, PHP_URL_PATH));
            $transients = $wpdb->get_col("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_swift_performance_dynamic_{$url_path}_%'");
            foreach ($transients as $transient) {
                  delete_transient(str_replace('_transient_','',$transient));
            }

            // Update Warmup Table
            $id = Swift_Performance::get_warmup_id($permalink);
            Swift_Performance::mysql_query("UPDATE " . SWIFT_PERFORMANCE_TABLE_PREFIX . "warmup SET timestamp = 0, type = '' WHERE id = '{$id}'");

            do_action('swift_performance_after_clear_single_cached_item', $permalink, $post_id);
      }

      /**
       * Clear user cache
       * @param int $user_id
       */
      public static function clear_user_cache($user_id){
            $user = get_userdata($user_id);
            $hash = md5($user->user_login . NONCE_SALT);

            do_action('swift_performance_before_clear_user_cache', $user_id);
            if (Swift_Performance::check_option('caching-mode', array('disk_cache_rewrite', 'disk_cache_php'), 'IN') && file_exists(SWIFT_PERFORMANCE_CACHE_DIR)){

                  $Directory = new RecursiveDirectoryIterator(SWIFT_PERFORMANCE_CACHE_DIR);
                  $Iterator = new RecursiveIteratorIterator($Directory);
                  $Regex = new RegexIterator($Iterator, '#'.$hash.'#i', RecursiveRegexIterator::GET_MATCH);

                  foreach ($Regex as $filename => $file){
                        $filename = rtrim($filename, '.');
                        if (file_exists($filename) && is_dir($filename)){
                              self::recursive_rmdir(str_replace(SWIFT_PERFORMANCE_CACHE_DIR, '', $filename));
                        }
                  }

                  Swift_Performance::log('Clear user cache (disk) ID: ' . $user_id, 9);
            }
            else {
                  // Memcached
                  $memcached = self::get_memcache_instance();
                  $keys = $memcached->getAllKeys();
                  foreach($keys as $item) {
                      if(preg_match('~'.$user->user_login.'~', $item)) {
                          $memcached->delete($item);
                          Swift_Performance::log('Clear user cache (memcached) ID: ' . $user_id, 9);
                      }
                  }
            }

            do_action('swift_performance_after_clear_user_cache', $user_id);
      }

      /**
       * Clear object cache transients
       * @param $type string
       */
      public static function clear_transients($type = 'all'){
            do_action('swift_performance_before_clear_transients', $type);
            global $wpdb;
            switch ($type) {
                  case 'ajax':
                        $wpdb->query('DELETE FROM ' . $wpdb->options . ' WHERE option_name LIKE "%swift_performance_ajax_%"');
                        Swift_Performance::log('Clear all transients', 9);
                        break;
                  case 'dynamic':
                        $wpdb->query('DELETE FROM ' . $wpdb->options . ' WHERE option_name LIKE "%swift_performance_dynamic_%"');
                        Swift_Performance::log('Clear all transients', 9);
                        break;
                  case 'identical':
                        $wpdb->query('DELETE FROM ' . $wpdb->options . ' WHERE option_name LIKE "%swift_performance_is_identical_%"');
                        Swift_Performance::log('Clear all transients', 9);
                        break;
                  case 'all':
                  default:
                        $wpdb->query('DELETE FROM ' . $wpdb->options . ' WHERE option_name LIKE "%swift_performance_is_identical_%" OR option_name LIKE "%swift_performance_ajax_%" OR option_name LIKE "%swift_performance_dynamic_%"');
                        Swift_Performance::log('Clear all transients', 9);
                        break;
            }
            do_action('swift_performance_after_clear_transients', $type);
      }

      /**
       * Is current page cacheable
       * @return boolean
       */
      public static function is_cacheable(){
            // ignore fbclid and gclid
            $get = $_GET;
            unset($get['gclid']);
            unset($get['fbclid']);

            $ajax_cache       = !defined('DOING_AJAX');
            $xmlrpc           = !defined('XMLRPC_REQUEST');
            $logged_in        = (!Swift_Performance::is_user_logged_in() || Swift_Performance::check_option('enable-caching-logged-in-users', 1));
            $is_404           = (Swift_Performance::check_option('cache-404',1) || !Swift_Performance::is_404());
            $query_string     = (isset($_SERVER['HTTP_X_PREBUILD']) || Swift_Performance::check_option('ignore-query-string', 1) || empty($get));
            $post             = (isset($_SERVER['HTTP_X_PREBUILD']) || empty($_POST));
            $archive          = (Swift_Performance::check_option('exclude-archive',1) && Swift_Performance::is_archive() ? false : true);
            $author           = (Swift_Performance::check_option('exclude-author',1) && Swift_Performance::is_author() ? false : true);
            $rest             = (Swift_Performance::check_option('exclude-rest',1) && Swift_Performance::is_rest() ? false : true);
            $feed             = (Swift_Performance::check_option('exclude-feed',1) && Swift_Performance::is_feed() ? false : true);
            $maintenance      = !self::is_maintenance();
            return apply_filters('swift_performance_is_cacheable', !self::is_exluded() && $archive && $author && $rest && $feed && $maintenance && $is_404 && !Swift_Performance::is_admin() && $post && $query_string && $logged_in && $ajax_cache && $xmlrpc && !self::is_crawler() && !Swift_Performance::is_password_protected());
      }

      /**
       * Is current AJAX request cacheable
       * @return boolean
       */
      public static function is_cacheable_dynamic(){
            // Is there any dynamic parameter?
            if (empty($_GET) && empty($_POST)){
                  return apply_filters('swift_performance_is_cacheable_dynamic', false);
            }

            if (Swift_Performance::check_option('dynamic-caching', 1)){
                  $cacheable = false;
                  foreach (array_filter((array)Swift_Performance::get_option('cacheable-dynamic-requests')) as $key){
                        if (isset($_REQUEST[$key])){
                              $cacheable = true;
                              break;
                        }
                  }
                  $xmlrpc           = !defined('XMLRPC_REQUEST');
                  $logged_in        = (!Swift_Performance::is_user_logged_in() || Swift_Performance::check_option('enable-caching-logged-in-users', 1));
                  $is_404           = (Swift_Performance::check_option('cache-404',1) || !Swift_Performance::is_404());
                  $archive          = (Swift_Performance::check_option('exclude-archive',1) && Swift_Performance::is_archive() ? false : true);
                  $author           = (Swift_Performance::check_option('exclude-author',1) && Swift_Performance::is_author() ? false : true);
                  $rest             = (Swift_Performance::check_option('exclude-rest',1) && Swift_Performance::is_rest() ? false : true);
                  $feed             = (Swift_Performance::check_option('exclude-feed',1) && Swift_Performance::is_feed() ? false : true);
                  $maintenance      = !self::is_maintenance();
                  return apply_filters('swift_performance_is_cacheable_dynamic', !self::is_exluded() && $is_404 && $maintenance && $cacheable && $logged_in && $xmlrpc && $archive && $author && $rest && !Swift_Performance::is_admin() && !self::is_crawler() && !Swift_Performance::is_password_protected());
            }
            return apply_filters('swift_performance_is_cacheable_dynamic', false);
      }

      /**
       * Is current AJAX request cacheable
       * @return boolean
       */
      public static function is_cacheable_ajax(){
            return apply_filters('swift_performance_is_cacheable_ajax', ((!Swift_Performance::is_user_logged_in() || Swift_Performance::check_option('enable-caching-logged-in-users', 1)) && defined('DOING_AJAX') && DOING_AJAX && isset($_REQUEST['action']) && in_array($_REQUEST['action'], array_filter((array)Swift_Performance::get_option('cacheable-ajax-actions')))));
      }

      /**
      * Is given URL and/or id for prebuild
      * @param string $url
      * @param int $id
      * @return boolean
      */
      public static function is_object_cacheable($url, $id = 0){
            global $wpdb;

            $cacheable = true;
            $excluded = false;

            // Check dynamic parameters
            $query_string = parse_url($url, PHP_URL_QUERY);
            parse_str($query_string, $query);
            if (!empty($query)){
                  if (Swift_Performance::check_option('dynamic-caching', 1)){
                        $cacheable_dynamic = false;
                        foreach (array_filter((array)Swift_Performance::get_option('cacheable-dynamic-requests')) as $key){
                              if (isset($query[$key])){
                                    $cacheable_dynamic = true;
                                    break;
                              }
                        }
                        if (!$cacheable_dynamic){
                              return false;
                        }
                  }
                  else {
                        return false;
                  }
            }

            // Excluded strings
            foreach (array_filter((array)Swift_Performance::get_option('exclude-strings')) as $exclude_string){
                  if (!empty($exclude_string)){
                        if (substr($exclude_string,0,1) == '#' && substr($exclude_string,strlen($exclude_string)-1) == '#'){
                              if (preg_match($exclude_string, parse_url($url, PHP_URL_PATH))){
                                    return false;
                              }
                        }
                        else {
                              if (strpos(parse_url($url, PHP_URL_PATH), $exclude_string) !== false){
                                    return false;
                              }
                        }
                  }
            }

            // Excluded pages

            // First get the id if we don't have it yet
            if (empty($id)){
                  $id = url_to_postid($url);
            }

            // If id is still empty is not a post, walk away
            if (empty($id)){
                  return true;
            }

            // Manually excluded pages
            if(in_array($id, (array)Swift_Performance::get_option('exclude-pages'))){
                  return false;
            }

            // Excluded post types
            if (in_array(get_post_type($id), (array)Swift_Performance::get_option('exclude-post-types'))){
                  return false;
            }

            // Password protected
            if (Swift_Performance::is_password_protected($id)){
                  return false;
            }

            // WooCommerce
            if(class_exists('woocommerce')){
                  $results = $wpdb->get_results("SELECT option_value FROM {$wpdb->options} WHERE option_name LIKE 'woocommerce_%_page_id' AND option_name NOT IN ('woocommerce_shop_page_id', 'woocommerce_terms_page_id')", ARRAY_A);
                  foreach((array)$results as $result){
                        if (!empty($result['option_value']) && $result['option_value'] == $id){
                              return false;
                        }
                  }
            }

            return true;
      }

      /**
       * Is current request excluded from cache
       * @return boolean
       */
      public static function is_exluded(){
            $excluded = false;

            // Excluded strings
            foreach (array_filter((array)Swift_Performance::get_option('exclude-strings')) as $exclude_string){
                  if (!empty($exclude_string)){
                        if(substr($exclude_string,0,1) == '#' && substr($exclude_string,strlen($exclude_string)-1) == '#'){
                              $excluded = preg_match($exclude_string, parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
                        }
                        else {
                              $excluded = strpos(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), $exclude_string) !== false;
                        }
                  }
            }

            // Excluded content parts
            if (!$excluded){
                  foreach (array_filter((array)Swift_Performance::get_option('exclude-content-parts')) as $exclude_content_part){
                        if (!empty($exclude_content_part)){
                              if(substr($exclude_content_part,0,1) == '#' && substr($exclude_content_part,strlen($exclude_content_part)-1) == '#'){
                                    $excluded = preg_match($exclude_content_part, $GLOBALS['swift_performance']->modules['cache']->buffer);
                              }
                              else {
                                    $excluded = strpos($GLOBALS['swift_performance']->modules['cache']->buffer, $exclude_content_part) !== false;
                              }
                        }
                  }
            }

            // Excluded user agents
            if (!$excluded){
                  foreach (array_filter((array)Swift_Performance::get_option('exclude-useragents')) as $exclude_ua){
                        if (!empty($exclude_ua)){
                              if(substr($exclude_ua,0,1) == '#' && substr($exclude_ua,strlen($exclude_ua)-1) == '#'){
                                    $excluded = preg_match($exclude_ua, $_SERVER['HTTP_USER_AGENT']);
                              }
                              else {
                                    $excluded = strpos($_SERVER['HTTP_USER_AGENT'], $exclude_ua) !== false;
                              }
                        }
                  }
            }

            // Excluded pages
            if (!$excluded){
                  // Manually excluded pages
                  $excluded = (get_the_ID() != 0 && in_array(get_the_ID(), (array)Swift_Performance::get_option('exclude-pages')) );

                  // WooCommerce
                  if(class_exists('woocommerce')){
                        global $wpdb;
                        $results = $wpdb->get_results("SELECT option_value FROM {$wpdb->options} WHERE option_name LIKE 'woocommerce_%_page_id' AND option_name NOT IN ('woocommerce_shop_page_id', 'woocommerce_terms_page_id')", ARRAY_A);
                        foreach((array)$results as $result){
                              if (!empty($result['option_value']) && $result['option_value'] == get_the_ID()){
                                    $excluded = true;
                              }
                        }
                  }

            }

            // Excluded post types
            if (!$excluded){
                  $post_type = get_post_type();
                  if (!empty($post_type) && in_array($post_type, (array)Swift_Performance::get_option('exclude-post-types'))){
                        $excluded = true;
                  }
            }

            return apply_filters('swift_performance_is_excluded', $excluded);

      }

      /**
       * Detect crawlers
       */
      public static function is_crawler(){
            $crawlers = array( '.*Java.*outbrain', '008\/', '192\.comAgent', '2ip\.ru', '404checker', '^bluefish ', '^Calypso v\/', '^COMODO DCV', '^DangDang', '^DavClnt', '^FDM ', '^git\/', '^Goose\/', '^HTTPClient\/', '^Java\/', '^Jeode\/', '^Jetty\/', '^Mget', '^Microsoft URL Control', '^NG\/[0-9\.]', '^NING\/', '^PHP\/[0-9]', '^RMA\/', '^Ruby|Ruby\/[0-9]', '^scrutiny\/', '^VSE\/[0-9]', '^WordPress\.com', '^XRL\/[0-9]', '^ZmEu', 'a3logics\.in', 'A6-Indexer', 'a\.pr-cy\.ru', 'Aboundex', 'aboutthedomain', 'Accoona-AI-Agent', 'acoon', 'acrylicapps\.com\/pulp', 'adbeat', 'AddThis', 'ADmantX', 'adressendeutschland', 'Advanced Email Extractor v', 'agentslug', 'AHC', 'aihit', 'aiohttp\/', 'Airmail', 'akula\/', 'alertra', 'alexa site audit', 'Alibaba\.Security\.Heimdall', 'alyze\.info', 'amagit', 'AndroidDownloadManager', 'Anemone', 'Ant\.com', 'Anturis Agent', 'AnyEvent-HTTP\/', 'Apache-HttpClient\/', 'AportWorm\/[0-9]', 'AppEngine-Google', 'Arachmo', 'arachnode', 'Arachnophilia', 'aria2', 'asafaweb.com', 'AskQuickly', 'Astute', 'asynchttp', 'autocite', 'Autonomy', 'B-l-i-t-z-B-O-T', 'Backlink-Ceck\.de', 'Bad-Neighborhood', 'baidu\.com', 'baypup\/[0-9]', 'baypup\/colbert', 'BazQux', 'BCKLINKS', 'BDFetch', 'BegunAdvertising\/', 'BigBozz', 'biglotron', 'BingLocalSearch', 'BingPreview', 'binlar', 'biNu image cacher', 'biz_Directory', 'Blackboard Safeassign', 'Bloglovin', 'BlogPulseLive', 'BlogSearch', 'Blogtrottr', 'boitho\.com-dc', 'BPImageWalker', 'Braintree-Webhooks', 'Branch Metrics API', 'Branch-Passthrough', 'Browsershots', 'BUbiNG', 'Butterfly\/', 'BuzzSumo', 'CAAM\/[0-9]', 'CakePHP', 'CapsuleChecker', 'CaretNail', 'catexplorador', 'cb crawl', 'CC Metadata Scaper', 'Cerberian Drtrs', 'CERT\.at-Statistics-Survey', 'cg-eye', 'changedetection', 'Charlotte', 'CheckHost', 'checkprivacy', 'chkme\.com', 'CirrusExplorer\/', 'CISPA Vulnerability Notification', 'CJNetworkQuality', 'clips\.ua\.ac\.be', 'Cloud mapping experiment', 'CloudFlare-AlwaysOnline', 'Cloudinary\/[0-9]', 'cmcm\.com', 'coccoc', 'CommaFeed', 'Commons-HttpClient', 'Comodo SSL Checker', 'contactbigdatafr', 'convera', 'copyright sheriff', 'Covario-IDS', 'CrawlForMe\/[0-9]', 'cron-job\.org', 'Crowsnest', 'curb', 'Curious George', 'curl', 'cuwhois\/[0-9]', 'cybo\.com', 'DareBoost', 'DataparkSearch', 'dataprovider', 'Daum(oa)?[ \/][0-9]', 'DeuSu', 'developers\.google\.com\/\+\/web\/snippet\/', 'Digg', 'Dispatch\/', 'dlvr', 'DMBrowser-UV', 'DNS-Tools Header-Analyzer', 'DNSPod-reporting', 'docoloc', 'Dolphin http client\/', 'DomainAppender', 'dotSemantic', 'downforeveryoneorjustme', 'downnotifier\.com', 'DowntimeDetector', 'Dragonfly File Reader', 'drupact', 'Drupal \(\+http:\/\/drupal\.org\/\)', 'dubaiindex', 'EARTHCOM', 'Easy-Thumb', 'ec2linkfinder', 'eCairn-Grabber', 'ECCP', 'ElectricMonk', 'elefent', 'EMail Exractor', 'EmailWolf', 'Embed PHP Library', 'Embedly', 'europarchive\.org', 'evc-batch\/[0-9]', 'EventMachine HttpClient', 'Evidon', 'Evrinid', 'ExactSearch', 'ExaleadCloudview', 'Excel\/', 'Exif Viewer', 'Exploratodo', 'ezooms', 'facebookexternalhit', 'facebookplatform', 'fairshare', 'Faraday v', 'Faveeo', 'Favicon downloader', 'FavOrg', 'Feed Wrangler', 'Feedbin', 'FeedBooster', 'FeedBucket', 'FeedBunch\/[0-9]', 'FeedBurner', 'FeedChecker', 'Feedly', 'Feedspot', 'Feedwind\/[0-9]', 'feeltiptop', 'Fetch API', 'Fetch\/[0-9]', 'Fever\/[0-9]', 'findlink', 'findthatfile', 'FlipboardBrowserProxy', 'FlipboardProxy', 'FlipboardRSS', 'fluffy', 'flynxapp', 'forensiq', 'FoundSeoTool\/[0-9]', 'free thumbnails', 'FreeWebMonitoring SiteChecker', 'Funnelback', 'g00g1e\.net', 'GAChecker', 'ganarvisitas\/[0-9]', 'geek-tools', 'Genderanalyzer', 'Genieo', 'GentleSource', 'GetLinkInfo', 'getprismatic\.com', 'GetURLInfo\/[0-9]', 'GigablastOpenSource', 'github\.com\/', 'Go [\d\.]* package http', 'Go-http-client', 'gofetch', 'GomezAgent', 'gooblog', 'Goodzer\/[0-9]', 'Google favicon', 'Google Keyword Suggestion', 'Google Keyword Tool', 'Google PP Default', 'Google Search Console', 'Google Web Preview', 'Google-Adwords', 'Google-Apps-Script', 'Google-Calendar-Importer', 'Google-HTTP-Java-Client', 'Google-Publisher-Plugin', 'Google-SearchByImage', 'Google-Site-Verification', 'Google-Structured-Data-Testing-Tool', 'Google-Youtube-Links', 'google_partner_monitoring', 'GoogleDocs', 'GoogleHC\/', 'GoogleProducer', 'GoScraper', 'GoSpotCheck', 'GoSquared-Status-Checker', 'gosquared-thumbnailer', 'GotSiteMonitor', 'grabify', 'Grammarly', 'grouphigh', 'grub-client', 'GTmetrix', 'gvfs\/', 'HAA(A)?RTLAND http client', 'Hatena', 'hawkReader', 'HEADMasterSEO', 'HeartRails_Capture', 'heritrix', 'hledejLevne\.cz\/[0-9]', 'Holmes', 'HootSuite Image proxy', 'Hootsuite-WebFeed\/[0-9]', 'HostTracker', 'ht:\/\/check', 'htdig', 'HTMLParser\/', 'HTTP-Header-Abfrage', 'http-kit', 'HTTP-Tiny', 'HTTP_Compression_Test', 'http_request2', 'http_requester', 'HttpComponents', 'httphr', 'HTTPMon', 'PEAR HTTPRequest', 'httpscheck', 'httpssites_power', 'httpunit', 'HttpUrlConnection', 'httrack', 'hosterstats', 'huaweisymantec', 'HubPages.*crawlingpolicy', 'HubSpot Connect', 'HubSpot Marketing Grader', 'HyperZbozi.cz Feeder', 'i2kconnect\/', 'ichiro', 'IdeelaborPlagiaat', 'IDG Twitter Links Resolver', 'IDwhois\/[0-9]', 'Iframely', 'igdeSpyder', 'IlTrovatore', 'ImageEngine\/', 'Imagga', 'InAGist', 'inbound\.li parser', 'InDesign%20CC', 'infegy', 'infohelfer', 'InfoWizards Reciprocal Link System PRO', 'Instapaper', 'inpwrd\.com', 'Integrity', 'integromedb', 'internet_archive', 'InternetSeer', 'internetVista monitor', 'IODC', 'IOI', 'iplabel', 'IPS\/[0-9]', 'ips-agent', 'IPWorks HTTP\/S Component', 'iqdb\/', 'Irokez', 'isitup\.org', 'iskanie', 'iZSearch', 'janforman', 'Jigsaw', 'Jobboerse', 'jobo', 'Jobrapido', 'JS-Kit', 'KeepRight OpenStreetMap Checker', 'KeyCDN Perf Test', 'Keywords Research', 'KickFire', 'KimonoLabs\/', 'Kml-Google', 'knows\.is', 'kouio', 'KrOWLer', 'kulturarw3', 'KumKie', 'L\.webis', 'Larbin', 'LayeredExtractor', 'LibVLC', 'libwww', 'Licorne Image Snapshot', 'Liferea\/', 'link checker', 'Link Valet', 'link_thumbnailer', 'LinkAlarm\/', 'linkCheck', 'linkdex', 'LinkExaminer', 'linkfluence', 'linkpeek', 'LinkTiger', 'LinkWalker', 'Lipperhey', 'livedoor ScreenShot', 'LoadImpactPageAnalyzer', 'LoadImpactRload', 'LongURL API', 'looksystems\.net', 'ltx71', 'lwp-trivial', 'lycos', 'LYT\.SR', 'mabontland', 'MagpieRSS', 'Mail.Ru', 'MailChimp\.com', 'Mandrill', 'MapperCmd', 'marketinggrader', 'Mediapartners-Google', 'MegaIndex\.ru', 'Melvil Rawi\/', 'MergeFlow-PageReader', 'Metaspinner', 'MetaURI', 'Microsearch', 'Microsoft-WebDAV-MiniRedir', 'Microsoft Data Access Internet Publishing Provider Protocol', 'Microsoft Office ', 'Microsoft Windows Network Diagnostics', 'Mindjet', 'Miniflux', 'mixdata dot com', 'mixed-content-scan', 'Mnogosearch', 'mogimogi', 'Mojolicious \(Perl\)', 'monitis', 'Monitority\/[0-9]', 'montastic', 'MonTools', 'Moreover', 'Morning Paper', 'mowser', 'Mrcgiguy', 'mShots', 'MVAClient', 'nagios', 'Najdi\.si\/', 'Needle\/', 'NETCRAFT', 'NetLyzer FastProbe', 'netresearch', 'NetShelter ContentScan', 'NetTrack', 'Netvibes', 'Neustar WPM', 'NeutrinoAPI', 'NewsBlur .*Finder', 'NewsGator', 'newsme', 'newspaper\/', 'NG-Search', 'nineconnections\.com', 'NLNZ_IAHarvester', 'Nmap Scripting Engine', 'node-superagent', 'node\.io', 'nominet\.org\.uk', 'Norton-Safeweb', 'Notifixious', 'notifyninja', 'nuhk', 'nutch', 'Nuzzel', 'nWormFeedFinder', 'Nymesis', 'Ocelli\/[0-9]', 'oegp', 'okhttp', 'Omea Reader', 'omgili', 'Online Domain Tools', 'OpenCalaisSemanticProxy', 'Openstat\/', 'OpenVAS', 'Optimizer', 'Orbiter', 'OrgProbe\/[0-9]', 'ow\.ly', 'ownCloud News', 'OxfordCloudService\/[0-9]', 'Page Analyzer', 'Page Valet', 'page2rss', 'page_verifier', 'PagePeeker', 'Pagespeed\/[0-9]', 'Panopta', 'panscient', 'parsijoo', 'PayPal IPN', 'Pcore-HTTP', 'Pearltrees', 'peerindex', 'Peew', 'PhantomJS\/', 'Photon\/', 'phpcrawl', 'phpservermon', 'Pi-Monster', 'ping\.blo\.gs\/', 'Pingdom', 'Pingoscope', 'PingSpot', 'pinterest\.com', 'Pizilla', 'Ploetz \+ Zeller', 'Plukkie', 'PocketParser', 'Pompos', 'Porkbun', 'Port Monitor', 'postano', 'PostPost', 'postrank', 'PowerPoint\/', 'Priceonomics Analysis Engine', 'PritTorrent\/[0-9]', 'Prlog', 'probethenet', 'Project 25499', 'Promotion_Tools_www.searchenginepromotionhelp.com', 'prospectb2b', 'Protopage', 'proximic', 'pshtt, https scanning', 'PTST ', 'PTST\/[0-9]+', 'Pulsepoint XT3 web scraper', 'Python-httplib2', 'python-requests', 'Python-urllib', 'Qirina Hurdler', 'QQDownload', 'Qseero', 'Qualidator.com SiteAnalyzer', 'Quora Link Preview', 'Qwantify', 'Radian6', 'RankSonicSiteAuditor', 'Readability', 'RealPlayer%20Downloader', 'RebelMouse', 'redback\/', 'Redirect Checker Tool', 'ReederForMac', 'request\.js', 'ResponseCodeTest\/[0-9]', 'RestSharp', 'RetrevoPageAnalyzer', 'Riddler', 'Rival IQ', 'Robosourcer', 'Robozilla\/[0-9]', 'ROI Hunter', 'RPT-HTTPClient', 'RSSOwl', 'safe-agent-scanner', 'SalesIntelligent', 'SauceNAO', 'SBIder', 'Scoop', 'scooter', 'ScoutJet', 'ScoutURLMonitor', 'Scrapy', 'ScreenShotService\/[0-9]', 'Scrubby', 'search\.thunderstone', 'SearchSight', 'Seeker', 'semanticdiscovery', 'semanticjuice', 'Semiocast HTTP client', 'SEO Browser', 'Seo Servis', 'seo-nastroj.cz', 'Seobility', 'SEOCentro', 'SeoCheck', 'SeopultContentAnalyzer', 'Server Density Service Monitoring', 'servernfo\.com', 'Seznam screenshot-generator', 'Shelob', 'Shoppimon Analyzer', 'ShoppimonAgent\/[0-9]', 'ShopWiki', 'ShortLinkTranslate', 'shrinktheweb', 'SilverReader', 'SimplePie', 'SimplyFast', 'Site-Shot\/', 'Site24x7', 'SiteBar', 'SiteCondor', 'siteexplorer\.info', 'SiteGuardian', 'Siteimprove\.com', 'Sitemap(s)? Generator', 'Siteshooter B0t', 'SiteTruth', 'sitexy\.com', 'SkypeUriPreview', 'slider\.com', 'slurp', 'SMRF URL Expander', 'SMUrlExpander', 'Snappy', 'SniffRSS', 'sniptracker', 'Snoopy', 'sogou web', 'SortSite', 'spaziodati', 'Specificfeeds', 'speedy', 'SPEng', 'Spinn3r', 'spray-can', 'Sprinklr ', 'spyonweb', 'Sqworm', 'SSL Labs', 'StackRambler', 'Statastico\/', 'StatusCake', 'Stratagems Kumo', 'Stroke.cz', 'StudioFACA', 'suchen', 'summify', 'Super Monitoring', 'Surphace Scout', 'SwiteScraper', 'Symfony2 BrowserKit', 'SynHttpClient-Built', 'Sysomos', 'T0PHackTeam', 'Tarantula\/', 'Taringa UGC', 'teoma', 'terrainformatica\.com', 'Test Certificate Info', 'Tetrahedron\/[0-9]', 'The Drop Reaper', 'The Expert HTML Source Viewer', 'theinternetrules', 'theoldreader\.com', 'Thumbshots', 'ThumbSniper', 'TinEye', 'Tiny Tiny RSS', 'topster', 'touche.com', 'Traackr.com', 'truwoGPS', 'tweetedtimes\.com', 'Tweetminster', 'Tweezler\/', 'Twikle', 'Twingly', 'ubermetrics-technologies', 'uclassify', 'UdmSearch', 'Untiny', 'UnwindFetchor', 'updated', 'Upflow', 'URLChecker', 'URLitor.com', 'urlresolver', 'Urlstat', 'UrlTrends Ranking Updater', 'Vagabondo', 'vBSEO', 'via ggpht\.com GoogleImageProxy', 'VidibleScraper\/', 'visionutils', 'vkShare', 'voltron', 'voyager\/', 'VSAgent\/[0-9]', 'VSB-TUO\/[0-9]', 'VYU2', 'w3af\.org', 'W3C-checklink', 'W3C-mobileOK', 'W3C_I18n-Checker', 'W3C_Unicorn', 'wangling', 'WatchMouse', 'WbSrch\/', 'web-capture\.net', 'Web-Monitoring', 'Web-sniffer', 'Webauskunft', 'WebCapture', 'WebClient\/', 'webcollage', 'WebCookies', 'WebCorp', 'WebDoc', 'WebFetch', 'WebImages', 'WebIndex', 'webkit2png', 'webmastercoffee', 'webmon ', 'webscreenie', 'Webshot', 'Website Analyzer\/', 'websitepulse[+ ]checker', 'Websnapr\/', 'Webthumb\/[0-9]', 'WebThumbnail', 'WeCrawlForThePeace', 'WeLikeLinks', 'WEPA', 'WeSEE', 'wf84', 'wget', 'WhatsApp', 'WhatsMyIP', 'WhatWeb', 'WhereGoes\?', 'Whibse', 'Whynder Magnet', 'Windows-RSS-Platform', 'WinHttpRequest', 'wkhtmlto', 'wmtips', 'Woko', 'Word\/', 'WordPress\/', 'wotbox', 'WP Engine Install Performance API', 'wprecon\.com survey', 'WPScan', 'wscheck', 'WWW-Mechanize', 'www\.monitor\.us', 'XaxisSemanticsClassifier', 'Xenu Link Sleuth', 'XING-contenttabreceiver\/[0-9]', 'XmlSitemapGenerator', 'xpymep([0-9]?)\.exe', 'Y!J-(ASR|BSC)', 'Yaanb', 'yacy', 'Yahoo Ad monitoring', 'Yahoo Link Preview', 'YahooCacheSystem', 'YahooYSMcm', 'YandeG', 'yandex', 'yanga', 'yeti', ' YLT', 'Yo-yo', 'Yoleo Consumer', 'yoogliFetchAgent', 'YottaaMonitor', 'yourls\.org', 'Zao', 'Zemanta Aggregator', 'Zend\\\\Http\\\\Client', 'Zend_Http_Client', 'zgrab', 'ZnajdzFoto', 'ZyBorg', '[a-z0-9\-_]*((?<!cu)bot|crawler|archiver|transcoder|spider|uptime|validator|fetcher)');
            if (Swift_Performance::check_option('exclude-crawlers',1, '!=')){
                  return false;
            }

            return preg_match('~('.implode('|', $crawlers).')~', $_SERVER['HTTP_USER_AGENT']);
      }

      /**
       * Is cached version identical for the request
       * @return boolean
       */
      public static function is_identical($key = ''){
            if (Swift_Performance::check_option('resource-saving-mode', 1)){
                  $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                  $key = (empty($key) && (self::is_cacheable_ajax() || self::is_cacheable_dynamic()) ? Swift_Performance::get_unique_id() : (empty($key) ? $request_uri : $key));
                  $identical = get_transient('swift_performance_is_identical_' . md5($key));
                  if ($identical !== false){
                        return true;
                  }
                  else {
                        set_transient('swift_performance_is_identical_' . md5($key), true, 3600);
                  }
            }

            return false;
      }

      /**
       * Check is maintenance mode active
       * @return boolean
       */
      public static function is_maintenance(){
            if (!file_exists( ABSPATH . '.maintenance' )){
                  return false;
            }

      	global $upgrading;

      	include( ABSPATH . '.maintenance' );
      	// If the $upgrading timestamp is older than 10 minutes, don't die.
      	if ( ( time() - $upgrading ) >= 600 ){
                  return false;
            }
      		return true;
      }

      /**
       * Locate cache file and return the first match if exists
       * @return string|boolean
       */
      public static function locate_cache_file($permalink){
            $cache_file = false;

            $basedir = trailingslashit(Swift_Performance::get_option('cache-path')) . SWIFT_PERFORMANCE_CACHE_BASE_DIR;
            $basedir_regex = trailingslashit(Swift_Performance::get_option('cache-path')) . SWIFT_PERFORMANCE_CACHE_BASE_DIR . trailingslashit('('.implode('|', apply_filters('swift_performance_enabled_hosts', array(parse_url(Swift_Performance::home_url(), PHP_URL_HOST)))).')/' . trim(preg_quote(parse_url($permalink, PHP_URL_PATH)), '/'));

            if (@file_exists($basedir)){
                  $Directory = new RecursiveDirectoryIterator($basedir);
                  $Iterator = new RecursiveIteratorIterator($Directory);
                  $Regex = new RegexIterator($Iterator, '#'.$basedir_regex.'(@prefix/([_a-z0-9]+)/)?(desktop|mobile)/(unauthenticated|(authenticated/(a-z0-9+)))/((index|404)\.html|index\.xml|index\.json)$#i', RecursiveRegexIterator::GET_MATCH);

                  foreach ($Regex as $filename=>$file){
                          $cache_file = $filename;
                          break;
                  }
            }

            return $cache_file;
      }

      /**
       * Check if given URL is already in cache
       * @param string $permalink
       * @return boolean
       */
      public static function is_cached($permalink){
            return (self::get_cache_type($permalink) !== false);
      }

      /**
      * Get cached URL type
      * @param string $permalink
      * @return boolean|string (html|json|xml|404|false)
      */
      public static function get_cache_type($permalink){
            if (Swift_Performance::check_option('caching-mode', array('disk_cache_rewrite', 'disk_cache_php'), 'IN')){
                  $cache_file = Swift_Performance_Cache::locate_cache_file($permalink);
                  if (empty($cache_file)){
                        return false;
                  }

                  $file = trailingslashit(dirname($cache_file));

                  if (!file_exists($file)){
                        return false;
                  }
                  if (file_exists($file . 'index.html')){
                        return 'html';
                  }
                  if (file_exists($file . 'index.json')){
                        return 'json';
                  }
                  if (file_exists($file . 'index.xml')){
                        return 'xml';
                  }
                  if (file_exists($file . '404.html')){
                        return '404';
                  }
            }
            else if (Swift_Performance::check_option('caching-mode', 'memcached_php')){
                  $path = dirname(Swift_Performance_Cache::locate_cache_file($permalink));

                  $cached_request = Swift_Performance_Cache::memcached_get($path . 'index.html');
                  if (!empty($cached_request)){
                        return 'html';
                  }

                  $cached_request   = Swift_Performance_Cache::memcached_get($path . 'index.xml');
                  if (!empty($cached_request)) {
                        return 'xml';
                  }

                  $cached_request   = Swift_Performance_Cache::memcached_get($path . 'index.json');
                  if (!empty($cached_request)) {
                        return 'json';
                  }

                  $cached_request   = Swift_Performance_Cache::memcached_get($path . '404.html');
                  if (!empty($cached_request)) {
                        return '404';
                  }
            }
            return false;
      }

      /**
      * Get cache creation time
      * @param string $permalink
      * @return int
      */
      public static function get_cache_time($permalink){
            $time = 0;
            $path = trailingslashit(dirname(Swift_Performance_Cache::locate_cache_file($permalink)));
            if (self::is_cached($permalink)){
                  if (Swift_Performance::check_option('caching-mode', array('disk_cache_rewrite', 'disk_cache_php'), 'IN')){
                        @$time = filectime($path);
                  }
                  else {
                        $cached_request = Swift_Performance_Cache::memcached_get($path . 'index.html');
                        if (empty($cached_request)) {
                              $cached_request = Swift_Performance_Cache::memcached_get($path . 'index.xml');
                        }
                        if (empty($cached_request)) {
                              $cached_request = Swift_Performance_Cache::memcached_get($path . 'index.json');
                        }
                        if (empty($cached_request)) {
                              $cached_request = Swift_Performance_Cache::memcached_get($path . '404.html');
                        }
                        if (!empty($cached_request) && isset($cached_request['time'])){
                              $time = $cached_request['time'];
                        }
                  }
            }
            return (int)$time;
      }

      /**
       * Write cached file
       * @param string $file file name
       * @param string $content file content (default empty)
       * @param boolean $force override existing (default false)
       * @return string public URL
       */
      public static function write_file($file, $content = '', $force = false){
            // Play in the sandbox only...
            if (strpos($file, './')){
                  return;
            }

            // Don't write empty content
            if (empty($content)){
                  return;
            }

            $dir = SWIFT_PERFORMANCE_CACHE_DIR . dirname($file);
            // Write cached file if file doesn't exists or we use force mode
            if ($force || !file_exists(SWIFT_PERFORMANCE_CACHE_DIR . $file)){
                  if (!file_exists($dir)){
                        @mkdir($dir, 0777, true);
                  }
                  @file_put_contents(SWIFT_PERFORMANCE_CACHE_DIR . $file, $content);

            }

            // Logged in hash
            $hash = '';
            if (isset($_COOKIE[LOGGED_IN_COOKIE])){
                  list(,,, $hash) = explode('|', $_COOKIE[LOGGED_IN_COOKIE]);
            }

            // Create empty index.html in every folder recursively to prevent directory index in cache
            $current = SWIFT_PERFORMANCE_CACHE_DIR;
            $folders = explode('/', dirname($file));
            for ($i=0;$i<count($folders);$i++){
                  $current .= $folders[$i] . '/';
                  if (($current == SWIFT_PERFORMANCE_CACHE_DIR . 'js/' || ($folders[$i] == 'authenticated' && isset($folders[$i+1]) && $folders[$i+1] == md5($hash))) && !file_exists($current . 'index.html')){
                        @touch($current . 'index.html');
                  }
            }

            Swift_Performance::log('Write file ' . SWIFT_PERFORMANCE_CACHE_URL . $file, 9);

            // Return with cached file URL
            return SWIFT_PERFORMANCE_CACHE_URL . $file;
      }

      /**
       * Clear cache folder recursively
       * @param string $dir
       * @return boolean
       */
      public static function recursive_rmdir($dir = '', $check_filetime = false){
            $basedir = trailingslashit(Swift_Performance::get_option('cache-path')) . SWIFT_PERFORMANCE_CACHE_BASE_DIR;

            foreach (apply_filters('swift_performance_enabled_hosts', array(parse_url(Swift_Performance::home_url(), PHP_URL_HOST))) as $host){
                  $cache_dir = trailingslashit($basedir . $host);
                  if (!file_exists($cache_dir . $dir)){
            		continue;
            	}

                  if (strpos(realpath($cache_dir . $dir), realpath($cache_dir)) === false){
                        continue;
                  }

            	$files = array_diff(scandir($cache_dir . $dir), array('.','..'));
            	foreach ($files as $file) {
                        if (!$check_filetime || (time() - filectime($cache_dir . $dir . '/'. $file) >= Swift_Performance::get_option('cache-expiry-time') && !in_array($dir, array('/css','/js'))) ){
            		      is_dir($cache_dir . $dir . '/'. $file) ? self::recursive_rmdir($dir . '/'. $file, $check_filetime) : @unlink($cache_dir . $dir . '/'. $file);
                        }
            	}

            	@rmdir($cache_dir . $dir);
            }
      }

      /**
       * Send PURGE request to varnish
       * @param string $url
       * @param boolean $wildcard (default false)
       */
      public static function purge_varnish_url($url, $wildcard = false){
            do_action('swift_performance_before_purge_varnish', $url, $wildcard);
            $varnish_host = Swift_Performance::get_option('custom-varnish-host');
            if (empty($varnish_host)){
                  $varnish_host = 'http://' . parse_url(Swift_Performance::home_url(), PHP_URL_HOST);
            }

            $parsed_url = parse_url($url);
            $purge_url  = $varnish_host . (isset($parsed_url['path']) ? $parsed_url['path'] : '') . ($wildcard ? '.*' : '');

		$response = wp_remote_request($purge_url, array( 'method' => apply_filters('swift_performance_purge_varnish_method','PURGE'), 'headers' => array( 'host' => parse_url(home_url(), PHP_URL_HOST), 'X-Purge-Method' => ($wildcard ? 'regex' : 'exact'))));
            Swift_Performance::log('Purge Varnish URL: ' . $purge_url, 9);
            do_action('swift_performance_after_purge_varnish', $url, $wildcard);
      }

      /**
       * Get first 20 CF zones
       * @return boolean|array
       */
      public static function get_cloudflare_zones(){
            if (Swift_Performance::check_option('cloudflare-email', '') || Swift_Performance::check_option('cloudflare-api-key', '')){
                  return false;
            }

            $host       = Swift_Performance::get_option('cloudflare-host');
            $host       = (empty($host) ? parse_url(Swift_Performance::home_url(), PHP_URL_HOST) : $host);
            $zones      = get_transient('swift_performance_'.$host.'_cf_zones');
            if (empty($zones)){
                  $response = wp_remote_get('https://api.cloudflare.com/client/v4/zones?name='.$host.'&status=active&page=1&per_page=20&order=status&direction=desc&match=all',
                        array(
                              'headers' => array(
                        		'X-Auth-Email' => Swift_Performance::get_option('cloudflare-email'),
                        		'X-Auth-Key' => Swift_Performance::get_option('cloudflare-api-key'),
                        		'Content-Type' => 'application/json'
      	                  )
                        )
                  );
                  if (is_wp_error($response)){
                        Swift_Performance::log('Cloudflare get zones failed: ' . $response->get_error_message(), 1);
                  }
                  else {
                        $decoded = json_decode($response['body']);
                        if (isset($decoded->errors) && !empty($decoded->errors)){
                              Swift_Performance::log('Cloudflare API error: ' . $decoded->errors[0]->message, 1);
                        }
                        else if (isset($decoded->result)){
                              $zones   = $decoded->result;
                              // Cache it for 60 sec
                              set_transient('swift_performance_'.$host.'_cf_zones', $zones, 60);
                        }
                        else {
                              Swift_Performance::log('Cloudflare zones are missing', 6);
                        }
                  }

            }

            return $zones;
      }

      /**
       * Get CF zones and cache it
       * @param string|array $files
       * @return boolean|array
       */
      public static function purge_cloudflare_zones($files = array()){
            do_action('swift_performance_before_purge_cloudflare_zones', $files);

            if (empty($files)){
                  $body = '{"purge_everything":true}';
            }
            else {
                  $body = '{"files":' . json_encode((array)$files) . '}';
            }

            $zones = self::get_cloudflare_zones();
            if (!empty($zones) && is_array($zones)){
                  foreach ($zones as $zone) {
                        $response = wp_remote_request('https://api.cloudflare.com/client/v4/zones/'.$zone->id.'/purge_cache', array(
                  		'method' => 'DELETE',
                  		'body' => $body,
                              'headers' => array(
                        		'X-Auth-Email' => Swift_Performance::get_option('cloudflare-email'),
                        		'X-Auth-Key' => Swift_Performance::get_option('cloudflare-api-key'),
                        		'Content-Type' => 'application/json'
      	                  )
                  	));
                        if (is_wp_error($response)){
                              Swift_Performance::log('Cloudflare purge zone failed. Zonde ID: ' . $zone->id . ' Error:' . $response->get_error_message(), 1);
                        }
                        else{
                              $decoded = json_decode($response['body']);

                              if (isset($decoded->success) && $decoded->success == 'true'){
                                    if (empty($files)){
                                          Swift_Performance::log('Cloudflare zone ' . $zone->id . ' all files were purged.', 9);
                                    }
                                    else {
                                          Swift_Performance::log('Cloudflare zone ' . $zone->id . ' were purged. Files: ' . implode(', ', (array)$files), 9);
                                    }
                              }
                              else {
                                    Swift_Performance::log('Cloudflare purge zone failed. Zonde ID: ' . $zone->id . ' Error:' . json_encode($decoded->errors));
                              }
                        }
                  }
            }
            do_action('swift_performance_after_purge_cloudflare_zones', $files);
      }

      /**
       * Get REST API url for specific post (if exists)
       * @return string
       */
      public static function get_rest_url($post_id){
            $post       = get_post_type_object($post_id);
            $post_type  = get_post_type($post_id);
            $namespace  = 'wp/v2/';
            $url        = '';
            if (function_exists('get_rest_url')){
                  if (isset($post->rest_base)){
                        $url = get_rest_url() . $namespace . $post->rest_base . '/' . $post_id . '/';
                  }
                  else if ($post_type == 'post') {
                        $url = get_rest_url() . $namespace . 'posts/' . $post_id . '/';
                  }
                  else if ($post_type == 'page') {
                        $url = get_rest_url() . $namespace . 'pages/' . $post_id . '/';
                  }
            }
            return $url;
      }

      /**
       * Get archive page urls for specific post
       * @return array
       */
      public static function get_archive_urls($post_id){
            $namespace  = 'wp/v2/';
            $urls = array();

            // Categories
            $categories = get_the_category($post_id);
            if (!empty($categories)){
                  foreach ($categories as $category){
                        $urls[] = get_category_link($category->term_id);
      			$urls[] = get_rest_url() . $namespace . 'categories/' . $category->term_id . '/';
                  }
            }

            // Tags
            $tags = get_the_category($post_id);
            if (!empty($tags)){
                  foreach ($tags as $tag){
                        $urls[] = get_tag_link($tag->term_id);
      			$urls[] = get_rest_url() . $namespace . 'tags/' . $tag->term_id . '/';
                  }
            }

            // WooCommerce product
            if (get_post_type($post_id) == 'product' || get_post_type($post_id) == 'product_variation'){
                  $urls[] = get_permalink(get_option('woocommerce_shop_page_id'));
            }

            return $urls;
      }


      /**
       * Get term urls for specific post
       * @return array
       */
      public static function get_term_urls($post_id){
            $taxonomies = get_post_taxonomies($post_id);
            if (!empty($taxonomies)){
                  foreach ($taxonomies as $taxonomy){
                        $terms = wp_get_post_terms($post_id, $taxonomy);
                        foreach ($terms as $term) {
                              $urls[] = get_term_link($term);
                              $urls[] = get_rest_url() . $namespace. trailingslashit($term->taxonomy) . $term->slug . '/';
                        }
                  }
            }
      }

      /**
       * Get author page urls for specific post
       * @return array
       */
      public static function get_author_urls($post_id){
            $urls = array();
            $namespace  = 'wp/v2/';
            $author_id = get_post_field('post_author', $post_id);
            $urls[] = get_author_posts_url($author_id);
            if (Swift_Performance::check_option('cache-feed',1)){
                  $urls[] = get_author_feed_link($author_id);
            }
            if (function_exists('get_rest_url')){
                  $urls[] = get_rest_url() . $namespace . 'users/' . $author_id . '/';
            }

            return $urls;
      }

      /**
       * Get an instance of Memcached API
       * @return Memcached
       */
      public static function get_memcache_instance(){
            if (!isset($GLOBALS['swift_performance_memcached']) || !is_object($GLOBALS['swift_performance_memcached'])){
                  $result = false;
                  // Create Memcached instance
                  if (class_exists('Memcached')){
                        $GLOBALS['swift_performance_memcached'] = new Memcached();
                        $result = $GLOBALS['swift_performance_memcached']->addServer(Swift_Performance::get_option('memcached-host'), (int)Swift_Performance::get_option('memcached-port'));
                  }
                  // Memcached isn't exists
                  else {
                        $fail = true;
                  }

                  // Fallback if init was failed
                  if ($result === false){
                        Swift_Performance::set_option('caching-mode', 'disk_cache_php');
                        Swift_Performance::update_option('caching-mode', 'disk_cache_php');
                  }
            }
            return @$GLOBALS['swift_performance_memcached'];
      }

      /**
       * Set content in memcached
       * @param string $path
       * @param string $content
       */
      public static function memcached_set($path, $content){
            $memcached = Swift_Performance_Cache::get_memcache_instance();

            $memcached->set('swift-performance_' . $path, $content, Swift_Performance::get_option('cache-expiry-time'));
      }

      /**
      * Get content from memcached
      * @param string $path
      */
      public static function memcached_get($path){
            $memcached = Swift_Performance_Cache::get_memcache_instance();

            return $memcached->get('swift-performance_' . $path);
      }

      /**
       * Proxy the current request and return the results
       * @return string
       */
      public static function proxy_request(){
            // Headers
            $headers = array('X-Proxy' => 'Swift_Performance');
            foreach ($_SERVER as $key => $value) {
                  $headers[preg_replace('~^http_~i', '', $key)] = $value;
            }

            // Cookies
            $cookies = array();
            foreach ( $_COOKIE as $name => $value ) {
                  $cookies[] = new WP_Http_Cookie( array( 'name' => $name, 'value' => $value ) );
            }

            // Proxy the original request
            $response = wp_remote_post(home_url($_SERVER['REQUEST_URI']), array(
                  'method' => 'POST',
                  'timeout' => 45,
                  'blocking' => true,
                  'headers' => $headers,
                  'body' => $_POST,
                  'cookies' => $cookies
                )
            );

            if (!is_wp_error($response)){
                  return $response['body'];
            }

            return false;
      }

      /**
       * Remove http to avoid mixed content on cached pages
       */
      public static function avoid_mixed_content($html){
            if (Swift_Performance::check_option('avoid-mixed-content',1)){
                  // Avoid mixed content issues
                  $html = preg_replace('~https?://([^"\'\s]*)\.(jpe?g|png|gif|swf|flv|mpeg|mpg|mpe|3gp|mov|avi|wav|flac|mp2|mp3|m4a|mp4|m4p|aac)~i', "//$1.$2", $html);
                  $html = preg_replace('~src=(\'|")https?:~', 'src=$1', $html);
                  $html = preg_replace('~<link rel=\'stylesheet\'((?!href=).)*href=(\'|")https?:~', '<link rel=\'stylesheet\'$1href=$2', $html);
            }
            return $html;
      }

      /**
       * Generate and send HTTP2 server push headers
       */
      public static function server_push_headers($content){
            $assets = array();

            if (!empty($content)){
                  preg_match_all('~<link rel="stylesheet" href="([^"]+)"~', $content, $styles);
                  foreach ($styles[1] as $style) {
                        $assets[] = "<{$style}>; rel=preload; as=style";
                  }
                  preg_match_all('~<script src="([^"]+)" type="text/javascript">~', $content, $scripts);
                  foreach ($scripts[1] as $script) {
                        $assets[] = "<{$script}>; rel=preload; as=script";
                  }
                  preg_match_all('~xhr.open("GET", "([^"]+)")~', $content, $scripts);
                  foreach ($scripts[1] as $script) {
                        $assets[] = "<{$script}>; rel=preload; as=script";
                  }
            }

            if (!empty($assets)){
                  header('Link: ' . implode(',', $assets));
            }
      }

}

// Create instance
if (Swift_Performance::check_option('enable-caching', 1)){
      return new Swift_Performance_Cache();
}
else {
      return false;
}

?>

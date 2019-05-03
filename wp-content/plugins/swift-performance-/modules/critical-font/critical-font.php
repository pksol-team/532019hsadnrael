<?php

class Swift_Performance_Critical_Font {

      /**
       * Supported fonts
       */
      public $fonts = array(
            'FontAwesome' => 'fontawesome'
      );

      /**
       * Enqueued critical fonts
       */
      public $critical_font_srcs = array();

      public function __construct(){
            // Load assets on backend
		add_action('admin_enqueue_scripts', array($this, 'load_assets'));

            add_action('wp_ajax_swift_performance_enqueue_critical_font', array(__CLASS__, 'enqueue'));
            add_action('wp_ajax_swift_performance_dequeue_critical_font', array(__CLASS__, 'dequeue'));
            add_action('wp_ajax_swift_performance_scan_used_icons', array(__CLASS__, 'scan'));

            // Enqueue frontend
            add_action('wp_enqueue_scripts', function(){
                  foreach ($this->fonts as $font) {
                        if (file_exists(WP_CONTENT_DIR . '/fonts/' . SWIFT_PERFORMANCE_CACHE_BASE_DIR . $font . '/webfont.css')){
                              $url = content_url('fonts/' . SWIFT_PERFORMANCE_CACHE_BASE_DIR . $font . '/webfont.css');
                              $this->critical_font_srcs[] = apply_filters('style_loader_src', $url, 'swift-performance-critical-font');
                              wp_enqueue_style($font, $url);
                        }
                  }
            },PHP_INT_MAX);
      }

      /**
       * Load assets
       */
      public function load_assets($hook){
            if($hook == 'tools_page_'.SWIFT_PERFORMANCE_SLUG) {
                  wp_enqueue_style( SWIFT_PERFORMANCE_SLUG . '-fontawesome', SWIFT_PERFORMANCE_URI . 'modules/critical-font/fonts/fontawesome/css/font-awesome.min.css', array(), SWIFT_PERFORMANCE_VER );
            }
      }

      /**
       * Generate and enqueue font
       */
      public static function enqueue(){

            // Check user and nonce
            if (!current_user_can('manage_options') || !isset($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'swift-performance-ajax-nonce')){
                  wp_send_json(
                        array(
                              'type' => 'critical',
                              'text' => __('Your session has expired. Please refresh the page and try again.', 'swift-performance')
                        )
                  );
            }

            if (!isset($_REQUEST['content']) || empty($_REQUEST['content'])){
                  wp_send_json(
                        array(
                              'type' => 'critical',
                              'text' => esc_html__('Please select at least one icon.', 'swift-performance')
                        )
                  );
                  die;
            }

            // Set font
            switch ($_REQUEST['font']) {
                  case 'fontawesome':
                  default:
                        $font = 'fontawesome';
                        break;
            }

            // Font dir
            $dir = trailingslashit(WP_CONTENT_DIR . '/fonts/' . SWIFT_PERFORMANCE_CACHE_BASE_DIR . $font);

            // Save active icons
            $active = get_option('swift-perforomance-critical-font', array());
            $active[$font] = $_REQUEST['active'];
            update_option('swift-perforomance-critical-font', $active, false);


            // Create directory if not exists
            if (!file_exists($dir)){
                  if (is_writable(WP_CONTENT_DIR)){
                        @mkdir($dir, 0777, true);
                  }
                  else{
                        wp_send_json(
                              array(
                                    'type' => 'critical',
                                    'text' => sprintf(esc_html__('%s is not writable. Please change permissions.', 'swift-performance'), WP_CONTENT_DIR)
                              )
                        );
                  }
            }

            $response = wp_remote_post (
                  SWIFT_PERFORMANCE_API_URL . 'critical_font' ,array(
                              'timeout' => 30,
                              'sslverify' => false,
                              'user-agent' => 'SwiftPerformance',
                              'headers' => array (
                                          'X-ENVATO-PURCHASE-KEY' => Swift_Performance::get_option('purchase-key')
                              ),
                              'body' => array (
                                          'site' => Swift_Performance::home_url(),
                                          'args' => array(
                                                'font' => $font,
                                                'content' => $_REQUEST['content']
                                          ),
                                          'v'	 => SWIFT_PERFORMANCE_VER
                              )
                  )
            );
            if (!is_wp_error($response)){
                  Swift_Performance::log('Critical Font API call success: ' . $font, 9);

                  $fonts = json_decode($response['body'], true);
                  if (empty($fonts)){
                        wp_send_json(
                              array(
                                    'type' => 'critical',
                                    'text' => esc_html__('API error', 'swift-performance')
                              )
                        );
                        self::clear_font_dir($dir);
                        Swift_Performance::log('Critical Font API call failed (' . $response->get_error_message() . ')', 1);
                  }
                  else {
                        self::clear_font_dir($dir, false);
                        file_put_contents($dir . 'webfont-'.hash('crc32', $fonts['svg']).'.svg', base64_decode($fonts['svg']));
                        file_put_contents($dir . 'webfont-'.hash('crc32', $fonts['eot']).'.eot', base64_decode($fonts['eot']));
                        file_put_contents($dir . 'webfont-'.hash('crc32', $fonts['ttf']).'.ttf', base64_decode($fonts['ttf']));
                        file_put_contents($dir . 'webfont-'.hash('crc32', $fonts['woff']).'.woff', base64_decode($fonts['woff']));

                        // Create CSS
                        ob_start();
                        include SWIFT_PERFORMANCE_DIR . 'modules/critical-font/fonts/' . $font . '/prototype.php';
                        $prototype  = ob_get_clean();
                        $css        = $prototype . $_REQUEST['css'];
                        file_put_contents ($dir . 'webfont.css', stripcslashes($css));

                        Swift_Performance_Cache::clear_all_cache();

                        wp_send_json(
                              array(
                                    'type' => 'success',
                                    'text' => __('Critical font enqueued', 'swift-performance'),
                                    'status_message' => __('Enqueued', 'swift-performance')
                              )
                        );
                  }
            }
            else {
                  wp_send_json(
                        array(
                              'type' => 'critical',
                              'text' => sprintf(esc_html__('Critical Font API call failed: %s', 'swift-performance'), $response->get_error_message())
                        )
                  );
                  self::clear_font_dir($dir);
                  Swift_Performance::log('Critical Font API call failed (' . $response->get_error_message() . ')', 1);
            }
      }

      /**
      * Dequeue font
      */
      public static function dequeue(){
            // Check user and nonce
            if (!current_user_can('manage_options') || !isset($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'swift-performance-ajax-nonce')){
                  wp_send_json(
                        array(
                              'type' => 'critical',
                              'text' => __('Your session has expired. Please refresh the page and try again.', 'swift-performance')
                        )
                  );
            }

            switch ($_REQUEST['font']) {
                  case 'fontawesome':
                  default:
                        $font = 'fontawesome';
                        break;
            }
            $dir = trailingslashit(WP_CONTENT_DIR . '/fonts/' . SWIFT_PERFORMANCE_CACHE_BASE_DIR . $font);

            // Remove font dir
            self::clear_font_dir($dir);

            Swift_Performance_Cache::clear_all_cache();

            wp_send_json(
                  array(
                        'type' => 'success',
                        'text' => __('Critical font dequeued', 'swift-performance'),
                        'status_message' => __('Dequeued', 'swift-performance')
                  )
            );
      }

      /**
       * Scan fonts
       */
      public static function scan(){
            // Check user and nonce
            if (!current_user_can('manage_options') || !isset($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'swift-performance-ajax-nonce')){
                  wp_send_json(
                        array(
                              'type' => 'critical',
                              'text' => __('Your session has expired. Please refresh the page and try again.', 'swift-performance')
                        )
                  );
            }

            $icons = array();
            global $wpdb;

            switch ($_REQUEST['font']){
                  case 'fontawesome':
                  default:
                        $pattern = 'fa-([a-z0-9_-]{3,})';
                        break;
            }

            // Theme
            $Directory = new RecursiveDirectoryIterator(get_template_directory());
            $Iterator = new RecursiveIteratorIterator($Directory);
            $Regex = new RegexIterator($Iterator, '/\.(php|html?|js)$/i', RecursiveRegexIterator::GET_MATCH);
            foreach($Regex as $filename=>$file){
                  $content = file_get_contents($filename);

                  preg_match_all('~'.$pattern.'~i',$content, $_icons);
                  if (count($_icons[0]) < 20){
                        $icons = array_merge($icons, $_icons[0]);
                  }
            }

            // Childtheme
            if (get_stylesheet_directory() !== get_template_directory()){
                  $Directory = new RecursiveDirectoryIterator(get_stylesheet_directory());
                  $Iterator = new RecursiveIteratorIterator($Directory);
                  $Regex = new RegexIterator($Iterator, '/\.(php|html?|js)$/i', RecursiveRegexIterator::GET_MATCH);
                  foreach($Regex as $filename=>$file){
                        $content = file_get_contents($filename);

                        preg_match_all('~'.$pattern.'~i',$content, $_icons);
                        if (count($_icons[0]) < 20){
                              $icons = array_merge($icons, $_icons[0]);
                        }
                  }
            }

            // Active plugins
            foreach (get_option('active_plugins') as $plugin){
                  $Directory = new RecursiveDirectoryIterator(WP_PLUGIN_DIR . '/' . dirname($plugin));
                  $Iterator = new RecursiveIteratorIterator($Directory);
                  $Regex = new RegexIterator($Iterator, '/\.(php|html?|js)$/i', RecursiveRegexIterator::GET_MATCH);
                  foreach($Regex as $filename=>$file){
                        $content = file_get_contents($filename);

                        preg_match_all('~'.$pattern.'~i',$content, $_icons);
                        if (count($_icons[0]) < 20){
                              $icons = array_merge($icons, $_icons[0]);
                        }
                  }
            }

            foreach ($wpdb->get_col($wpdb->prepare("SELECT post_content FROM {$wpdb->posts} WHERE post_content REGEXP %s UNION SELECT option_value FROM {$wpdb->options} WHERE option_name != 'swift-perforomance-critical-font'", $pattern)) as $content){
                  preg_match_all('~'.$pattern.'~i',$content, $_icons);
                  $icons = array_merge($icons, $_icons[0]);
            }

            wp_send_json(array('selectors' => array_unique($icons)));
      }

      /**
       * Return supported fonts
       */
      public static function get_fonts(){
            return (array)$GLOBALS['swift_performance']->modules['critical-font']->fonts;
      }

      /**
       * Check is current URL critical font css
       * @return boolean
       */
      public static function is_critical_font($url){
            return (isset($GLOBALS['swift_performance']->modules['critical-font']->critical_font_srcs) && in_array($url, $GLOBALS['swift_performance']->modules['critical-font']->critical_font_srcs));
      }

      /**
       * Check is font enqueued
       */
      public static function is_enqueued($font){
            $dir = trailingslashit(WP_CONTENT_DIR . '/fonts/' . SWIFT_PERFORMANCE_CACHE_BASE_DIR . $font);
            return (file_exists($dir));
      }

      /**
       * Generate Font List
       * @return array
       */
      public static function font_list($font){
            $fonts      = array();
            $active     = get_option('swift-perforomance-critical-font', array());
            switch ($font) {
                  case 'fontawesome':
                  default:
                        $class = file_get_contents(SWIFT_PERFORMANCE_DIR . 'modules/critical-font/fonts/fontawesome/css/font-awesome.min.css');
                        preg_match_all('~([^\{\}]*)(\s*)?\{(\s*)?content:(\s*)?("|\')([^"\']*)("|\');?(\s*)?\}~', $class, $rules);
                        for ($i=0; $i<count($rules[1]); $i++){
                              $class = trim(preg_replace('~:before(.*)~','', $rules[1][$i]), '.');
                              $fonts[$i] = array(
                                    'class' => $class,
                                    'selector' => $rules[1][$i],
                                    'content' => $rules[6][$i],
                                    'active'  => in_array($class, (array)$active[$font])
                              );
                        }
                        break;
            }

            return $fonts;
      }

      /**
       * Remove font dir
       */
      public static function clear_font_dir($dir, $remove_dir = true){
            // Clear font dir
            $files = array_diff(scandir($dir), array('.','..'));
            foreach ($files as $file) {
                  @unlink($dir . $file);
            }

            // Remove dir
            if ($remove_dir){
                  @rmdir($dir);
            }
      }
}

return new Swift_Performance_Critical_Font();
?>

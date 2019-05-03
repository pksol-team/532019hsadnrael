<?php

class Swift_Performance_Third_Party {

      /**
       * Create Swift_Performance_Third_Party object
       */
      public function __construct(){
            // WooCommerce GEOIP
            if (Swift_Performance::check_option('woocommerce-geoip-support',1) && Swift_Performance::check_option('caching-mode', array('memcached_php', 'disk_cache_php'), 'IN')){
                  add_filter('swift_performance_cache_folder_prefix', array(__CLASS__, 'woocommerce_geoip_prefix'));
                  add_action('swift_performance_prebuild_cache_hit', array(__CLASS__, 'woocommerce_geiop_prebuild'));
            }

            // WooCommerce Session Cache
            if (Swift_Performance::check_option('woocommerce-session-cache', 1)){
                  add_action( 'swift_performance_woocommerce_session_cache_prebuild', array(__CLASS__, 'woocommerce_session_cache_prebuild'), 10, 2);

                  global $wpdb;
                  $shop_pages = (array)$wpdb->get_col("SELECT post_name FROM {$wpdb->posts} LEFT JOIN {$wpdb->options} ON option_value = ID WHERE option_name IN ('woocommerce_cart_page_id', 'woocommerce_checkout_page_id')");

                  foreach (array('wp_login','woocommerce_ajax_added_to_cart', 'woocommerce_removed_coupon','woocommerce_cart_emptied','woocommerce_add_to_cart','woocommerce_cart_item_removed','woocommerce_cart_item_restored','woocommerce_applied_coupon') as $action){
                        add_action($action, array(__CLASS__, 'woocommere_clear_session_cache'), PHP_INT_MAX);
                  }
                  add_filter('woocommerce_update_cart_action_cart_updated', array(__CLASS__, 'woocommere_clear_session_cache'), PHP_INT_MAX);

                  $cookie_name = apply_filters( 'woocommerce_cookie', 'wp_woocommerce_session_' . COOKIEHASH );
                  if (in_array(trim($_SERVER['REQUEST_URI'],'/'), $shop_pages) && isset($_COOKIE[$cookie_name]) && !empty($_COOKIE[$cookie_name]) && !isset($_POST['update_cart'])) {
                        add_filter('swift_performance_is_cacheable_dynamic', '__return_true');
                        $_POST['woocommerce-session-cache'] = md5($_COOKIE[$cookie_name]);
                  }
            }

            // Disable WooCommerce Cart Fragments AJAX
            add_action( 'wp_enqueue_scripts', array(__CLASS__, 'dequeue_woocommerce_cart_fragments'), 11);

            // Sitepress domain mapping
            add_filter('swift_performance_enabled_hosts', array(__CLASS__, 'sitepress_domain_mapping'));

            // WooCommerce Geolocation + Caching prebuild fix
            add_filter('option_woocommerce_default_customer_address', function($value){
                  if ($value == 'geolocation_ajax' && (isset($_GET['swift-preview']) || isset($_SERVER['HTTP_X_PREBUILD']) || isset($_SERVER['HTTP_X_MERGE_ASSETS']))){
                        return 'base';
                  }
                  else {
                        return $value;
                  }
            });

            // AJAXIFY WooCommerce prices
            if (Swift_Performance::check_option('woocommerce-price-ajaxify', 1)){
                  add_filter('swift_performance_ajaxify', function($selectors){
                        $selectors[] = '.woocommerce-Price-amount';
                        return $selectors;
                  });
            }
      }

      /**
       * Disable Cart Fragments
       */
      public static function dequeue_woocommerce_cart_fragments() {
            $disable = false;
            if (Swift_Performance::check_option('disable-cart-fragments', 'everywhere')){
                  $disable = true;
            }
            else if (Swift_Performance::check_option('disable-cart-fragments', 'non-shop')){
                  global $wpdb;
                  $results = $wpdb->get_col("SELECT option_value FROM {$wpdb->options} WHERE option_name LIKE 'woocommerce_%_page_id'", ARRAY_A);

                  if ((!function_exists('is_shop') || !is_shop()) || in_array(get_the_ID(), $results)){
                        $disable = true;
                  }
            }
            else if (Swift_Performance::check_option('disable-cart-fragments', 'specified-pages')){
                  $pages = (array)Swift_Performance::get_option('disable-cart-fragments-pages');
                  if (in_array(get_the_ID(), $pages)){
                        $disable = true;
                  }
            }
            else if (Swift_Performance::check_option('disable-cart-fragments', 'specified-urls')){
                  $urls = (array)Swift_Performance::get_option('disable-cart-fragments-urls');
                  foreach ($urls as $url){
                        if (strpos(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), parse_url($url, PHP_URL_PATH)) !== false){
                              $disable = true;
                        }
                  }
            }
            if ($disable){
                  wp_dequeue_script('wc-cart-fragments');
            }
      }

      /**
       * Run prebuild cache for all enabled countries
       */
       public static function woocommerce_geiop_prebuild($permalink){
            // Get allowed countries
            $allowed_countries = array_filter((array)Swift_Performance::get_option('woocommerce-geoip-allowed-countries'));

            if(empty($allowed_countries) && file_exists(WP_PLUGIN_DIR . '/woocommerce/i18n/countries.php')){
                  $allowed_countries = array_keys((array)apply_filters( 'woocommerce_countries', include WP_PLUGIN_DIR . '/woocommerce/i18n/countries.php'));
            }

            foreach ((array)$allowed_countries as $allowed_country) {
                  // Add country code to prebuild header
                  add_filter('swift_performance_prebuild_headers', function($headers) use ($allowed_country){
                        $headers['X-swift-country-code'] = strtoupper($allowed_country);
                        return $headers;
                  });

                  // Add country code to mobile prebuild header
                  add_filter('swift_performance_mobile_prebuild_headers', function($headers) use ($allowed_country){
                        $headers['X-swift-country-code'] = strtoupper($allowed_country);
                        return $headers;
                  });

                  Swift_Performance::prebuild_cache_hit($permalink);
            }
       }

      /**
       * Add country prefix
       */
      public static function woocommerce_geoip_prefix($prefix){
            if (isset($_SERVER['HTTP_X_SWIFT_COUNTRY_CODE'])){
                  add_filter('woocommerce_geolocate_ip', function(){
                        return $_SERVER['HTTP_X_SWIFT_COUNTRY_CODE'];
                  });
            }

            if(@file_exists(WP_PLUGIN_DIR . '/woocommerce/includes/class-wc-geolocation.php')){
                  include_once WP_PLUGIN_DIR . '/woocommerce/includes/class-wc-geolocation.php';
                  include_once WP_PLUGIN_DIR . '/woocommerce/includes/class-wc-geo-ip.php';

                  $geoloacte            = WC_Geolocation::geolocate_ip();
                  $allowed_countries    = (array)Swift_Performance::get_option('woocommerce-geoip-allowed-countries');
                  if (isset($geoloacte['country']) && !empty($geoloacte['country']) && (empty($allowed_countries) || in_array($geoloacte['country'], $allowed_countries)) ){
                        $prefix    = $geoloacte['country'];
                  }
            }

            return $prefix;
      }

      /**
       * Clear WooCommerce session cache
       */
      public static function woocommere_clear_session_cache($param){
            global $wpdb;
            $cookie_name = apply_filters( 'woocommerce_cookie', 'wp_woocommerce_session_' . COOKIEHASH );
            $hash = hash('crc32', serialize(array('woocommerce-session-cache' => md5($_COOKIE[$cookie_name]))));
            if (isset($_COOKIE[$cookie_name])){
                  $transients = $wpdb->get_col("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_swift_performance_dynamic_%_{$hash}'");
                  foreach ($transients as $transient) {
                        delete_transient(str_replace('_transient_','',$transient));
                  }

                  // Prebuild cache
                  $useragent = apply_filters('swift_performance_session_cache_useragent', (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:52.0) Gecko/20100101 Firefox/52.0'));
                  wp_schedule_single_event(time(), 'swift_performance_woocommerce_session_cache_prebuild', array($_COOKIE, $useragent));
            }

            return $param;
      }

      /**
       * Preload session cache
       * @param $array $user_cookies
       */
      public static function woocommerce_session_cache_prebuild($user_cookies, $useragent){
            global $wpdb;
            $shop_pages = (array)$wpdb->get_col("SELECT post_name FROM {$wpdb->posts} LEFT JOIN {$wpdb->options} ON option_value = ID WHERE option_name IN ('woocommerce_cart_page_id', 'woocommerce_checkout_page_id')");
            if (Swift_Performance::check_option('enable-remote-prebuild-cache', 1, '!=')){
                  $cookies = array();
                  foreach ($user_cookies as $name => $value) {
                      $cookies[] = new WP_Http_Cookie( array( 'name' => $name, 'value' => $value ) );
                  }

                  foreach ($shop_pages as $shop_page){
                        $response = wp_remote_get( trailingslashit(home_url($shop_page)), array('useragent' => $useragent, 'headers' => array('X-merge-assets' => 'true', 'X-Prebuild' => 'true'),  'cookies' => $cookies ) );
                  }
            }
      }

      /**
       * Detect third party cache
       * should run after plugins_loaded
       */
      public static function detect_cache(){
            $detected = false;

            // WP Engine detected
            if (class_exists("WpeCommon")) {
                  $detected = true;
            }

            // SG Optimizer detected
            if (function_exists('sg_cachepress_purge_cache')) {
                  $sg_cachepress = get_option('sg_cachepress');

                  if (isset($sg_cachepress['enable_cache']) && $sg_cachepress['enable_cache'] === 1){
                        $detected = true;
                  }
            }

            // Third party cache was detected
            if ($detected && !defined('SWIFT_PERFORMANCE_DISABLE_CACHE')){
                  // Hide caching options in settings
                  add_action('luv_framework_before_render_sections', function($that){
                        unset($that->args['sections']['caching']);

                        // optimize-prebuild-only
                        unset($that->args['sections']['optimization']['general']['fields'][2]);

                        // merge-background-only
                        unset($that->args['sections']['optimization']['general']['fields'][3]);
                  });

                  // Force disable prebuild/background only modes
                  Swift_Performance::update_option('optimize-prebuild-only', 0);
                  Swift_Performance::update_option('merge-background-only', 0);

                  // Disable caching
                  define('SWIFT_PERFORMANCE_DISABLE_CACHE', true);
            }

      }

      /**
       * Clear known third party caches
       */
      public static function clear_cache(){
            // Godaddy
            if (class_exists("\\WPaaS\\Cache")){
                  \WPaaS\Cache::ban();
            }

            // WP Engine
            if (class_exists("WpeCommon")) {
                  if (method_exists('WpeCommon', 'purge_varnish_cache')){
                        WpeCommon::purge_varnish_cache();
                  }
                  if (method_exists('WpeCommon', 'purge_memcached')){
                      WpeCommon::purge_memcached();
                  }
                  if (method_exists('WpeCommon', 'clear_maxcdn_cache')){
                      WpeCommon::clear_maxcdn_cache();
                  }
            }

            // Siteground
            if (function_exists('sg_cachepress_purge_cache')) {
                  sg_cachepress_purge_cache();
            }
      }

      /**
       * Add filter for enabled hosts
       * @param array $hosts
       * @return array
       */
      public static function sitepress_domain_mapping($hosts){
            global $sitepress;
            if (!empty($sitepress) && is_callable(array($sitepress, 'get_setting'))){
                  $domains = $sitepress->get_setting( 'language_domains', array() );
                  if (!empty($domains)){
                        $hosts = array_merge($hosts, $domains);
                  }
            }
            return $hosts;
      }

}

return new Swift_Performance_Third_Party();

?>

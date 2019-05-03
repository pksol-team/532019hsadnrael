<?php ob_start();?>
<?php
if (Swift_Performance::check_option('server-push',1)){
      $server_push = array();
      if (Swift_Performance::check_option('merge-styles',1) && Swift_Performance::check_option('critical-css',1) && Swift_Performance::check_option('inline_critical_css',0)){
            $server_push[] = '<'. Swift_Performance::home_dir() . str_replace(ABSPATH, '/', trailingslashit(trailingslashit(self::get_option('cache-path'))).SWIFT_PERFORMANCE_CACHE_BASE_DIR) . '$http_host$request_uri/css/%PREFIX%critical.css>; rel=preload; as=style';
      }
      if (Swift_Performance::check_option('merge-styles',1) && Swift_Performance::check_option('inline_full_css',0)){
            $server_push[] = '<'. Swift_Performance::home_dir() . str_replace(ABSPATH, '/', trailingslashit(trailingslashit(self::get_option('cache-path'))).SWIFT_PERFORMANCE_CACHE_BASE_DIR) . '$http_host$request_uri/css/%PREFIX%full.css>; rel=preload; as=style';
      }
      if (Swift_Performance::check_option('merge-scripts',1) && Swift_Performance::check_option('inline-merged-scripts',0)){
            $server_push[] = '<'. Swift_Performance::home_dir() . str_replace(ABSPATH, '/', trailingslashit(trailingslashit(self::get_option('cache-path'))).SWIFT_PERFORMANCE_CACHE_BASE_DIR) . '$http_host$request_uri/js/%PREFIX%scripts.js>; rel=preload; as=script';
      }
}
?>

set $swift_cache 1;
if ($request_method = POST){
	set $swift_cache 0;
}

if ($args != ''){
	set $swift_cache 0;
}

if ($http_cookie ~* "wordpress_logged_in") {
	set $swift_cache 0;
}

if ($request_uri ~ ^/<?php echo str_replace(ABSPATH, '', trailingslashit(self::get_option('cache-path'))).SWIFT_PERFORMANCE_CACHE_BASE_DIR; ?>([^/]*)/assetproxy) {
      set $swift_cache 0;
}

if (!-f "<?php echo trailingslashit(self::get_option('cache-path')).SWIFT_PERFORMANCE_CACHE_BASE_DIR; ?>/$http_host/$request_uri/desktop/unauthenticated/index.html") {
	set $swift_cache 0;
}

<?php if (Swift_Performance::check_option('mobile-support',1)):?>
set $swift_mobile_cache 1;
if (!-f "<?php echo trailingslashit(self::get_option('cache-path')).SWIFT_PERFORMANCE_CACHE_BASE_DIR; ?>/$http_host/$request_uri/mobile/unauthenticated/index.html") {
	set $swift_mobile_cache 0;
}

if ($http_user_agent ~* (Mobile|Android|Silk|Kindle|BlackBerry|Opera+Mini|Opera+Mobi)) {
      set $swift_cache "{$swift_cache}{$swift_mobile_cache}";
}

if ($swift_cache = 11){
    rewrite .* /<?php echo str_replace(ABSPATH, '', trailingslashit(trailingslashit(self::get_option('cache-path'))).SWIFT_PERFORMANCE_CACHE_BASE_DIR); ?>$http_host/$request_uri/mobile/unauthenticated/index.html last;
}

<?php if (Swift_Performance::check_option('server-push',1) && !empty($server_push)):?>
location ~ mobile/unauthenticated/index.html {
	add_header Link "<?php echo implode(',',array_map(function($str){return str_replace('%PREFIX%', 'mobile-', $str);}, $server_push))?>";
}
<?php endif;?>

<?php endif;?>
if ($swift_cache = 1){
    rewrite .* /<?php echo str_replace(ABSPATH, '', trailingslashit(trailingslashit(self::get_option('cache-path'))).SWIFT_PERFORMANCE_CACHE_BASE_DIR); ?>$http_host/$request_uri/desktop/unauthenticated/index.html last;
}

<?php if (Swift_Performance::check_option('server-push',1) && !empty($server_push)):?>
location ~ desktop/unauthenticated/index.html {
	add_header Link "<?php echo implode(',',array_map(function($str){return str_replace('%PREFIX%', 'desktop-', $str);}, $server_push))?>";
}
<?php endif;?>

<?php return ob_get_clean();?>

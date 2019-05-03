<?php ob_start();?>
<?php
if (Swift_Performance::check_option('server-push',1)){
      $server_push = array();
      if (Swift_Performance::check_option('merge-styles',1) && Swift_Performance::check_option('critical-css',1) && Swift_Performance::check_option('inline_critical_css',0)){
            $server_push[] = '<'.Swift_Performance::home_dir().'/%{cache_base_uri}e/css/%PREFIX%critical.css>; rel=preload; as=style';
      }
      if (Swift_Performance::check_option('merge-styles',1) && Swift_Performance::check_option('inline_full_css',0)){
            $server_push[] = '<'.Swift_Performance::home_dir().'/%{cache_base_uri}e/css/%PREFIX%full.css>; rel=preload; as=style';
      }
      if (Swift_Performance::check_option('merge-scripts',1) && Swift_Performance::check_option('inline-merged-scripts',0)){
            $server_push[] = '<'.Swift_Performance::home_dir().'/%{cache_base_uri}e/js/%PREFIX%scripts.js>; rel=preload; as=script';
      }
}
?>
<?php if (Swift_Performance::check_option('proxy-cache', 1)) :?>
<IfModule mod_headers.c>
    <filesMatch "\.(html|htm)$">
        Header set Cache-Control "s-maxage=<?php echo Swift_Performance::get_option('proxy-cache-maxage');?>, max-age=0, public, must-revalidate"
    </filesMatch>
</IfModule>
<?php endif;?>

<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase <?php echo parse_url(home_url('/'), PHP_URL_PATH)."\n";?>
RewriteCond %{REQUEST_METHOD} !POST
RewriteCond %{QUERY_STRING} ^$
RewriteCond %{HTTP:Cookie} !^.*(wordpress_logged_in).*$
RewriteCond %{REQUEST_URI} !^/<?php echo str_replace(ABSPATH, '', trailingslashit(self::get_option('cache-path'))).SWIFT_PERFORMANCE_CACHE_BASE_DIR; ?>([^/]*)/assetproxy
<?php if (Swift_Performance::check_option('mobile-support',1)):?>
RewriteCond %{HTTP_USER_AGENT} (Mobile|Android|Silk|Kindle|BlackBerry|Opera+Mini|Opera+Mobi) [NC]
RewriteCond <?php echo trailingslashit(self::get_option('cache-path')).SWIFT_PERFORMANCE_CACHE_BASE_DIR; ?>%{HTTP_HOST}%{REQUEST_URI}/mobile/unauthenticated/index.html -f
RewriteRule (.*) <?php echo str_replace(ABSPATH, '', trailingslashit(self::get_option('cache-path'))).SWIFT_PERFORMANCE_CACHE_BASE_DIR; ?>%{HTTP_HOST}%{REQUEST_URI}/mobile/unauthenticated/index.html [L]

<?php if (Swift_Performance::check_option('server-push',1) && !empty($server_push)):?>
<IfModule mod_headers.c>
RewriteCond %{HTTP_USER_AGENT} (Mobile|Android|Silk|Kindle|BlackBerry|Opera+Mini|Opera+Mobi) [NC]
RewriteRule (.*)/mobile/unauthenticated/(.*).html - [E=cache_base_uri:$1]
Header set Link "<?php echo implode(',',array_map(function($str){return str_replace('%PREFIX%', 'mobile-', $str);}, $server_push))?>" env=cache_base_uri
</IfModule>
<?php endif;?>

RewriteCond %{REQUEST_METHOD} !POST
RewriteCond %{QUERY_STRING} ^$
RewriteCond %{HTTP:Cookie} !^.*(wordpress_logged_in).*$
RewriteCond %{REQUEST_URI} !^/<?php echo str_replace(ABSPATH, '', trailingslashit(self::get_option('cache-path'))).SWIFT_PERFORMANCE_CACHE_BASE_DIR; ?>([^/]*)/assetproxy
RewriteCond %{HTTP_USER_AGENT} !(Mobile|Android|Silk|Kindle|BlackBerry|Opera+Mini|Opera+Mobi) [NC]
RewriteCond <?php echo trailingslashit(self::get_option('cache-path')).SWIFT_PERFORMANCE_CACHE_BASE_DIR; ?>%{HTTP_HOST}%{REQUEST_URI}/desktop/unauthenticated/index.html -f
RewriteRule (.*) <?php echo str_replace(ABSPATH, '', trailingslashit(self::get_option('cache-path'))).SWIFT_PERFORMANCE_CACHE_BASE_DIR; ?>%{HTTP_HOST}%{REQUEST_URI}/desktop/unauthenticated/index.html [L]

<?php if (Swift_Performance::check_option('server-push',1) && !empty($server_push)):?>
<IfModule mod_headers.c>
RewriteCond %{HTTP_USER_AGENT} !(Mobile|Android|Silk|Kindle|BlackBerry|Opera+Mini|Opera+Mobi) [NC]
RewriteRule (.*)/desktop/unauthenticated/(.*).html - [E=cache_base_uri:$1]
Header set Link "<?php echo implode(',',array_map(function($str){return str_replace('%PREFIX%', 'desktop-', $str);}, $server_push))?>" env=cache_base_uri
</IfModule>
<?php endif;?>

<?php else:?>
RewriteCond <?php echo trailingslashit(self::get_option('cache-path')).SWIFT_PERFORMANCE_CACHE_BASE_DIR; ?>%{HTTP_HOST}%{REQUEST_URI}/desktop/unauthenticated/index.html -f
RewriteRule (.*) <?php echo str_replace(ABSPATH, '', trailingslashit(self::get_option('cache-path'))).SWIFT_PERFORMANCE_CACHE_BASE_DIR; ?>%{HTTP_HOST}%{REQUEST_URI}/desktop/unauthenticated/index.html [L]

<?php if (Swift_Performance::check_option('server-push',1) && !empty($server_push)):?>
<IfModule mod_headers.c>
RewriteRule (.*)/desktop/unauthenticated/(.*).html - [E=cache_base_uri:$1]
Header set Link "<?php echo implode(',',array_map(function($str){return str_replace('%PREFIX%', 'desktop-', $str);}, $server_push))?>" env=cache_base_uri
</IfModule>
<?php endif;?>

<?php endif;?>
</IfModule>
<?php return ob_get_clean();?>

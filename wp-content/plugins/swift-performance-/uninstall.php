<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

if (!current_user_can('activate_plugins')){
      return;
}

if (!defined('SWIFT_PERFORMANCE_UNINSTALL')){
      define('SWIFT_PERFORMANCE_UNINSTALL', true);
}

include_once __DIR__ . '/performance.php';

if (is_multisite()){
      global $wpdb;
      foreach ($wpdb->get_col("SELECT blog_id FROM $wpdb->blogs") as $blog_id){
            switch_to_blog($blog_id);
            Swift_Performance::uninstall();
            restore_current_blog();
      }
}
else {
      Swift_Performance::uninstall();
}

?>

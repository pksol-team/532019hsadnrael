<?php

class Swift_Performance_DB_Optimizer {

      /**
       * Create instance
       */

      public function __construct(){
            add_action('wp_ajax_swift_performance_db_optimizer', array(__CLASS__, 'ajax_handler'));

            // Create schedule hooks
            $options = array('optimize_tables','reindex_tables', 'clear_expired_transients', 'clear_revisions', 'clear_trashed_posts', 'clear_orphan_postmeta', 'clear_orphan_attachments', 'clear_duplicated_postmeta', 'clear_spam_comments', 'clear_trashed_comments', 'clear_orphan_commentmeta', 'clear_duplicated_commentmeta', 'clear_orphan_termmeta', 'clear_orphan_usermeta', 'clear_duplicated_usermeta');
            foreach ($options as $option){
                  add_action('swift_performance_dbo_' . $option, array(__CLASS__, $option));
            }

            // Create cron schedules
            add_filter( 'cron_schedules',	function ($schedules){
                  // Common cache
                  $schedules['swift_performance_dbo_weekly'] = array(
                        'interval' => 604800,
                        'display' => 'Weekly'
                  );

                  $schedules['swift_performance_dbo_twice_monthly'] = array(
                        'interval' => 1209600,
                        'display' => 'Twice_monthly'
                  );

                  $schedules['swift_performance_dbo_monthly'] = array(
                        'interval' => 2592000,
                        'display' => 'Monthly'
                  );

                  return $schedules;
            });

      }

      /**
       * Print the schedule/unshedule action link
       */
      public static function schedule($option){
            $class = (wp_next_scheduled('swift_performance_dbo_' . $option) ? ' is-scheduled' : '');
            echo '<a href="#" id="trigger-'.esc_attr($option).'" class="swift-toggle-scheduled-dbo'.$class.'" data-option="'.esc_attr($option).'"><strong><i class="fa fa-clock-o"></i></strong></a>';

      }

      /**
       * Print schedule form
       */
      public static function schedule_form($option){
            $freq  = wp_get_schedule('swift_performance_dbo_' . $option);

            echo '<form id="schedule-'.esc_attr($option).'" class="swift-dbo-schedule-selector swift-hidden">';
      	echo '<input id="swift_performance_dbo_'.esc_attr($option).'_twicedaily" ' . checked($freq, 'twicedaily', false) . ' name="freq" type="radio" value="twicedaily"><label for="swift_performance_dbo_'.esc_attr($option).'_twicedaily">'.esc_html__('12 hours', 'swift-performance').'</label>';
      	echo '<input id="swift_performance_dbo_'.esc_attr($option).'_daily" ' . checked($freq, 'daily', false) . ' name="freq" type="radio" value="daily"><label for="swift_performance_dbo_'.esc_attr($option).'_daily">'.esc_html__('Daily', 'swift-performance').'</label>';
      	echo '<input id="swift_performance_dbo_'.esc_attr($option).'_weekly" ' . checked($freq, 'swift_performance_dbo_weekly', false) . ' name="freq" type="radio" value="swift_performance_dbo_weekly"><label for="swift_performance_dbo_'.esc_attr($option).'_weekly">'.esc_html__('Weekly', 'swift-performance').'</label>';
      	echo '<input id="swift_performance_dbo_'.esc_attr($option).'_twice_monthly" ' . checked($freq, 'swift_performance_dbo_twice_monthly', false) . ' name="freq" type="radio" value="swift_performance_dbo_twice_monthly"><label for="swift_performance_dbo_'.esc_attr($option).'_twice_monthly">'.esc_html__('Biweekly', 'swift-performance').'</label>';
      	echo '<input id="swift_performance_dbo_'.esc_attr($option).'_monthly" ' . checked($freq, 'swift_performance_dbo_monthly', false) . ' name="freq" type="radio" value="swift_performance_dbo_monthly"><label for="swift_performance_dbo_'.esc_attr($option).'_monthly">'.esc_html__('Monthly', 'swift-performance').'</label>';
      	echo '<div class="swift-button-container"><a href="#" class="swift-scheduled-dbo-change swift-btn swift-btn-blue" data-action="set-schedule">'.esc_html__('Save', 'swift-performance').'</a><a href="#" class="swift-scheduled-dbo-change swift-btn swift-btn-gray" data-action="clear-schedule">'.esc_html__('Clear', 'swift-performance').'</a></div>';
      	echo '<input type="hidden" name="option" value="'.esc_attr($option).'"></form>';
      }

      /**
       * Global AJAX handler
       */
      public static function ajax_handler(){
            if (!current_user_can('manage_options') || !isset($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'swift-performance-ajax-nonce')){
                  echo __('Your session has expired. Please refresh the page and try again.', 'swift-performance');
                  die;
            }

            // Extend timeout
            Swift_Performance::set_time_limit(120, 'db_optimizer_ajax');

            $action = isset($_REQUEST['swift-action']) ? $_REQUEST['swift-action'] : '';
            switch ($action) {
                  case 'optimize-tables':
                        Swift_Performance_DB_Optimizer::optimize_tables();
                        echo sprintf(__('%d tables optimized', 'swift-performance'), Swift_Performance_DB_Optimizer::count_tables());
                        break;
                  case 'reindex-tables':
                        Swift_Performance_DB_Optimizer::reindex_tables();
                        echo sprintf(__('%d tables reindexed', 'swift-performance'), Swift_Performance_DB_Optimizer::count_tables());
                        break;
                  case 'clear-expired-transients':
                        Swift_Performance_DB_Optimizer::clear_expired_transients();
                        echo Swift_Performance_DB_Optimizer::count_expired_transients();
                        break;
                  case 'clear-revisions':
                        Swift_Performance_DB_Optimizer::clear_revisions();
                        echo Swift_Performance_DB_Optimizer::count_revisions();
                        break;
                  case 'clear-trashed-posts':
                        Swift_Performance_DB_Optimizer::clear_trashed_posts();
                        echo Swift_Performance_DB_Optimizer::count_trashed_posts();
                        break;
                  case 'clear-orphan-postmeta':
                        Swift_Performance_DB_Optimizer::clear_orphan_postmeta();
                        echo Swift_Performance_DB_Optimizer::count_orphan_postmeta();
                        break;
                  case 'clear-orphan-attachments':
                        Swift_Performance_DB_Optimizer::clear_orphan_attachments();
                        echo Swift_Performance_DB_Optimizer::count_orphan_attachments();
                        break;
                  case 'clear-duplicated-postmeta':
                        Swift_Performance_DB_Optimizer::clear_duplicated_postmeta();
                        echo Swift_Performance_DB_Optimizer::count_duplicated_postmeta();
                        break;
                  case 'clear-spam-comments':
                        Swift_Performance_DB_Optimizer::clear_spam_comments();
                        echo Swift_Performance_DB_Optimizer::count_spam_comments();
                        break;
                  case 'clear-trashed-comments':
                        Swift_Performance_DB_Optimizer::clear_trashed_comments();
                        echo Swift_Performance_DB_Optimizer::count_trashed_comments();
                        break;
                  case 'clear-orphan-commentmeta':
                        Swift_Performance_DB_Optimizer::clear_orphan_commentmeta();
                        echo Swift_Performance_DB_Optimizer::count_orphan_commentmeta();
                        break;
                  case 'clear-duplicated-commentmeta':
                        Swift_Performance_DB_Optimizer::clear_duplicated_commentmeta();
                        echo Swift_Performance_DB_Optimizer::count_duplicated_commentmeta();
                        break;
                  case 'clear-orphan-termmeta':
                        Swift_Performance_DB_Optimizer::clear_orphan_termmeta();
                        echo Swift_Performance_DB_Optimizer::count_orphan_termmeta();
                        break;
                  case 'clear-orphan-usermeta':
                        Swift_Performance_DB_Optimizer::clear_orphan_usermeta();
                        echo Swift_Performance_DB_Optimizer::count_orphan_usermeta();
                        break;
                  case 'clear-duplicated-usermeta':
                        Swift_Performance_DB_Optimizer::clear_duplicated_usermeta();
                        echo Swift_Performance_DB_Optimizer::count_duplicated_usermeta();
                        break;
                  case 'set-schedule':
                        $hook = 'swift_performance_dbo_' . $_REQUEST['option'];
                        wp_clear_scheduled_hook($hook);
                        wp_schedule_event(time(), $_REQUEST['freq'], $hook);
                        Swift_Performance_DB_Optimizer::schedule($_REQUEST['option']);
                        break;
                  case 'clear-schedule':
                        $hook = 'swift_performance_dbo_' . $_REQUEST['option'];
                        wp_clear_scheduled_hook($hook);
                        Swift_Performance_DB_Optimizer::schedule($_REQUEST['option']);
                        break;
            }
            die;
      }

      /**
       * Count expired transients
       * @return int
       */
      public static function count_expired_transients(){
            global $wpdb;
      	return $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_timeout\__%%' AND option_value < NOW()");
      }

      /**
       * Count revisions
       * @return int
       */
      public static function count_revisions(){
            global $wpdb;
      	return $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE `post_type` LIKE 'revision'");
      }

      /**
      * Count trashed posts
      * @return int
      */
      public static function count_trashed_posts(){
            global $wpdb;
            return $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE `post_status` LIKE 'trash'");
      }

      /**
       * Get autoload size
       * @return int
       */
      public static function get_autoload_size(){
            global $wpdb;
            return $wpdb->get_var("SELECT SUM(LENGTH(option_value)) as autoload_size FROM {$wpdb->options} WHERE autoload='yes'");
      }

      /**
       * Count orphan postmeta
       * @return int
       */
      public static function count_orphan_postmeta(){
            global $wpdb;
            return $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE post_id NOT IN (SELECT ID FROM {$wpdb->posts})");
      }

      /**
       * Count orphan attachments
       * @return int
       */
      public static function count_orphan_attachments(){
            global $wpdb;
            return $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_parent NOT IN (SELECT ID FROM {$wpdb->posts}) AND post_parent != 0 AND post_type = 'attachment'");
      }

      /**
       * Count duplicated post meta
       * @return int
       */
      public static function count_duplicated_postmeta(){
            global $wpdb;
            return count($wpdb->get_results("SELECT pm.meta_id AS meta_id, pm.post_id AS post_id FROM {$wpdb->postmeta} pm INNER JOIN (SELECT post_id, meta_key, meta_value, COUNT(*) FROM {$wpdb->postmeta} GROUP BY post_id, meta_key, meta_value HAVING COUNT(*) > 1) pm2 ON pm.post_id = pm2.post_id AND pm.meta_key = pm2.meta_key AND pm.meta_value = pm2.meta_value WHERE pm.meta_key NOT IN ('_price', '_used_by')"));

      }

      /**
       * Count spam comments
       * @return int
       */
      public static function count_spam_comments(){
            global $wpdb;
            return $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_approved = 'spam'");
      }

      /**
      * Count trashed comments
      * @return int
      */
      public static function count_trashed_comments(){
            global $wpdb;
            return $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_approved = 'trash'");
      }

      /**
      * Count orphan commentmeta
      * @return int
      */
      public static function count_orphan_commentmeta(){
            global $wpdb;
            return $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->commentmeta} WHERE comment_id NOT IN (SELECT comment_ID FROM {$wpdb->comments})");
      }

      /**
      * Count orphan termmeta
      * @return int
      */
      public static function count_orphan_termmeta(){
            global $wpdb;
            return $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->termmeta} WHERE term_id NOT IN (SELECT term_id FROM {$wpdb->terms})");
      }

      /**
      * Count orphan usermeta
      * @return int
      */
      public static function count_orphan_usermeta(){
            global $wpdb;
            return $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE user_id NOT IN (SELECT ID FROM {$wpdb->users})");
      }

      /**
      * Count duplicated commentmeta
      * @return int
      */
      public static function count_duplicated_commentmeta(){
            global $wpdb;
            return count($wpdb->get_results("SELECT cm.meta_id AS meta_id, cm.comment_id AS comment_id FROM {$wpdb->commentmeta} cm INNER JOIN (SELECT comment_id, meta_key, meta_value, COUNT(*) FROM {$wpdb->commentmeta} GROUP BY comment_id, meta_key, meta_value HAVING COUNT(*) > 1) cm2 ON cm.comment_id = cm2.comment_id AND cm.meta_key = cm2.meta_key AND cm.meta_value = cm2.meta_value"));
      }

      /**
      * Count duplicated usermeta
      * @return int
      */
      public static function count_duplicated_usermeta(){
            global $wpdb;
            return count($wpdb->get_results("SELECT um.umeta_id AS umeta_id, um.user_id AS user_id FROM {$wpdb->usermeta} um INNER JOIN (SELECT user_id, meta_key, meta_value, COUNT(*) FROM {$wpdb->usermeta} GROUP BY user_id, meta_key, meta_value HAVING COUNT(*) > 1) um2 ON um.user_id = um2.user_id AND um.meta_key = um2.meta_key AND um.meta_value = um2.meta_value"));
      }

      /**
      * Count tables
      * @return int
      */
      public static function count_tables(){
            global $wpdb;
            $tables = $wpdb->get_results("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = SCHEMA() AND TABLE_NAME LIKE '{$wpdb->prefix}%'");
            return count($tables);
      }

      /**
       * Get 10 largest autoloaded options
       * @return array
       */
      public static function get_largest_autoloaded_options(){
            global $wpdb;
            return $wpdb->get_results("SELECT option_name, LENGTH(option_value) AS size FROM {$wpdb->options} WHERE autoload='yes' ORDER BY size DESC LIMIT 10");
      }

      /**
       * Clear expired transients
       */
      public static function clear_expired_transients(){
            global $wpdb;
      	$options = $wpdb->get_col("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_%' AND option_value < NOW()");
            foreach ($options as $option) {
                  if ( strpos( $option, '_site_transient_' ) !== false ) {
				delete_site_transient( str_replace( '_site_transient_timeout_', '', $option ) );
			} else {
				delete_transient( str_replace( '_transient_timeout_', '', $option ) );
			}
            }
      }

      /**
       * Clear revisions
       */
      public static function clear_revisions(){
            global $wpdb;
      	$posts = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE `post_type` LIKE 'revision'");
            foreach ($posts as $id) {
                  wp_delete_post_revision($id);
            }
      }

      /**
      * Clear trashed posts
      */
      public static function clear_trashed_posts(){
            global $wpdb;
            $posts = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE `post_status` LIKE 'trash'");
            foreach ($posts as $id) {
                  wp_delete_post($id, true);
            }
      }

      /**
       * Clear orphan postmeta
       */
      public static function clear_orphan_postmeta(){
            global $wpdb;
            $postmeta = $wpdb->get_results("SELECT meta_id, post_id, meta_key FROM {$wpdb->postmeta} WHERE post_id NOT IN (SELECT ID FROM {$wpdb->posts})");
            foreach ($postmeta as $meta) {
			if (empty($meta->post_id)) {
				$wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_id = {$meta->meta_id}");
			} else {
				delete_post_meta( $meta->post_id, $meta->meta_key );
			}
            }
      }

      /**
       * Clear orphan attachments
       */
      public static function clear_orphan_attachments(){
            global $wpdb;
            $attachments = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_parent NOT IN (SELECT ID FROM {$wpdb->posts}) AND post_parent != 0 AND post_type = 'attachment'");
            foreach ($attachments as $attachment) {
                  wp_delete_attachment($attachment, true);
            }
      }

      /**
       * Clear duplicated post meta
       */
      public static function clear_duplicated_postmeta(){
            global $wpdb;
            $_postmeta = array();
            $postmeta = $wpdb->get_results("SELECT pm.meta_id AS meta_id, pm.post_id AS post_id FROM {$wpdb->postmeta} pm INNER JOIN (SELECT post_id, meta_key, meta_value, COUNT(*) FROM {$wpdb->postmeta} GROUP BY post_id, meta_key, meta_value HAVING COUNT(*) > 1) pm2 ON pm.post_id = pm2.post_id AND pm.meta_key = pm2.meta_key AND pm.meta_value = pm2.meta_value WHERE pm.meta_key NOT IN ('_price', '_used_by')");
            foreach($postmeta as $meta){
                  $_postmeta[$meta->post_id][] = $meta->meta_id;
            }

            foreach ($_postmeta as $post_id => $meta_ids) {
                  array_pop($meta_ids);
                  $wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_id IN (" . implode( ',', $meta_ids ) . ")");
            }
      }

      /**
       * Clear spam comments
       */
      public static function clear_spam_comments(){
            global $wpdb;
            $comments = $wpdb->get_col("SELECT comment_ID FROM $wpdb->comments WHERE comment_approved = 'spam'");
            foreach ( $comments as $comment ) {
			wp_delete_comment($comment, true );
		}
      }

      /**
      * Clear trashed comments
      */
      public static function clear_trashed_comments(){
            global $wpdb;
            $comments = $wpdb->get_col("SELECT comment_ID FROM $wpdb->comments WHERE comment_approved = 'trash'");
            foreach ( $comments as $comment ) {
			wp_delete_comment($comment, true );
		}
      }

      /**
      * Clear orphan commentmeta
      */
      public static function clear_orphan_commentmeta(){
            global $wpdb;
            $commentmeta = $wpdb->get_results("SELECT meta_id, comment_id, meta_key FROM {$wpdb->commentmeta} WHERE comment_id NOT IN (SELECT comment_ID FROM {$wpdb->comments})");
            foreach ($commentmeta as $meta) {
			if (empty($meta->comment_id)) {
				$wpdb->query("DELETE FROM $wpdb->commentmeta WHERE meta_id = {$meta->meta_id}");
			} else {
				delete_comment_meta( $meta->comment_id, $meta->meta_key );
			}
            }
      }

      /**
      * Clear orphan termmeta
      */
      public static function clear_orphan_termmeta(){
            global $wpdb;
            $termmeta = $wpdb->get_results("SELECT meta_id, term_id, meta_key FROM {$wpdb->termmeta} WHERE term_id NOT IN (SELECT term_id FROM {$wpdb->terms})");
            foreach ($termmeta as $meta) {
                  if (empty($meta->term_id)) {
                        $wpdb->query("DELETE FROM $wpdb->termmeta WHERE meta_id = {$meta->meta_id}");
                  } else {
                        delete_term_meta( $meta->term_id, $meta->meta_key );
                  }
            }
      }

      /**
      * Clear orphan usermeta
      */
      public static function clear_orphan_usermeta(){
            global $wpdb;
            $usermeta = $wpdb->get_results("SELECT umeta_id, user_id, meta_key FROM {$wpdb->usermeta} WHERE user_id NOT IN (SELECT ID FROM {$wpdb->users})");
            foreach ($usermeta as $meta) {
                  if (empty($meta->user_id)) {
                        $wpdb->query("DELETE FROM $wpdb->usermeta WHERE meta_id = {$meta->umeta_id}");
                  } else {
                        delete_user_meta( $meta->user_id, $meta->meta_key );
                  }
            }
      }

      /**
      * Clear duplicated commentmeta
      */
      public static function clear_duplicated_commentmeta(){
            global $wpdb;
            $_commentmeta = array();
            $commentmeta = $wpdb->get_results("SELECT cm.meta_id AS meta_id, cm.comment_id AS comment_id FROM {$wpdb->commentmeta} cm INNER JOIN (SELECT comment_id, meta_key, meta_value, COUNT(*) FROM {$wpdb->commentmeta} GROUP BY comment_id, meta_key, meta_value HAVING COUNT(*) > 1) cm2 ON cm.comment_id = cm2.comment_id AND cm.meta_key = cm2.meta_key AND cm.meta_value = cm2.meta_value");
            foreach($commentmeta as $meta){
                  $_commentmeta[$meta->comment_id][] = $meta->meta_id;
            }

            foreach ($_commentmeta as $comment_id => $meta_ids) {
                  array_pop($meta_ids);
                  $wpdb->query("DELETE FROM $wpdb->commentmeta WHERE meta_id IN (" . implode( ',', $meta_ids ) . ")");
            }
      }

      /**
      * Clear duplicated usermeta
      */
      public static function clear_duplicated_usermeta(){
            global $wpdb;
            $_usermeta = array();
            $usermeta = $wpdb->get_results("SELECT um.umeta_id AS umeta_id, um.user_id AS user_id FROM {$wpdb->usermeta} um INNER JOIN (SELECT user_id, meta_key, meta_value, COUNT(*) FROM {$wpdb->usermeta} GROUP BY user_id, meta_key, meta_value HAVING COUNT(*) > 1) um2 ON um.user_id = um2.user_id AND um.meta_key = um2.meta_key AND um.meta_value = um2.meta_value");
            foreach($usermeta as $meta){
                  $_usermeta[$meta->user_id][] = $meta->umeta_id;
            }

            foreach ($_usermeta as $user_id => $meta_ids) {
                  array_pop($meta_ids);
                  $wpdb->query("DELETE FROM $wpdb->usermeta WHERE umeta_id IN (" . implode( ',', $meta_ids ) . ")");
            }
      }

      /**
       * Optimize all tables
       */
      public static function optimize_tables(){
            global $wpdb;
            $tables = $wpdb->get_col("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = SCHEMA() AND TABLE_NAME LIKE '{$wpdb->prefix}%'");
		$tables = implode( ',', $tables );
		$wpdb->query("OPTIMIZE TABLE {$tables}");
      }

      /**
       * Reindex all tables which match the prefix
       * @param string $prefix
       */
      public static function reindex_tables($table = ''){
            global $wpdb;
            $like = $wpdb->prefix . $table . '%';
            $tables = $wpdb->get_results($wpdb->prepare("SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = SCHEMA() AND TABLE_NAME LIKE %s", $like));
            foreach ($tables as $table){
                  $table_name = esc_sql($table->TABLE_NAME);
                  switch ($table->ENGINE){
                        case 'MyISAM':
                        $wpdb->query("REPAIR TABLE {$table_name}");
                        break;
                        case 'InnoDB':
                        $wpdb->query("ALTER TABLE {$table_name} ENGINE = InnoDB");
                        break;
                  }
            }
      }

}

return new Swift_Performance_DB_Optimizer();

?>

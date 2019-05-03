<?php
if (! class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Swift_Performance_Cache_Status_Table extends WP_List_Table
{
    public function get_columns()
    {
        $columns = array(
                  'status'            => __('Status', 'swift-performance'),
                  'url'               => __('URL', 'swift-performance'),
                  'priority'          => __('Priority', 'swift-performance'),
                  'priority_editor'   => __('Prebuild priority', 'swift-performance'),
                  'date'              => __('Cache date', 'swift-performance'),
                  'timestamp'         => __('Timestamp', 'swift-performance'),
            );
        return $columns;
    }

    public function prepare_items()
    {
        $items = $this->get_items();

        $columns = $this->get_columns();
        $hidden = array('priority', 'timestamp');
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        usort($items, array( &$this, 'usort_reorder' ));

        $per_page = 30;
        $current_page = $this->get_pagenum();
        $total_items = count($items);

        $found_data = array_slice($items, (($current_page-1)*$per_page), $per_page);

        $this->set_pagination_args(array(
                'total_items' => $total_items,
                'per_page'    => $per_page
            ));
        $this->items = $found_data;
    }

    public function column_default($item, $column_name)
    {
        return $item[ $column_name ];
    }

    public function get_sortable_columns()
    {
        $sortable_columns = array(
                'url'               => array('url',false),
                'date'              => array('timestamp',false),
                'priority_editor'   => array('priority', false)
            );
        return $sortable_columns;
    }

    public function get_items()
    {
        if (get_transient('swift_performance_initial_prebuild_links') !== false) {
            return array();
        }

        global $wpdb;
        $items = $missing = $urls = array();

        // Upgrade warmup keys if necessary
        $home_url = '^https?://(www\.)?' . preg_quote(parse_url(home_url(), PHP_URL_HOST)) . '/?$';
        $home_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM " . SWIFT_PERFORMANCE_TABLE_PREFIX . "warmup WHERE url REGEXP %s", $home_url));
        if (!empty($home_id) && $home_id != Swift_Performance::get_warmup_id(home_url())) {
            // Reset warmup table
            Swift_Performance::mysql_query("TRUNCATE " . SWIFT_PERFORMANCE_TABLE_PREFIX . 'warmup');
        }

        // Prepare URLs
        foreach (Swift_Performance::get_prebuild_urls(false) as $warmup) {
            $urls[Swift_Performance::get_warmup_id($warmup['url'])] = $warmup;
        }

        // Get pages which are missing from warmup
        $cache_info = Swift_Performance::cache_status();
        foreach ($cache_info['files'] as $url) {
            if (!isset($urls[Swift_Performance::get_warmup_id($url)])) {
                $urls[Swift_Performance::get_warmup_id($url)] = array(
                              'priority' => PHP_INT_MAX,
                              'url' => $url
                        );
            }
        }
        foreach ($urls as $url) {
            if (isset($cache_info['files'][rtrim($url['url'], '/')]) || isset($cache_info['files'][Swift_Performance::get_warmup_id($url['url'])])) {
                $cache_type       = (isset($url['type']) ? $url['type'] : Swift_Performance_Cache::get_cache_type($url['url']));
                $time             = (isset($url['timestamp']) ? $url['timestamp'] : Swift_Performance_Cache::get_cache_time($url['url']));
            } else {
                $cache_type       = (isset($url['type']) && !empty($url['type']) ? $url['type'] : false);
                $time             = (isset($url['timestamp']) ? $url['timestamp'] : 0);
            }

            if (empty($cache_type)) {
                $cache_status = 'not-cached';
            } elseif ($cache_type == '404') {
                $cache_status = '404';
            } elseif ($cache_type == 'error') {
                $cache_status = 'not-cacheable';
            } elseif ($cache_type == 'redirect') {
                $cache_status = 'redirect';
            } else {
                $cache_status = 'cached';
            }

            // Filtering
            if (isset($_REQUEST['s']) && !empty($_REQUEST['s']) && strpos(strtolower($url['url']), strtolower($_REQUEST['s'])) === false) {
                continue;
            }
            if (isset($_REQUEST['cache-status-filter']) && !empty($_REQUEST['cache-status-filter']) && $cache_status != $_REQUEST['cache-status-filter']) {
                continue;
            }

            $items[] = array(
                        'priority'         => $url['priority'],
                        'url'              => $url['url'],
                        'cache_status'	 => $cache_status,
                        'timestamp'        => $time,
                  );
        }

        return $items;
    }

    public function column_status($item)
    {
          $status           = '<span title="' . esc_attr__('Cached', 'swift-performance') . '" class="dashicons dashicons-yes'.($item['cache_status'] != 'cached' ? ' swift-hidden' : '').'"></span>';
          $status          .= '<span title="' . esc_attr__('Missing From Cache', 'swift-performance') . '" class="dashicons dashicons-no'.($item['cache_status'] != 'not-cached' ? ' swift-hidden' : '').'"></span>';
          $status          .= '<span title="' . esc_attr__('Cached 404', 'swift-performance') . '" class="dashicons dashicons-warning'.($item['cache_status'] != '404' ? ' swift-hidden' : '').'"></span>';
          $status          .= '<span title="' . esc_attr__('Not cacheable', 'swift-performance') . '" class="dashicons dashicons-editor-strikethrough'.($item['cache_status'] != 'not-cacheable' ? ' swift-hidden' : '').'"></span>';
          $status          .= '<span title="' . esc_attr__('Redirect', 'swift-performance') . '" class="dashicons dashicons-redo'.($item['cache_status'] != 'redirect' ? ' swift-hidden' : '').'"></span>';
          return $status;
    }

    public function column_priority_editor($item)
    {
        return ($item['priority'] != PHP_INT_MAX ? '<form class="swift-priority-update"><span class="edit-container"><input type="number" name="priorities['.esc_attr(Swift_Performance::get_warmup_id($item['url'])).']" class="priority-holder" value="'.esc_attr($item['priority']).'"><button class="swift-btn swift-btn-gray">'.esc_html__('Update', 'swift-performance').'</button></span></form>' : '-');
    }

    public function column_date($item)
    {
        return ($item['timestamp'] > 0 ? get_date_from_gmt(date('Y-m-d H:i:s', $item['timestamp']), get_option('date_format') . ' ' .get_option('time_format')) : '-');
    }

    public function column_url($item)
    {
        $is_cached      = ($item['cache_status'] == 'cached');
        $subcache_urls  = self::get_subcache($item['url']);
        $actions = array(
                  'view' => '<span class="view-cached'.(!$is_cached ? ' swift-hidden' : '').'" ><a href="'.esc_url(add_query_arg('force-cached', '1', $item['url'])).'" target="_blank">'.esc_html__('View cached', 'swift-performance').'</a> | </span><a href="'.esc_url($item['url']).'" target="_blank">'.esc_html__('View', 'swift-performance').'</a>',
                  'action' => '<a class="clear-cache'.(!$is_cached ? ' swift-hidden' : '').'" data-url="'.esc_attr($item['url']).'" data-status="'.$item['cache_status'].'" href="#">'.esc_html__('Clear cache', 'swift-performance').'</a><a class="do-cache'.($is_cached ? ' swift-hidden' : '').'" data-url="'.esc_attr($item['url']).'" href="#">'.esc_html__('Cache page', 'swift-performance').'</a>',
                  'delete' => '<a class="remove-warmup-url" data-url="'.esc_attr($item['url']).'" href="#">'.esc_html__('Remove URL', 'swift-performance').'</a>'
            );

        if (!empty($subcache_urls)) {
            $subcache_label = $subcache_title = __('Subcache: ', 'swift-performance');

            $count = 0;
            foreach ($subcache_urls as $_subcache) {
                if ($count < 5) {
                    $subcache_label .= $_subcache . ', ';
                    $count++;
                }
                $subcache_title .= $_subcache . ', ';
            }

            // format label and title
            $subcache_label = (count($subcache_urls) > 5 ? trim($subcache_label, ',') . (count($subcache_urls) - $count) . ' more' : trim($subcache_label, ', '));
            $subcache_title = trim($subcache_title, ', ');

            $subcache = '<span class="subcache" title="'.$subcache_title.'">' . $subcache_label . '</span>';
        } else {
            $subcache = '';
        }

        return sprintf('%1$s %2$s', urldecode($item['url']) . $subcache, $this->row_actions($actions));
    }

    public function usort_reorder($a, $b)
    {
        $orderby = (! empty($_GET['orderby'])) ? $_GET['orderby'] : 'priority';

        $order = (! empty($_GET['order'])) ? $_GET['order'] : 'asc';

        if ($orderby == 'priority') {
            $result = ($a['priority'] > $b['priority'] ? 1 : ($a['priority'] == $b['priority'] ? 0 : -1));
        } elseif ($orderby == 'timestamp') {
            $result = ($a['timestamp'] > $b['timestamp'] ? 1 : ($a['timestamp'] == $b['timestamp'] ? 0 : -1));
        } else {
            $result = strcmp($a[$orderby], $b[$orderby]);
        }
        return ($order === 'asc') ? $result : -$result;
    }

    protected function extra_tablenav($which)
    {
        include_once SWIFT_PERFORMANCE_DIR . 'includes/cache-status/table-nav.php';
    }

    protected function get_table_classes()
    {
        return array( 'widefat', 'fixed', 'striped', 'swift-performance-list-table' );
    }

    /**
    * Message to be displayed when there are no items
    */
    public function no_items()
    {
        if (get_transient('swift_performance_initial_prebuild_links') !== false) {
            _e('Scanning URLs...', 'swift-performance');
        } else {
            _e('No items found.');
        }
    }

    /**
     * Get prefix from URL
     * @param string $url
     * @return string
     */
    public static function get_subcache($url)
    {
        $subcache = array();
        $basedir = trailingslashit(SWIFT_PERFORMANCE_CACHE_DIR) . trailingslashit(parse_url($url, PHP_URL_PATH)) . '@prefix';
        if (@file_exists($basedir)) {
            $subcache = array_diff(scandir($basedir), array('..', '.'));
        }
        return $subcache;
    }
}

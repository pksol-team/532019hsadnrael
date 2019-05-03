<?php
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Swift_Performance_Image_Optimizer_Table extends WP_List_Table {

      public $stat = array();

      function get_columns(){
            $columns = array(
                  'cb'              => '<input type="checkbox" />',
                  'status'          => __('Status', 'swift-performance'),
                  'preview'         => __('Preview', 'swift-performance'),
                  'url'             => __('URL', 'swift-performance'),
                  'link'            => __('URL', 'swift-performance'),
                  'size'            => __('Size', 'swift-performance'),
                  'size_show'       => __('Size', 'swift-performance'),
                  'dimensions'      => __('Dimensions', 'swift-performance'),
            );
            return $columns;
      }

      function prepare_items() {
            $items = $this->get_items();

            $columns = $this->get_columns();
            $hidden = array('size', 'url');
            $sortable = $this->get_sortable_columns();
            $this->_column_headers = array($columns, $hidden, $sortable);

            usort( $items, array( &$this, 'usort_reorder' ) );

            $per_page = 30;
            $current_page = $this->get_pagenum();
            $total_items = count($items);

            $found_data = array_slice($items,(($current_page-1)*$per_page),$per_page);

            $this->set_pagination_args( array(
                'total_items' => $total_items,
                'per_page'    => $per_page
            ));
            $this->items = $found_data;
      }

      function column_default( $item, $column_name ) {
            return $item[ $column_name ];
      }

      function get_sortable_columns() {
            $sortable_columns = array(
                'link'         => array('link',false),
                'size_show'   => array('size', false)
            );
            return $sortable_columns;
      }

      function get_items(){
            global $wpdb;
            $items      = array();
            $images     = $wpdb->get_results("SELECT hash, file, type, width, height, original, quality, status FROM " . SWIFT_PERFORMANCE_IMAGE_TABLE, ARRAY_A);
            $this->stat = array(
                  'ids'             => array(),
                  'total'           => count($images),
                  'optimized'       => 0,
                  'original_size'   => 0,
                  'current_size'    => 0,
                  'queued'          => 0,
                  'exluded'         => 0,
                  'error'           => 0,
            );

            foreach ($images as $image){
                  if (!file_exists(SWIFT_PERFORMANCE_SITE_ROOT_DIR . $image['file'])){
                        $wpdb->query($wpdb->prepare("DELETE FROM " . SWIFT_PERFORMANCE_IMAGE_TABLE . " WHERE file = %s", $image['file']));
                        continue;
                  }

                  // Get size
                  $size = filesize(SWIFT_PERFORMANCE_SITE_ROOT_DIR . $image['file']);

                  // Statistics
                  $this->stat['optimized']      += ($image['status'] == 1 ? 1 : 0);
                  $this->stat['queued']         += (in_array($image['status'], array(2,3)) ? 1 : 0);
                  $this->stat['original_size']  += (int)$image['original'];
                  $this->stat['current_size']   += (int)$size;
                  $this->stat['exluded']        += ($image['status'] == -1 ? 1 : 0);

                  $original_exists  = file_exists(SWIFT_PERFORMANCE_SITE_ROOT_DIR . $image['file'].'.swift-original');

                  // Not writable
                  if (!is_writable(SWIFT_PERFORMANCE_SITE_ROOT_DIR . $image['file'])){
                        $image['status']  = -2;
                        $status           = '<span title="' . esc_attr__('Not Writable', 'swift-performance') . '" class="dashicons dashicons-warning"></span>';
                        $this->stat['error']++;
                  }
                  // Too large file
                  elseif ($size > 10*1024*1024){
                        $image['status']  = -2;
                        $status           = '<span title="' . esc_attr__('Too Large', 'swift-performance') . '" class="dashicons dashicons-warning"></span>';
                        $this->stat['error']++;
                  }
                  else {
                        $status           = '<span title="' . esc_attr__('Optimized', 'swift-performance') . '" class="optimized dashicons dashicons-yes'.($image['status'] != 1 ? ' swift-hidden' : '').'"></span>';
                        $status          .= '<span title="' . esc_attr__('Not Optimized', 'swift-performance') . '" class="not-optimized dashicons dashicons-no'.($image['status'] != 0 ? ' swift-hidden' : '').'"></span>';
                        $status          .= '<span title="' . esc_attr__('Excluded', 'swift-performance') . '" class="excluded dashicons dashicons-minus'.($image['status'] != -1 ? ' swift-hidden' : '').'"></span>';
                        $status          .= '<span title="' . esc_attr__('Queued', 'swift-performance') . '" class="queued dashicons dashicons-editor-ol'.(!in_array($image['status'], array(2,3)) ? ' swift-hidden' : '').'"></span>';
                  }

                  // Filtering
                  if (isset($_REQUEST['s']) && !empty($_REQUEST['s']) && strpos(strtolower($image['file']), strtolower($_REQUEST['s'])) === false){
                        continue;
                  }
                  if (isset($_REQUEST['status-filter']) && $_REQUEST['status-filter'] != ''){
                        $skip = false;
                        switch ($_REQUEST['status-filter']) {
                              case -2:
                                    if ($image['status'] != -2){
                                          $skip = true;
                                    }
                                    break;
                              case -1:
                                    if ($image['status'] != -1){
                                          $skip = true;
                                    }
                                    break;
                              case 0:
                                    if (!in_array($image['status'], array(-2,-1,0))){
                                          $skip = true;
                                    }
                                    break;
                              case 1:
                                    if ($image['status'] != 1){
                                          $skip = true;
                                    }
                                    break;
                              case 2:
                                    if (!in_array($image['status'], array(2,3))){
                                          $skip = true;
                                    }
                                    break;
                        }
                        if ($skip){
                              continue;
                        }
                  }

                  // Build columns
                  $url        = esc_url(Swift_Performance::home_url() . $image['file']);
                  $size_text  = Swift_Performance_Image_Optimizer::formatted_size($size);

                  $original_size = '';
                  if (!empty($image['original'])){
                        $original_size = sprintf(esc_html__('(Original: %s)'),Swift_Performance_Image_Optimizer::formatted_size($image['original']));
                  }

                  $bgsize = ($image['width'] < 140 ? 'unset' : 'contain');
                  $items[] = array(
                        'hash'            => $image['hash'],
                        'preview'         => '<a href="%s" target="_blank" class="swift-image-optimizer-preview quickview" style="background-image:url(\'%s\');background-size:'.esc_attr($bgsize).'" data-hash="'.esc_attr($image['hash']).'"></a>',
                        'bgsize'          => $bgsize,
                        'link'            => '<a href="%s" target="_blank">%s</a>',
                        'url'             => esc_url($url),
                        'file'            => $image['file'],
                        'original'        => $original_exists,
                        'original_size'   => $original_size,
                        'size'            => (int)$size,
                        'size_show'       => $size_text,
                        'dimensions'      => esc_html($image['width'] . '&times;' . $image['height'] . 'px'),
                        'status'          => $status,
                        'status_int'      => $image['status']
                  );

                  // Collect ids
                  if (isset($_REQUEST['s']) && !empty($_REQUEST['s'])){
                        $this->stat['ids'][$image['hash']] = array(
                              'hash'      => $image['hash'],
                              'src'       => $url,
                              'bgsize'    => $bgsize
                        );
                  }
            }

            $this->stat['ids'] = (object)$this->stat['ids'];

            return $items;
      }

      function column_preview($item){
            $url = esc_url(add_query_arg('cache-buster', md5_file(SWIFT_PERFORMANCE_SITE_ROOT_DIR . $item['file']), $item['url']));
            return sprintf($item['preview'], $url, $url);
      }

      function column_link($item) {
            $url = esc_url(add_query_arg('cache-buster', md5_file(SWIFT_PERFORMANCE_SITE_ROOT_DIR . $item['file']), $item['url']));
            $link = sprintf($item['link'], $url, $item['url']);
            if ($item['status_int'] == -2){
                  return $link;
            }
            $actions = array(
                  'exclude'         => '<a class="single-exclude'.($item['status'] == -1 ? ' swift-hidden' : '').'" data-hash="'.esc_attr($item['hash']).'" href="#">'.esc_html__('Exclude', 'swift-performance').'</a>',
                  'include'         => '<a class="single-include'.($item['status'] != -1 ? ' swift-hidden' : '').'" data-hash="'.esc_attr($item['hash']).'" href="#">'.esc_html__('Include', 'swift-performance').'</a>',
                  'optimize'        => '<a class="single-optimize" data-hash="'.esc_attr($item['hash']).'" href="#">'.esc_html__('Optimize', 'swift-performance').'</a>',
                  'remove-original' => '<a class="remove-original'.(!$item['original'] ? ' swift-hidden' : '').'" data-hash="'.esc_attr($item['hash']).'" href="#">'.esc_html__('Remove Original', 'swift-performance').'</a>',
                  'restore'         => '<a class="single-restore'.(!$item['original'] ? ' swift-hidden' : '').'" data-hash="'.esc_attr($item['hash']).'" href="#">'.esc_html__('Restore Original', 'swift-performance').'</a>'
            );

            return sprintf('%1$s %2$s', $link, $this->row_actions($actions) );
      }

      function column_size_show($item) {
            $actions = array();
            if ($item['status_int'] == 1 && !empty($item['original_size'])){
                  $actions['original'] = $item['original_size'];
            }
            return sprintf('%1$s %2$s', $item['size_show'], $this->row_actions($actions) );
      }

      function column_cb($item) {
        return '<input type="checkbox" class="swift-bulk-select-checkbox" name="images[]" id="' . $item['hash'] . '" data-src="' . $item['url'] . '" data-bgsize="' . $item['bgsize'] . '" ' . ($item['status_int'] == -2 ? 'disabled="disabled"' : '') . '/>';
      }

      function usort_reorder( $a, $b ) {
            $orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'link';

            $order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'asc';

            if ($orderby == 'size'){
                  $result = ($a['size'] > $b['size'] ? 1 : ($a['size'] == $b['size'] ? 0 : -1));
            }
            else {
                  $result = strcmp( $a[$orderby], $b[$orderby] );
            }
            return ( $order === 'asc' ) ? $result : -$result;
      }

      protected function row_actions( $actions, $always_visible = false ) {
		$action_count = count( $actions );
		$i = 0;

		if ( !$action_count )
			return '';

		$out = '<div class="' . ( $always_visible ? 'row-actions visible' : 'row-actions' ) . '">';
		foreach ( $actions as $action => $link ) {
			++$i;
			( $i == $action_count || strpos($link, 'swift-hidden') !== false) ? $sep = '' : $sep = ' | ';
			$out .= "<span class='$action'>$link$sep</span>";
		}
		$out .= '</div>';

		$out .= '<button type="button" class="toggle-row"><span class="screen-reader-text">' . __( 'Show more details' ) . '</span></button>';

		return $out;
	}

      /**
       * Include table header
       */
      protected function extra_tablenav($which){
            include_once SWIFT_PERFORMANCE_IMAGE_OPTIMIZER_DIR . 'templates/table-nav.php';
      }


      /**
       * Add plugin class to table
       */
      protected function get_table_classes(){
            return array( 'widefat', 'fixed', 'striped', 'swift-performance-list-table' );
      }

      /**
      * Message to be displayed when there are no items
      */
      public function no_items() {
            if (get_transient('swift_image_optimizer_load_images') !== false){
                  _e('Scanning images...', 'swift-performance');
            }
            else {
                  _e( 'No items found.' );
            }
      }
}
?>

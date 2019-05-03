<?php
      $search = (isset($_REQUEST['s']) ? $_REQUEST['s'] : '');
?>
<div class="swift-table-nav">
      <form class="swift-list-table-filter" action="<?php echo set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . preg_replace('~\?(.*)~','',$_SERVER['REQUEST_URI'] ))?>">
            <select name="status-filter">
                  <option value=""><?php esc_html_e('All Status', 'swift-performance');?></option>
                  <option value="1"<?php echo (isset($_REQUEST['status-filter']) ? ' ' . selected($_REQUEST['status-filter'], '1', false) : '')?>><?php esc_html_e('Optimized', 'swift-performance');?></option>
                  <option value="0"<?php echo (isset($_REQUEST['status-filter']) ? ' ' . selected($_REQUEST['status-filter'], '0', false) : '')?>><?php esc_html_e('Not Optimized', 'swift-performance');?></option>
                  <option value="2"<?php echo (isset($_REQUEST['status-filter']) ? ' ' . selected($_REQUEST['status-filter'], '2', false) : '')?>><?php esc_html_e('Queued', 'swift-performance');?></option>
                  <option value="-1"<?php echo (isset($_REQUEST['status-filter']) ? ' ' . selected($_REQUEST['status-filter'], '-1', false) : '')?>><?php esc_html_e('Excluded', 'swift-performance');?></option>
                  <option value="-2"<?php echo (isset($_REQUEST['status-filter']) ? ' ' . selected($_REQUEST['status-filter'], '-2', false) : '')?>><?php esc_html_e('Error', 'swift-performance');?></option>
            </select>
            <input type="hidden" name="page" value="<?php echo SWIFT_PERFORMANCE_SLUG; ?>">
            <input type="hidden" name="subpage" value="image-optimizer">
            <label class="screen-reader-text" for="swift-list-table-search-input"><?php esc_html_e('Search:', 'swift-performance');?></label>
            <input type="search" id="swift-list-table-search-input" class="swift-input" placeholder="<?php esc_html_e('Start typing...', 'swift-performance')?>" name="s" value="<?php echo esc_attr($search);?>">
            <button class="swift-btn swift-btn-gray"><?php esc_html_e('Filter', 'swift-performance');?></button>
            <?php if (!empty($search)):?>
                  <a href="#" id="swift-optimizer-select-all" class="swift-btn swift-btn-gray"><?php esc_html_e('Select All', 'swift-performance');?></a>
            <?php endif;?>
      </form>
</div>

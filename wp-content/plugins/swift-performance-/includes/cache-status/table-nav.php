<div class="swift-table-nav">
      <form class="swift-list-table-filter" action="<?php echo set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . preg_replace('~\?(.*)~','',$_SERVER['REQUEST_URI'] ))?>">
            <select name="cache-status-filter">
                  <option value=""><?php esc_html_e('All Status', 'swift-performance');?></option>
                  <option value="cached"<?php echo (isset($_REQUEST['cache-status-filter']) ? ' ' . selected($_REQUEST['cache-status-filter'], 'cached', false) : '')?>><?php esc_html_e('Cached', 'swift-performance');?></option>
                  <option value="not-cached"<?php echo (isset($_REQUEST['cache-status-filter']) ? ' ' . selected($_REQUEST['cache-status-filter'], 'not-cached', false) : '')?>><?php esc_html_e('Not Cached', 'swift-performance');?></option>
                  <option value="not-cacheable"<?php echo (isset($_REQUEST['cache-status-filter']) ? ' ' . selected($_REQUEST['cache-status-filter'], 'not-cacheable', false) : '')?>><?php esc_html_e('Not Cacheable', 'swift-performance');?></option>
                  <option value="404"<?php echo (isset($_REQUEST['cache-status-filter']) ? ' ' . selected($_REQUEST['cache-status-filter'], '404', false) : '')?>><?php esc_html_e('Cached 404', 'swift-performance');?></option>
                  <option value="redirect"<?php echo (isset($_REQUEST['cache-status-filter']) ? ' ' . selected($_REQUEST['cache-status-filter'], 'redirect', false) : '')?>><?php esc_html_e('Redirect', 'swift-performance');?></option>
            </select>
            <input type="hidden" name="page" value="<?php echo SWIFT_PERFORMANCE_SLUG; ?>">
            <input type="hidden" name="subpage" value="cache-status">
            <label class="screen-reader-text" for="swift-list-table-search-input">Search:</label>
            <input type="search" id="swift-list-table-search-input" class="swift-input" placeholder="<?php esc_html_e('Start typing...', 'swift-performance')?>" name="s" value="<?php echo (isset($_REQUEST['s']) ? esc_attr($_REQUEST['s']) : '');?>">
            <button class="swift-btn swift-btn-gray"><?php esc_html_e('Filter', 'swift-performance');?></button>
      </form>
</div>

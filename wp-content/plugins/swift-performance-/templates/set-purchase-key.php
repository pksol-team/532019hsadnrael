<?php defined('ABSPATH') or die("KEEP CALM AND CARRY ON");?>

<div class="swift-message swift-purchase-key-warning">
	<p>
		<span class="dashicons dashicons-warning"></span>
		<span class="swift-message-text"><?php esc_html_e('This feature requires a valid purchase key', 'swift-performance');?></span>
	</p>
      <p class="swift-centered">
      	<a href="<?php echo esc_url(add_query_arg(array('subpage' => 'settings', 'tab' => '1'), menu_page_url('swift-performance', false)));?>" class="swift-btn swift-btn-green"><?php esc_html_e('Go to Settings', 'swift-performance')?></a>
            <a href="https://swteplugins.com" target="_blank" class="swift-btn swift-btn-brand"><?php esc_html_e('Purchase a license', 'swift-performance')?></a>
      </p>
</div>

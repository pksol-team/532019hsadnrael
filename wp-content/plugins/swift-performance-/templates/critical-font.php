<?php defined('ABSPATH') or die("KEEP CALM AND CARRY ON");?>
<?php if (Swift_Performance::check_option('purchase-key', '')):?>
      <div class="swift-message critical"><?php esc_html_e('Critical Font option needs a valid API key.', 'swift-performance')?></div>
<?php else:?>
<div class="swift-message swift-hidden"></div>
<div class="swift-critical-font-container" data-font="fontawesome">
      <h2><?php esc_html_e('Font Awesome', 'swift-performance');?></h2>
      <strong class="status"><?php echo (Swift_Performance_Critical_Font::is_enqueued('fontawesome') ? esc_html_e('Enqueued', 'swift-performance') : '' . esc_html_e('Not enqueued', 'swift-performance'))?></strong>
      <ul class="swift-buttonset">
            <li><a href="#" class="swift-enqueue-critical-font swift-btn swift-btn-blue"><?php echo (Swift_Performance_Critical_Font::is_enqueued('fontawesome') ? esc_html__('Update Critical Font', 'swift-performance') : esc_html__('Enqueue Critical Font', 'swift-performance'));?></a></li>
            <li><a href="#" class="swift-dequeue-critical-font swift-btn swift-btn-brand<?php echo (!Swift_Performance_Critical_Font::is_enqueued('fontawesome') ? ' swift-hidden' : '');?>"><?php esc_html_e('Dequeue Critical Font', 'swift-performance')?></a></li>
            <li><a href="#" class="swift-scan-used-icons swift-btn swift-btn-green"><?php echo esc_html_e('Scan used icons', 'swift-performance');?></a></li>
      </ul>
      <div class="swift-font-selector-container">
            <input type="text" class="swift-critical-font-filter" placeholder="<?php esc_html_e('Search', 'swift-performance');?>"><a href="#" class="swift-btn swift-clear-critical-font-filter"><span class="dashicons dashicons-no-alt"></span></a>
            <a href="#" class="swift-critical-font-clear-all swift-btn swift-btn-gray"><?php echo esc_html_e('Clear all', 'swift-performance');?></a>
            <ul class="swift-font-selector">
                  <?php foreach (Swift_Performance_Critical_Font::font_list('fontawesome') as $font):?>
                        <li class="<?php echo ($font['active'] ? 'active' : '');?>" data-class="<?php echo esc_attr($font['class']);?>" data-selector="<?php echo esc_attr($font['selector']);?>" data-content="<?php echo esc_attr($font['content']);?>"><i class="fa <?php echo esc_attr($font['class']);?>"></i></li>
                  <?php endforeach;?>
            </ul>
      </div>
</div>
<?php endif;?>

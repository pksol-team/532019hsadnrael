<?php if (Swift_Performance::check_option('merge-scripts',1)):?>
<div class="swift-meta-box-group">
      <strong><?php _e('Include Scripts', 'swift-performance');?></strong><br>
      <div class="swift-hidden sample">
            <div class="swift-meta-box-row">
                  <input type="url" name="swift-performance[include-scripts][]">
                  <a href="#" class="remove-row"><?php esc_html_e('Remove', 'swift-performance');?></a>
            </div>
      </div>
      <?php foreach((array)$include_scripts as $script):?>
            <div class="swift-meta-box-row">
                  <input type="url" name="swift-performance[include-scripts][]" value="<?php echo esc_attr($script);?>">
                  <a href="#" class="remove-row"><?php esc_html_e('Remove', 'swift-performance');?></a>
            </div>
      <?php endforeach;?>
      <a href="#" class="add-new-row swift-btn swift-btn-blue"><?php _e('Add More');?></a>
</div>
<?php endif;?>
<?php if (Swift_Performance::check_option('merge-styles',1)):?>
<div class="swift-meta-box-group">
      <strong><?php _e('Include Styles', 'swift-performance');?></strong><br>
      <div class="swift-hidden sample">
            <div class="swift-meta-box-row">
                  <input type="url" name="swift-performance[include-styles][]">
                  <a href="#" class="remove-row"><?php esc_html_e('Remove', 'swift-performance');?></a>
            </div>
      </div>
      <?php $include_scripts = get_post_meta(get_the_ID(), 'swift_include_scripts', true);?>
      <?php foreach((array)$include_styles as $style):?>
            <div class="swift-meta-box-row">
                  <input type="url" name="swift-performance[include-styles][]" value="<?php echo esc_attr($style);?>">
                  <a href="#" class="remove-row"><?php esc_html_e('Remove', 'swift-performance');?></a>
            </div>
      <?php endforeach;?>
      <a href="#" class="add-new-row swift-btn swift-btn-blue"><?php _e('Add More');?></a>
</div>
<?php endif;?>
<?php if (Swift_Performance::check_option('merge-scripts', 1, '!=') && Swift_Performance::check_option('merge-styles', 1, '!=')):?>
      <p class="swift-centered"><?php _e('There is no available option for this page', 'swift-performance');?></p>
<?php endif;?>

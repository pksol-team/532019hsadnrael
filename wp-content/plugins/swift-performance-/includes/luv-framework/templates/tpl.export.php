<div class="<?php echo implode(' ', $classes);?>" id="<?php echo esc_attr($prefix . $field['id']);?>-container"<?php echo (!empty($default) ? ' data-default="' . $default . '"' : '');?> data-type="<?php echo $field['type'];?>">
      <div class="luv-framework-field-title">
            <strong><?php echo esc_html_e('Export Settings');?></strong>
            <div class="luv-framework-field-description">
                  <?php echo esc_html_e('You can export and download current settings here.', 'luv-framework'); ?>
            </div>
      </div>
      <div class="luv-framework-field-inner">
      	<a href="<?php echo add_query_arg(array('luv-action' => 'export', 'nonce' => wp_create_nonce('luv-framework-export')), admin_url())?>" class="luv-framework-button" target="_blank"><?php esc_html_e('Download', 'luv-framework');?></a>
      </div>
</div>

<div class="<?php echo implode(' ', $classes);?>" id="<?php echo esc_attr($prefix . $field['id']);?>-container"<?php echo (!empty($default) ? ' data-default="' . $default . '"' : '');?> data-type="<?php echo $field['type'];?>">
      <div class="luv-framework-field-title">
            <strong><?php echo esc_html_e('Import Settings');?></strong>
            <div class="luv-framework-field-description">
                  <?php echo esc_html_e('Upload previously exported configuration file.', 'luv-framework'); ?>
            </div>
      </div>
      <div class="luv-framework-field-inner">
      	<div class="luv-framework-import-file-container">
                  <label><i class="fas fa-cloud-upload-alt"></i> <?php esc_html_e('Choose file', 'luv-framework');?></label>
                  <input type="file" class="luv-framework-import-file">
            </div>
            <textarea class="luv-framework-import luv-hidden"></textarea>
      </div>
</div>

<div class="luv-framework-confirm-import luv-hidden">
      <h6 class="luv-modal__title"><?php esc_html_e('Hey!', 'swift-performance');?></h6>
      <p class="luv-modal__text"><?php esc_html_e('Import configuration file will override current settings. Do you proceed?', 'swift-performance');?></p>
      <a href="#" class="luv-framework-button btn-green" data-luv-proceed-import data-field-id="<?php echo esc_attr($prefix . $field['id']);?>"><?php esc_html_e('Import', 'swift-performance');?></a>
      <a href="#" class="luv-framework-button btn-red" data-luv-close-modal><?php esc_html_e('Cancel', 'swift-performance');?></a>
</div>

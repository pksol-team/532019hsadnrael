<div class="<?php echo implode(' ', $classes);?>" id="<?php echo esc_attr($prefix . $field['id']);?>-container"<?php echo (!empty($default) ? ' data-default="' . $default . '"' : '');?> data-type="<?php echo $field['type'];?>">
      <div class="luv-framework-field-title">
            <strong><?php echo esc_html($label);?></strong>
            <?php if (!empty($info)):?>
                  <a href="#" class="luv-framework-show-info">?</a>
                  <div class="luv-framework-info">
                        <?php echo luv_framework_kses($info);?>
                  </div>
            <?php endif;?>
            <?php if (!empty($description)):?>
                  <div class="luv-framework-field-description">
                        <?php echo esc_html($description); ?>
                  </div>
            <?php endif;?>
      </div>
      <div class="luv-framework-field-inner">
     	 <textarea class="luv-framework-editor-field" data-mode="<?php echo esc_attr($mode);?>"<?php echo (isset($field['placeholder']) ? ' placeholder="' . esc_attr($field['placeholder']) . '"' : ''); ?> name="<?php echo esc_attr($name);?>"><?php echo esc_textarea($field['value']);?></textarea>
      </div>
</div>

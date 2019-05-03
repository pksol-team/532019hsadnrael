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
            <input id="<?php echo esc_attr($name);?>" type="checkbox" class="ios8-switch" name="<?php echo esc_attr($name);?>" value="1" <?php echo ($field['value'] == 1 ? ' checked' : ''); ?>>
            <label for="<?php echo esc_attr($name);?>"></label>
      </div>
</div>

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
            <?php if (!empty($field['value'])):?>
                  <input class="luv-framework-text-field luv-hidden" type="text" name="<?php echo esc_attr($name);?>" value="<?php echo md5($field['value'])?>">
                  <span class="luv-license-placeholder">
                  <?php for ($i=0;$i<32;$i++):?>
                        <i class="fas fa-circle"></i>
                  <?php endfor;?>
                  </span>
                  <a href="#" class="luv-clear-license-field" title="<?php esc_attr_e('Remove purchase key', 'swift-performance');?>"><i class="fas fa-times"></i></a>
            <?php else:?>
      	      <input class="luv-framework-text-field" type="text"<?php echo (isset($field['placeholder']) ? ' placeholder="' . esc_attr($field['placeholder']) . '"' : ''); ?> name="<?php echo esc_attr($name);?>">
            <?php endif;?>
      </div>
</div>

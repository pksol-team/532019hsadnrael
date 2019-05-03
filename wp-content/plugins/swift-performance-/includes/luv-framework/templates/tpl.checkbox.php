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
     	 <?php if (isset($field['options'])): ?>
                  <a href="#" class="luv-framework-button luv-framework-toggle-all"><?php esc_html_e('Toggle All', 'luv-framework')?></a>
	            <?php foreach ($field['options'] as $key => $option):?>
	                  <label>
	                        <input type="checkbox" name="<?php echo esc_attr($name);?>" value="<?php echo esc_attr($key);?>"<?php echo (in_array($key, (array)$field['value']) ? ' checked' : ''); ?>>
                              <?php echo esc_html($option);?>
	                  </label>
	            <?php endforeach;?>
	      <?php endif; ?>
      </div>
</div>

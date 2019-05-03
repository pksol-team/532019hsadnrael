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
	      <select class="luv-framework-dropdown-field" name="<?php echo esc_attr($name);?>"<?php echo (isset($field['multiple']) && $field['multiple'] ? ' multiple' : '');?>>
	            <?php if (isset($field['options'])): ?>
	                  <?php foreach ($field['options'] as $key => $option):?>
	                        <option value="<?php echo esc_attr($key);?>"<?php echo ((is_array($field['value']) && in_array($key, $field['value'])) || ($field['value'] == $key) ? ' selected' : ''); ?>><?php echo esc_html($option);?></option>
	                  <?php endforeach;?>
	            <?php endif; ?>
	      </select>
      </div>
</div>

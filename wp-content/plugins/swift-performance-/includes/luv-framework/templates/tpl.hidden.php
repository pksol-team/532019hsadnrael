<div class="<?php echo implode(' ', $classes);?>" id="<?php echo esc_attr($prefix . $field['id']);?>-container"<?php echo (!empty($default) ? ' data-default="' . $default . '"' : '');?> data-type="<?php echo $field['type'];?>">
      <div class="luv-framework-field-inner">
      	<input class="luv-framework-text-field" type="hidden" name="<?php echo esc_attr($name);?>" value="<?php echo esc_attr($field['value'])?>">
      </div>
</div>

<div class="<?php echo implode(' ', $classes);?>"<?php echo (!empty($default) ? ' data-default="' . $default . '"' : '');?> data-type="<?php echo $field['type'];?>" id="<?php echo esc_attr($prefix . $field['id']);?>-container">
      <div class="luv-framework-multitext-outer luv-framework-multitext-sample luv-hidden">
            <input class="luv-framework-text-field" type="text"<?php echo (isset($field['placeholder']) ? ' placeholder="' . esc_attr($field['placeholder']) . '"' : ''); ?> data-name="<?php echo esc_attr($name);?>">
            <a href="#" class="luv-framework-remove-multi-field">&times;</a>
      </div>
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
      <?php if (!empty($field['value'])):?>
            <?php foreach ((array)$field['value'] as $value): ?>
                  <div class="luv-framework-multitext-outer">
                        <input class="luv-framework-text-field" type="text" name="<?php echo esc_attr($name);?>" value="<?php echo esc_attr($value)?>">
                        <a href="#" class="luv-framework-remove-multi-field">&times;</a>
                  </div>
            <?php endforeach; ?>
      <?php else:?>
            <div class="luv-framework-multitext-outer">
                  <input class="luv-framework-text-field" type="text"<?php echo (isset($field['placeholder']) ? ' placeholder="' . esc_attr($field['placeholder']) . '"' : ''); ?> name="<?php echo esc_attr($name);?>">
                  <a href="#" class="luv-framework-remove-multi-field">&times;</a>
            </div>
      <?php endif;?>
      <a href="#" class="luv-framework-button luv-framework-add-multi-field"><?php esc_html_e('Add more', 'luv-framework');?></a>

</div>

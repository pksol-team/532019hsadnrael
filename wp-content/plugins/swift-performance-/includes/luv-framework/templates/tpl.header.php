<?php do_action('luv_framework_before_framework_header');?>
<div class="luv-framework-header">
      <input type="text" class="luv-framework-search" data-fieldset="#fieldset-<?php echo $this->unique_id;?>" placeholder="<?php esc_attr_e('Search', 'luv-framework');?>">
      <ul class="luv-framework-header-menu">
            <?php do_action('luv_framework_before_header_buttons', $this);?>
            <?php if ($this->args['ajax']):?>
            <li>
                  <a href="#" class="luv-framework-button luv-framework-ajax-save" data-action="<?php echo $this->ajax_save_endpoint;?>" data-fieldset="#fieldset-<?php echo $this->unique_id;?>"><?php esc_html_e('Save changes', 'luv-framework');?></a>
            </li>
            <?php endif;?>
            <li>
                  <a href="#" class="luv-framework-button luv-framework-reset-section"><?php esc_html_e('Reset Section');?></a>
            </li>
            <li>
                  <a href="#" class="luv-framework-button luv-framework-reset-all"><?php esc_html_e('Reset All');?></a>
            </li>
            <?php do_action('luv_framework_after_header_buttons', $this);?>
      </ul>
</div>

<?php // Reset modals ?>

<div class="luv-confirm-reset-section luv-hidden">
      <h6 class="luv-modal__title"><?php esc_html_e('Hey!', 'swift-performance');?></h6>
      <p class="luv-modal__text"><?php esc_html_e('Are you sure? Resetting will lose all custom values in this section.', 'swift-performance');?></p>
      <a href="#" class="swift-btn swift-btn-blue" data-luv-proceed-reset-section data-action="<?php echo $this->ajax_save_endpoint;?>" data-fieldset="#fieldset-<?php echo $this->unique_id;?>"><?php esc_html_e('Reset Section', 'swift-performance');?></a>
      <a href="#" class="swift-btn swift-btn-brand" data-luv-close-modal><?php esc_html_e('Dismiss', 'swift-performance');?></a>
</div>

<div class="luv-confirm-reset-all luv-hidden">
      <h6 class="luv-modal__title"><?php esc_html_e('Hey!', 'swift-performance');?></h6>
      <p class="luv-modal__text"><?php esc_html_e('Are you sure? Resetting will lose all custom values.', 'swift-performance');?></p>
      <a href="#" class="swift-btn swift-btn-blue" data-luv-proceed-reset-all data-action="<?php echo $this->ajax_save_endpoint;?>" data-fieldset="#fieldset-<?php echo $this->unique_id;?>"><?php esc_html_e('Reset All', 'swift-performance');?></a>
      <a href="#" class="swift-btn swift-btn-brand" data-luv-close-modal><?php esc_html_e('Dismiss', 'swift-performance');?></a>
</div>
<?php do_action('luv_framework_after_framework_header');?>

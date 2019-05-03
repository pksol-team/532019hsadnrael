<?php
/**
 * Post Type Template Single Post edit page
 *
 *
 * @link       http://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/admin/partials
 */
?>
<form method="post" action="<?php echo admin_url('admin-ajax.php?action=' . $this->plugin_name . '_ajax_themes_save') ?>">
    <input type="hidden" value="<?php echo wp_create_nonce($this->plugin_name . '_them_ajax'); ?>"
           name="<?php echo $this->plugin_name ?>_nonce"/>
           <?php do_settings_sections($this->settings_section) ?>

    <p class="submit">
        <input type="button" id="<?php echo $this->plugin_name ?>_submit" class="button button-primary" value="<?php _e('Save', 'ptb') ?>"/>
    </p>
    <div id="<?php echo $this->plugin_name ?>_success_text" class="updated"></div>
</form>


<script type="text/javascript">
    jQuery(function () {
        PTB.init({
            prefix: '<?php echo $this->plugin_name ?>_',
            template_type: '<?php echo $this->type ?>'
        });
    });
</script>

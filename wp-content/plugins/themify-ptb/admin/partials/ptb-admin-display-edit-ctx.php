<?php
/**
 * Custom Taxonomy edit or add form
 *
 *
 * @link       http://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/admin/partials
 */
?>

<?php
if (isset($_REQUEST['action']) && 'add' === $_REQUEST['action']) {
    $title = __('Add New Taxonomy', 'ptb');
    $add_ctx = '';
} else {
    $title = __('Edit Taxonomy', 'ptb');
    $add_ctx = sprintf('<a href="?page=%s&action=%s" class="add-new-h2">%s</a>', $_REQUEST['page'], 'add', __('Add New', 'ptb'));
}
?>

<div class="wrap ptb-wrapper">
    <h2>
        <?php echo esc_html($title); ?>
        <?php echo $add_ctx; ?>
    </h2>

    <div class="ptb_notices">
        <?php settings_errors($this->plugin_name . '_notices'); ?>
        <div id="ptb_ajax_message" class="error below-h2 hidden">
            <span id="ptb-ajax-notification-nonce"
                  class="hidden"><?php echo wp_create_nonce('ajax-ptb-ctx-nonce'); ?></span>
        </div>
    </div>

    <form method="post" action="options.php">

        <?php settings_fields('ptb_plugin_options'); ?>
        <?php do_settings_sections($this->plugin_name . '-ctx') ?>

        <?php
        if (isset($_REQUEST['action']) && 'add' === $_REQUEST['action']) {
            submit_button(__('Save', 'ptb'));
        } else {
            submit_button(__('Update', 'ptb'));
        }
        ?>

    </form>
</div><!-- .wrap -->

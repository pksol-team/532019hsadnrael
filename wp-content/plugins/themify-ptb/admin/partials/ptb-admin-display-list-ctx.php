<?php
/**
 * Shows list of custom taxonomies
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/admin/partials
 */
$ctxListTable = new PTB_List_Table_CTX($this->plugin_name, $this->version, $this->options);
$ctxListTable->prepare_items();

$title = __('Taxonomies', 'ptb');

$add_ctx = sprintf('<a href="?page=%s&action=%s" class="add-new-h2">%s</a>', $_REQUEST['page'], 'add', __('Add New', 'ptb'));
?>
<div class="wrap">

    <h2>
        <?php echo esc_html($title); ?>
        <?php echo $add_ctx; ?>
    </h2>

    <span id="ptb-ajax-action-nonce" class="hidden"><?php echo wp_create_nonce('ajax-ptb-ctx-nonce'); ?></span>

    <?php settings_errors($this->plugin_name . '_notices'); ?>

    <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
    <form id="ptb-ctx-filter" method="post">
        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <!-- Now we can render the completed list table -->
        <?php $ctxListTable->display() ?>
    </form>

</div>
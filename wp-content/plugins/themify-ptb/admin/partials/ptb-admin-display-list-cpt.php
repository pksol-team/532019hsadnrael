<?php
/**
 * Provide a dashboard view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/admin/partials
 */
if (isset($_REQUEST['settings-updated']) && $_REQUEST['action'] === 'add'): //redirect to themplate page
    ?>
    <?php
    $posts = array_reverse($this->options->get_custom_post_types());
    $last_post_type = $posts[0];
    $slug = $last_post_type->slug;
    ?>
    <script type="text/javascript">
        window.location.href = '<?php echo admin_url('admin.php?page=ptb-ptt&action=add&slug=' . $slug); ?>';
    </script>
    <?php exit; ?>
<?php endif; ?>
<?php
$cptListTable = new PTB_List_Table_CPT($this->plugin_name, $this->version, $this->options);
$cptListTable->prepare_items();

$title = __('Post Types', 'ptb');

$add_cpt = sprintf('<a href="?page=%s&action=%s" class="add-new-h2">%s</a>', $_REQUEST['page'], 'add', __('Add New', 'ptb'));
?>
<div class="wrap">

    <h2>
        <?php echo esc_html($title); ?>
        <?php echo $add_cpt; ?>
    </h2>

    <span id="ptb-ajax-action-nonce" class="hidden"><?php echo wp_create_nonce('ajax-ptb-cpt-nonce'); ?></span>

    <?php settings_errors($this->plugin_name . '_notices'); ?>

    <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
    <form id="ptb-cpt-filter" method="post">
        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <!-- Now we can render the completed list table -->
        <?php $cptListTable->display() ?>
    </form>

</div>

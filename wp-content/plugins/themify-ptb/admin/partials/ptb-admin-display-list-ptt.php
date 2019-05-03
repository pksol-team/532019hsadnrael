<?php
/**
 * Shows list of post type templates
 *
 * @link       http://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/admin/partials
 */
if (isset($_REQUEST['settings-updated']) && $_REQUEST['action'] == 'add'): //redirect to themplate page
    ?>
    <?php $t_id = key(array_reverse($this->options->option_post_type_templates)); ?>
    <script type="text/javascript">
        window.location.href = '<?php echo admin_url('admin.php?page=ptb-ptt&action=edit&&ptb-ptt=' . $t_id); ?>';
    </script>
    <?php exit; ?>
<?php endif; ?>
<?php
$pttListTable = new PTB_List_Table_PTT($this->plugin_name, $this->version, $this->options);
$pttListTable->prepare_items();
$title = __('Templates', 'ptb');
$add_ptt = sprintf('<a href="?page=%s&action=%s" class="add-new-h2">%s</a>', $_REQUEST['page'], 'add', __('Add New', 'ptb'));
?>
<div class="wrap">

    <h2>
        <?php echo esc_html($title); ?>
        <?php echo $add_ptt; ?>
    </h2>


    <?php settings_errors($this->plugin_name . '_notices'); ?>
    <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
    <form id="ptb-ptt-filter" method="post">
        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <!-- Now we can render the completed list table -->
        <?php $pttListTable->display() ?>
    </form>

</div>
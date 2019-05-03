<?php

class PTB_Form_ImportExport {

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $plugin_name The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $version The current version of the plugin.
     */
    protected $version;
    protected $slug_admin_io;
    protected $settings_section_import;
    protected $settings_section_export;

    /**
     * The options management class of the the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      PTB_Options $options Manipulates with plugin options
     */
    protected $options;
    protected $key;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     *
     * @param string $plugin_name
     * @param string $version
     * @param PTB_Options $options the plugin options instance
     *
     */
    public function __construct($plugin_name, $version, $options) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

        $this->key = 'io';
        $this->settings_section_import = 'settings_section_import';
        $this->settings_section_export = 'settings_section_export';

        $this->options = $options;
    }

    public function add_settings_fields($slug_admin_io) {

        $this->slug_admin_io = $slug_admin_io;

        add_settings_section(
                $this->settings_section_export, __("Export", "ptb"), array($this, 'export_section_cb'), $this->slug_admin_io
        );

        add_settings_section(
                $this->settings_section_import, __("Import", "ptb"), array($this, 'import_section_cb'), $this->slug_admin_io
        );
    }

    public function export_section_cb() {

        $lng = PTB_Utils::get_current_language_code();
        ?>

        <div class="ptb_interface ptb_export_wrapper">

            <div class="ptb_export_radio_wrapper">
                <label for="ptb_export_option_linked">
                    <input type="radio" name="ptb_export_mode" value="linked" id="ptb_export_option_linked"
                           checked="checked">
        <?php _e("Export Post Types and its associated Taxonomies & Templates", "ptb"); ?>
                </label>
                <br/>
                <label for="ptb_export_option_separately">
                    <input type="radio" name="ptb_export_mode" value="separately" id="ptb_export_option_separately">
        <?php _e("Export Separately", "ptb"); ?>
                </label>
            </div>

            <h2 class="nav-tab-wrapper">
                <a href="#ptb_export_cpt_list" class="nav-tab nav-tab-active"
                   data-target="cpt"><?php _e('Post Types'); ?></a>
                <a href="#ptb_export_ctx_list" class="nav-tab" style="display: none;"
                   data-target="ctx"><?php _e('Taxonomies'); ?></a>
                <a href="#ptb_export_ptt_list" class="nav-tab" style="display: none;"
                   data-target="ptt"><?php _e('Templates'); ?></a>
            </h2>

            <div class="ptb_tab_content_wrapper">
                <div id="ptb_export_cpt_list" class="ptb_tab_content">
        <?php
        $cpt_collection = $this->options->get_custom_post_types();
        foreach ($cpt_collection as $cpt) :
            ?>
                        <label for="ptb_cpt_export_<?php echo $cpt->slug; ?>">
                            <input type="checkbox" name="ptb_cpt_export[]" id="ptb_cpt_export_<?php echo $cpt->slug; ?>" value="<?php echo $cpt->slug; ?>"> <?php echo PTB_Utils::get_label($cpt->plural_label); ?>
                        </label>
        <?php endforeach; ?>
                </div>

                <div id="ptb_export_ctx_list" class="ptb_tab_content" style="display: none;">
        <?php
        $ctx_collection = $this->options->get_custom_taxonomies();
        foreach ($ctx_collection as $ctx) :
            ?>
                        <label for="ptb_ctx_export_<?php echo $ctx->slug; ?>">
                            <input type="checkbox" name="ptb_ctx_export[]" id="ptb_ctx_export_<?php echo $ctx->slug; ?>" value="<?php echo $ctx->slug; ?>"> <?php echo $ctx->plural_label[$lng]; ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div id="ptb_export_ptt_list" class="ptb_tab_content" style="display: none;">
                    <?php
                    $ptt_collection = $this->options->get_post_type_templates();
                    foreach ($ptt_collection as $key => $ptt) :
                        ?>
                        <label for="ptb_ctx_export_<?php echo $key; ?>">
                            <input type="checkbox" name="ptb_ctx_export[]" id="ptb_ctx_export_<?php echo $key; ?>" value="<?php echo $key; ?>"> <?php echo $ptt->get_name(); ?>
                        </label>
                    <?php endforeach; ?>
                </div>

            </div>

            <form method="post" action="options.php" id="ptb_form_export">
                <?php settings_fields('ptb_plugin_options'); ?>
                <input type="hidden" name="ptb_plugin_options[ptb_ie_export]" value=""/>
                <a href="#export" class="ptb_ie_button" id="ptb_export"><?php _e('Export', 'ptb') ?></a>
            </form>

        </div>

        <?php
    }

    public function import_section_cb() {
        ?>

        <div class="ptb_interface ptb_import_wrapper">

            <form method="post" action="options.php" enctype="multipart/form-data" id="ptb_form_import">
                <?php settings_fields('ptb_plugin_options'); ?>
                <input type="hidden" name="ptb_plugin_options[ptb_ie_import]"/>
                <input type="file" name="ptb_import_file" class="ptb_import_file"/>
                <a href="#import" class="ptb_ie_button" id="ptb_import"><?php _e('Import', 'ptb') ?></a>
            </form>

        </div>


        <?php
    }

    public function export($input) {

        $export = json_decode($input['ptb_ie_export'], true);

        $mode = $export['mode'];
        $target = $export['target'];
        $list = !empty($export['list']) ? $export['list'] : array();

        $result = $this->options->get_options_blueprint();

        if ($mode == 'separately') {

            switch ($target) {
                case 'cpt':
                    $collection = $this->options->get_custom_post_types_options();
                    break;
                case 'ctx':
                    $collection = $this->options->get_custom_taxonomies_options();
                    break;
                case 'ptt':
                    $collection = $this->options->get_templates_options();
                    break;
                default:
                    $collection = array();
            }

            foreach ($collection as $key => &$value) {
                if (!in_array($key, $list)) {
                    unset($collection[$key]);
                } elseif ($target == 'cpt') {
                    $value[PTB_Custom_Post_Type::TAXONOMIES] = array();
                } elseif ($target == 'ctx') {
                    $value[PTB_Custom_Taxonomy::ATTACH_TO] = array();
                }
            }

            $result[$target] = $collection;
        } elseif ($mode == 'linked') {

            $cpt_collection = $this->options->get_custom_post_types_options();
            $ctx_collection = $this->options->get_custom_taxonomies_options();
            $ptt_collection = $this->options->get_templates_options();

            $post_types = array();
            $taxonomies = array();
            $templates = array();

            foreach ($cpt_collection as $cpt_slug => &$value) {

                if (in_array($cpt_slug, $list)) {

                    $post_types[$cpt_slug] = $cpt_collection[$cpt_slug];

                    foreach ($value[PTB_Custom_Post_Type::TAXONOMIES] as $ctx_slug) {

                        if (array_key_exists($ctx_slug, $ctx_collection)) {

                            $taxonomies[$ctx_slug] = $ctx_collection[$ctx_slug];
                        }
                    }
                }
            }

            foreach ($taxonomies as $ctx_slug => &$ctx) {

                foreach ($ctx[PTB_Custom_Taxonomy::ATTACH_TO] as $cpt_slug) {

                    if (!in_array($cpt_slug, $list)) {

                        PTB_Utils::remove_from_array($cpt_slug, $ctx[PTB_Custom_Taxonomy::ATTACH_TO]);
                    }
                }
            }

            foreach ($ptt_collection as $ptt_id => $ptt) {

                if (in_array($ptt['post_type'], $list)) {

                    $templates[$ptt_id] = $ptt;
                }
            }

            $result['cpt'] = $post_types;
            $result['ctx'] = $taxonomies;
            $result['ptt'] = $templates;
        }

        ignore_user_abort(true);

        nocache_headers();
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename=ptb-settings-export-' . date('m-d-Y') . '.json');
        header("Expires: 0");

        echo json_encode($result);
        exit;
    }

    public function import($input) {

        $tmp = explode('.', $_FILES['ptb_import_file']['name']);
        $extension = end($tmp);

        if ($extension != 'json') {

            add_settings_error($this->plugin_name . '_notices', '', __('Please upload a valid .json file', "ptb"), 'error');

            return;
        }

        $import_file = $_FILES['ptb_import_file']['tmp_name'];

        if (empty($import_file)) {

            add_settings_error($this->plugin_name . '_notices', '', __('Please upload a file to import', "ptb"), 'error');

            return;
        }

        // Retrieve the settings from the file and convert the json object to an array.
        $data = json_decode(file_get_contents($import_file), true);

        if (array_key_exists('plugin', $data) && $this->plugin_name == $data['plugin']) {

            $options = $this->options;

            $cpt_collection = $options->get_custom_post_types_options();
            $ctx_collection = $options->get_custom_taxonomies_options();
            $ptt_collection = $options->get_templates_options();

            $post_types = array_key_exists('cpt', $data) ? $data['cpt'] : array();
            $taxonomies = array_key_exists('ctx', $data) ? $data['ctx'] : array();
            $templates = array_key_exists('ptt', $data) ? $data['ptt'] : array();

            foreach ($post_types as $cpt_key => $cpt) {

                $cpt_collection[$cpt_key] = $cpt;
            }

            foreach ($taxonomies as $ctx_key => $ctx) {

                $ctx_collection[$ctx_key] = $ctx;
            }

            foreach ($templates as $ptt_key => $ptt) {

                $ptt_collection[$ptt_key] = $ptt;
            }

            $options->set_custom_post_types_options($cpt_collection);
            $options->set_custom_taxonomies_options($ctx_collection);
            $options->set_templates_options($ptt_collection);
            global $wpdb;
            $options->set_flush();
            $options->update();
            if (isset($wpdb->last_error) && $wpdb->last_error) {
                add_settings_error($this->plugin_name . '_notices', '', __("Import failed", "ptb"), 'error');
            } else {
                add_settings_error($this->plugin_name . '_notices', '', __("Imported data has been successfully processed", "ptb"), 'updated');
                flush_rewrite_rules();
            }
        } else {

            add_settings_error($this->plugin_name . '_notices', '', __("Imported data has wrong format", "ptb"), 'error');
        }
    }

}

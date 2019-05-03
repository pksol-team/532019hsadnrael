<?php

/**
 * The edit and add form class of custom taxonomy
 *
 * @link       http://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/includes
 */

/**
 * The edit and add form class of custom taxonomy
 *
 * @since      1.0.0
 * @package    PTB
 * @subpackage PTB/includes
 * @author     Themify <ptb@themify.me>
 */
class PTB_Form_CTX {

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

    /**
     * The id of current taxonomy. Empty string if taxonomy is new.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $id The id of current taxonomy.
     */
    private $id;

    /**
     * The id of current taxonomy object.
     *
     * @since    1.0.0
     * @access   private
     * @var      PTB_Custom_Taxonomy $ctx The id of current taxonomy.
     */
    private $ctx;
    private $key;
    private $settings_section_ctx;
    private $settings_section_cl;
    private $settings_section_ad;
    private $slug_admin_ctx;

    /**
     * The options management class of the the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      PTB_Options $options Manipulates with plugin options
     */
    private $options;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @var      string $plugin_name The name of this plugin.
     * @var      string $version The version of this plugin.
     *
     * @param PTB_Options $options
     */
    public function __construct($plugin_name, $version, $options) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

        $this->options = $options;

        $this->set_defaults();
    }

    /**
     * Set default values
     *
     * @since 1.0.0
     */
    private function set_defaults() {

        $this->id = '';
        $this->ctx = new PTB_Custom_Taxonomy($this->plugin_name, $this->version);

        if (isset($_REQUEST['action'])) {

            if ('edit' === $_REQUEST['action'] && !empty($_REQUEST['slug'])) {

                $this->id = sanitize_key($_REQUEST['slug']);

                if ($this->options->has_custom_taxonomy($this->id)) {

                    $this->ctx = $this->options->get_custom_taxonomy($this->id);
                }
            }
        }

        $this->key = 'ctx';

        $this->settings_section_ctx = 'settings_section_ctx';
        $this->settings_section_cl = 'settings_section_cl';
        $this->settings_section_ad = 'settings_section_ad';
    }

    /**
     * This function adds settings sections and corresponding fields.
     * Called from PTB_Admin::display_custom_taxonomies
     *
     * @since 1.0.0
     *
     * @param string $slug_admin_ctx Main settings slug
     */
    public function add_settings_fields($slug_admin_ctx) {

        $this->slug_admin_ctx = $slug_admin_ctx;

        add_settings_section(
                $this->settings_section_ctx, '', array($this, 'cpt_section_cb'), $this->slug_admin_ctx
        );

        $this->add_fields_main();

        // Custom Labels section
        add_settings_section(
                $this->settings_section_cl, __('Custom Labels', 'ptb'), array($this, 'cl_section_cb'), $this->slug_admin_ctx
        );

        $this->add_fields_custom_labels();

        // Advanced options section
        add_settings_section(
                $this->settings_section_ad, __('Advanced Options', 'ptb'), array($this, 'ad_section_cb'), $this->slug_admin_ctx
        );

        $this->add_fields_advanced_options();
    }

    /**
     * Callback for custom taxonomy settings section
     *
     * @since 1.0.0
     */
    public function cpt_section_cb() {

        echo $this->generate_input_text(PTB_Custom_Taxonomy::ID, $this->id, true);
    }

    /**
     * Callback for custom labels settings section
     *
     * @since 1.0.0
     */
    public function cl_section_cb() {

        echo '<div class="ptb-collapse"></div>';
    }

    /**
     * Callback for advanced options settings section
     *
     * @since 1.0.0
     */
    public function ad_section_cb() {

        echo '<div class="ptb-collapse"></div>';
    }

    /**
     * Add the fields to the main section
     *
     * @since 1.0.0
     */
    private function add_fields_main() {

        add_settings_field(
                $this->get_field_id(PTB_Custom_Taxonomy::SINGULAR_LABEL), __('Singular Label', 'ptb'), array($this, 'ctx_singular_label_cb'), $this->slug_admin_ctx, $this->settings_section_ctx, array('label_for' => $this->get_field_id(PTB_Custom_Taxonomy::SINGULAR_LABEL))
        );

        add_settings_field(
                $this->get_field_id(PTB_Custom_Taxonomy::PLURAL_LABEL), __('Plural Label', 'ptb'), array($this, 'ctx_plural_label_cb'), $this->slug_admin_ctx, $this->settings_section_ctx, array('label_for' => $this->get_field_id(PTB_Custom_Taxonomy::PLURAL_LABEL))
        );

        add_settings_field(
                $this->get_field_id(PTB_Custom_Taxonomy::SLUG), __('Taxonomy Slug', 'ptb'), array($this, 'ctx_taxonomy_cb'), $this->slug_admin_ctx, $this->settings_section_ctx, array('label_for' => $this->get_field_id(PTB_Custom_Taxonomy::SLUG))
        );

        add_settings_field(
                $this->get_field_id(PTB_Custom_Taxonomy::ATTACH_TO), __('Attach to', 'ptb'), array($this, 'ctx_attach_to_cb'), $this->slug_admin_ctx, $this->settings_section_ctx, array('label_for' => $this->get_field_id(PTB_Custom_Taxonomy::ATTACH_TO))
        );
    }

    public function ctx_taxonomy_cb() {

        echo $this->generate_input_text(
                        PTB_Custom_Taxonomy::SLUG, $this->ctx->slug, false, __('(eg. producer. Should only contain lowercase english characters and the underscore or dash characters (no space or special characters)', 'ptb')
        );
    }

    public function ctx_singular_label_cb() {

        echo $this->generate_input_text(
                        PTB_Custom_Taxonomy::SINGULAR_LABEL, $this->ctx->singular_label, false, __('(eg. Producer)', 'ptb')
        );
    }

    public function ctx_plural_label_cb() {

        echo $this->generate_input_text(
                        PTB_Custom_Taxonomy::PLURAL_LABEL, $this->ctx->plural_label, false, __('(eg. Producers)', 'ptb')
        );
    }

    public function ctx_attach_to_cb() {

        $custom_post_types = PTB_Options::get_all_post_types();
        $custom_post_types_by_tax = PTB_Options::get_post_types_by_taxonomy($this->ctx->slug);

        $checkboxes = array();

        foreach ($custom_post_types as $post_type) {

            $key = $post_type->name;
            $checkbox = $this->generate_input_checkbox(
                    'post_type_' . $key, $key, $post_type->label, isset($_GET['ptype']) && $_GET['ptype'] === $key ? true : $this->ctx->is_attached_to_post_type($key) || in_array($post_type, $custom_post_types_by_tax)
            );
            $checkboxes[] = $checkbox;
        }
        $key = PTB_Custom_Taxonomy::POST_TYPE_PAGE;
        $checkbox_page = $this->generate_input_checkbox(
                'post_type_' . $key, $key, __('Page', 'ptb'), $this->ctx->is_attached_to_post_type($key)
        );

        $key = PTB_Custom_Taxonomy::POST_TYPE_POST;
        $checkbox_post = $this->generate_input_checkbox(
                'post_type_' . $key, $key, __('Post', 'ptb'), $this->ctx->is_attached_to_post_type($key)
        );
        $arrays = PTB_Utils::array_divide($checkboxes);

        empty($arrays[0]) ? $arrays[0] = array($checkbox_page) : array_unshift($arrays[0], $checkbox_page);
        empty($arrays[1]) ? $arrays[1] = array($checkbox_post) : array_unshift($arrays[1], $checkbox_post);

        printf(
                '<fieldset class="clearfix"><div class="ptb-pull-left">%1$s</div><div class="ptb-pull-left">%2$s</div></fieldset>', implode('', $arrays[0]), implode('', $arrays[1])
        );
        printf(__('Check which post type(s) to assign this taxonomy to %s.', 'ptb'), '<a href="//codex.wordpress.org/Function_Reference/register_taxonomy" target="_blank">' . __('(learn more)', 'ptb') . '</a>');
    }

    public function ctx_is_hierarchical_cb() {

        echo $this->generate_input_radio_yes_no(
                        PTB_Custom_Taxonomy::IS_HIERARCHICAL, $this->ctx->is_hierarchical, __('Whether the taxonomy is hierarchical (e.g. producer). Allows parent to be specified.', 'ptb')
        );
    }

    /**
     * Add the fields to the custom labels section
     *
     * @since 1.0.0
     */
    private function add_fields_custom_labels() {

        $fields = array(
            PTB_Custom_Taxonomy::CL_SEARCH_ITEMS => __('Search Items', 'ptb'),
            PTB_Custom_Taxonomy::CL_POPULAR_ITEMS => __('Popular Items', 'ptb'),
            PTB_Custom_Taxonomy::CL_ALL_ITEMS => __('All Items', 'ptb'),
            PTB_Custom_Taxonomy::CL_PARENT_ITEM => __('Parent Item', 'ptb'),
            PTB_Custom_Taxonomy::CL_PARENT_ITEM_COLON => __('Parent Item Colon', 'ptb'),
            PTB_Custom_Taxonomy::CL_EDIT_ITEM => __('Edit Item', 'ptb'),
            PTB_Custom_Taxonomy::CL_UPDATE_ITEM => __('Update Item', 'ptb'),
            PTB_Custom_Taxonomy::CL_ADD_NEW_ITEM => __('Add New Item', 'ptb'),
            PTB_Custom_Taxonomy::CL_NEW_ITEM_NAME => __('New Item Name', 'ptb'),
            PTB_Custom_Taxonomy::CL_SEPARATE_ITEMS_WITH_COMMAS => __('Separate Items With Commas', 'ptb'),
            PTB_Custom_Taxonomy::CL_ADD_OR_REMOVE_ITEMS => __('Add or Remove Items', 'ptb'),
            PTB_Custom_Taxonomy::CL_CHOOSE_FROM_MOST_USED => __('Choose From Most Used', 'ptb'),
            PTB_Custom_Taxonomy::CL_MENU_NAME => __('Menu Name', 'ptb'),
        );


        // Set custom labels default values
        $languages = PTB_Utils::get_all_languages();

        foreach ($languages as $code => $lng) {
            $this->ctx->cl_search_items[$code] = !empty($this->ctx->cl_search_items[$code]) ? $this->ctx->cl_search_items[$code] : __('Search %s', 'ptb');
            $this->ctx->cl_popular_items[$code] = !empty($this->ctx->cl_popular_items[$code])? $this->ctx->cl_popular_items[$code] : __('Popular %s', 'ptb');
            $this->ctx->cl_all_items[$code] = !empty($this->ctx->cl_all_items[$code])? $this->ctx->cl_all_items[$code] : __('All %s', 'ptb');
            $this->ctx->cl_parent_item[$code] = !empty($this->ctx->cl_parent_item[$code])? $this->ctx->cl_parent_item[$code] : __('Parent %s', 'ptb');
            $this->ctx->cl_parent_item_colon[$code] = !empty($this->ctx->cl_parent_item_colon[$code])? $this->ctx->cl_parent_item_colon[$code] : __('Parent %s:', 'ptb');
            $this->ctx->cl_edit_item[$code] = !empty($this->ctx->cl_edit_item[$code])? $this->ctx->cl_edit_item[$code] : __('Edit %s', 'ptb');
            $this->ctx->cl_update_item[$code] = !empty($this->ctx->cl_update_item[$code]) ? $this->ctx->cl_update_item[$code] : __('Update %s', 'ptb');
            $this->ctx->cl_add_new_item[$code] = !empty($this->ctx->cl_add_new_item[$code])? $this->ctx->cl_add_new_item[$code] : __('Add New %s', 'ptb');
            $this->ctx->cl_new_item_name[$code] = !empty($this->ctx->cl_new_item_name[$code])? $this->ctx->cl_new_item_name[$code] : __('New %s Name', 'ptb');
            $this->ctx->cl_separate_items_with_commas[$code] = !empty($this->ctx->cl_separate_items_with_commas[$code])? $this->ctx->cl_separate_items_with_commas[$code] : __('Separate %s with commas', 'ptb');
            $this->ctx->cl_add_or_remove_items[$code] = !empty($this->ctx->cl_add_or_remove_items[$code])? $this->ctx->cl_add_or_remove_items[$code] : __('Add or remove %s', 'ptb');
            $this->ctx->cl_choose_from_most_used[$code] = !empty($this->ctx->cl_choose_from_most_used[$code])? $this->ctx->cl_choose_from_most_used[$code] : __('Choose from the most used %s', 'ptb');
            $this->ctx->cl_menu_name[$code] = !empty($this->ctx->cl_menu_name[$code])? $this->ctx->cl_menu_name[$code] : __('%s', 'ptb');
        }
        foreach ($fields as $key => $label) {

            add_settings_field(
                    $this->get_field_id($key), $label, array($this, 'add_fields_custom_labels_cb'), $this->slug_admin_ctx, $this->settings_section_cl, array(
                'label_for' => $this->get_field_id($key),
                'referrer' => $key
                    )
            );
        }
    }

    /**
     * Renders the form fields of custom labels
     *
     * @since 1.0.0
     *
     * @param array $args
     */
    public function add_fields_custom_labels_cb($args) {

        $referrer = $args['referrer'];

        switch ($referrer) {

            case PTB_Custom_Taxonomy::CL_SEARCH_ITEMS :

                echo $this->generate_input_text(
                                PTB_Custom_Taxonomy::CL_SEARCH_ITEMS, $this->ctx->cl_search_items, false, __("The search items text.", 'ptb')
                );
                break;

            case PTB_Custom_Taxonomy::CL_POPULAR_ITEMS :

                echo $this->generate_input_text(
                                PTB_Custom_Taxonomy::CL_POPULAR_ITEMS, $this->ctx->cl_popular_items, false, __("The popular items text. This string is not used on hierarchical taxonomies.", 'ptb')
                );
                break;

            case PTB_Custom_Taxonomy::CL_ALL_ITEMS :

                echo $this->generate_input_text(
                                PTB_Custom_Taxonomy::CL_ALL_ITEMS, $this->ctx->cl_all_items, false, __("The all items text.", 'ptb')
                );
                break;

            case PTB_Custom_Taxonomy::CL_PARENT_ITEM :

                echo $this->generate_input_text(
                                PTB_Custom_Taxonomy::CL_PARENT_ITEM, $this->ctx->cl_parent_item, false, __("The parent item text. This string is not used on non-hierarchical taxonomies such as post tags.", 'ptb')
                );
                break;

            case PTB_Custom_Taxonomy::CL_PARENT_ITEM_COLON :

                echo $this->generate_input_text(
                                PTB_Custom_Taxonomy::CL_PARENT_ITEM_COLON, $this->ctx->cl_parent_item_colon, false, __("The same as parent_item, but with colon : in the end.", 'ptb')
                );
                break;

            case PTB_Custom_Taxonomy::CL_EDIT_ITEM :

                echo $this->generate_input_text(
                                PTB_Custom_Taxonomy::CL_EDIT_ITEM, $this->ctx->cl_edit_item, false, __("The edit item text.", 'ptb')
                );
                break;

            case PTB_Custom_Taxonomy::CL_UPDATE_ITEM :

                echo $this->generate_input_text(
                                PTB_Custom_Taxonomy::CL_UPDATE_ITEM, $this->ctx->cl_update_item, false, __("The update item text.", 'ptb')
                );
                break;

            case PTB_Custom_Taxonomy::CL_ADD_NEW_ITEM :

                echo $this->generate_input_text(
                                PTB_Custom_Taxonomy::CL_ADD_NEW_ITEM, $this->ctx->cl_add_new_item, false, __("The add new item text.", 'ptb')
                );
                break;

            case PTB_Custom_Taxonomy::CL_NEW_ITEM_NAME :

                echo $this->generate_input_text(
                                PTB_Custom_Taxonomy::CL_NEW_ITEM_NAME, $this->ctx->cl_new_item_name, false, __("The new item name text.", 'ptb')
                );
                break;

            case PTB_Custom_Taxonomy::CL_SEPARATE_ITEMS_WITH_COMMAS :

                echo $this->generate_input_text(
                                PTB_Custom_Taxonomy::CL_SEPARATE_ITEMS_WITH_COMMAS, $this->ctx->cl_separate_items_with_commas, false, __("The separate item with commas text used in the taxonomy meta box. This string is not used on hierarchical taxonomies.", 'ptb')
                );
                break;

            case PTB_Custom_Taxonomy::CL_ADD_OR_REMOVE_ITEMS :

                echo $this->generate_input_text(
                                PTB_Custom_Taxonomy::CL_ADD_OR_REMOVE_ITEMS, $this->ctx->cl_add_or_remove_items, false, __("The add or remove items text and used in the meta box when JavaScript is disabled. This string is not used on hierarchical taxonomies.", 'ptb')
                );
                break;

            case PTB_Custom_Taxonomy::CL_CHOOSE_FROM_MOST_USED :

                echo $this->generate_input_text(
                                PTB_Custom_Taxonomy::CL_CHOOSE_FROM_MOST_USED, $this->ctx->cl_choose_from_most_used, false, __("The choose from most used text used in the taxonomy meta box. This string is not used on hierarchical taxonomies.", 'ptb')
                );
                break;

            case PTB_Custom_Taxonomy::CL_MENU_NAME :

                echo $this->generate_input_text(
                                PTB_Custom_Taxonomy::CL_MENU_NAME, $this->ctx->cl_menu_name, false, __("The menu name text. This string is the name to give menu items. If not set, defaults to value of name label.", 'ptb')
                );
                break;
        }
    }

    /**
     * Add the fields to the advanced options section
     *
     * @since 1.0.0
     */
    private function add_fields_advanced_options() {

        add_settings_field(
                $this->get_field_id(PTB_Custom_Taxonomy::IS_HIERARCHICAL), __('Hierarchical', 'ptb'), array($this, 'ctx_is_hierarchical_cb'), $this->slug_admin_ctx, $this->settings_section_ad, array('label_for' => $this->get_field_id(PTB_Custom_Taxonomy::IS_HIERARCHICAL))
        );

        $fields = array(
            PTB_Custom_Taxonomy::AD_PUBLICLY_QUERYABLE => __('Publicly Queryable', 'ptb'),
            PTB_Custom_Taxonomy::AD_SHOW_UI => __('Show UI', 'ptb'),
            PTB_Custom_Taxonomy::AD_SHOW_TAG_CLOUD => __('Show Tag Cloud', 'ptb'),
            PTB_Custom_Taxonomy::AD_SHOW_ADMIN_COLUMN => __('Show Admin Column', 'ptb')
        );

        foreach ($fields as $key => $label) {

            add_settings_field(
                    $this->get_field_id($key), $label, array($this, 'add_fields_advanced_options_cb'), $this->slug_admin_ctx, $this->settings_section_ad, array(
                'label_for' => $this->get_field_id($key),
                'referrer' => $key
                    )
            );
        }
    }

    /**
     * Renders the form fields of advanced options
     *
     * @since 1.0.0
     *
     * @param array $args
     */
    public function add_fields_advanced_options_cb($args) {

        $referrer = $args['referrer'];

        switch ($referrer) {

            case PTB_Custom_Taxonomy::AD_PUBLICLY_QUERYABLE :

                echo $this->generate_input_radio_yes_no(
                                PTB_Custom_Taxonomy::AD_PUBLICLY_QUERYABLE, $this->ctx->ad_publicly_queryable, __('If the taxonomy should be publicly queryable.', 'ptb')
                );
                break;

            case PTB_Custom_Taxonomy::AD_SHOW_UI :

                echo $this->generate_input_radio_yes_no(
                                PTB_Custom_Taxonomy::AD_SHOW_UI, $this->ctx->ad_show_ui, __('Whether to generate a default UI for managing this taxonomy.', 'ptb')
                );
                break;

            case PTB_Custom_Taxonomy::AD_SHOW_TAG_CLOUD :

                echo $this->generate_input_radio_yes_no(
                                PTB_Custom_Taxonomy::AD_SHOW_TAG_CLOUD, $this->ctx->ad_show_tag_cloud, __('Whether to allow the Tag Cloud widget to use this taxonomy.', 'ptb')
                );
                break;

            case PTB_Custom_Taxonomy::AD_SHOW_ADMIN_COLUMN :

                echo $this->generate_input_radio_yes_no(
                                PTB_Custom_Taxonomy::AD_SHOW_ADMIN_COLUMN, $this->ctx->ad_show_admin_column, __('Whether to allow automatic creation of taxonomy columns on associated post-types table.', 'ptb')
                );
                break;
        }
    }

    /**
     * @param array $input The inputs array of custom taxonomy
     *
     * @since    1.0.0
     */
    public function process_options($input) {

        $this->id = '';
        $lng = PTB_Utils::get_current_language_code();
        $this->ctx = new PTB_Custom_Taxonomy($this->plugin_name, $this->version);

        $id_key = $this->get_field_id(PTB_Custom_Taxonomy::ID);

        if (array_key_exists($id_key, $input)) {

            $this->id = sanitize_text_field($input[$id_key]);

            if ($this->options->has_custom_taxonomy($this->id)) {

                $this->ctx = $this->options->get_custom_taxonomy($this->id);
            }
        }

        if (!empty($this->id)) {

            $this->extract_data($input);

            $this->options->edit_custom_taxonomy($this->id, $this->ctx);

            $message = sprintf(
                    __('Custom taxonomy "%1$s" successfully updated.', 'ptb'), $this->ctx->singular_label[$lng]
            );
        } else {

            $this->extract_data($input);

            $this->options->add_custom_taxonomy($this->ctx);

            $message = sprintf(
                    __('Custom taxonomy "%1$s" successfully added.', 'ptb'), $this->ctx->singular_label[$lng]
            );
        }

        add_settings_error($this->plugin_name . '_notices', '', $message, 'updated');
    }

    /**
     * Extract the data from $input array to custom taxonomy object
     *
     * @since 1.0.0
     *
     * @param array $input
     */
    private function extract_data($input) {
        $languages = PTB_Utils::get_all_languages();
        foreach ($languages as $code => $lng) {
            $this->ctx->singular_label[$code] = sanitize_text_field($input[$this->get_field_id(PTB_Custom_Taxonomy::SINGULAR_LABEL)][$code]);
            $this->ctx->plural_label[$code] = sanitize_text_field($input[$this->get_field_id(PTB_Custom_Taxonomy::PLURAL_LABEL)][$code]);

            // Extract custom labels

            $this->ctx->cl_search_items[$code] = sanitize_text_field($input[$this->get_field_id(PTB_Custom_Taxonomy::CL_SEARCH_ITEMS)][$code]);
            $this->ctx->cl_popular_items[$code] = sanitize_text_field($input[$this->get_field_id(PTB_Custom_Taxonomy::CL_POPULAR_ITEMS)][$code]);
            $this->ctx->cl_all_items[$code] = sanitize_text_field($input[$this->get_field_id(PTB_Custom_Taxonomy::CL_ALL_ITEMS)][$code]);
            $this->ctx->cl_parent_item[$code] = sanitize_text_field($input[$this->get_field_id(PTB_Custom_Taxonomy::CL_PARENT_ITEM)][$code]);
            $this->ctx->cl_parent_item_colon[$code] = sanitize_text_field($input[$this->get_field_id(PTB_Custom_Taxonomy::CL_PARENT_ITEM_COLON)][$code]);
            $this->ctx->cl_edit_item[$code] = sanitize_text_field($input[$this->get_field_id(PTB_Custom_Taxonomy::CL_EDIT_ITEM)][$code]);
            $this->ctx->cl_update_item[$code] = sanitize_text_field($input[$this->get_field_id(PTB_Custom_Taxonomy::CL_UPDATE_ITEM)][$code]);
            $this->ctx->cl_add_new_item[$code] = sanitize_text_field($input[$this->get_field_id(PTB_Custom_Taxonomy::CL_ADD_NEW_ITEM)][$code]);
            $this->ctx->cl_new_item_name[$code] = sanitize_text_field($input[$this->get_field_id(PTB_Custom_Taxonomy::CL_NEW_ITEM_NAME)][$code]);
            $this->ctx->cl_separate_items_with_commas[$code] = sanitize_text_field($input[$this->get_field_id(PTB_Custom_Taxonomy::CL_SEPARATE_ITEMS_WITH_COMMAS)][$code]);
            $this->ctx->cl_add_or_remove_items[$code] = sanitize_text_field($input[$this->get_field_id(PTB_Custom_Taxonomy::CL_ADD_OR_REMOVE_ITEMS)][$code]);
            $this->ctx->cl_choose_from_most_used[$code] = sanitize_text_field($input[$this->get_field_id(PTB_Custom_Taxonomy::CL_CHOOSE_FROM_MOST_USED)][$code]);
            $this->ctx->cl_menu_name[$code] = sanitize_text_field($input[$this->get_field_id(PTB_Custom_Taxonomy::CL_MENU_NAME)] [$code]);
        }
        $this->ctx->slug = sanitize_text_field($input[$this->get_field_id(PTB_Custom_Taxonomy::SLUG)]);

        $this->ctx->is_hierarchical = ( 'Yes' == sanitize_text_field($input[$this->get_field_id(PTB_Custom_Taxonomy::IS_HIERARCHICAL)]) );

        $custom_post_types = PTB_Options::get_all_post_types();

        $post_type_name = PTB_Custom_Taxonomy::POST_TYPE_PAGE;
        $this->ctx->attach_to_post_type($post_type_name, array_key_exists($this->get_field_id('post_type_' . $post_type_name), $input));

        $post_type_name = PTB_Custom_Taxonomy::POST_TYPE_POST;
        $this->ctx->attach_to_post_type($post_type_name, array_key_exists($this->get_field_id('post_type_' . $post_type_name), $input));

        foreach ($custom_post_types as $post_type) {
            $this->ctx->attach_to_post_type($post_type->name, array_key_exists($this->get_field_id('post_type_' . $post_type->name), $input));
        }


        // Extract advanced options

        $this->ctx->ad_publicly_queryable = ( 'Yes' == sanitize_text_field($input[$this->get_field_id(PTB_Custom_Taxonomy::AD_PUBLICLY_QUERYABLE)]) );
        $this->ctx->ad_show_ui = ( 'Yes' == sanitize_text_field($input[$this->get_field_id(PTB_Custom_Taxonomy::AD_SHOW_UI)]) );
        $this->ctx->ad_show_tag_cloud = ( 'Yes' == sanitize_text_field($input[$this->get_field_id(PTB_Custom_Taxonomy::AD_SHOW_TAG_CLOUD)]) );
        $this->ctx->ad_show_admin_column = ( 'Yes' == sanitize_text_field($input[$this->get_field_id(PTB_Custom_Taxonomy::AD_SHOW_ADMIN_COLUMN)]) );
    }

    /*     * *************************************************************************************************************** */
    // Helper functions (todo: move these function to interface or make static)
    /*     * *************************************************************************************************************** */

    /**
     * Helper function to generate settings field id
     *
     * @since 1.0.0
     *
     * @param string $field_key The key of settings field
     *
     * @return string The generated id of settings field
     */
    private function get_field_id($field_key, $lng = false) {

        return !$lng ?
                sprintf('%s_%s_%s', $this->plugin_name, $this->key, $field_key) : sprintf('%s_%s_%s_%s', $this->plugin_name, $this->key, $field_key, $lng);
    }

    /**
     * Helper function to generate settings field name
     *
     * @since 1.0.0
     *
     * @param string $field_key The key of settings field
     *
     * @return string The generated name of settings field
     */
    private function get_field_name($field_key, $lng = false) {

        return !$lng ?
                sprintf('ptb_plugin_options[%s]', $this->get_field_id($field_key, $lng)) : sprintf('ptb_plugin_options[%s][%s]', $this->get_field_id($field_key), $lng);
    }

    /**
     * Helper function to generate input checkbox
     *
     * @since 1.0.0
     *
     * @param string $id
     * @param string $value
     * @param string $title
     * @param bool $checked
     *
     * @return string
     */
    private function generate_input_checkbox($id, $value, $title, $checked) {

        $input = sprintf(
                '<label for="%1$s"><input type="checkbox" name="%2$s" id="%1$s" value="%3$s" %4$s> %5$s</label><br>', esc_attr($this->get_field_id($id)), esc_attr($this->get_field_name($id)), esc_attr($value), checked($checked, true, false), esc_attr($title)
        );

        return $input;
    }

    /**
     * Helper function to generate input radio with Yes/No
     *
     * @since 1.0.0
     *
     * @param string $id
     * @param bool $selected
     * @param string $description
     *
     * @return string
     */
    private function generate_input_radio_yes_no($id, $selected, $description = '') {

        $input = sprintf(
                '<fieldset>' .
                '<label for="%1$s_yes" title="Yes"><input type="radio" id="%1$s_yes" name="%2$s" value="Yes" %3$s /> <span>%5$s</span></label>&nbsp;&nbsp;' .
                '<label for="%1$s_no" title="No"><input type="radio" id="%1$s_no" name="%2$s" value="No" %4$s /> <span>%6$s</span></label><br>' .
                '</fieldset>', esc_attr($this->get_field_id($id)), esc_attr($this->get_field_name($id)), checked($selected, true, false), checked($selected, false, false), __('Yes', 'ptb'), __('No', 'ptb')
        );

        $description = sprintf('<p class="description">%s</p>', $description);

        $result = empty($description) ? $input : $input . $description;

        return $result;
    }

    /**
     * Helper function to generate input text or hidden
     *
     * @since 1.0.0
     *
     * @param string $id
     * @param string $value
     * @param bool $hidden
     * @param string $description
     *
     * @return string
     */
    private function generate_input_text($id, $value, $hidden = false, $description = '') {

        $input = '';
        if ($hidden || in_array($id, array(PTB_Custom_Taxonomy::SLUG))) {

            $input = sprintf(
                    '<input type="%1$s" %2$s id="%3$s" name="%4$s" value="%5$s" />', $hidden ? 'hidden' : 'text', $hidden ? '' : 'class="regular-text"', esc_attr($this->get_field_id($id)), esc_attr($this->get_field_name($id)), esc_attr($value)
            );
            $result = $input;
        } else {

            $languages = PTB_Utils::get_all_languages();
            if (count($languages) > 1) {
                $input = '<ul class="' . $this->plugin_name . '_language_tabs">';
                foreach ($languages as $code => $lng) {

                    $class = isset($lng['selected']) ? ' class="' . $this->plugin_name . '_active_tab_lng"' : '';
                    $input .= '<li' . $class . '>';
                    $input .= '<a class="' . $this->plugin_name . '_lng_' . $code . '" title="' . $lng['name'] . '" href="#"></a>';
                    $input .= '</li>';
                }
                $input .= '</ul>';
            }
            $input .= '<ul class="' . $this->plugin_name . '_language_fields">';
            foreach ($languages as $code => $lng) {

                $val = isset($value[$code]) ? $value[$code] : '';

                $class = isset($lng['selected']) ? ' class="' . $this->plugin_name . '_active_lng"' : '';
                $input .= '<li' . $class . '>';
                $input .= sprintf(
                        '<input type="%1$s" %2$s id="%3$s" name="%4$s" value="%5$s" />', 'text', 'class="regular-text"', esc_attr($this->get_field_id($id, $code)), esc_attr($this->get_field_name($id, $code)), esc_attr($val)
                );
                $input .= '</li>';
            }
            $input .= '</ul>';
        }
        $description = sprintf('<p class="description">%s</p>', $description);
        $result = empty($description) ? $input : $input . $description;
        return $result;
    }

    private function generate_select($id, $options_label_value, $selected = '') {

        $options = '';
        foreach ($options_label_value as $label => $value) {
            $options .= sprintf(
                    '<option value="%s" %s>%s</option>', esc_attr($value), selected($selected, $value, false), esc_attr($label)
            );
        }

        $result = sprintf(
                '<select id="%s" name="%s">%s</select>', esc_attr($this->get_field_id($id)), esc_attr($this->get_field_name($id)), $options
        );

        return $result;
    }

}

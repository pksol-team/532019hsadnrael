<?php
/**
 * The edit and add form class of custom post type
 *
 * @link       http://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/includes
 */

/**
 * The edit and add form class of custom post type
 *
 * @since      1.0.0
 * @package    PTB
 * @subpackage PTB/includes
 * @author     Themify <ptb@themify.me>
 */
class PTB_Form_CPT {

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The string used to uniquely identify this plugin.
     */
    private $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of the plugin.
     */
    private $version;

    /**
     * The id of current post type. Empty string if post type is new.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $id The id of current post type.
     */
    private $id;

    /**
     * The id of current post type object.
     *
     * @since    1.0.0
     * @access   private
     * @var      PTB_Custom_Post_Type $cpt The id of current post type.
     */
    private $cpt;
    private $key;
    private $settings_section_cpt;
    private $settings_section_mb;
    private $settings_section_cl;
    private $settings_section_ad;
    private $slug_admin_cpt;
    private $input_cmb_data;

    /**
     * The options management class of the the plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      PTB_Options $options Manipulates with plugin options
     */
    private $options;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     *
     * @param string $plugin_name
     * @param string $version
     * @param PTB_Options $options
     *
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
    public function set_defaults() {

        $this->id = '';
        $this->cpt = new PTB_Custom_Post_Type($this->plugin_name, $this->version);

        if (isset($_REQUEST['action'])) {

            if ('edit' === $_REQUEST['action'] && isset($_REQUEST['post_type'])) {

                $this->id = sanitize_key($_REQUEST['post_type']);

                if ($this->options->has_custom_post_type($this->id)) {

                    $this->cpt = $this->options->get_custom_post_type($this->id);
                }
            }
        }
        $this->key = 'cpt';

        $this->settings_section_cpt = 'settings_section_cpt';
        $this->settings_section_mb = 'settings_section_mb';
        $this->settings_section_cl = 'settings_section_cl';
        $this->settings_section_ad = 'settings_section_ad';

        $this->input_cmb_data = 'cmb_data';
    }

    /**
     * This function adds settings sections and corresponding fields.
     * Called from PTB_Admin::display_custom_post_types
     *
     * @since 1.0.0
     *
     * @param string $slug_admin_cpt Main settings slug
     */
    public function add_settings_fields($slug_admin_cpt) {

        $this->slug_admin_cpt = $slug_admin_cpt;

        add_settings_section(
                $this->settings_section_cpt, '', array($this, 'cpt_section_cb'), $this->slug_admin_cpt
        );

        $this->add_fields_main();

        add_settings_section(
                $this->settings_section_mb, __('Meta Box Builder', 'ptb'), array($this, 'cmb_section_cb'), $this->slug_admin_cpt
        );

        // Custom Labels section
        add_settings_section(
                $this->settings_section_cl, __('Custom Labels', 'ptb'), array($this, 'cl_section_cb'), $this->slug_admin_cpt
        );

        $this->add_fields_custom_labels();

        // Advanced options section
        add_settings_section(
                $this->settings_section_ad, __('Advanced Options', 'ptb'), array($this, 'ad_section_cb'), $this->slug_admin_cpt
        );

        $this->add_fields_advanced_options();
    }

    /**
     * Callback for custom post type settings section
     *
     * @since 1.0.0
     */
    public function cpt_section_cb() {

        echo $this->generate_input_text(PTB_Custom_Post_Type::ID, $this->id, true);
    }

    /**
     * Callback for meta box settings section
     *
     * @since 1.0.0
     */
    public function cmb_section_cb() {

        printf(
                '<p>%s</p>', sprintf(__(' Meta box allows users to enter additional meta data in the post. The fields you create here will appear in admin add/edit post page %s.', 'ptb'), '<a href="//themify.me/docs/post-type-builder-plugin-documentation#meta-box-builder">' . __('(learn more)', 'ptb') . '</a>')
        );

        $cmb_types = PTB_Options::get_cmb_types();

        $html = '';

        foreach ($cmb_types as $cmb_type_id => $cmb_type_args) {

            $html .= sprintf(
                    '<a href="#" data-type="%s" id="ptb_cmb_%s" class="ptb_cmb_button">%s</a>', esc_attr($cmb_type_id), esc_attr($cmb_type_id), esc_html($cmb_type_args['name'])
            );
        }

        printf(
                '<div class="ptb_cmb_panel_wrapper"><div class="ptb_cmb_panel">%s%s</div>%s</div>', '<ul class="ptb_cmb_items_wrapper"></ul>', sprintf('<div class="ptb_cmb_add_field"><span class="ti-plus circle"></span>%s</div>', __('Add Field', 'ptb')), sprintf('<div class="ptb_cmb_buttons_wrapper_wrapper"><div class="ptb_cmb_buttons_wrapper">%s</div></div>', $html)
        );

        $meta_box_data = apply_filters('ptb_cpt_cmb_deserialize', $this->cpt->meta_boxes);

        printf(
                '<input type="hidden" id="%s" name="%s" value="%s" />', $this->get_input_id($this->input_cmb_data), $this->get_input_name($this->input_cmb_data), htmlentities(json_encode($meta_box_data))
        );

        $languages = PTB_Utils::get_all_languages();
        $lng_count = count($languages) > 1;
        foreach ($cmb_types as $type => $args) {

            $id = sprintf("%s_{{id}}", $type);
            ?>

            <li id="<?php echo $id; ?>" data-template="<?php echo $type; ?>"
                class="ptb_cmb_item_wrapper clearfix gutter-default" style="display: none;">
                <!-- Panel Header -->
                <div class="ptb_cmb_item_title_wrapper">
                    <h4 class="ptb_cmb_item_title">
                        <?php echo $args["name"]; ?>
                    </h4>
                    <span class="ti-angle-up ptb_cmb_item_collapse"></span>
                    <span class="ti-close ptb_cmb_item_remove"></span>
                </div>
                <!-- END Panel Header -->
                <!-- Panel Body -->
                <div class="ptb_cmb_item_body">
                    <div class="ptb_cmb_input_row">
                        <label for="<?php echo $id; ?>_name" class="ptb_cmb_input_label">
                            <?php _e("Name", 'ptb'); ?>
                        </label>

                        <div class="ptb_cmb_input">
                            <?php if ($lng_count): ?>
                                <ul class="ptb_language_tabs">
                                    <?php foreach ($languages as $code => $lng): ?>
                                        <li <?php if (isset($lng['selected'])): ?>class="ptb_active_tab_lng"<?php endif; ?>>
                                            <a class="ptb_lng_<?php echo $code ?>"  title="<?php echo $lng['name'] ?>" href="#"></a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            <ul class="ptb_language_fields">
                                <?php foreach ($languages as $code => $lng): ?>
                                    <li <?php if (isset($lng['selected'])): ?>class="ptb_active_lng ptb_meta_name"<?php endif; ?>>
                                        <input type="text" id="<?php echo $id; ?>_name_<?php echo $code ?>"/>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <div class="ptb_cmb_input_row">
                        <label for="<?php echo $id; ?>_description" class="ptb_cmb_input_label">
                            <?php _e("Description", 'ptb'); ?>
                        </label>

                        <div class="ptb_cmb_input">
                            <?php if ($lng_count): ?>
                                <ul class="ptb_language_tabs">
                                    <?php foreach ($languages as $code => $lng): ?>
                                        <li <?php if (isset($lng['selected'])): ?>class="ptb_active_tab_lng"<?php endif; ?>>
                                            <a class="ptb_lng_<?php echo $code ?>" title="<?php echo $lng['name'] ?>" href="#"></a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            <ul class="ptb_language_fields">
                                <?php foreach ($languages as $code => $lng): ?>
                                    <li <?php if (isset($lng['selected'])): ?>class="ptb_active_lng"<?php endif; ?>>
                                        <input type="text" id="<?php echo $id; ?>_description_<?php echo $code ?>"/>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                     <div class="ptb_cmb_input_row">
                        <label for="<?php echo $id; ?>_slug" class="ptb_cmb_input_label">
                            <?php _e("Meta Key", 'ptb'); ?>
                        </label>
                        <div class="ptb_cmb_input">
                            <input type="text" id="<?php echo $id; ?>_slug" class="ptb_cmb_slug"/>
                            <?php _e('Should only contain lowercase English characters, no space or special characters and unique. To avoid any conflicts, this input is not editable after the post type is created','ptb')?>
                        </div>
                    </div>
                    <?php
                    // Action for specific meta box type
                    do_action('ptb_cmb_template_' . $type, $id, $languages);
                    ?>
                </div>
                <!-- END Panel Body -->
            </li>

            <?php
        }


        echo '<div class="ptb-collapse-separator"></div>';
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
                $this->get_field_id(PTB_Custom_Post_Type::SINGULAR_LABEL), __('Singular Label', 'ptb'), array($this, 'post_type_singular_label_cb'), $this->slug_admin_cpt, $this->settings_section_cpt, array('label_for' => $this->get_field_id(PTB_Custom_Post_Type::SINGULAR_LABEL))
        );

        add_settings_field(
                $this->get_field_id(PTB_Custom_Post_Type::PLURAL_LABEL), __('Plural Label', 'ptb'), array($this, 'post_type_plural_label_cb'), $this->slug_admin_cpt, $this->settings_section_cpt, array('label_for' => $this->get_field_id(PTB_Custom_Post_Type::PLURAL_LABEL))
        );

        add_settings_field(
                $this->get_field_id(PTB_Custom_Post_Type::SLUG), __('Post Type Slug', 'ptb'), array($this, 'post_type_slug_cb'), $this->slug_admin_cpt, $this->settings_section_cpt, array('label_for' => $this->get_field_id(PTB_Custom_Post_Type::SLUG))
        );
        
        add_settings_field(
                $this->get_field_id(PTB_Custom_Post_Type::DESCRIPTION), __('Description', 'ptb'), array($this, 'post_type_description_cb'), $this->slug_admin_cpt, $this->settings_section_cpt, array('label_for' => $this->get_field_id(PTB_Custom_Post_Type::DESCRIPTION))
        );

        add_settings_field(
                $this->get_field_id(PTB_Custom_Post_Type::METABOX_SECTION_NAME), __('Metabox Section Name', 'ptb'), array($this, 'post_type_metabox_section_cb'), $this->slug_admin_cpt, $this->settings_section_cpt, array('label_for' => $this->get_field_id(PTB_Custom_Post_Type::METABOX_SECTION_NAME))
        );

        add_settings_field(
                $this->get_field_id(PTB_Custom_Post_Type::SUPPORTS), __('Supports', 'ptb'), array($this, 'post_type_supports_cb'), $this->slug_admin_cpt, $this->settings_section_cpt, array('label_for' => $this->get_field_id(PTB_Custom_Post_Type::SUPPORTS))
        );

        add_settings_field(
                $this->get_field_id(PTB_Custom_Post_Type::TAXONOMIES), __('Taxonomies', 'ptb'), array($this, 'post_type_tax_cb'), $this->slug_admin_cpt, $this->settings_section_cpt, array('label_for' => $this->get_field_id(PTB_Custom_Post_Type::TAXONOMIES))
        );
    }

    /**
     * Add the fields to the custom labels section
     *
     * @since 1.0.0
     */
    private function add_fields_custom_labels() {

        $fields = array(
            PTB_Custom_Post_Type::CL_ADD_NEW => __('Add New', 'ptb'),
            PTB_Custom_Post_Type::CL_ADD_NEW_ITEM => __('Add New Item', 'ptb'),
            PTB_Custom_Post_Type::CL_EDIT_ITEM => __('Edit Item', 'ptb'),
            PTB_Custom_Post_Type::CL_NEW_ITEM => __('New Item', 'ptb'),
            PTB_Custom_Post_Type::CL_ALL_ITEMS => __('All Items', 'ptb'),
            PTB_Custom_Post_Type::CL_VIEW_ITEM => __('View Item', 'ptb'),
            PTB_Custom_Post_Type::CL_SEARCH_ITEMS => __('Search Items', 'ptb'),
            PTB_Custom_Post_Type::CL_NOT_FOUND => __('Not Found', 'ptb'),
            PTB_Custom_Post_Type::CL_NOT_FOUND_IN_TRASH => __('Not Found In Trash', 'ptb'),
            PTB_Custom_Post_Type::CL_PARENT_ITEM_COLON => __('Parent Item Colon', 'ptb'),
            PTB_Custom_Post_Type::CL_MENU_NAME => __('Menu Name', 'ptb'),
        );

        $languages = PTB_Utils::get_all_languages();
        // Set custom labels default values
        foreach ($languages as $code => $lng) {

            $this->cpt->cl_add_new[$code] = !empty($this->cpt->cl_add_new[$code]) ? $this->cpt->cl_add_new[$code] : __('Add New', 'ptb');
            $this->cpt->cl_add_new_item[$code] = !empty($this->cpt->cl_add_new_item[$code])? $this->cpt->cl_add_new_item[$code] : __('Add New %s', 'ptb');
            $this->cpt->cl_edit_item[$code] = !empty($this->cpt->cl_edit_item[$code])? $this->cpt->cl_edit_item[$code] : __('Edit %s', 'ptb');
            $this->cpt->cl_new_item[$code] = !empty($this->cpt->cl_new_item[$code])? $this->cpt->cl_new_item[$code] : __('New %s', 'ptb');
            $this->cpt->cl_all_items[$code] = !empty($this->cpt->cl_all_items[$code])? $this->cpt->cl_all_items[$code] : __('All %s', 'ptb');
            $this->cpt->cl_view_item[$code] = !empty($this->cpt->cl_view_item[$code])? $this->cpt->cl_view_item[$code] : __('View %s', 'ptb');
            $this->cpt->cl_search_items[$code] = !empty($this->cpt->cl_search_items[$code])? $this->cpt->cl_search_items[$code] : __('Search %s', 'ptb');
            $this->cpt->cl_not_found[$code] = !empty($this->cpt->cpt->cl_not_found[$code])? $this->cpt->cl_not_found[$code] : __('Not found.', 'ptb');
            $this->cpt->cl_not_found_in_trash[$code] = !empty($this->cpt->cl_not_found_in_trash[$code])? $this->cpt->cl_not_found_in_trash[$code] : __('Not found in Trash.', 'ptb');
            $this->cpt->cl_parent_item_colon[$code] = !empty($this->cpt->cl_parent_item_colon[$code])? $this->cpt->cl_parent_item_colon[$code] : __('Parent %s:', 'ptb');
            $this->cpt->cl_menu_name[$code] = !empty($this->cpt->cl_menu_name[$code])? $this->cpt->cl_menu_name[$code] : __('%s', 'ptb');
        }

        add_settings_field(
                '', sprintf(__('These labels are used in WordPress admin interface. Leave everything as default or change it to your custom labels %s.', 'ptb'), '<a href="https://codex.wordpress.org/Function_Reference/register_post_type" target="_blank">' . __('(learn more)', 'ptb') . '</a>'), array($this, 'add_description_fields'), $this->slug_admin_cpt, $this->settings_section_cl
        );
        foreach ($fields as $key => $label) {

            add_settings_field(
                    $this->get_field_id($key), $label, array($this, 'add_fields_custom_labels_cb'), $this->slug_admin_cpt, $this->settings_section_cl, array(
                'label_for' => $this->get_field_id($key),
                'referrer' => $key
                    )
            );
        }
    }

    /**
     * Add the fields to the advanced options section
     *
     * @since 1.0.0
     */
    private function add_fields_advanced_options() {

        add_settings_field(
                '', sprintf(__('These are advanced post type options. Leave everything as default if you are not sure what they are %s.', 'ptb'), '<a href="https://codex.wordpress.org/Function_Reference/register_post_type#Arguments" target="_blank">' . __('(learn more)', 'ptb') . '</a>'), array($this, 'add_description_fields'), $this->slug_admin_cpt, $this->settings_section_ad
        );
        add_settings_field(
                $this->get_field_id(PTB_Custom_Post_Type::IS_HIERARCHICAL), __('Hierarchical', 'ptb'), array($this, 'post_type_is_hierarchical_cb'), $this->slug_admin_cpt, $this->settings_section_ad, array('label_for' => $this->get_field_id(PTB_Custom_Post_Type::IS_HIERARCHICAL))
        );

        add_settings_field(
                $this->get_field_id(PTB_Custom_Post_Type::HAS_ARCHIVE), __('Has Archive', 'ptb'), array($this, 'post_type_has_archive_cb'), $this->slug_admin_cpt, $this->settings_section_ad, array('label_for' => $this->get_field_id(PTB_Custom_Post_Type::HAS_ARCHIVE))
        );



        $fields = array(
            PTB_Custom_Post_Type::AD_PUBLICLY_QUERYABLE => __('Publicly Queryable', 'ptb'),
            PTB_Custom_Post_Type::AD_EXCLUDE_FROM_SEARCH => __('Exclude from Search', 'ptb'),
            PTB_Custom_Post_Type::AD_CAN_EXPORT => __('Can Export', 'ptb'),
            PTB_Custom_Post_Type::AD_SHOW_UI => __('Show UI', 'ptb'),
            PTB_Custom_Post_Type::AD_SHOW_IN_NAV_MENUS => __('Show in WordPress Menus', 'ptb'),
            PTB_Custom_Post_Type::AD_SHOW_IN_MENU => __('Show in Admin Menu', 'ptb'),
            PTB_Custom_Post_Type::AD_MENU_POSITION => __('Menu Position', 'ptb'),
            PTB_Custom_Post_Type::AD_MENU_ICON => __('Menu Icon', 'ptb'),
            PTB_Custom_Post_Type::AD_CAPABILITY_TYPE => __('Capability Type', 'ptb'),
                //PTB_Custom_Post_Type::AD_REWRITE_SLUG        => __( 'Rewrite Slug', 'ptb' ),
        );


        foreach ($fields as $key => $label) {

            add_settings_field(
                    $this->get_field_id($key), $label, array($this, 'add_fields_advanced_options_cb'), $this->slug_admin_cpt, $this->settings_section_ad, array(
                'label_for' => $this->get_field_id($key),
                'referrer' => $key
                    )
            );
        }
    }

    /*
     * Main Fields Callbacks
     *
     * */

    public function post_type_singular_label_cb() {

        echo $this->generate_input_text(
                        PTB_Custom_Post_Type::SINGULAR_LABEL, $this->cpt->singular_label, false, __('(eg. Movie)', 'ptb')
        );
    }

    public function post_type_plural_label_cb() {

        echo $this->generate_input_text(
                        PTB_Custom_Post_Type::PLURAL_LABEL, $this->cpt->plural_label, false, __('(eg. Movies)', 'ptb')
        );
    }

    public function post_type_slug_cb() {
        $description = __("Slug name should only contain lowercase English characters, no space or special characters. Restricted names: post, page, menu as they are reserved for WP.", 'ptb');
        if (function_exists('icl_object_id')) {//wpml activated
            $description.='<br/>';
            $description.=__('WARNING: if you rename the post type slug after entering multilingual posts, the multilingual posts will be merged with English.', 'ptb');
        }
        echo $this->generate_input_text(
                        PTB_Custom_Post_Type::SLUG, $this->cpt->slug, false, $description
        );
    }

    public function post_type_description_cb() {

        echo$this->generate_input_text(
                        PTB_Custom_Post_Type::DESCRIPTION, $this->cpt->description, false, __("A short description of the post type", 'ptb')
        );
    }
    
    public function post_type_metabox_section_cb(){
        echo $this->generate_input_text(
                        PTB_Custom_Post_Type::METABOX_SECTION_NAME, $this->cpt->metabox_section_name, false, __("PTB Metabox Section Name", 'ptb')
        );
    }

    public function post_type_is_hierarchical_cb() {

        echo $this->generate_input_radio_yes_no(
                        PTB_Custom_Post_Type::IS_HIERARCHICAL, $this->cpt->is_hierarchical, __('Whether the post type is hierarchical (e.g. page). Allows parent to be specified.', 'ptb')
        );
    }

    public function post_type_has_archive_cb() {

        echo $this->generate_input_radio_yes_no(
                        PTB_Custom_Post_Type::HAS_ARCHIVE, $this->cpt->has_archive, __('Enables post type archive.', 'ptb')
        );
    }

    public function post_type_supports_cb() {

        $key = PTB_Custom_Post_Type::SUPPORT_TITLE;
        $input_title = $this->generate_input_checkbox(
                'support_' . $key, $key, __('Title', 'ptb'), $this->cpt->has_support_for($key)
        );

        $key = PTB_Custom_Post_Type::SUPPORT_EDITOR;
        $input_editor = $this->generate_input_checkbox(
                'support_' . $key, $key, __('Content / Editor', 'ptb'), $this->cpt->has_support_for($key)
        );

        $key = PTB_Custom_Post_Type::SUPPORT_THUMBNAIL;
        $input_thumbnail = $this->generate_input_checkbox(
                'support_' . $key, $key, __('Featured Image', 'ptb'), $this->cpt->has_support_for($key)
        );

        $key = PTB_Custom_Post_Type::SUPPORT_EXCERPT;
        $input_excerpt = $this->generate_input_checkbox(
                'support_' . $key, $key, __('Excerpt', 'ptb'), $this->cpt->has_support_for($key)
        );

        $key = PTB_Custom_Post_Type::SUPPORT_COMMENTS;
        $input_comments = $this->generate_input_checkbox(
                'support_' . $key, $key, __('Comments', 'ptb'), $this->cpt->has_support_for($key)
        );
        
        $key = PTB_Custom_Post_Type::SUPPORT_REVISIONS;
        $input_revision = $this->generate_input_checkbox(
                'support_' . $key, $key, __('Revisions', 'ptb'), $this->cpt->has_support_for($key)
        );
        
        $key = PTB_Custom_Post_Type::SUPPORT_CUSTOM_FIELDS;
        $input_custom_fields = $this->generate_input_checkbox(
                'support_' . $key, $key, __('Custom Field', 'ptb'), $this->cpt->has_support_for($key)
        );
        
        $key = PTB_Custom_Post_Type::SUPPORT_AUTHOR;
        $input_author = $this->generate_input_checkbox(
                'support_' . $key, $key, __('Author', 'ptb'), $this->cpt->has_support_for($key)
        );

        $key = PTB_Custom_Post_Type::TAXONOMY_CATEGORY;
        $input_categories = $this->generate_input_checkbox(
                'taxonomy_' . $key, $key, __('Categories', 'ptb'), $this->cpt->has_taxonomy($key)
        );

        $key = PTB_Custom_Post_Type::TAXONOMY_TAGS;
        $input_tags = $this->generate_input_checkbox(
                'taxonomy_' . $key, $key, __('Tags', 'ptb'), $this->cpt->has_taxonomy($key)
        );
        $arrays[0] = array(
            $input_title,
            $input_editor,
            $input_thumbnail,
            $input_categories,
            $input_revision
        );

        $arrays[1] = array(
            $input_excerpt,
            $input_comments,
            $input_author,
            $input_tags,
            $input_custom_fields
        );

        printf(
                '<fieldset class="clearfix"><div class="ptb-pull-left">%1$s</div><div class="ptb-pull-left">%2$s</div></fieldset>', implode('', $arrays[0]), implode('', $arrays[1])
        );
        printf(__('These are the standard features from WordPress %s.', 'ptb'), '<a href="https://codex.wordpress.org/Post_Types#Custom_Post_Types" target="_blank">' . __('(learn more)', 'ptb') . '</a>');
    }

    public function post_type_tax_cb() {

        $taxonomies = PTB_Options::get_all_custom_taxonomies();

        $checkboxes = array();

        foreach ($taxonomies as $taxonomy) {

            $key = $taxonomy->name;
            $checkbox = $this->generate_input_checkbox(
                    'taxonomy_' . $key, $key, $taxonomy->label, $this->cpt->has_taxonomy($key)
            );
            $checkboxes[] = $checkbox;
        }


        $arrays = PTB_Utils::array_divide($checkboxes);
        if (!empty($arrays)) {

            printf(
                    '<fieldset class="clearfix"><div class="ptb-pull-left">%1$s</div><div class="ptb-pull-left">%2$s</div></fieldset>', implode('', $arrays[0]), isset($arrays[1]) && !empty($arrays[1]) ? implode('', $arrays[1]) : ''
            );
        }
        printf(
                '<h2 style="margin-left: -4px;"><a target="_blank" href="?page=%s&action=add&ptype=%s" class="add-new-h2">%s</a></h2>', 'ptb-ctx', $this->id, __('Add Taxonomies', 'ptb')
        );
        printf(__('Taxonomies are like sub categories and tags for custom post types %s.', 'ptb'), '<a href="https://codex.wordpress.org/Taxonomies" target="_blank">' . __('(learn more)', 'ptb') . '</a>');
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

            case PTB_Custom_Post_Type::CL_ADD_NEW :

                echo $this->generate_input_text(
                                PTB_Custom_Post_Type::CL_ADD_NEW, $this->cpt->cl_add_new, false, __("The add new text. The default is Add New for both hierarchical and non-hierarchical types.", 'ptb')
                );
                break;

            case PTB_Custom_Post_Type::CL_ADD_NEW_ITEM :

                echo $this->generate_input_text(
                                PTB_Custom_Post_Type::CL_ADD_NEW_ITEM, $this->cpt->cl_add_new_item, false, __("The add new item text. Default is Add New Post/Add New Page.", 'ptb')
                );
                break;

            case PTB_Custom_Post_Type::CL_EDIT_ITEM:

                echo $this->generate_input_text(
                                PTB_Custom_Post_Type::CL_EDIT_ITEM, $this->cpt->cl_edit_item, false, __("The edit item text. Default is Edit Post/Edit Page.", 'ptb')
                );
                break;

            case PTB_Custom_Post_Type::CL_NEW_ITEM:

                echo $this->generate_input_text(
                                PTB_Custom_Post_Type::CL_NEW_ITEM, $this->cpt->cl_new_item, false, __("The view item text. Default is View Post/View Page.", 'ptb')
                );
                break;

            case PTB_Custom_Post_Type::CL_ALL_ITEMS:

                echo $this->generate_input_text(
                                PTB_Custom_Post_Type::CL_ALL_ITEMS, $this->cpt->cl_all_items, false, __("The all items text used in the menu. Default is the Name label.", 'ptb')
                );
                break;

            case PTB_Custom_Post_Type::CL_VIEW_ITEM:

                echo $this->generate_input_text(
                                PTB_Custom_Post_Type::CL_VIEW_ITEM, $this->cpt->cl_view_item, false, __("The view item text. Default is View Post/View Page.", 'ptb')
                );
                break;

            case PTB_Custom_Post_Type::CL_SEARCH_ITEMS:

                echo $this->generate_input_text(
                                PTB_Custom_Post_Type::CL_SEARCH_ITEMS, $this->cpt->cl_search_items, false, __("The search items text. Default is Search Posts/Search Pages.", 'ptb')
                );
                break;

            case PTB_Custom_Post_Type::CL_NOT_FOUND:

                echo $this->generate_input_text(
                                PTB_Custom_Post_Type::CL_NOT_FOUND, $this->cpt->cl_not_found, false, __("The not found text. Default is No posts found/No pages found.", 'ptb')
                );
                break;

            case PTB_Custom_Post_Type::CL_NOT_FOUND_IN_TRASH:

                echo $this->generate_input_text(
                                PTB_Custom_Post_Type::CL_NOT_FOUND_IN_TRASH, $this->cpt->cl_not_found_in_trash, false, __("The not found in trash text. Default is No posts found in Trash/No pages found in Trash.", 'ptb')
                );
                break;

            case PTB_Custom_Post_Type::CL_PARENT_ITEM_COLON:

                echo $this->generate_input_text(
                                PTB_Custom_Post_Type::CL_PARENT_ITEM_COLON, $this->cpt->cl_parent_item_colon, false, __("The parent text. This string isn't used on non-hierarchical types. In hierarchical ones the default is Parent Page.", 'ptb')
                );
                break;

            case PTB_Custom_Post_Type::CL_MENU_NAME:

                echo $this->generate_input_text(
                                PTB_Custom_Post_Type::CL_MENU_NAME, $this->cpt->cl_menu_name, false, __("This string is the name to give menu items.", 'ptb')
                );
                break;
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

            case PTB_Custom_Post_Type::AD_PUBLICLY_QUERYABLE :

                echo $this->generate_input_radio_yes_no(
                                PTB_Custom_Post_Type::AD_PUBLICLY_QUERYABLE, $this->cpt->ad_publicly_queryable, __('Whether post_type queries can be performed from the front end.', 'ptb')
                );
                break;

            case PTB_Custom_Post_Type::AD_EXCLUDE_FROM_SEARCH :

                echo $this->generate_input_radio_yes_no(
                                PTB_Custom_Post_Type::AD_EXCLUDE_FROM_SEARCH, $this->cpt->ad_exclude_from_search, __('Whether to exclude posts with this post type from search results.', 'ptb')
                );
                break;

            case PTB_Custom_Post_Type::AD_CAN_EXPORT :

                echo $this->generate_input_radio_yes_no(
                                PTB_Custom_Post_Type::AD_CAN_EXPORT, $this->cpt->ad_can_export, __('Can this post_type be exported.', 'ptb')
                );
                break;

            case PTB_Custom_Post_Type::AD_SHOW_UI :

                echo $this->generate_input_radio_yes_no(
                                PTB_Custom_Post_Type::AD_SHOW_UI, $this->cpt->ad_show_ui, __('Whether to generate a default UI for managing this post type.', 'ptb')
                );
                break;

            case PTB_Custom_Post_Type::AD_SHOW_IN_NAV_MENUS :

                echo $this->generate_input_radio_yes_no(
                                PTB_Custom_Post_Type::AD_SHOW_IN_NAV_MENUS, $this->cpt->ad_show_in_nav_menus, __('Include this post type in Appearance > Menus for selection.', 'ptb')
                );
                break;

            case PTB_Custom_Post_Type::AD_SHOW_IN_MENU :

                echo $this->generate_input_radio_yes_no(
                                PTB_Custom_Post_Type::AD_SHOW_IN_MENU, $this->cpt->ad_show_in_menu, __('Show this post type in the WordPress admin menu.', 'ptb')
                );
                break;

            case PTB_Custom_Post_Type::AD_MENU_POSITION :

                echo $this->generate_select(
                                PTB_Custom_Post_Type::AD_MENU_POSITION, $this->get_menu_positions(), $this->cpt->ad_menu_position, __('The position to place this post type menu on admin sidebar.', 'ptb')
                );
                break;

            case PTB_Custom_Post_Type::AD_MENU_ICON :

                echo $this->generate_select(
                                PTB_Custom_Post_Type::AD_MENU_ICON, $this->get_dashicons(), $this->cpt->ad_menu_icon, sprintf(__('View %s', 'ptb'), '<a href="//developer.wordpress.org/resource/dashicons/#search" target="_blank">' . __('all icons', 'ptb') . '</a>')
                );
                break;

            case PTB_Custom_Post_Type::AD_CAPABILITY_TYPE :

                $capability_types = array(
                    'Post' => 'post',
                    'Page' => 'page'
                );

                echo $this->generate_select(
                                PTB_Custom_Post_Type::AD_CAPABILITY_TYPE, $capability_types, $this->cpt->ad_capability_type, __('The string to use to build the read, edit, and delete capabilities.', 'ptb')
                );
                break;

            /* case PTB_Custom_Post_Type::AD_REWRITE_SLUG :

              print( $this->generate_input_text(
              PTB_Custom_Post_Type::AD_REWRITE_SLUG,
              $this->cpt->ad_rewrite_slug,
              false,
              __( 'Default will use post type name.', 'ptb' )
              ) );

              break; */
        }
    }

    /**
     * Process the $input array
     *
     * @since    1.0.0
     *
     * @param array $input The inputs array of custom post type
     */
    public function process_options($input) {

        $this->id = '';
        $this->cpt = new PTB_Custom_Post_Type($this->plugin_name, $this->version);
        $lang = PTB_Utils::get_current_language_code();
        $id_key = $this->get_field_id(PTB_Custom_Post_Type::ID);
        
        if (array_key_exists($id_key, $input)) {

            $this->id = sanitize_text_field($input[$id_key]);

            if ($this->options->has_custom_post_type($this->id)) {

                $this->cpt = $this->options->get_custom_post_type($this->id);
            }
        }

        $this->extract_data($this->cpt, $input);

        if (!empty($this->id)) {



            $this->options->edit_custom_post_type($this->id, $this->cpt);

            $message = sprintf(
                    __('Custom post type "%1$s" successfully updated.', 'ptb'), $this->cpt->singular_label[$lang]
            );
        } else {

            $this->options->add_custom_post_type($this->cpt);

            $message = sprintf(
                    __('Custom post type "%1$s" successfully added.', 'ptb'), $this->cpt->singular_label[$lang]
            );
        }

        add_settings_error($this->plugin_name . '_notices', '', $message, 'updated');
    }

    /**
     * Extract the data from $input array to custom post type object
     *
     * @since 1.0.0
     *
     * @param PTB_Custom_Post_Type $cpt
     * @param array $input
     */
    private function extract_data($cpt, $input) {

        $languages = PTB_Utils::get_all_languages();

        $cpt->slug = strtolower(sanitize_text_field($input[$this->get_field_id(PTB_Custom_Post_Type::SLUG)]));
        foreach ($languages as $code => $lng) {
            $cpt->description[$code] = sanitize_text_field($input[$this->get_field_id(PTB_Custom_Post_Type::DESCRIPTION)][$code]);
            $cpt->singular_label[$code] = sanitize_text_field($input[$this->get_field_id(PTB_Custom_Post_Type::SINGULAR_LABEL)][$code]);
            $cpt->plural_label[$code] = sanitize_text_field($input[$this->get_field_id(PTB_Custom_Post_Type::PLURAL_LABEL)][$code]);
            $cpt->metabox_section_name[$code] = sanitize_text_field($input[$this->get_field_id(PTB_Custom_Post_Type::METABOX_SECTION_NAME)][$code]);

            // Extract custom labels

            $cpt->cl_add_new[$code] = sanitize_text_field($input[$this->get_field_id(PTB_Custom_Post_Type::CL_ADD_NEW)][$code]);
            $cpt->cl_add_new_item[$code] = sanitize_text_field($input[$this->get_field_id(PTB_Custom_Post_Type::CL_ADD_NEW_ITEM)][$code]);
            $cpt->cl_edit_item[$code] = sanitize_text_field($input[$this->get_field_id(PTB_Custom_Post_Type::CL_EDIT_ITEM)][$code]);
            $cpt->cl_new_item[$code] = sanitize_text_field($input[$this->get_field_id(PTB_Custom_Post_Type::CL_NEW_ITEM)][$code]);
            $cpt->cl_all_items[$code] = sanitize_text_field($input[$this->get_field_id(PTB_Custom_Post_Type::CL_ALL_ITEMS)][$code]);
            $cpt->cl_view_item[$code] = sanitize_text_field($input[$this->get_field_id(PTB_Custom_Post_Type::CL_VIEW_ITEM)][$code]);
            $cpt->cl_search_items[$code] = sanitize_text_field($input[$this->get_field_id(PTB_Custom_Post_Type::CL_SEARCH_ITEMS)][$code]);
            $cpt->cl_not_found[$code] = sanitize_text_field($input[$this->get_field_id(PTB_Custom_Post_Type::CL_NOT_FOUND)][$code]);
            $cpt->cl_not_found_in_trash[$code] = sanitize_text_field($input[$this->get_field_id(PTB_Custom_Post_Type::CL_NOT_FOUND_IN_TRASH)][$code]);
            $cpt->cl_parent_item_colon[$code] = sanitize_text_field($input[$this->get_field_id(PTB_Custom_Post_Type::CL_PARENT_ITEM_COLON)][$code]);
            $cpt->cl_menu_name[$code] = sanitize_text_field($input[$this->get_field_id(PTB_Custom_Post_Type::CL_MENU_NAME)][$code]);

            // Extract advanced options
            /* $cpt->ad_rewrite_slug[ $code ] = isset($input[ $this->get_field_id( PTB_Custom_Post_Type::AD_REWRITE_SLUG ) ][ $code ])
              && $input[ $this->get_field_id( PTB_Custom_Post_Type::AD_REWRITE_SLUG ) ][ $code ]
              ?
              sanitize_text_field( $input[ $this->get_field_id( PTB_Custom_Post_Type::AD_REWRITE_SLUG ) ][ $code ] )
              : $cpt->slug; */
        }


        $cpt->is_hierarchical = ( 'Yes' == sanitize_text_field($input[$this->get_field_id(PTB_Custom_Post_Type::IS_HIERARCHICAL)]) );
        $cpt->has_archive = ( 'Yes' == sanitize_text_field($input[$this->get_field_id(PTB_Custom_Post_Type::HAS_ARCHIVE)]) );

        $feature = PTB_Custom_Post_Type::SUPPORT_TITLE;
        $cpt->set_support_for($feature, array_key_exists($this->get_field_id('support_' . $feature), $input));

        $feature = PTB_Custom_Post_Type::SUPPORT_EDITOR;
        $cpt->set_support_for($feature, array_key_exists($this->get_field_id('support_' . $feature), $input));

        $feature = PTB_Custom_Post_Type::SUPPORT_THUMBNAIL;
        $cpt->set_support_for($feature, array_key_exists($this->get_field_id('support_' . $feature), $input));

        $feature = PTB_Custom_Post_Type::SUPPORT_EXCERPT;
        $cpt->set_support_for($feature, array_key_exists($this->get_field_id('support_' . $feature), $input));

        $feature = PTB_Custom_Post_Type::SUPPORT_COMMENTS;
        $cpt->set_support_for($feature, array_key_exists($this->get_field_id('support_' . $feature), $input));
        
        $feature = PTB_Custom_Post_Type::SUPPORT_REVISIONS;
        $cpt->set_support_for($feature, array_key_exists($this->get_field_id('support_' . $feature), $input));
        
        $feature = PTB_Custom_Post_Type::SUPPORT_CUSTOM_FIELDS;
        $cpt->set_support_for($feature, array_key_exists($this->get_field_id('support_' . $feature), $input));

        $feature = PTB_Custom_Post_Type::SUPPORT_AUTHOR;
        $cpt->set_support_for($feature, array_key_exists($this->get_field_id('support_' . $feature), $input));

        $feature = PTB_Custom_Post_Type::TAXONOMY_CATEGORY;
        $cpt->set_taxonomy($feature, array_key_exists($this->get_field_id('taxonomy_' . $feature), $input));

        $feature = PTB_Custom_Post_Type::TAXONOMY_TAGS;
        $cpt->set_taxonomy($feature, array_key_exists($this->get_field_id('taxonomy_' . $feature), $input));

        $taxonomies = PTB_Options::get_all_custom_taxonomies();

        foreach ($taxonomies as $taxonomy) {
            $feature = $taxonomy->name;
            $cpt->set_taxonomy($feature, array_key_exists($this->get_field_id('taxonomy_' . $feature), $input));
        }

        if (isset($input[$this->get_input_id($this->input_cmb_data)])) {
            $cpt->meta_boxes = json_decode($input[$this->get_input_id($this->input_cmb_data)], true);
        }


        // Extract advanced options

        $cpt->ad_publicly_queryable = ( 'Yes' == sanitize_text_field($input[$this->get_field_id(PTB_Custom_Post_Type::AD_PUBLICLY_QUERYABLE)]) );
        $cpt->ad_exclude_from_search = ( 'Yes' == sanitize_text_field($input[$this->get_field_id(PTB_Custom_Post_Type::AD_EXCLUDE_FROM_SEARCH)]) );
        $cpt->ad_can_export = ( 'Yes' == sanitize_text_field($input[$this->get_field_id(PTB_Custom_Post_Type::AD_CAN_EXPORT)]) );
        $cpt->ad_show_ui = ( 'Yes' == sanitize_text_field($input[$this->get_field_id(PTB_Custom_Post_Type::AD_SHOW_UI)]) );
        $cpt->ad_show_in_nav_menus = ( 'Yes' == sanitize_text_field($input[$this->get_field_id(PTB_Custom_Post_Type::AD_SHOW_IN_NAV_MENUS)]) );
        $cpt->ad_show_in_menu = ( 'Yes' == sanitize_text_field($input[$this->get_field_id(PTB_Custom_Post_Type::AD_SHOW_IN_MENU)]) );
        $cpt->ad_menu_position = sanitize_text_field($input[$this->get_field_id(PTB_Custom_Post_Type::AD_MENU_POSITION)]);
        $cpt->ad_menu_icon = sanitize_text_field($input[$this->get_field_id(PTB_Custom_Post_Type::AD_MENU_ICON)]);
        $cpt->ad_capability_type = sanitize_text_field($input[$this->get_field_id(PTB_Custom_Post_Type::AD_CAPABILITY_TYPE)]);
    }

    /*     * *************************************************************************************************************** */
    // Helper functions (todo: move these function to interface or make static)
    /*     * *************************************************************************************************************** */

    /**
     * Return the generated input name by key
     * todo: replace with get_field_name()
     *
     * @since 1.0.0
     *
     * @param string $key The key of input
     *
     * @return string
     */
    private function get_input_name($key) {

        $name = sprintf(
                'ptb_plugin_options[%s_post_type_%s]', $this->plugin_name, $key
        );

        return $name;
    }

    /**
     * Return the generated input id by key
     * todo: replace with get_field_id()
     *
     * @since 1.0.0
     *
     * @param string $key The key of input
     *
     * @return string
     */
    private function get_input_id($key) {

        $name = sprintf(
                '%s_post_type_%s', $this->plugin_name, $key
        );

        return $name;
    }

    /**
     * Helper function to generate settings field id
     *
     * @since 1.0.0
     *
     * @param string $field_key The key of settings field
     * @param string $lng code languages
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
     * @param string $lng code languages
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
                '<label for="%1$s"><input type="checkbox" name="%2$s" id="%1$s" value="%3$s" %4$s /> %5$s</label><br>', esc_attr($this->get_field_id($id)), esc_attr($this->get_field_name($id)), esc_attr($value), checked($checked, true, false), esc_attr($title)
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
        if($selected){
            $selected = true;
        }
        $input = sprintf(
                '<fieldset>' .
                '<label for="%1$s_yes" title="Yes"><input type="radio" id="%1$s_yes" name="%2$s" value="Yes" %3$s /> <span>%5$s</span></label>&nbsp;&nbsp;' .
                '<label for="%1$s_no" title="No"><input type="radio" id="%1$s_no" name="%2$s" value="No" %4$s /> <span>%6$s</span></label><br/>' .
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
        if ($hidden || in_array($id, array(PTB_Custom_Post_Type::SLUG))) {

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

    private function generate_select($id, $options_label_value, $selected = '', $description = '') {

        $options = '';
        foreach ($options_label_value as $label => $value) {
            $options .= sprintf(
                    '<option value="%s" %s>%s</option>', esc_attr($value), selected($selected, $value, false), esc_attr($label)
            );
        }

        $result = sprintf(
                '<select id="%s" name="%s">%s</select>', esc_attr($this->get_field_id($id)), esc_attr($this->get_field_name($id)), $options
        );

        $description = sprintf('<p class="description">%s</p>', $description);

        $result = empty($description) ? $result : $result . $description;

        return $result;
    }

    /*     * *************************************************************************************************************** */

    private function get_dashicons() {

        $dashicons = array(
            'menu' => 'dashicons-menu',
            'admin site' => 'dashicons-admin-site',
            'dashboard' => 'dashicons-dashboard',
            'admin post' => 'dashicons-admin-post',
            'admin media' => 'dashicons-admin-media',
            'admin links' => 'dashicons-admin-links',
            'admin page' => 'dashicons-admin-page',
            'admin comments' => 'dashicons-admin-comments',
            'admin appearance' => 'dashicons-admin-appearance',
            'admin plugins' => 'dashicons-admin-plugins',
            'admin users' => 'dashicons-admin-users',
            'admin tools' => 'dashicons-admin-tools',
            'admin settings' => 'dashicons-admin-settings',
            'admin network' => 'dashicons-admin-network',
            'admin home' => 'dashicons-admin-home',
            'admin generic' => 'dashicons-admin-generic',
            'admin collapse' => 'dashicons-admin-collapse',
            'welcome write blog' => 'dashicons-welcome-write-blog',
            'welcome add page' => 'dashicons-welcome-add-page',
            'welcome view site' => 'dashicons-welcome-view-site',
            'welcome widgets menus' => 'dashicons-welcome-widgets-menus',
            'welcome comments' => 'dashicons-welcome-comments',
            'welcome learn more' => 'dashicons-welcome-learn-more',
            'format aside' => 'dashicons-format-aside',
            'format image' => 'dashicons-format-image',
            'format gallery' => 'dashicons-format-gallery',
            'format video' => 'dashicons-format-video',
            'format status' => 'dashicons-format-status',
            'format quote' => 'dashicons-format-quote',
            'format chat' => 'dashicons-format-chat',
            'format audio' => 'dashicons-format-audio',
            'camera' => 'dashicons-camera',
            'images alt' => 'dashicons-images-alt',
            'images alt2' => 'dashicons-images-alt2',
            'video alt' => 'dashicons-video-alt',
            'video alt2' => 'dashicons-video-alt2',
            'video alt3' => 'dashicons-video-alt3',
            'image crop' => 'dashicons-image-crop',
            'image rotate left' => 'dashicons-image-rotate-left',
            'image rotate right' => 'dashicons-image-rotate-right',
            'image flip vertical' => 'dashicons-image-flip-vertical',
            'image flip horizontal' => 'dashicons-image-flip-horizontal',
            'undo' => 'dashicons-undo',
            'redo' => 'dashicons-redo',
            'editor bold' => 'dashicons-editor-bold',
            'editor italic' => 'dashicons-editor-italic',
            'editor ul' => 'dashicons-editor-ul',
            'editor ol' => 'dashicons-editor-ol',
            'editor quote' => 'dashicons-editor-quote',
            'editor alignleft' => 'dashicons-editor-alignleft',
            'editor aligncenter' => 'dashicons-editor-aligncenter',
            'editor alignright' => 'dashicons-editor-alignright',
            'editor insertmore' => 'dashicons-editor-insertmore',
            'editor spellcheck' => 'dashicons-editor-spellcheck',
            'editor distractionfree' => 'dashicons-editor-distractionfree',
            'editor kitchensink' => 'dashicons-editor-kitchensink',
            'editor underline' => 'dashicons-editor-underline',
            'editor justify' => 'dashicons-editor-justify',
            'editor textcolor' => 'dashicons-editor-textcolor',
            'editor paste word' => 'dashicons-editor-paste-word',
            'editor paste text' => 'dashicons-editor-paste-text',
            'editor removeformatting' => 'dashicons-editor-removeformatting',
            'editor video' => 'dashicons-editor-video',
            'editor customchar' => 'dashicons-editor-customchar',
            'editor outdent' => 'dashicons-editor-outdent',
            'editor indent' => 'dashicons-editor-indent',
            'editor help' => 'dashicons-editor-help',
            'editor strikethrough' => 'dashicons-editor-strikethrough',
            'editor unlink' => 'dashicons-editor-unlink',
            'editor rtl' => 'dashicons-editor-rtl',
            'align left' => 'dashicons-align-left',
            'align right' => 'dashicons-align-right',
            'align center' => 'dashicons-align-center',
            'align none' => 'dashicons-align-none',
            'lock' => 'dashicons-lock',
            'calendar' => 'dashicons-calendar',
            'visibility' => 'dashicons-visibility',
            'post status' => 'dashicons-post-status',
            'edit' => 'dashicons-edit',
            'trash' => 'dashicons-trash',
            'arrow up' => 'dashicons-arrow-up',
            'arrow down' => 'dashicons-arrow-down',
            'arrow right' => 'dashicons-arrow-right',
            'arrow left' => 'dashicons-arrow-left',
            'arrow up alt' => 'dashicons-arrow-up-alt',
            'arrow down alt' => 'dashicons-arrow-down-alt',
            'arrow right alt' => 'dashicons-arrow-right-alt',
            'arrow left alt' => 'dashicons-arrow-left-alt',
            'arrow up alt2' => 'dashicons-arrow-up-alt2',
            'arrow down alt2' => 'dashicons-arrow-down-alt2',
            'arrow right alt2' => 'dashicons-arrow-right-alt2',
            'arrow left alt2' => 'dashicons-arrow-left-alt2',
            'sort' => 'dashicons-sort',
            'leftright' => 'dashicons-leftright',
            'list view' => 'dashicons-list-view',
            'exerpt view' => 'dashicons-exerpt-view',
            'share' => 'dashicons-share',
            'share alt' => 'dashicons-share-alt',
            'share alt2' => 'dashicons-share-alt2',
            'twitter' => 'dashicons-twitter',
            'rss' => 'dashicons-rss',
            'facebook' => 'dashicons-facebook',
            'facebook alt' => 'dashicons-facebook-alt',
            'googleplus' => 'dashicons-googleplus',
            'networking' => 'dashicons-networking',
            'hammer' => 'dashicons-hammer',
            'art' => 'dashicons-art',
            'migrate' => 'dashicons-migrate',
            'performance' => 'dashicons-performance',
            'wordpress' => 'dashicons-wordpress',
            'wordpress alt' => 'dashicons-wordpress-alt',
            'pressthis' => 'dashicons-pressthis',
            'update' => 'dashicons-update',
            'screenoptions' => 'dashicons-screenoptions',
            'info' => 'dashicons-info',
            'cart' => 'dashicons-cart',
            'feedback' => 'dashicons-feedback',
            'cloud' => 'dashicons-cloud',
            'translation' => 'dashicons-translation',
            'tag' => 'dashicons-tag',
            'category' => 'dashicons-category',
            'yes' => 'dashicons-yes',
            'no' => 'dashicons-no',
            'no alt' => 'dashicons-no-alt',
            'plus' => 'dashicons-plus',
            'minus' => 'dashicons-minus',
            'dismiss' => 'dashicons-dismiss',
            'marker' => 'dashicons-marker',
            'star filled' => 'dashicons-star-filled',
            'star half' => 'dashicons-star-half',
            'star empty' => 'dashicons-star-empty',
            'flag' => 'dashicons-flag',
            'location' => 'dashicons-location',
            'location alt' => 'dashicons-location-alt',
            'vault' => 'dashicons-vault',
            'shield' => 'dashicons-shield',
            'shield alt' => 'dashicons-shield-alt',
            'search' => 'dashicons-search',
            'slides' => 'dashicons-slides',
            'analytics' => 'dashicons-analytics',
            'chart pie' => 'dashicons-chart-pie',
            'chart bar' => 'dashicons-chart-bar',
            'chart line' => 'dashicons-chart-line',
            'chart area' => 'dashicons-chart-area',
            'groups' => 'dashicons-groups',
            'businessman' => 'dashicons-businessman',
            'id' => 'dashicons-id',
            'id alt' => 'dashicons-id-alt',
            'products' => 'dashicons-products',
            'awards' => 'dashicons-awards',
            'forms' => 'dashicons-forms',
            'portfolio' => 'dashicons-portfolio',
            'book' => 'dashicons-book',
            'book alt' => 'dashicons-book-alt',
            'download' => 'dashicons-download',
            'upload' => 'dashicons-upload',
            'backup' => 'dashicons-backup',
            'lightbulb' => 'dashicons-lightbulb',
            'smiley' => 'dashicons-smiley'
        );

        return $dashicons;
    }

    private function get_menu_positions() {

        $positions = array(
            'below Posts' => 5,
            'below Media' => 10,
            'below Links' => 15,
            'below Pages' => 20,
            'below comments' => 25,
            'below first separator' => 60,
            'below Plugins' => 65,
            'below Users' => 70,
            'below Tools' => 75,
            'below Settings' => 80,
            'below second separator' => 100
        );
        if (is_plugin_active('contact-form-7/wp-contact-form-7.php')) {//check if contact form 7 is active
            unset($positions['below comments']);
        }
        return $positions;
    }

    public function add_description_fields($label) {
        return $label;
    }

}

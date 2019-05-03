<?php

/**
 * Custom Taxonomy class.
 *
 * This class helps to create custom taxonomy arguments
 *
 * @link       http://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/includes
 */

/**
 * Custom Taxonomy class.
 *
 * This class helps to create custom taxonomy arguments
 *
 * @since      1.0.0
 * @package    PTB
 * @subpackage PTB/includes
 * @author     Themify <ptb@themify.me>
 */
class PTB_Custom_Taxonomy {

    // Constants
    const ID = 'id';
    const SLUG = 'slug';
    const SINGULAR_LABEL = 'singular_label';
    const PLURAL_LABEL = 'plural_label';
    const ATTACH_TO = 'attach_to';
    const IS_HIERARCHICAL = 'is_hierarchical';
    // Build in post types to attach
    const POST_TYPE_PAGE = 'page';
    const POST_TYPE_POST = 'post';
    // Custom Labels
    const CL_SEARCH_ITEMS = 'search_items';
    const CL_POPULAR_ITEMS = 'popular_items';
    const CL_ALL_ITEMS = 'all_items';
    const CL_PARENT_ITEM = 'parent_item';
    const CL_PARENT_ITEM_COLON = 'parent_item_colon';
    const CL_EDIT_ITEM = 'edit_item';
    const CL_UPDATE_ITEM = 'update_item';
    const CL_ADD_NEW_ITEM = 'add_new_item';
    const CL_NEW_ITEM_NAME = 'new_item_name';
    const CL_SEPARATE_ITEMS_WITH_COMMAS = 'separate_items_with_commas';
    const CL_ADD_OR_REMOVE_ITEMS = 'add_or_remove_items';
    const CL_CHOOSE_FROM_MOST_USED = 'choose_from_most_used';
    const CL_MENU_NAME = 'menu_name';
    // Advanced options
    const AD_PUBLICLY_QUERYABLE = 'publicly_queryable';
    const AD_SHOW_UI = 'show_ui';
    const AD_SHOW_TAG_CLOUD = 'show_tag_cloud';
    const AD_SHOW_ADMIN_COLUMN = 'show_admin_column';
    const AD_UNREGISTE = 'unregister';

    // Private properties
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;
    // Public properties
    public $id;
    public $slug;
    public $singular_label;
    public $plural_label;
    public $attach_to;
    public $is_hierarchical;
    public $cl_search_items;
    public $cl_popular_items;
    public $cl_all_items;
    public $cl_parent_item;
    public $cl_parent_item_colon;
    public $cl_edit_item;
    public $cl_update_item;
    public $cl_add_new_item;
    public $cl_new_item_name;
    public $cl_separate_items_with_commas;
    public $cl_add_or_remove_items;
    public $cl_choose_from_most_used;
    public $cl_menu_name;
    public $ad_publicly_queryable;
    public $ad_show_ui;
    public $ad_show_tag_cloud;
    public $ad_show_admin_column;
    public $unregister = false;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @var      string $plugin_name The name of the plugin.
     * @var      string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

        $this->set_defaults();
    }

    /**
     * Set default values of the properties
     *
     * @since 1.0.0
     */
    private function set_defaults() {

        $this->id = '';
        $this->slug = '';

        $this->singular_label = array();
        $this->plural_label = array();
        $this->attach_to = array();
        $this->is_hierarchical = true;

        // Set custom labels default values
        $this->cl_search_items = array();
        $this->cl_popular_items = array();
        $this->cl_all_items = array();
        $this->cl_parent_item = array();
        $this->cl_parent_item_colon = array();
        $this->cl_edit_item = array();
        $this->cl_update_item = array();
        $this->cl_add_new_item = array();
        $this->cl_new_item_name = array();
        $this->cl_separate_items_with_commas = array();
        $this->cl_add_or_remove_items = array();
        $this->cl_choose_from_most_used = array();
        $this->cl_menu_name = array();


        // Set advanced options default values;
        $this->ad_publicly_queryable = true;
        $this->ad_show_ui = true;
        $this->ad_show_tag_cloud = true;
        $this->ad_show_admin_column = true;
        $this->unregister = false;
    }

    /**
     * Returns the args array of custom taxonomy for registration
     *
     * @since   1.0.0
     *
     * @return array The array of arguments for registration
     */
    public function get_args() {

        $singular_label = PTB_Utils::get_label($this->singular_label);
        $plural_label = PTB_Utils::get_label($this->plural_label);
        $search_items = PTB_Utils::get_label($this->cl_search_items);
        $popular_items = PTB_Utils::get_label($this->cl_popular_items);
        $all_items = PTB_Utils::get_label($this->cl_all_items);
        $parent_item = PTB_Utils::get_label($this->cl_parent_item);
        $parent_item_colon = PTB_Utils::get_label($this->cl_parent_item_colon);
        $edit_item = PTB_Utils::get_label($this->cl_edit_item);
        $update_item = PTB_Utils::get_label($this->cl_update_item);
        $add_new_item = PTB_Utils::get_label($this->cl_add_new_item);
        $new_item_name = PTB_Utils::get_label($this->cl_new_item_name);
        $separate_ = PTB_Utils::get_label($this->cl_separate_items_with_commas);
        $add_or_remove_items = PTB_Utils::get_label($this->cl_add_or_remove_items);
        $most_used = PTB_Utils::get_label($this->cl_choose_from_most_used);
        $menu_name = PTB_Utils::get_label($this->cl_menu_name);
        $low_plural_label = strtolower($plural_label);

        $labels = array(
            'name' => $plural_label,
            'singular_name' => $singular_label,
            // Custom labels
            'search_items' => sprintf($search_items, $plural_label),
            'popular_items' => sprintf($popular_items, $plural_label),
            'all_items' => sprintf($all_items, $plural_label),
            'parent_item' => sprintf($parent_item, $singular_label),
            'parent_item_colon' => sprintf($parent_item_colon, $singular_label),
            'edit_item' => sprintf($edit_item, $singular_label),
            'update_item' => sprintf($update_item, $singular_label),
            'add_new_item' => sprintf($add_new_item, $singular_label),
            'new_item_name' => sprintf($new_item_name, $singular_label),
            'separate_items_with_commas' => sprintf($separate_, $low_plural_label),
            'add_or_remove_items' => sprintf($add_or_remove_items, $low_plural_label),
            'choose_from_most_used' => sprintf($most_used, $low_plural_label),
            'menu_name' => sprintf($menu_name, $singular_label),
        );

        $args = array(
            'labels' => $labels,
            'public' => $this->ad_publicly_queryable,
            'hierarchical' => $this->is_hierarchical,
            'show_ui' => $this->ad_show_ui,
            'show_tagcloud' => $this->ad_show_tag_cloud,
            'show_admin_column' => $this->ad_show_admin_column,
            'unregister'=>$this->unregister
                //'rewrite'      => array( 'slug' => preg_replace( '/\s+/', '', strtolower( $this->plural_label ) ) ),
        );

        return $args;
    }

    /**
     * Serialization of custom taxonomy class for storing in WP options.
     * This function mainly used by PTB_Options class.
     *
     * @since   1.0.0
     *
     * @return array Serialized array of custom taxonomy
     */
    public function serialize() {

        $args = array(
            self::ID => $this->id,
            self::SLUG => $this->slug,
            self::SINGULAR_LABEL => $this->singular_label,
            self::PLURAL_LABEL => $this->plural_label,
            self::ATTACH_TO => array_values($this->attach_to),
            self::IS_HIERARCHICAL => $this->is_hierarchical,
            // Custom Labels
            self::CL_SEARCH_ITEMS => $this->cl_search_items,
            self::CL_POPULAR_ITEMS => $this->cl_popular_items,
            self::CL_ALL_ITEMS => $this->cl_all_items,
            self::CL_PARENT_ITEM => $this->cl_parent_item,
            self::CL_PARENT_ITEM_COLON => $this->cl_parent_item_colon,
            self::CL_EDIT_ITEM => $this->cl_edit_item,
            self::CL_UPDATE_ITEM => $this->cl_update_item,
            self::CL_ADD_NEW_ITEM => $this->cl_add_new_item,
            self::CL_NEW_ITEM_NAME => $this->cl_new_item_name,
            self::CL_SEPARATE_ITEMS_WITH_COMMAS => $this->cl_separate_items_with_commas,
            self::CL_ADD_OR_REMOVE_ITEMS => $this->cl_add_or_remove_items,
            self::CL_CHOOSE_FROM_MOST_USED => $this->cl_choose_from_most_used,
            self::CL_MENU_NAME => $this->cl_menu_name,
            // Advanced options
            self::AD_PUBLICLY_QUERYABLE => $this->ad_publicly_queryable,
            self::AD_SHOW_UI => $this->ad_show_ui,
            self::AD_SHOW_TAG_CLOUD => $this->ad_show_tag_cloud,
            self::AD_SHOW_ADMIN_COLUMN => $this->ad_show_admin_column,
            self::AD_UNREGISTE=>$this->unregister
        );

        return $args;
    }

    /**
     * De-serialization of custom taxonomy class from options.
     * This function mainly used by PTB_Options class and
     * should be called right after constructor.
     *
     * @since   1.0.0
     *
     * @param array $source Serialized options of custom taxonomy
     *
     */
    public function deserialize($source) {

        if (isset($source[self::ID])) {
            $this->id = $source[self::ID];
        }

        if (isset($source[self::SLUG])) {
            $this->slug = $source[self::SLUG];
        }

        if (isset($source[self::SINGULAR_LABEL])) {
            $this->singular_label = $source[self::SINGULAR_LABEL];
        }

        if (isset($source[self::PLURAL_LABEL])) {
            $this->plural_label = $source[self::PLURAL_LABEL];
        }

        if (isset($source[self::ATTACH_TO])) {
            $this->attach_to = $source[self::ATTACH_TO];
        }

        if (isset($source[self::IS_HIERARCHICAL])) {
            $this->is_hierarchical = $source[self::IS_HIERARCHICAL];
        }

        // Custom Labels

        if (isset($source[self::CL_SEARCH_ITEMS])) {
            $this->cl_search_items = $source[self::CL_SEARCH_ITEMS];
        }

        if (isset($source[self::CL_POPULAR_ITEMS])) {
            $this->cl_popular_items = $source[self::CL_POPULAR_ITEMS];
        }

        if (isset($source[self::CL_ALL_ITEMS])) {
            $this->cl_all_items = $source[self::CL_ALL_ITEMS];
        }

        if (isset($source[self::CL_PARENT_ITEM])) {
            $this->cl_parent_item = $source[self::CL_PARENT_ITEM];
        }

        if (isset($source[self::CL_PARENT_ITEM_COLON])) {
            $this->cl_parent_item_colon = $source[self::CL_PARENT_ITEM_COLON];
        }

        if (isset($source[self::CL_EDIT_ITEM])) {
            $this->cl_edit_item = $source[self::CL_EDIT_ITEM];
        }

        if (isset($source[self::CL_UPDATE_ITEM])) {
            $this->cl_update_item = $source[self::CL_UPDATE_ITEM];
        }

        if (isset($source[self::CL_ADD_NEW_ITEM])) {
            $this->cl_add_new_item = $source[self::CL_ADD_NEW_ITEM];
        }

        if (isset($source[self::CL_NEW_ITEM_NAME])) {
            $this->cl_new_item_name = $source[self::CL_NEW_ITEM_NAME];
        }

        if (isset($source[self::CL_SEPARATE_ITEMS_WITH_COMMAS])) {
            $this->cl_separate_items_with_commas = $source[self::CL_SEPARATE_ITEMS_WITH_COMMAS];
        }

        if (isset($source[self::CL_ADD_OR_REMOVE_ITEMS])) {
            $this->cl_add_or_remove_items = $source[self::CL_ADD_OR_REMOVE_ITEMS];
        }

        if (isset($source[self::CL_CHOOSE_FROM_MOST_USED])) {
            $this->cl_choose_from_most_used = $source[self::CL_CHOOSE_FROM_MOST_USED];
        }

        if (isset($source[self::CL_MENU_NAME])) {
            $this->cl_menu_name = $source[self::CL_MENU_NAME];
        }

        // Advanced options

        if (isset($source[self::AD_PUBLICLY_QUERYABLE])) {
            $this->ad_publicly_queryable = $source[self::AD_PUBLICLY_QUERYABLE];
        }

        if (isset($source[self::AD_SHOW_UI])) {
            $this->ad_show_ui = $source[self::AD_SHOW_UI];
        }

        if (isset($source[self::AD_SHOW_TAG_CLOUD])) {
            $this->ad_show_tag_cloud = $source[self::AD_SHOW_TAG_CLOUD];
        }

        if (isset($source[self::AD_SHOW_ADMIN_COLUMN])) {
            $this->ad_show_admin_column = $source[self::AD_SHOW_ADMIN_COLUMN];
        }
        if (isset($source[self::AD_UNREGISTE])) {
            $this->unregister = $source[self::AD_UNREGISTE];
        }
    }

    /**
     * Check whether taxonomy attached to post type
     *
     * @since 1.0.0
     *
     * @param string $post_type
     *
     * @return bool
     */
    public function is_attached_to_post_type($post_type) {

        return in_array($post_type, $this->attach_to);
    }

    /**
     * Attach to or detach from post type based on $state
     *
     * @since 1.0.0
     *
     * @param string $post_type
     * @param bool $state
     */
    public function attach_to_post_type($post_type, $state) {

        if (true === $state) {

            PTB_Utils::add_to_array($post_type, $this->attach_to);
        } else {

            PTB_Utils::remove_from_array($post_type, $this->attach_to);
        }
    }

}

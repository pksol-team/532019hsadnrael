<?php
/**
 * The plugin options management class
 *
 * @link       http://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/includes
 */

/**
 * The plugin options helper class
 *
 *
 * @package    PTB
 * @subpackage PTB/includes
 * @author     Themify <ptb@themify.me>
 */
class PTB_Options {

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

    /**
     * The plugin options array
     *
     * @since    1.0.0
     * @access   private
     * @var      array $options The options of the plugin.
     */
    private $options;

    /**
     * Custom Post Types array
     *
     * @since    1.0.0
     * @access   private
     * @var      array $option_custom_post_types The options of custom post types.
     */
    private $option_custom_post_types;

    /**
     * Custom Taxonomies array
     *
     * @since    1.0.0
     * @access   private
     * @var      array $option_custom_taxonomies The options of custom taxonomies.
     */
    private $option_custom_taxonomies;

    /**
     * Custom Post Types Templates array
     *
     * @since    1.0.0
     * @access   private
     * @var      array $option_post_type_templates The options of custom post types templates.
     */
    public $option_post_type_templates;


    /* options keys */
    private $options_key_plugin_name;
    private $options_key_version;
    private $options_key_custom_css;
    private $options_key_custom_post_types;
    private $options_key_custom_taxonomies;
    private $options_key_post_type_templates;
    private $options_key_custom_meta_boxes;
    private $prefix_cpt_id;
    private $prefix_ctx_id;
    public  $prefix_ptt_id;
    private $settings_key;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @var      string $plugin_name The name of this plugin.
     * @var      string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

        /* options keys */
        $this->settings_key = $this->plugin_name . '_plugin_options';

        $this->options_key_plugin_name = 'plugin';
        $this->options_key_version = 'version';

        $this->options_key_custom_post_types = 'cpt';
        $this->options_key_custom_post_types = 'cpt';
        $this->options_key_custom_taxonomies = 'ctx';
        $this->options_key_post_type_templates = 'ptt';
        $this->options_key_custom_css = 'css';
        $this->options_key_custom_meta_boxes = 'meta_boxes';

        $this->prefix_cpt_id = 'ptb_cpt_';
        $this->prefix_ctx_id = 'ptb_ctx_';
        $this->prefix_ptt_id = 'ptb_ptt_';

        $this->load_options();
    }

    //==================================================================================================================
    // Options
    //==================================================================================================================

    /**
     * Loads the plugin options.
     * Default options created if plugin options are empty
     *
     * @since 1.0.0
     */
    protected function load_options() {

        $this->options = get_option($this->settings_key);

        if (empty($this->options)) {

            $this->options = $this->get_options_blueprint();
        }
        $this->option_custom_post_types = &$this->options[$this->options_key_custom_post_types];

        $this->option_custom_taxonomies = &$this->options[$this->options_key_custom_taxonomies];

        $this->option_post_type_templates = &$this->options[$this->options_key_post_type_templates];
    }

    public function get_options_blueprint() {

        return array(
            $this->options_key_plugin_name => $this->plugin_name,
            $this->options_key_version => $this->version,
            $this->options_key_custom_post_types => array(),
            $this->options_key_custom_taxonomies => array(),
            $this->options_key_post_type_templates => array()
        );
    }

    /**
     * Updates the plugin options.
     *
     * @since 1.0.0
     *
     * @param bool $recreate
     *
     * @return bool
     */
    public function update($recreate = false) {

        if ($recreate) {

            if (delete_option($this->settings_key)) {

                return add_option($this->settings_key, $this->options, '', 'yes');
            } else {

                return false;
            }
        } else {

            return update_option($this->settings_key, $this->options);
        }
    }

    //==================================================================================================================
    // Getters
    //==================================================================================================================

    /**
     * Getter of settings key
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_settings_key() {

        return $this->settings_key;
    }

    /**
     * Getter of options array
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_options() {

        return $this->options;
    }

    /**
     * setter of options array
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function set_options($options) {

        $this->options = $options;
    }

    /**
     * Getter of custom post types array
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_custom_post_types_options() {

        return $this->option_custom_post_types;
    }
    
    /**
     * Getter of custom taxonomies array
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_custom_taxonomies_options() {

        return $this->option_custom_taxonomies;
    }

    /**
     * Getter of custom post types templates array
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_templates_options() {

        return $this->option_post_type_templates;
    }

    /**
     * Setter of custom post types array
     *
     * @since 1.0.0
     *
     * @param array $value
     *
     */
    public function set_custom_post_types_options($value) {

        $this->options[$this->options_key_custom_post_types] = $value;
    }

    /**
     * Getter of custom css 
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_custom_css() {

        return isset($this->options[$this->options_key_custom_css]) ? $this->options[$this->options_key_custom_css] : false;
    }

    /**
     * Setter of custom post types array
     *
     * @since 1.0.0
     *
     * @param array $value
     *
     */
    public function set_custom_css($value) {

        $this->options[$this->options_key_custom_css] = $value;
    }

    /**
     * Setter of custom taxonomies array
     *
     * @since 1.0.0
     *
     * @param array $value
     *
     */
    public function set_custom_taxonomies_options($value) {

        $this->options[$this->options_key_custom_taxonomies] = $value;
    }

    /**
     * Setter of custom post types templates array
     *
     * @since 1.0.0
     *
     * @param array $value
     *
     */
    public function set_templates_options($value) {

        $this->options[$this->options_key_post_type_templates] = $value;
    }

    //==================================================================================================================
    // Custom Post Type
    //==================================================================================================================

    /**
     * Registers the custom post types of the plugin
     *
     * @since    1.0.0
     */
    public function ptb_register_custom_post_types() {

        $cpt_objects_array = $this->get_custom_post_types();
        $is_admin = is_admin();
        if ($is_admin) {
            $admin = new PTB_Admin($this->plugin_name, $this->version, $this);
        } 
        global $wp_post_types;
        foreach ($cpt_objects_array as $cpt_object) {
            $register = apply_filters('ptb_filter_register_cpt',true,$cpt_object->slug);
            if($register && $this->is_custom_post_type_registered($cpt_object->slug)){
                if(isset($wp_post_types[$cpt_object->slug])){
                    unset($wp_post_types[$cpt_object->slug]);
                }
                
                $args = $cpt_object->get_args();
                if(in_array($cpt_object->slug,array('post','page','attachment','revision','nav_menu_item'))){
                    $args['_builtin'] = true;
                }
                register_post_type($cpt_object->slug, $args);
                if ($is_admin) {
                    $cmb = $this->get_cpt_cmb_options($cpt_object->slug);
                    if (!empty($cmb)) {
                        add_action('add_meta_boxes_' . $cpt_object->slug, array($this, 'add_custom_meta_boxes'), 10, 1);
                    }
                    add_filter('manage_edit-' . $cpt_object->slug . '_columns', array($admin, 'ptb_colums'));
                    add_filter('posts_clauses', array($admin, 'ptb_sort_colums'), 11, 2);
                }
                do_action('ptb_register_custom_post_type',$cpt_object->slug,$args);
            }
        }
        if (isset($this->options['flush'])) {
            unset($this->options['flush']);
            flush_rewrite_rules();
            $this->update();
        }
    }

    /**
     * Adds custom post type to options
     *
     * @since 1.0.0
     *
     * @param PTB_Custom_Post_Type $cpt
     */
    public function add_custom_post_type($cpt) {

        $cpt->id = $cpt->slug;

        $id = $cpt->id;
        $cpt_options = &$this->options[$this->options_key_custom_post_types];
        $cpt_options[$id] = $cpt->serialize();

        $custom_meta_boxes = $cpt_options[$id][$this->options_key_custom_meta_boxes];

        foreach ($custom_meta_boxes as $key => $options) {
            if ($options['deleted']) {
                unset($cpt_options[$id][$this->options_key_custom_meta_boxes][$key]);
            }
        }

        $this->synchronize_post_type_to_taxonomy($id, $cpt);
        $this->set_flush();
    }


    

    /**
     * Edits (replace) custom post type in options
     *
     * @since 1.0.0
     *
     * @param $id
     * @param PTB_Custom_Post_Type $cpt
     * @param bool $continue
     */
    public function edit_custom_post_type($id, $cpt, $continue = true) {

       
        if ($this->has_custom_post_type($id)) {

            $cpt->id = $cpt->slug;

            $meta_keys = $cpt->meta_boxes;

            foreach ($meta_keys as $key => $options) {
                if ($options['deleted']) {
                    $this->remove_custom_meta($id, $key);
                    unset($cpt->meta_boxes[$key]);
                }
            }
            //remove old id
            unset($this->option_custom_post_types[$id]);

            //add new id
            $new_id = $cpt->slug;
            $this->option_custom_post_types[$new_id] = $cpt->serialize();

            $ptt = $this->get_post_type_template_by_type($id);

            if (!is_null($ptt)) {

                $ptt->set_post_type($cpt->slug);
                $this->update_post_type_template($ptt);
            }
            if ($continue) {
                $this->synchronize_post_type_to_taxonomy($id, $cpt);
            }
            global $wpdb;
            if ($id != $new_id) {
                $wpdb->query("UPDATE $wpdb->posts SET post_type = '$new_id' WHERE post_type = '$id'");

                //get post type templates
                $post_type_template_set = $this->option_post_type_templates;

                foreach ($post_type_template_set as $template_id => $post_type_template_args) {

                    //check for assigned template
                    if ($post_type_template_args['post_type'] === $id) {

                        $template_obj = new PTB_Post_Type_Template($this->plugin_name, $this->version);
                        $template_obj->deserialize($post_type_template_args);
                        $template_obj->set_id($template_id);
                        $template_obj->set_post_type($new_id);

                        //update template custom meta boxes

                        $archive = $template_obj->get_archive();
                        $single = $template_obj->get_single();
                        $template_obj->set_archive($archive);
                        $template_obj->set_single($single);
                    }
                }
                $this->set_flush();
            }
            do_action('ptb_cpt_update', $id, $cpt->slug);
        }
    }

    /**
     * Removes custom post type from database and options
     *
     * @since 1.0.0
     *
     * @param $id
     *
     * @return bool true if post type removed successfully and false otherwise
     */
    public function remove_custom_post_type($id) {

        if ($this->has_custom_post_type($id)) {
            $remove = apply_filters('ptb_filter_remove_posts', true, $id);
            if ($remove) {
                $query = new WP_Query(array(
                    'post_type' => $id,
                    'post_status' => array(
                        'publish',
                        'pending',
                        'draft',
                        'auto-draft',
                        'future',
                        'private',
                        'inherit',
                        'trash'
                    ),
                    'posts_per_page' => - 1
                ));

                $posts_to_delete = $query->get_posts();

                foreach ($posts_to_delete as $post) {

                    wp_delete_post($post->ID, true);
                }
            }

            $this->remove_custom_post_type_from_custom_taxonomies($id);
            $themplate = $this->get_post_type_template_by_type($id);
            if (isset($themplate)) {
                $this->remove_post_type_template($themplate->get_id());
            }
            unset($this->option_custom_post_types[$id]);
            do_action('ptb_cpt_remove', $id);
            return true;
        }

        return false;
    }
    
    /**
     * Unregister custom post type
     *
     * @since 1.2.8
     *
     * @param $id
     *
     * @return bool true if post type unregistered successfully and false otherwise
     */
    public function unregister_custom_post_type($id) {

        if ($this->has_custom_post_type($id)) {
            $this->option_custom_post_types[$id]['unregister'] = true;
            do_action('ptb_cpt_unregister', $id);
            return true;
        }

        return false;
    }
    
    /**
     * Check if custom post type is registered
     *
     * @since 1.2.8
     *
     * @param $id
     *
     * @return bool
     */
    public function is_custom_post_type_registered($id) {

        return $this->has_custom_post_type($id) && empty($this->option_custom_post_types[$id]['unregister']);
    }
    
    /**
     * Register custom post type
     *
     * @since 1.2.8
     *
     * @param $id
     *
     * @return bool true if post type registered successfully and false otherwise
     */
    public function register_custom_post_type($id) {

        if ($this->has_custom_post_type($id)) {
            unset($this->option_custom_post_types[$id]['unregister']);
            do_action('ptb_cpt_register', $id);
            return true;
        }

        return false;
    }

    /**
     * Removes custom custom post type from taxonomies
     *
     * @since 1.0.0
     *
     * @param string $id the id of custom taxonomy
     */
    public function remove_custom_post_type_from_custom_taxonomies($id) {

        foreach ($this->option_custom_taxonomies as $ctx_id => $ctx_option) {

            $ctx = new PTB_Custom_Taxonomy($this->plugin_name, $this->version);

            $ctx->deserialize($ctx_option);

            if ($ctx->is_attached_to_post_type($id)) {

                $ctx->attach_to_post_type($id, false);

                $this->edit_custom_taxonomy($ctx_id, $ctx);
            }
        }
    }

    /**
     * Synchronize all custom post types registered by this plugin
     * from custom taxonomy.
     *
     * @since 1.0.0
     *
     * @param string $id post type id
     * @param PTB_Custom_Post_Type $cpt
     */
    private function synchronize_post_type_to_taxonomy($id, $cpt) {

        $taxonomies = $this->get_custom_taxonomies();

        foreach ($taxonomies as $ctx) {

            $state = $cpt->has_taxonomy($ctx->slug);
            $ctx->attach_to_post_type($cpt->slug, $state);

            if ($cpt->slug != $id) {

                $ctx->attach_to_post_type($id, false);
            }

            $this->edit_custom_taxonomy($ctx->id, $ctx, false);
        }
    }

    /**
     * Checks for custom post type existence by id
     *
     * @param $id
     *
     * @return bool
     */
    public function has_custom_post_type($id) {
        return array_key_exists($id, $this->option_custom_post_types);
    }

    /**
     * Returns the custom post type by ID. Returns null if post type does not exists.
     *
     * @since 1.0.0
     *
     * @param string $id the id of post type
     *
     * @return PTB_Custom_Post_Type | null
     */
    public function get_custom_post_type($id) {

        if ($this->has_custom_post_type($id)) {

            $cpt_options = $this->option_custom_post_types[$id];

            $cpt = new PTB_Custom_Post_Type($this->plugin_name, $this->version);
            $cpt->deserialize($cpt_options);
        } else {

            $cpt = null;
        }

        return $cpt;
    }

    /**
     * Returns the custom post types array
     *
     * @since 1.0.0
     *
     * @return PTB_Custom_Post_Type[]
     */
    public function get_custom_post_types() {

        $cpt_objects_array = array();

        foreach ($this->option_custom_post_types as $id=>$source) {
            $cpt_object = $this->get_custom_post_type($id);
            if($cpt_object){
                $cpt_objects_array[] = $cpt_object;
            }
        }

        return $cpt_objects_array;
    }

    /**
     * Returns all registered public custom post types
     *
     * @since 1.0.0
     *
     * @return array
     */
    public static function get_all_post_types() {
        $args = array(
            'public' => true,
            '_builtin' => false
        );
        $output = 'objects'; // or names
        $operator = 'and'; // 'and' or 'or'
        return get_post_types($args, $output, $operator);
    }

    /**
     * Returns all post types containing given taxonomy
     *
     * @since 1.0.0
     *
     * @param string $tax
     *
     * @return string[]
     */
    public static function get_post_types_by_taxonomy($tax) {

        $result = array();

        $post_types = self::get_all_post_types();

        foreach ($post_types as $post_type) {

            $taxonomies = get_object_taxonomies($post_type->name);

            if (in_array($tax, $taxonomies)) {

                PTB_Utils::add_to_array($post_type, $result);
            }
        }

        return $result;
    }

    //==================================================================================================================
    // Custom Taxonomies
    //==================================================================================================================

    /**
     * Registers the custom taxonomies of the plugin
     *
     * @since    1.0.0
     */
    public function ptb_register_custom_taxonomies() {

        $ctx_objects_array = $this->get_custom_taxonomies();

        foreach ($ctx_objects_array as $ctx_object) {
            if($this->is_custom_taxonomy_registered($ctx_object->slug)){
                register_taxonomy($ctx_object->slug, $ctx_object->attach_to, $ctx_object->get_args());
            }
        }
    }

    /**
     * Adds new custom taxonomy
     *
     * @since 1.0.0
     *
     * @param PTB_Custom_Taxonomy $ctx
     */
    public function add_custom_taxonomy($ctx) {

        $ctx->id = $ctx->slug;

        $id = $ctx->id;
        $ct_options = &$this->options[$this->options_key_custom_taxonomies];
        $ct_options[$id] = $ctx->serialize();

        $this->synchronize_taxonomy_to_post_type($id, $ctx);
        $this->set_flush();
    }


    

    /**
     * Updates custom taxonomy by id
     *
     * @since 1.0.0
     *
     * @param string $id
     * @param PTB_Custom_Taxonomy $ctx
     * @param bool $continue
     */
    public function edit_custom_taxonomy($id, $ctx, $continue = true) {

        if ($this->has_custom_taxonomy($id)) {

            $ctx->id = $ctx->slug;

            $this->option_custom_taxonomies[$id] = $ctx->serialize();
            if($continue){
                $this->synchronize_taxonomy_to_post_type($id, $ctx);
            }

            //remove old id
            unset($this->option_custom_taxonomies[$id]);

            //add new id
            $new_id = $ctx->slug;
            $this->option_custom_taxonomies[$new_id] = $ctx->serialize();
            if ($new_id != $id) {
                global $wpdb;
                $wpdb->query("UPDATE $wpdb->term_taxonomy SET taxonomy = '$new_id' WHERE taxonomy = '$id'");
                $this->set_flush();
            }
        }
    }

    /**
     * Removes custom taxonomy from database and options
     *
     * @since 1.0.0
     *
     * @param $id
     *
     * @return bool true if taxonomy removed successfully and false otherwise
     */
    public function remove_custom_taxonomy($id) {

        if ($this->has_custom_taxonomy($id)) {
            $remove = apply_filters('ptb_filter_remove_terms', true, $id);
            if($remove){
                $all_tax_to_delete = get_terms($id, array('hide_empty' => 0));

                foreach ($all_tax_to_delete as $term) {
                    wp_delete_term($term->term_id, $id);
                }
            }
            $this->remove_custom_taxonomy_from_custom_post_types($id);

            unset($this->option_custom_taxonomies[$id]);

            return true;
        }

        return false;
    }
    /**
     * Unregister custom taxonomy
     *
     * @since 1.2.8
     *
     * @param $id
     *
     * @return bool true if taxonomy unregistered successfully and false otherwise
     */
    public function unregister_custom_taxonomy($id) {

        if ($this->has_custom_taxonomy($id)) {
            $this->option_custom_taxonomies[$id]['unregister'] = true;
            do_action('ptb_ctx_unregister', $id);
            return true;
        }

        return false;
    }
    
    /**
     * Register custom taxonomy
     *
     * @since 1.2.8
     *
     * @param $id
     *
     * @return bool true if taxonomy registered successfully and false otherwise
     */
    public function register_custom_taxonomy($id) {

        if ($this->has_custom_taxonomy($id)) {
            unset($this->option_custom_taxonomies[$id]['unregister']);
            do_action('ptb_ctx_register', $id);
            return true;
        }

        return false;
    }
    
    /**
     * Check if custom taxonomy is registered
     *
     * @since 1.2.8
     *
     * @param $id
     *
     * @return bool
     */
    public function is_custom_taxonomy_registered($id) {

        return $this->has_custom_taxonomy($id) && empty($this->option_custom_taxonomies[$id]['unregister']);
    }
    
    /**
     * Removes custom taxonomy from custom post types
     *
     * @since 1.0.0
     *
     * @param string $id the id of custom taxonomy
     */
    public function remove_custom_taxonomy_from_custom_post_types($id) {

        foreach ($this->option_custom_post_types as $cpt_id => $cpt_option) {

            $cpt = new PTB_Custom_Post_Type($this->plugin_name, $this->version);

            $cpt->deserialize($cpt_option);

            if ($cpt->has_taxonomy($id)) {

                $cpt->set_taxonomy($id, false);

                $this->edit_custom_post_type($cpt_id, $cpt);
            }
        }
    }

    /**
     * Synchronize all custom post types registered by this plugin
     * from custom taxonomy.
     *
     * @since 1.0.0
     *
     * @param string $id taxonomy id
     * @param PTB_Custom_Taxonomy $ctx
     */
    private function synchronize_taxonomy_to_post_type($id, $ctx) {

        $post_types = $this->get_custom_post_types();

        foreach ($post_types as $cpt) {

            $state = $ctx->is_attached_to_post_type($cpt->slug);
            $cpt->set_taxonomy($ctx->slug, $state);

            if ($ctx->slug != $id) {

                $cpt->set_taxonomy($id, false);
            }

            $this->edit_custom_post_type($cpt->id, $cpt, false);
        }
    }

    /**
     * Checks for custom taxonomy existence by id
     *
     * @since 1.0.0
     *
     * @param $id
     *
     * @return bool
     */
    public function has_custom_taxonomy($id) {
        
        return array_key_exists($id, $this->option_custom_taxonomies);
    }

    /**
     * Returns the custom taxonomy by ID. Returns null if taxonomy does not exists.
     *
     * @since 1.0.0
     *
     * @param string $id the id of taxonomy
     *
     * @return PTB_Custom_Taxonomy | null
     */
    public function get_custom_taxonomy($id) {

        if ($this->has_custom_taxonomy($id)) {

            $ctx_options = $this->option_custom_taxonomies[$id];
            $ctx = new PTB_Custom_Taxonomy($this->plugin_name, $this->version);
            $ctx->deserialize($ctx_options);
        } else {
            $ctx = null;
        }

        return $ctx;
    }

    /**
     * Returns the custom taxonomies array
     *
     * @since 1.0.0
     * @return PTB_Custom_Taxonomy[]
     */
    public function get_custom_taxonomies() {

        $ctx_objects_array = array();

        foreach ($this->option_custom_taxonomies as $id=>$source) {
            $ctx_object = $this->get_custom_taxonomy($id);
            if($ctx_object){
                $ctx_objects_array[] = $ctx_object;
            }
        }

        return $ctx_objects_array;
    }

    /**
     * Returns all non build in and public taxonomies
     *
     * @since 1.0.0
     *
     * @return array
     */
    public static function get_all_custom_taxonomies() {
        $args = array(
            'public' => true,
            '_builtin' => false
        );
        $output = 'objects'; // or names
        $operator = 'and'; // 'and' or 'or'
        return get_taxonomies($args, $output, $operator);
    }

    //==================================================================================================================
    // Custom Meta Boxes
    //==================================================================================================================

    /**
     * todo: add documentation
     * @since 1.0.0
     * @return mixed|void
     */
    public static function get_cmb_types() {
        return apply_filters('ptb_cmb_types', array());
    }

    /**
     * todo: add documentation
     * @since 1.0.0
     *
     * @param $cpt_type
     * @param $cmb_key
     *
     * @return bool
     */
    private function remove_custom_meta($cpt_type, $cmb_key) {
        if ($this->has_custom_post_type($cpt_type)) {

            $query = new WP_Query(array(
                'post_type' => $cpt_type,
                'post_status' => array(
                    'publish',
                    'pending',
                    'draft',
                    'auto-draft',
                    'future',
                    'private',
                    'inherit',
                    'trash'
                ),
                'posts_per_page' => - 1
            ));

            $posts = $query->get_posts();

            foreach ($posts as $post) {
                delete_post_meta($post->ID, 'ptb_' . $cmb_key);
            }

            return true;
        }

        return false;
    }

    /**
     * Add custom meta boxes from plugin options
     *
     * @since 1.0.0
     *
     * @param WP_Post $post the post object
     */
    public function add_custom_meta_boxes($post) {
        $post_type = $this->get_custom_post_type($post->post_type);
        if(!empty($post_type)){
            $section_name = !empty($post_type->metabox_section_name)?PTB_Utils::get_label($post_type->metabox_section_name):'';
            if(empty($section_name)){
                $section_name =  __("PTB Meta Box", 'ptb');
            }
            add_meta_box(
                    'ptb_cmb_' . $post->ID, $section_name, array($this, 'add_custom_meta_box_cb'), $post->post_type, 'normal', 'default', array(
                'post_type' => $post
                    )
            );
        }
    }

    /**
     * todo: add documentation
     * @since 1.0.0
     *
     * @param $cpt_id
     *
     * @return array
     */
    public function get_cpt_cmb_options($cpt_id) {

        if ($this->has_custom_post_type($cpt_id)) {
            return $this->option_custom_post_types[$cpt_id][$this->options_key_custom_meta_boxes];
        } else {
            return array();
        }
    }

    /**
     * @since 1.0.0
     *
     * @param string $post_type
     * @param array $cmb_options post type options
     * @param array $post_support post type support
     * @param array $post_taxonomies post type support taxonomies
     *
     * @return void
     */
    public function get_post_type_data($post_type, array &$cmb_options = array(), array &$post_support = array(), array &$post_taxonomies = array()) {
        static $post_type_data = array();
        if(!isset($post_type_data[$post_type])){
            $cmb_options = $this->get_cpt_cmb_options($post_type);
            $post_taxonomies = $this->get_cpt_cmb_taxonomies($post_type);
            $post_support = $this->get_cpt_cmb_support($post_type);
            $post_support = array_merge($post_support,array('custom_text','date','custom_image','permalink'));
            $post_support_names = array(
                'title'=>__('Title','ptb'),
                'author'=>__('Author', 'ptb'),
                'editor'=>__('Content', 'ptb'),
                'thumbnail'=>__('Featured Image', 'ptb'),
                'excerpt'=>__('Excerpt', 'ptb'),
                'comments'=>__('Comments', 'ptb'),
                'comment_count'=>__('Comment Count', 'ptb'),
                'custom_text'=> __('Static Text', 'ptb'),
                'date'=>__('Date', 'ptb'),
                'custom_image'=>__('Static Image', 'ptb'),
                'permalink'=>__('Permalink', 'ptb'),
            );
            if(in_array('comments',$post_support)){
                $post_support[] = 'comment_count';
            }
            foreach ($post_support as $s) {
                if($s!=='page-attributes' && $s!=='revisions' && $s!=='custom-fields'){
                    $cmb_options[$s] = array('type' => $s,'name'=>isset($post_support_names[$s])?$post_support_names[$s]:'');
                    $post_support[$s] = $s;
                }
            }
            unset(
                    $post_support['page-attributes'],
                    $post_support['revisions'],
                    $post_support['custom-fields']
                ); 
            if(!isset($post_support['comments'])){
                unset($cmb_options['comment_count'],$post_support['comment_count']);
            }
            if (!empty($post_taxonomies)) {
                $tag = array_search('post_tag', $post_taxonomies);
                if ($tag !== false) {
                    $post_support['post_tag'] = 'post_tag';
                    $cmb_options['post_tag'] = array('type' => 'post_tag', 'name' => __('Tags', 'ptb'));
                    unset($post_taxonomies[$tag]);
                }
                if (!empty($post_taxonomies)) {
                    $category = array_search('category', $post_taxonomies);
                    if ($category !== false) {
                        $post_support['category'] = 'category';
                        $cmb_options['category'] = array('type' => 'category','name' => __('Categories', 'ptb'));
                        unset($post_taxonomies[$category]);
                    }
                    if (!empty($post_taxonomies)) {
                        $post_support['taxonomies'] = 'taxonomies';
                        $cmb_options['taxonomies'] = array('type' => 'taxonomies', 'name' => __('Taxonomies', 'ptb'));
                    }
                }
            }
            $cmb_options = apply_filters('ptb_cmb_options_filter',$cmb_options,$post_type,$post_support);
            $post_type_data[$post_type] = array(
                $cmb_options,
                $post_taxonomies,
                $post_support
            );
        }
        else{
            list($cmb_options,$post_taxonomies,$post_support) = $post_type_data[$post_type];
        }
    }

    /**
     * todo: add documentation
     * @since 1.0.0
     *
     * @param $cpt_id
     *
     * @return array
     */
    public function get_cpt_cmb_support($cpt_id) {
        return $this->has_custom_post_type($cpt_id)?$this->option_custom_post_types[$cpt_id]['supports']:array();
    }

    /**
     * todo: add documentation
     * @since 1.0.0
     *
     * @param $cpt_id
     *
     * @return array
     */
    public function get_cpt_cmb_taxonomies($cpt_id) {
        return $this->has_custom_post_type($cpt_id)?$this->option_custom_post_types[$cpt_id]['taxonomies']:array();
    }

    /**
     * Callback for add_meta_box action
     *
     * @since 1.0.0
     *
     * @param WP_Post $post current post
     */
    public function add_custom_meta_box_cb($post) {

        $cmb_options = $this->get_cpt_cmb_options($post->post_type);
        $cmb_options = apply_filters('ptb_filter_cmb_body', $cmb_options, $post);
        if (empty($cmb_options)) {
            remove_meta_box('ptb_cmb_' . $post->ID, $post->post_type, 'normal');
            return;
        }
        wp_nonce_field('ptb_meta_box', 'ptb_meta_box_nonce');
        ?>

        <div class="ptb_post_cmb_wrapper">
            <?php
            foreach ($cmb_options as $meta_box_id => $args) {
                ?>
                <div class="ptb_post_cmb_item_wrapper ptb_post_cmb_item_<?php echo $args['type'] ?>" data-ptb-cmb-type="<?php echo $args['type'] ?>"
                     id="<?php echo $meta_box_id ?>">
                    <div class="ptb_post_cmb_title_wrapper">
                        <h4 class="ptb_post_cmb_name"><?php echo PTB_Utils::get_label($args['name']); ?></h4>
                    </div>
                    <div class="ptb_post_cmb_body_wrapper">
                        <?php
                        do_action('ptb_cmb_render', $post, $meta_box_id, $args);
                        do_action('ptb_cmb_render_' . $args['type'], $post, $meta_box_id, $args);
                        ?>
                        <p class="ptb_post_cmb_description"><?php echo PTB_Utils::get_label($args['description']); ?></p>
                    </div>
                </div>

                <?php
            }
            ?>
        </div>

        <?php
    }

    /**
     * Callback for save_post action
     *
     * @param $post_id
     * @param $post
     * @param $update
     *
     * @return mixed
     */
    public function save_custom_meta($post_id, $post, $update) {

        // Check if our nonce is set.
        if (!isset($_POST['ptb_meta_box_nonce'])) {
            return $post_id;
        }
        // Verify that the nonce is valid.
        if (!wp_verify_nonce($_POST['ptb_meta_box_nonce'], 'ptb_meta_box') || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) {
            return $post_id;
        }

        // Check the user's permissions.
        if ('page' === $_POST['post_type']) {

            if (!current_user_can('edit_page', $post_id)) {
                return $post_id;
            }
        } else {

            if (!current_user_can('edit_post', $post_id)) {
                return $post_id;
            }
        }

        /* OK, its safe for us to save the data now. */

        do_action('ptb_cmb_update', $post, $this);
    }

    /**
     * todo: add documentation
     * @since 1.0.0
     *
     * @param $option_key
     * @param $prefix
     *
     * @return mixed|string
     */
    public function get_next_id($option_key, $prefix) {
        $collection = $this->options[$option_key];

        if (empty($collection)) {
            $first_id = $prefix . str_pad('0', 4, '0', STR_PAD_LEFT);

            return $first_id;
        } else {
            $collection_keys = array_keys($collection);
            if($this->prefix_ptt_id==$prefix){
                foreach($collection_keys as $k=>&$col){
                    if(strpos($col,$prefix)===false){
                        unset($collection_keys[$k]);
                    }
                }
            }
            $max_id = max($collection_keys);

            return ++$max_id;
        }
    }

    //==================================================================================================================
    // Post Type Templates
    //==================================================================================================================

    /**
     * Returns post type template options by id
     *
     * @since 1.0.0
     *
     * @param string $id
     *
     * @return array
     */
    public function get_post_type_template($id) {
        return $this->has_post_type_template($id)?$this->option_post_type_templates[$id]:array();
    }

    /**
     * todo: add documentation
     * @since 1.0.0
     *
     * @param $id
     *
     * @return bool
     */
    public function has_post_type_template($id) {
        return array_key_exists($id, $this->option_post_type_templates);
    }

    /**
     * @param string $type
     *
     * @return null|PTB_Post_Type_Template
     */
    public function get_post_type_template_by_type($type) {

        $templates = $this->get_post_type_templates();

        foreach ($templates as $template) {

            if ($template->get_post_type() === $type) {

                return $template;
            }
        }

        return null;
    }

    /**
     * Updates post type template options.
     * The new one weill be added if nothing to update
     *
     * @since 1.0.0
     *
     * @param PTB_Post_Type_Template $ptt
     */
    public function update_post_type_template($ptt) {

        $ptt_id = $ptt->get_id();
        if (false === $this->has_post_type_template($ptt_id)) {

            $ptt_id = $this->get_next_id($this->options_key_post_type_templates, $this->prefix_ptt_id);
        }
        $this->option_post_type_templates[$ptt_id] = $ptt->serialize($ptt_id);
    }

    /**
     * todo: add documentation
     * @since 1.0.0
     *
     * @param $id
     */
    public function remove_post_type_template($id) {

        if ($this->has_post_type_template($id)) {

            unset($this->option_post_type_templates[$id]);
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Returns post type templates options
     *
     * @since 1.0.0
     *
     * @return PTB_Post_Type_Template[]
     */
    public function get_post_type_templates() {

        $post_type_templates = array();

        foreach ($this->option_post_type_templates as $id => $options) {

            $ptt = new PTB_Post_Type_Template($this->plugin_name, $this->version);
            $ptt->set_id($id);
            $ptt->deserialize($options);
            if ($ptt->has_archive() || $ptt->has_single()) {
                PTB_Utils::add_to_array($ptt, $post_type_templates);
            }
        }

        return $post_type_templates;
    }

    public function ptb_wp_editor($settings = array()) {
        if (!class_exists('_WP_Editors')) {
            require( ABSPATH . WPINC . '/class-wp-editor.php' );
        }
        $set = _WP_Editors::parse_settings('apid', $settings);
        if (!current_user_can('upload_files')) {
            $set['media_buttons'] = false;
        }
        if ($set['media_buttons']) {
            wp_enqueue_script('thickbox');
            wp_enqueue_style('thickbox');
            wp_enqueue_script('media-upload');
        }
        _WP_Editors::editor_settings('apid', $set);
        $ap_vars = array(
            'url' => get_home_url(),
            'includes_url' => includes_url()
        );
        wp_register_script('ap_wpeditor_init', dirname(plugin_dir_url(__FILE__)) . '/admin/js/ptb-wp-editor.js', array('jquery'), $this->version, true);
        wp_localize_script('ap_wpeditor_init', 'ap_vars', $ap_vars);
        wp_enqueue_script('ap_wpeditor_init');
    }

    public function add_template_styles() {
        $plugin_dir = dirname(plugin_dir_url(__FILE__));
        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_script('jquery-ui-droppable');
        wp_enqueue_style($this->plugin_name . '-colors', $plugin_dir . '/admin/themify-icons/themify.framework.css', array(), $this->version, 'all');
        wp_enqueue_style($this->plugin_name . '-themplate', $plugin_dir . '/admin/css/ptb-themplate.css', array($this->plugin_name . '-colors'), $this->version, 'all');
        $this->ptb_wp_editor();
        wp_enqueue_script($this->plugin_name . '-themplate', $plugin_dir . '/admin/js/ptb-themplate.js', array($this->plugin_name), $this->version, false);
    }

    /**
     * Set flush rewrite
     *
     * @since 1.1.3
     *
     * @return void
     */
    public function set_flush() {
        $this->options['flush'] = 1;
    }
    
    /**
     * Register and load the widget
     *
     * @since 1.2.8
     *
     * @return void
     */
    public function ptb_load_widget(){
        register_widget( 'PTB_Widget_Recent_Posts' );
    }
    
    
    public function get_shortcode_data($post_type=false) {
        if ($post_type) {
            $result = array();
            $templateObject = $this->get_post_type_template_by_type($post_type);
            if ($templateObject) {
                $cmb_options = $post_support = $post_taxonomies = array();
                $this->get_post_type_data($post_type, $cmb_options, $post_support, $post_taxonomies);
               

                $sortable = PTB_Form_PTT_Archive::get_sort_fields($cmb_options);
              
                $fields = $grids = $by = array();

                foreach ($sortable as $key => $s) {
                    $fields[] = array('text' => $s, 'value' => $key);
                } 
                unset($sortable);
                foreach (PTB_Form_PTT_Archive::$layouts as $k => $l) {
                    $grids[] = array('text' => ucfirst($k), 'value' => $k);
                }
                $by[] = array(
                    'text' => __('Ascending', 'ptb'),
                    'value' => 'asc'
                );
                $by[] = array(
                    'text' => __('Descending', 'ptb'),
                    'value' => 'desc'
                );
                $archive = $templateObject->get_archive();
                unset($archive['layout']);
                $archive['offset'] = 0;
                $archive['posts_per_page'] = isset($archive['ptb_ptt_offset_post']) && intval($archive['ptb_ptt_offset_post']) > 0 ? $archive['ptb_ptt_offset_post'] : get_option('posts_per_page');
                $archive['style'] = $archive['ptb_ptt_layout_post'];
                $archive['post_filter'] = 1;
                unset($archive['ptb_ptt_layout_post'], $archive['ptb_ptt_offset_post']);
                foreach ($archive as $key => $arh) {
                    $key = str_replace(array('ptb_ptt_', '_post'), '', $key);
                    $name = ucfirst(str_replace('_', ' ', $key));
                    $result['data'][$key] = array(
                        'label' => $name,
                        'value' => $arh
                    );
                    switch ($key) {
                        case 'order':
                            $result['data'][$key]['type'] = 'listbox';
                            $result['data'][$key]['values'] = $by;
                            break;
                        case 'orderby':
                            $result['data'][$key]['type'] = 'listbox';
                            $result['data'][$key]['values'] = $fields;
                            break;
                        case 'pagination':
                            $result['data'][$key]['type'] = 'radio';
                            $result['data'][$key]['values'] = 1;
                            break;
                        case 'style':
                            $result['data'][$key]['type'] = 'listbox';
                            $result['data'][$key]['values'] = $grids;
                            break;
                        case 'post_filter':
                            $result['data'][$key]['type'] = 'radio';
                            $result['data'][$key]['values'] = 1;
                            $result['data'][$key]['label'] = __('Post Filter', 'ptb');
                            break;
                        default:
                            $result['data'][$key]['type'] = 'textbox';
                            break;
                    }
                }
                $cmb_options = apply_filters('ptb_shortcode_cmb', $cmb_options,$post_type);
                if(!empty($cmb_options)){
                    $except = array(
                        'taxonomies',
                        'post_tag',
                        'category',
                        'comments',
                        'custom_text',
                        'date',
                        'permalink',
                        'custom_image'
                    );
                    $result['meta']['data'] = $result['field']['data'] = array();
                 
                    foreach ($cmb_options as $k => $m) {
                        $is_metabox = !isset($post_support[$k]);
                        if($is_metabox){
                            switch ($m['type']) {
                                case 'checkbox':
                                case 'select':
                                case 'radio_button':
                                    if(empty($m['options'])){
                                        continue 2;
                                    }
                                    $options = array(array('text'=>'---','value'=>''));
                                    foreach( $m['options'] as $opt){
                                        $options[] = array('text'=>  PTB_Utils::get_label($opt),'value'=>$opt['id'],'checked'=>!empty($opt['checked']));
                                    }
                                    $result['meta']['data'][$k]['type'] = ($m['type']==='select' && !empty($m['multipleSelects'])) || ($m['type']==='checkbox' && count($m['options'])>1)?'multiselect':'listbox';
                                    $result['meta']['data'][$k]['values'] = $options;
                                    $result['meta']['data'][$k]['hide'] = !empty($m['hide']);
                                    break;
                                case 'number':
                                    $result['meta']['data'][$k]['hide'] = !empty($m['range']);
                                    $result['meta']['data'][$k]['type'] = 'number';
                                    break;
                                default:
                                    $result['meta']['data'][$k]['hide'] = !empty($m['hide']) || $m['type']==='image';
                                    $result['meta']['data'][$k]['type'] = 'textbox';
                                    break;
                            }
                            $result['meta']['data'][$k]['name'] = 'ptb_meta_'.$k;
                            $result['meta']['data'][$k]['label'] = PTB_Utils::get_label($m['name']);
                        }
                        elseif(!in_array($k,$except)){  
                            $result['field']['data'][$k]['label'] = $m['name'];
                            $result['field']['data'][$k]['name'] = 'ptb_field_'.$k;
                            if($k==='comment_count'){
                                $result['field']['data'][$k]['type'] = 'number';
                            }
                            elseif($k==='thumbnail'){
                                $result['field']['data'][$k]['type'] = 'textbox';
                                $result['field']['data'][$k]['hide'] = true;
                            }
                            elseif($k==='author'){
                                global $wpdb;
                                $authors = get_users(
                                        array(
                                        'orderby'=>'name',
                                        'order'=>'ASC',
                                        'fields'=>'ids'
                                    
                                        ));
                                
                                $author_list = array(array('value'=>'','text'=>'---'));
                                if(!empty($authors)){
                                    $author_count = array();
                                    foreach ( (array) $wpdb->get_results( "SELECT DISTINCT post_author, COUNT(ID) AS count FROM $wpdb->posts WHERE " . get_private_posts_cap_sql( $post_type ) . " GROUP BY post_author" ) as $row ) {
                                        $author_count[$row->post_author] = $row->count;
                                    }
                                    foreach ( $authors as $author_id ) {
                                        $posts = isset( $author_count[$author_id] ) ? $author_count[$author_id] : 0;
                                        if($posts>0){
                                            $author = get_userdata( $author_id );
                                            $author_list[] = array('value'=>$author_id,'text'=>$author->display_name.' ( '.$posts.' )');
                                        }
                                    }
                                   
                                }
                                $result['field']['data'][$k]['hide_exist'] = true;
                                $result['field']['data'][$k]['type'] = 'multiselect';
                                $result['field']['data'][$k]['values'] = $author_list;
                            }
                            else{
                                if($k==='title'){
                                    $result['field']['data'][$k]['hide_exist'] = true;
                                }
                                $result['field']['data'][$k]['type'] = 'textbox';
                            }
                        }
                    }
                }
                 
                if(!empty($result['meta']['data'])){
                    $result['meta']['title'] = __('PTB Metaboxes','ptb');
                }
                
                if(!empty($result['field']['data'])){
                    $result['field']['title'] = __('Fields','ptb');
                }
                
                if (isset($post_support['category'])) {
                    $post_taxonomies['category'] = 'category';
                }
                
                if (isset($post_support['post_tag'])) {
                    $post_taxonomies['post_tag'] = 'post_tag';
                }
                if (!empty($post_taxonomies)) {
                    $result['tax']['data'] = array();
                    $operators = array( array(
                                            'text' => __('IN (Entries from the indicated terms)', 'ptb'),
                                            'value' => 'in'
                                        ),
                                        array(
                                            'text' => __('NOT IN (Records from all terms except those)', 'ptb'),
                                            'value' => 'not in'
                                        ),
                                        array(
                                            'text' => __('AND (Records simultaneously belonging to all specified terms)', 'ptb'),
                                            'value' => 'and'
                                        )
                                    );
                    foreach ($post_taxonomies as $taxes) {
                        $tax = get_taxonomy($taxes);
                        if(empty($tax)){
                            continue;
                        }
                        $values = get_categories(array(
                            'type' => $post_type,
                            'hide_empty' => 1,
                            'taxonomy' => $taxes,
                            'pad_counts'=>true
                        ));
                        if (empty($values)) {
                            continue;
                        }
                        $options = array(array('value'=>'','text'=>'---'));
                        foreach ($values as $v){
                            $options[] = array('value'=>$v->slug,'text'=>$v->name.' ( '.$v->count.' )');
                        }
                        $result['tax']['data'][$taxes] = array(
                            'values'=>$options,
                            'label'=>$tax->labels->name,
                            'name'=>'ptb_tax_'.$taxes,
                            'type'=>'multiselect',
                        );
                        $result['tax']['data'][$taxes.'_operator'] = array(
                            'values'=>$operators,
                            'label'=> sprintf(__('Operator of %s','ptb'),$tax->labels->name),
                            'name'=>$taxes.'_operator',
                            'type'=>'listbox',
                        );

                        if($tax->hierarchical){
                            $result['tax']['data'][$taxes.'_children'] = array(
                                'values'=>1,
                                'label'=>  sprintf(__('Exclude children of %s','ptb'),$tax->labels->name),
                                'name'=>$taxes.'_children',
                                'type'=>'radio',
                                'tooltip'=>__('Not include children for hierarchical taxonomies. Defaults includes','ptb')
                            );
                        }
                    }
                    if (!empty($result['tax']['data'])) {
                        $result['tax']['title'] = __('Taxonomies','ptb');
                        $result['tax']['data'] = array('logic'=>array(
                                                    'label'=>__('Taxonomies Logic', 'ptb'),
                                                    'type'=>'listbox',
                                                    'name'=>'logic',
                                                    'tooltip'=>__('The logical relationship between each inner taxonomy array when there is more than one','ptb'),
                                                    'values'=>array(     
                                                                array(
                                                                    'text' => __('OR', 'ptb'),
                                                                    'value' => 'or'
                                                                ),
                                                                array(
                                                                    'text' => __('AND', 'ptb'),
                                                                    'value' => 'and'
                                                                )
                                                            )
                                                ))
                                                +
                                                $result['tax']['data'];
                            
                    }
                }
            }
            $result['title'] = __('PTB Shortcode Options', 'ptb');
            return apply_filters('ptb_ajax_shortcode_result', $result, $post_type);
        }
        return false;
    }

}

<?php
/**
 * The dashboard-specific functionality of the plugin.
 *
 * @link       http://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/admin
 */

/**
 * The dashboard-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    PTB
 * @subpackage PTB/admin
 * @author     Themify <ptb@themify.me>
 */
class PTB_Admin {

    /**
     * The options management class of the the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      PTB_Options $options Manipulates with plugin options
     */
    protected $options;
    protected $cpt_form;
    protected $ctx_form;
    protected $ptt_form;
    protected $ie_form;
    protected $css_form;
    protected $ptt_archive_form;
    protected $ptt_single_form;

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
    private $slug_admin_cpt;
    private $slug_admin_ctx;
    private $slug_admin_ptt;
    private $slug_admin_ie;
    private $slug_admin_ptt_archive;
    private $slug_admin_ptt_single;
    private $slug_admin_css;
    private $slug_admin_flush;
    private $settings_key;
    private $settings_section;
    private $columns = array();

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     *
     * @param string $plugin_name
     * @param string $version
     * @param PTB_Options $options
     *
     * @private param string $plugin_name The name of this plugin.
     * @private param string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version, $options) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

        $this->settings_section = $this->plugin_name . '_main_section';

        $this->slug_admin_cpt = $this->plugin_name . '-cpt';
        $this->slug_admin_ctx = $this->plugin_name . '-ctx';
        $this->slug_admin_ptt = $this->plugin_name . '-ptt';
        $this->slug_admin_ie = $this->plugin_name . '-ie';
        $this->slug_admin_ptt_archive = $this->plugin_name . '-ptt-archive';
        $this->slug_admin_ptt_single = $this->plugin_name . '-ptt-single';
        $this->slug_admin_css = $this->plugin_name . '-css';
        $this->slug_admin_flush = $this->plugin_name . '-flush';

        $this->options = $options;

        $this->settings_key = $this->options->get_settings_key();

        $this->cpt_form = new PTB_Form_CPT($this->plugin_name, $this->version, $this->options);
        $this->ctx_form = new PTB_Form_CTX($this->plugin_name, $this->version, $this->options);
        $this->ptt_form = new PTB_Form_PTT($this->plugin_name, $this->version, $this->options);
        $this->ie_form = new PTB_Form_ImportExport($this->plugin_name, $this->version, $this->options);
        $this->css_form = new PTB_Form_Css($this->plugin_name, $this->version, $this->options);
    }

    /**
     * Get the current custom post type id or null
     *
     * @since 1.0.0
     *
     * @return string
     */
    public static function get_current_custom_post_type_id() {
        return isset($_REQUEST['action']) && 'edit' === $_REQUEST['action']?$_REQUEST['post_type']:'';
    }

    /**
     * Register the administration menu for this plugin into the WordPress Dashboard menu.
     * This function called from PTB main class and registered with 'admin_menu' hook.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {

        add_menu_page(
                __('Post Type Builder', 'ptb'), __('Post Type Builder', 'ptb'), 'manage_options', $this->slug_admin_cpt, array($this, 'display_custom_cpt_ctx'), 'dashicons-welcome-write-blog', '58.896427'
        );
        $menu = array($this->slug_admin_cpt => array(
                __('Post Types', 'ptb'),
                __('Post Types', 'ptb'),
                'manage_options'
            ),
            $this->slug_admin_ctx => array(
                __('Taxonomies', 'ptb'),
                __('Taxonomies', 'ptb'),
                'manage_options',
                array($this, 'display_custom_cpt_ctx')
            ),
            $this->slug_admin_ptt => array(
                __('Templates', 'ptb'),
                __('Templates', 'ptb'),
                'manage_options',
                array($this, 'display_templates')
            ),
            $this->slug_admin_ie => array(
                __('Import/Export', 'ptb'),
                __('Import/Export', 'ptb'),
                'manage_options',
                array($this, 'display_import_export')
            ),
            $this->slug_admin_flush => array(
                __('Flush Permalinks', 'ptb'),
                __('Flush Permalinks', 'ptb'),
                'manage_options',
                array($this, 'display_flush')
            ),
            $this->slug_admin_css => array(
                __('Custom CSS', 'ptb'),
                __('Custom CSS', 'ptb'),
                'manage_options',
                array($this, 'display_custom_css')
            ),
            'ptb-about' => array(
                __('About', 'ptb'),
                __('About', 'ptb'),
                'manage_options',
                array($this, 'display_about')
            ),
        );
        $menu = apply_filters('ptb_admin_menu', $menu);
        foreach ($menu as $slug => $options) {
            add_submenu_page($this->slug_admin_cpt, $options[0], $options[1], $options[2], $slug, isset($options[3]) ? $options[3] : false
            );
        }
    }

    /**
     * Register the plugin settings and settings section.
     * This function called from PTB main class and registered with 'admin_init' hook.
     *
     * @since    1.0.0
     */
    public function register_plugin_settings() {
        register_setting(
                $this->settings_key, $this->settings_key, array($this, 'sanitize_options_cb')
        );
        if (!get_transient('ptb_welcome_page')) {
            return;
        }
        delete_transient('ptb_welcome_page');

        if (!is_network_admin() && !isset($_GET['activate-multi'])) {
            wp_safe_redirect(add_query_arg(array('page' => 'ptb-about'), admin_url('admin.php')));
        }
    }

    /**
     * Callback function for settings registration
     *
     * @since 1.0.0
     *
     * @param array $input the inputs array of settings page
     *
     * @return mixed
     */
    public function sanitize_options_cb($input) {

        if (isset($input['ptb_cpt_id'])) {

            $this->cpt_form->process_options($input);
        } elseif (isset($input['ptb_ctx_id'])) {

            $this->ctx_form->process_options($input);
        } elseif (isset($input['ptb_ptt_archive'])) {

            $this->ptt_archive_form->process_options($input);
        } elseif (isset($input['ptb_ptt_single'])) {

            $this->ptt_single_form->process_options($input);
        } elseif (isset($input['ptb_ptt_id'])) {

            $this->ptt_form->process_options($input);
        } elseif (isset($input['ptb_ie_export'])) {

            $this->ie_form->export($input);
        } elseif (isset($input['ptb_ie_import'])) {

            $this->ie_form->import($input);
        } elseif (isset($input['ptb_css'])) {

            $this->css_form->process_options($input);
        }
        return $this->options->get_options();
    }

    /**
     * Render the custom post types/custom taxonomies page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_custom_cpt_ctx() {
        $page = sanitize_text_field($_REQUEST['page']);
        if($page!==$this->plugin_name.'-cpt' && $page!==$this->plugin_name.'-ctx'){
            return;
        }
        $type = $page===$this->plugin_name.'-cpt'?'cpt':'ctx';
        if($type==='cpt'){
            $this->cpt_form->add_settings_fields($this->slug_admin_cpt);
        }
        else{
            $this->ctx_form->add_settings_fields($this->slug_admin_ctx);
        }
        if (!empty($_REQUEST['action'])) {
            $action = sanitize_text_field($_REQUEST['action']);
            if ( !empty($_GET['slug']) && ('delete' === $action || 'copy' === $action)) {
                $slug = sanitize_key($_GET['slug']);
                $message = '';
                if ('copy' === $action && !empty($_GET['old_slug'])){
                    $message = sprintf(
                            __('Custom %1$s "%2$s" has been copied from "%3$s"', 'ptb'),($type=='cpt'?__('post type','ptb'):__('taxonomy','ptb')), $slug, sanitize_key($_GET['old_slug'])
                    );
                }
                elseif('delete' === $action){
                    $message = sprintf(
                        __('Custom %1$s "%2$s" successfully removed.', 'ptb'),($type=='cpt'?__('post type','ptb'):__('taxonomy','ptb')), $slug
                    );  
                } 
                if($message){
                    add_settings_error($this->plugin_name . '_notices', '', $message, 'updated');
                }
                include_once( 'partials/ptb-admin-display-list-'.$type.'.php' );
            } elseif ('edit' === $action || 'add' === $action) {

                if (isset($_REQUEST['settings-updated'])) {

                    include_once( 'partials/ptb-admin-display-list-'.$type.'.php' );
                } else {

                    include_once( 'partials/ptb-admin-display-edit-'.$type.'.php' );
                }   
            } else {
                include_once( 'partials/ptb-admin-display-list-'.$type.'.php' );
            }
        } else {

            include_once( 'partials/ptb-admin-display-list-'.$type.'.php' );
        }
    }


    /**
     * Render the custom templates page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_templates() {

        $this->ptt_form->add_settings_fields($this->slug_admin_ptt);

        if (isset($_GET['action'])) {

            $action = sanitize_text_field($_REQUEST['action']);

            if ('delete' === $action) {


                if (isset($_REQUEST['ptb-ptt'])) {

                    $id = $_REQUEST['ptb-ptt'];
                    $template = $this->options->get_post_type_template($id);
                    if (isset($template) && isset($template['name'])) {
                        check_admin_referer('ptt_nonce', 'nonce');
                        $message = sprintf(
                                __('%1$s template successfully removed.', 'ptb'), $template['name']
                        );
                        $this->options->remove_post_type_template($id);
                        $this->options->update();
                        add_settings_error($this->plugin_name . '_notices', '', $message, 'updated');
                    }
                    include_once( 'partials/ptb-admin-display-list-ptt.php' );
                }
            } elseif ('edit' === $action) {

                if (isset($_REQUEST['template'])) {

                    if ('archive' === $_REQUEST['template']) {

                        $this->ptt_archive_form->add_settings_fields($this->slug_admin_ptt_archive);

                        include_once( 'partials/ptb-admin-display-edit-ptt-archive.php' );
                    } elseif ('single' === $_REQUEST['template']) {

                        $this->ptt_single_form->add_settings_fields($this->slug_admin_ptt_single);

                        include_once( 'partials/ptb-admin-display-edit-ptt-single.php' );
                    }
                } else {

                    include_once( 'partials/ptb-admin-display-edit-ptt.php' );
                }
                $this->options->add_template_styles();
            } elseif ('add' === $action) {

                if (!isset($_REQUEST['settings-updated'])) {

                    include_once( 'partials/ptb-admin-display-edit-ptt.php' );
                } else {

                    include_once( 'partials/ptb-admin-display-list-ptt.php' );
                }
            }
        } else {
            include_once( 'partials/ptb-admin-display-list-ptt.php' );
        }
    }

    /**
     * Renders the Import/Export page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_import_export() {

        $this->ie_form->add_settings_fields($this->slug_admin_ie);
        ?>
        <div class="wrap">
            <h2><?php _e('Import/Export', 'ptb') ?></h2>

            <div class="ptb_notices">
                <?php settings_errors($this->plugin_name . '_notices'); ?>
            </div>
            <?php do_settings_sections($this->plugin_name . '-ie') ?>
        </div>
        <?php
    }

    /**
     * Renders Custom css page.
     *
     * @since    1.0.0
     */
    public function display_custom_css() {

        $this->css_form->add_settings_fields($this->slug_admin_css);
        ?>
        <div class="wrap">
            <h2><?php _e('Custom CSS', 'ptb') ?></h2>

            <div class="ptb_notices">
                <?php settings_errors($this->plugin_name . '_notices'); ?>
            </div>
            <?php do_settings_sections($this->slug_admin_css) ?>
        </div>
        <?php
    }

    /**
     * Renders About page.
     *
     * @since    1.0.0
     */
    public function display_about() {
        ?>
        <div class="wrap">
            <h2><?php _e('About Post Type Builder', 'ptb') ?></h2>
            <p><a href="//themify.me/post-type-builder">Post Type Builder</a> (PTB) is an &quot;all-in-one&quot; plugin that allows you to create custom post types, taxonomies, and post type templates.</p>

            <iframe style="max-width: 100%; margin: 40px 0 30px; border: solid 1px #000; display: block; clear: both;" width="760" height="420" src="//www.youtube.com/embed/xWUQ5EEuxnU?rel=0&amp;start=42" frameborder="0" allowfullscreen=""></iframe>

            <h3>Import Sample Data</h3>
            <p>If you are new with the plugin, it will help you to understand how it works by viewing some sample post types (like our <a href="//themify.me/demo/themes/post-type-builder/">demo</a>).</p>
            <ol>
                <li>To import the sample post types: 
                    <ul>
                        <li>- Go to Post Type Builder &gt; Import/Export &gt; import the 'ptb-sample-post-types.json' file from the plugin folder &quot;themify-ptb folder &gt; sample folder&quot;</li>
                    </ul>
                </li>
                <li>To import the sample WordPress  posts:
                    <ul>
                        <li>- Go to Tools &gt; Import &gt; click &quot;WordPress&quot; &gt; import the 'wp-sample-posts.xml' file from the plugin folder &quot;themify-ptb folder &gt; sample folder&quot;</li>
                    </ul>
                </li>
            </ol>
            <h3>Step-by-Step Overview</h3>
            <p>Below is a quick step-by-step overview on how to use the plugin. You may click on the links to read the detail documentation of each step.</p>
            <ol>
                <li>Create a new <a href="//themify.me/docs/post-type-builder-plugin-documentation#post-types">Post Type</a>
                    <ul>
                        <li>- Configure the <a href="//themify.me/docs/post-type-builder-plugin-documentation#meta-box-builder">Meta Box Builder</a></li>
                        <li>- Create optional <a href="//themify.me/docs/post-type-builder-plugin-documentation#taxonomies">Taxonomies</a> associated with the Post Type (taxonomy is like a type/group of the post type)</li>
                    </ul>
                </li>
                <li>Create <a href="//themify.me/docs/post-type-builder-plugin-documentation#template-builder">Templates</a> for the post type
                    <ul>
                        <li>- Edit the Archive Template</li>
                        <li>- Edit the Single Template</li>
                    </ul>
                </li>
                <li>When you are done with the post type and templates, you'll be ready to add custom posts</li>
                <li>After custom posts are added, you may use the <a href="//themify.me/docs/post-type-builder-plugin-documentation#shortcodes">shortcode generator</a> (located on the WordPress visual content editor) to display custom posts on any post/page.</li>
                <li>With <a href="//themify.me/docs/post-type-builder-plugin-documentation#wpml-integration">WPML plugin</a> (not included in the plugin), you may create additional multilingual posts.</li>
            </ol>
        </div>
        <?php
    }

    public function display_flush() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'ptb'));
        }
        if (isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'ptb_flush')) {
            add_settings_error('ptb-flush', '', __('Permalinks have been updated'), 'updated');
            settings_errors('ptb-flush');
            flush_rewrite_rules(true);
        }
        ?>  
        <form class="ptb-flush-form" action="" method="post">
            <h4>If you are experiencing 404 error after changing the post type slug, click "Flush Permalinks" to refresh the permalinks in WordPress.</h4>
            <?php
            submit_button(__('Flush Permlinks', 'ptb'));
            wp_nonce_field('ptb_flush', 'nonce');
            ?>
        </form>
        <?php
    }
    
    
    /**
     * Post type options validation function.
     * Checks is the post type name allowed to use?
     * Used from dashboards trough ajax call.
     *
     * @since 1.0.0
     */
    public function ptb_ajax_post_type_name_validate() {

        if (wp_verify_nonce($_REQUEST['nonce'], 'ajax-ptb-cpt-nonce')) {

            $cpt_slug = sanitize_text_field($_POST['slug']);
            $reserved_by_theme = array('menu', 'section');
            $message = '';

            if (post_type_exists($cpt_slug)) {

                $message = sprintf(__('Post type "%s" exists', 'ptb'), $cpt_slug);
                
            } elseif (strlen($cpt_slug) > 20) {

                $message = __('Post type name can\'t be longer than 20 symbols', 'ptb');
                
            } elseif (strlen($cpt_slug) < 1) {

                $message = __('Post type name can\'t be empty', 'ptb');
                
            } elseif (is_plugin_active('themify-builder/themify-builder.php') && in_array($cpt_slug, $reserved_by_theme)) {

                $message = __('Post type name is reserved by themify-builder, please type another name', 'ptb');
                
            } elseif (preg_match("/[^a-z0-9_-]/", $cpt_slug, $match)) {

                $message = __('Post type name should only contain lowercase letters and the underscore or dash character', 'ptb');
            }
            elseif(in_array($cpt_slug, PTB_Utils::get_reserved_terms())){
               $message = __('Post type name is reserved by <a href="//codex.wordpress.org/Reserved_Terms" target="_blank">WP</a>, please type another name', 'ptb');
            }

            die($message);
        }
    }
    
    
    /**
     * Taxonomy type options validation function.
     * Checks is the taxonomy name allowed to use?
     * Used from dashboards trough ajax call.
     *
     * @since 1.0.0
     */
    public function ptb_ajax_taxonomy_name_validate() {

        if (wp_verify_nonce($_REQUEST['nonce'], 'ajax-ptb-ctx-nonce')) {

            $ctx_slug = sanitize_text_field($_POST['slug']);
            $message = '';

            if (taxonomy_exists($ctx_slug)) {

                $message = sprintf(__('Taxonomy "%s" exists', 'ptb'), $ctx_slug);
            }
            elseif (strlen($ctx_slug) > 32) {

                $message = __('Taxonomy name can\'t be longer than 32 symbols', 'ptb');
            }
            elseif (strlen($ctx_slug) < 1) {

                $message = __('Taxonomy name can\'t be empty', 'ptb');
            }
            elseif (preg_match("/[^a-z0-9_]/", $ctx_slug, $match)) {

                $message = __('Taxonomy name should only contain lowercase letters and the underscore character', 'ptb');
            }
            elseif(in_array($ctx_slug, PTB_Utils::get_reserved_terms())){
               $message = __('Taxonomy name is reserved by <a href="//codex.wordpress.org/Reserved_Terms" target="_blank">WP</a>, please type another name', 'ptb');
            }
            die($message);
        }
    }

    /**
     * Register the JavaScript/Stylesheets for the dashboard.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in PTB_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The PTB_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        $screen = get_current_screen();
        $plugin_dir = plugin_dir_url(__FILE__);
        wp_register_style($this->plugin_name, $plugin_dir . 'css/ptb-admin.css', array(), $this->version, 'all');
        if ($screen->id != 'customize') {
            $id = __('Post Type Builder','ptb');//multilanguage screen id
            $id = sanitize_title($id);
            $screens = array($id.'_page_ptb-ptt', 'toplevel_page_ptb-cpt', $id.'_page_ptb-ctx', $id.'_page_ptb-ie');
            if ($screen->base === 'post' && $screen->id !== 'post' && $screen->id !== 'page') {
                $post_types = $this->options->get_custom_post_types();
                foreach ($post_types as $p) {
                    if ($screen->id == $p->id) {
                        $screens[] = $p->id;
                        break;
                    }
                }
            }
            $screens = apply_filters('ptb_screens', $screens, $screen);
            wp_register_script($this->plugin_name, $plugin_dir . 'js/ptb-admin.js', array('jquery'), $this->version, false);
            if (in_array($screen->id, $screens)) {
                unset($screen, $screens);
                if (!wp_style_is('themify-font-icons-css')) {
                    wp_enqueue_style('themify-font-icons-css', plugin_dir_url(dirname(__FILE__)) . 'admin/themify-icons/font-awesome.min.css', array(), $this->version, 'all');
                }
                if (!wp_style_is('themify-colorpicker')) {
                    wp_enqueue_style('themify-colorpicker', $plugin_dir . 'css/jquery/jquery.minicolors.css', array(), $this->version, 'all');
                }
                if (!wp_style_is('themify-icons')) {
                    wp_enqueue_style('themify-icons', $plugin_dir . 'themify-icons/themify-icons.css', array(), $this->version, 'all');
                }

                wp_enqueue_media();
                wp_enqueue_script('jquery-ui-core');
                wp_enqueue_script('jquery-ui-sortable');
                wp_enqueue_script('jquery-effects-core');
                wp_enqueue_script('jquery-effects-blind');
                wp_enqueue_script('themify-colorpicker-js', $plugin_dir . 'js/jquery/jquery.minicolors.min.js', array('jquery'), $this->version, false);
                $translation_array = array(
                    'post_type_delete' => __('All posts and template will be deleted. Do you want to delete this?', 'ptb'),
                    'remove_posts'=>__('Remove posts as well','ptb'),
                    'unregister_posts'=>__('Unregister the post type','ptb'),
                    'remove_terms'=>__('Remove terms as well','ptb'),
                    'taxonomy_delete' => __('Unregister the taxonomy', 'ptb'),
                    'template_delete' => __('Do you want to delete this?', 'ptb'),
                    'module_delete' => __('Do you want to delete this module?', 'ptb'),
                    'lng' => PTB_Utils::get_current_language_code()
                );
                wp_localize_script($this->plugin_name, 'ptb_js', $translation_array);
                wp_enqueue_script($this->plugin_name);
            } else {
                wp_enqueue_script($this->plugin_name);
            }
            wp_enqueue_style($this->plugin_name);
        }
    }

    public function add_ptb_shortcode() {

        if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
            return;
        }
        //shortcodes
        if ('true' == get_user_option('rich_editing')) {
            add_filter('mce_external_plugins', array($this, 'ptb_add_shortcodes_buttons'));
            add_filter('mce_buttons', array($this, 'ptb_register_button'));
            if (is_admin()) {
                add_action('admin_footer', array($this, 'ptb_get_shortcodes'));
            } else {
                add_action('wp_footer', array($this, 'ptb_get_shortcodes'));
            }
        }
    }

    /**
     * Add shortcode JS to the page
     *
     * @return HTML
     */
    public function ptb_get_shortcodes() {
        $themplates = $this->options->get_post_type_templates();
        $menu = array();
        foreach ($themplates as $k => $post_themes) {
            if ($post_themes->has_archive()) {
                $post_type = $post_themes->get_post_type();
                $custom_post = $this->options->get_custom_post_type($post_type);
                $name = PTB_Utils::get_label($custom_post->plural_label);
                $menu[] = "{'type':'{$post_type}','name':'{$name}'}";
            }
        }
        $menu = apply_filters('ptb_shorcode_template_menu', $menu);
        if (!empty($menu)) {
            echo '<script type="text/javascript">
                            var ptb_shortcodes_button = new Array();
                            var $ptb_url = "' . admin_url('admin-ajax.php?action=' . $this->plugin_name . '_ajax_get_post_type') . '";';
            foreach ($menu as $k => $post_themes) {
                echo "ptb_shortcodes_button['$k']=$post_themes;";
            }
            echo '</script>';
        }
    }

    /**
     * Add new Javascript to the plugin scrippt array
     *
     * @param  Array $plugin_array - Array of scripts
     *
     * @return Array
     */
    public function ptb_add_shortcodes_buttons($plugin_array) {
      
        $plugin_array[$this->plugin_name] = plugin_dir_url(__FILE__) . 'js/ptb-shortcode.js';

        return $plugin_array;
    }

    /**
     * Add new button to the buttons array
     *
     * @param  Array $buttons - Array of buttons
     *
     * @return Array
     */
    public function ptb_register_button($buttons) {
        $buttons[] = $this->plugin_name;
        return $buttons;
    }

    /**
     * Set post type colums
     *
     * @since 1.0.0
     */
    public function ptb_colums($columns) {
        foreach ($columns as $c => $t) {
            if (strpos($c, 'taxonomy-') !== false) {
                $this->columns[$c] = $t;
            }
        }
        if (!empty($this->columns)) {
            add_action('restrict_manage_posts', array($this, 'add_filters'));
        }
        return $columns;
    }

    public function add_filters() {
        if (!empty($this->columns)) {

            $args = array('hide_empty' => 1,
                'hierarchical' => 1,
                'hide_if_empty' => 1,
                'show_count' => 1,
                'value_field' => 'slug'
            );
            foreach ($this->columns as $col => $tax_name) {
                $tax_slug = str_replace('taxonomy-', '', $col);
                $slug = isset($_GET[$tax_slug]) && $_GET[$tax_slug] != -1 ? sanitize_key($_GET[$tax_slug]) : false;
                $args['taxonomy'] = $tax_slug;
                $args['show_option_all'] = sprintf(__('Show All %s', 'ptb'), $tax_name);
                $args['name'] = $args['id'] = $tax_slug;
                $args['selected'] = $slug;
                wp_dropdown_categories($args);
            }
        }
    }

    public function add_sort($columns) {
        foreach ($this->columns as $col => $name) {
            $columns[$col] = $col;
        }
        return array_merge($this->columns, $columns);
    }

    public function ptb_sort_colums($clauses, $wp_query) {
        global $wpdb;
        if (isset($wp_query->query['orderby']) && strpos($wp_query->query['orderby'], 'taxonomy-') !== false) {
            $slug = str_replace('taxonomy-', '', $wp_query->query['orderby']);
            if ($this->options->has_custom_taxonomy($slug)) {
                if (strpos($clauses['join'], 'JOIN ' . $wpdb->term_relationships . ' ON (wp_posts.ID = ' . $wpdb->term_relationships . '.object_id)') === false) {
                    $clauses['join'].="LEFT OUTER JOIN {$wpdb->term_relationships} ON {$wpdb->posts}.ID={$wpdb->term_relationships}.object_id ";
                    $clauses['where'] .= "AND (taxonomy = '$slug' OR taxonomy IS NULL)";
                }
                $clauses['join'].="LEFT OUTER JOIN {$wpdb->term_taxonomy} USING (term_taxonomy_id)
                                  LEFT OUTER JOIN {$wpdb->terms} USING (term_id)";
                $clauses['groupby'] = "object_id";
                $clauses['orderby'] = "GROUP_CONCAT({$wpdb->terms}.name ORDER BY name ASC)";
                $clauses['orderby'] .= ( 'ASC' == strtoupper($wp_query->get('order')) ) ? 'ASC' : 'DESC';
            }
        }

        return $clauses;
    }
    
    public function remove_disalog(){
        if (!empty($_REQUEST['type']) && !empty($_REQUEST['slug']) && !empty($_REQUEST['nonce']) && check_ajax_referer('ptb_remove_dialog','nonce', false)) {
            $slug = sanitize_key($_REQUEST['slug']); 
            $include = $_REQUEST['type']==='cpt'?$this->options->has_custom_post_type($slug): $this->options->has_custom_taxonomy($slug);
            if($include){
                include_once( 'partials/ptb-remove-dialog.php' );
            }
        }
        wp_die();
    }
    
    /**
     * Ajax handler of remove/unregister action
     *
     * @since 1.0.0
     */
    public function ptb_remove() {

        if (!empty($_POST['slug']) && !empty($_POST['type']) && check_ajax_referer('ptb_remove_'.$_POST['slug'],'nonce', false)) {
            $result = false;
            $type = sanitize_text_field($_POST['type']);
            $remove_all = !empty($_POST['remove']) && $_POST['remove']==='1';
            $slug = sanitize_key($_POST['slug']);
            $page = $this->plugin_name.'-'.$type;
            if($type==='cpt'){
                $result = $remove_all?$this->options->remove_custom_post_type($slug):$this->options->unregister_custom_post_type($slug);
            }
            elseif($type==='ctx'){
                $result = $remove_all?$this->options->remove_custom_taxonomy($slug):$this->options->unregister_custom_taxonomy($slug);
            }
            $data = array();
            if($result && $this->options->update()){
                $data['success'] = 1;
                $data['data'] = $remove_all?add_query_arg(array('slug'=>$slug,'action'=>'delete','page'=>$page),admin_url('admin.php')):add_query_arg(array('page'=>$page),admin_url('admin.php'));

            }
            else{
                 $data['data'] = __('Something goes wrong, please try again','ptb');
            }
            
            echo wp_json_encode($data);
        }
        wp_die();
    }
    
    /**
     * Ajax handler of reegister/unregister action
     *
     * @since 1.0.0
     */
    public function ptb_register() {

        if (!empty($_REQUEST['type']) && !empty($_REQUEST['slug']) && !empty($_REQUEST['nonce']) && check_ajax_referer('ptb_register','nonce', false)) {
            $result = false;
            $type = sanitize_text_field($_REQUEST['type']);
            $slug = sanitize_key($_REQUEST['slug']);
            if($type==='cpt'){
                $result = $this->options->is_custom_post_type_registered($slug)?$this->options->unregister_custom_post_type($slug):$this->options->register_custom_post_type($slug);
            }
            elseif($type==='ctx'){
                $result = $this->options->is_custom_taxonomy_registered($slug)?$this->options->unregister_custom_taxonomy($slug):$this->options->register_custom_taxonomy($slug);
            }
            if($result && $this->options->update()){
                echo wp_json_encode(array('data'=>add_query_arg(array('page'=>  $this->plugin_name.'-'.$type),admin_url('admin.php'))));
            }
           
        }
        wp_die();
    }
    
    /**
     * Ajax handler of copyaction
     *
     * @since 1.0.0
     */
    public function ptb_copy() {

        if (!empty($_REQUEST['type']) && !empty($_REQUEST['slug']) && !empty($_REQUEST['nonce']) && check_ajax_referer('ptb_'.$_REQUEST['type'].'_copy','nonce', false)) {
            $type = sanitize_text_field($_REQUEST['type']);
            if($type==='cpt' || $type==='ctx'){
                $slug = $old_slug = sanitize_key($_REQUEST['slug']);
                $i = 1;
                $result = false;
                $data = $type==='cpt'?$this->options->get_custom_post_type($slug):$this->options->get_custom_taxonomy($slug);
                if ($data) {
                    while (true) {
                        $slug = $slug . '-' . $i;
                        if (($type==='cpt' && !$this->options->has_custom_post_type($slug)) || ($type==='ctx' && !$this->options->has_custom_taxonomy($slug))) {
                            break;
                        }
                        $slug = $old_slug;
                        $i++;
                    }
                    $data->slug = $data->id = $slug;
                    if($type==='cpt'){
                        $this->options->add_custom_post_type($data);
                    }
                    else{
                        $this->options->add_custom_taxonomy($data);
                    }
                    $result = true;
                }
                if($result && $this->options->update()){
                    echo wp_json_encode(array('data'=>add_query_arg(array('slug'=>$slug,'action'=>'copy','page'=>'ptb-'.$type,'old_slug'=>$old_slug),admin_url('admin.php'))));
                }
            }
        }
        wp_die();
    }
    
    /**
     * Get post type layout themplate by type
     * Used from dashboards trough ajax call.
     *
     * @since 1.0.0
     */
    public function ptb_ajax_theme() {

        if (isset($_REQUEST['template']) && $_REQUEST['template'] && current_user_can('manage_options')) {

            $name = $_REQUEST['template'];
            $type = strtoupper($name);
            $class = 'PTB_Form_PTT_' . $type;

            if (class_exists($class) && $_REQUEST[$this->plugin_name . '-ptt']) {

                $themplate_id = $_REQUEST[$this->plugin_name . '-ptt'];
                $them = new $class($this->plugin_name, $this->version, $themplate_id);

                $them->add_settings_section($name);
            }
            wp_die();
        }
    }
    
    

    /**
     * Save post themplate
     * Used from dashboards trough ajax call.
     *
     * @since 1.0.0
     */
    public function ptb_ajax_theme_save() {
        if (check_ajax_referer($this->plugin_name . '_them_ajax', $this->plugin_name . '_nonce', true) && current_user_can('manage_options')) {
            $themplate_id = $_REQUEST[$this->plugin_name . '-' . PTB_Form_PTT_Them::$key];
            $them = new PTB_Form_PTT_Them($this->plugin_name, $this->version, $themplate_id);
            $them->save_themplate($_POST);
        }
    }
    
    /**
     * get post_type data
     * Used from shortcode trough ajax call.
     *
     * @since 1.0.0
     */
    public function ptb_ajax_get_post_type() {
        if(!empty($_POST['post_type'])){
            $res = $this->options->get_shortcode_data($_POST['post_type']);
            $res['meta_exists_tooltip'] = __('Show posts, which have not empty %s','ptb');
            $res['meta_like_start_tooltip'] = __('Show posts, which %s starting with your input value','ptb');
            $res['meta_like_end_tooltip'] = __('Show posts, which %s ending with your input value','ptb');
            if($res){
                echo wp_json_encode($res);
            }
        }
        wp_die();
    }
}

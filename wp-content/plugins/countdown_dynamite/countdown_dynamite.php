<?php

/*
Plugin Name: Countdown Dynamite
Plugin URI: http://countdowndynamite.com
Description: Creates smart countdown timers that  boost your profits.
Author: George Katsoudas
Developer: A. Oliinyk (Pumka.net)
Version: 2.2.6
Author URI: http://www.georgekatsoudas.com
Tested up to: 4.9
*/

/*
Version history:
2.2.6
    fixed H5T-X11-53NR: removed ZF files not compatible with PHP7+
2.2.5
    fixed 8077: expiration text not hidden
2.2.4
    fixed 7408: syntax error in embed code script
2.2.3
    fixed 7302: wrong placement of the above text
    fixed 7302: $ character in extra content created issues
2.2.1
    updated embed code tooltip text
2.2
    fixed: counter align issues
2.1.5
    fixed: counter elements didn't show up properly when switching visibility in
        admin
2.1.4
    fixed 6799: demo version bug
2.1.3
    fixed 6652: demo version bug
2.1.2
    fixed 6557: post editor crash with "once" option
2.1.1
    added: support for plugin updater on multisite
2.1
    added: ability to show content above the countdown
2.0.10
    added: support Forwarded-For header
2.0.9
    added: allow 5 min timeout for "once" option
2.0.8
    added: Google Data compression proxy support
2.0.7
    fixed H9M-TYS-52DT: do not run embed code in AJAX
2.0.6
    fixed 91T-Z5S-PH5W: incorrect IP address detected with CloudFlare
2.0.5
    fixed: Insert Shortcode button not shown for pages
2.0.4
    fixed LXM-5RW-P4B1: incorrect time to string conversion in some
          conditions
2.0.3
    fixed: JS error after countdown expiration
2.0.2
    fixed: multiple embedded countdowns didn't initialize on the same page
2.0.1
    changed: "Show this content below countdown" not required anymore
    fixed: post/page editor values not saved on error
2.0
    added: optionally hide counter after expiration
    added: support for run multiple counters on the same web page
    added: embed code for inserting to static/remote pages
    added: optionally restart countdown after specific duration
1.1.6
    TinyMCE 4.x compatibility
1.1.4
    fixed GMQ-LZL-Q9GS: workaround for PHP crash
1.1.3
    fixed HPU-AGN-2PH5: error if iconv extension not loaded
1.1.2
    fixed BH8-7QR-AH: conflict with OptimizePress theme editor
1.1.1
    fixed: Update button on post/page editor
1.1
    added: AJAX load mode
1.0.11
    CSS fixes for WP v3.8
1.0.10
    add shortcode support to widgets
1.0.9
    fixed 9GY-ZVX-BVG: HTML encoding applied by WP broke JavaScript code
1.0.8
    fixed S1P-Y78-NRS1: clicking redirect URL field moved focus out of it
1.0.7
    force usage of PluginUpdateChecker v1.3
1.0.6
    new update logic for less server load
1.0.5
    new update URL
1.0.4
    added: shortcode support in content fields
1.0.3
    fixed not found locale error
1.0.2
    fixed custom dash labels not applied
1.0.1
    minor fixes
1.0 Initial release
*/

require_once dirname(__FILE__) . '/library/compatibility.php';

//Register plug-in:
Ucd_WpPlugin::init();

class Ucd_WpPlugin
{
//------------------------------------------------------------------------------
// Static members
//------------------------------------------------------------------------------
    const PLUGIN_BASE_URL = 'http://countdowndynamite.com/';
    const AUTHOR_BASE_URL = 'http://www.georgekatsoudas.com/';

    /**
    * Version tag. Used for CSS & JS versioning.
    */
    const VERSION = '02.02.06';

    /**
    * Unique slug
    */
    const SLUG = 'countdown_dynamite';

    /**
    * Product name for auto-update system
    */
    const PRODUCT_NAME = self::SLUG;

    const SHORTCODE_NAME = 'countdown-dynamite';

    /**
    * Unique prefix
    */
    const PREFIX = 'ucd';

    /**
    * User-friendly title
    */
    const TITLE = 'Countdown Dynamite';

    /**
    * Menu item text
    */
    const MENU_LABEL = self::TITLE;

    /**
    * Required capability to access plug-in pages on a single site installation
    */
    const CAPABILITY = 'administrator';

    const COOKIE_BASE_NAME = 'visitor';
    const COOKIE_LIFETIME = 86400000; //1000 days

    static protected $_instance;

    static protected $_pluginBaseUrl;
    static protected $_pluginPath;
    static protected $_tablePrefix;

    static protected $_pendingRedirectUrl;

    static protected $_updater;
    static protected $_updaterCronHook;
    const UPDATE_CHECK_UNTERVAL = 12;

    static public $countdownId = 0;

    /**
    * Registers plug-in module
    */
    static public function init()
    {
        global $wpdb;

        // Auto update
        require dirname(__FILE__) . '/plugin-updates/plugin-update-checker.php';
        self::$_updater = new PluginUpdateChecker_1_3(
            'http://wppluginupdate.com/check/' . self::PRODUCT_NAME . '.json',
            __FILE__,
            self::PRODUCT_NAME,
            0
        );
        self::$_updaterCronHook = 'auto_check_updates-' . self::PRODUCT_NAME;
        add_action(self::$_updaterCronHook, array(self::$_updater, 'checkForUpdates'));

        self::$_pluginBaseUrl = plugin_dir_url(__FILE__);
        self::$_pluginPath = dirname(__FILE__);
        self::$_tablePrefix = $wpdb->prefix . self::PREFIX . '_';

        $class = get_class();

        register_activation_hook(__FILE__, array($class, 'activate'));
        register_deactivation_hook(__FILE__, array($class, 'deactivate'));

        if (is_admin()) {
            // Admin actions
            add_action('admin_init', array($class, 'adminInit'));
            add_action('admin_init', array($class, 'autoCheckUpdates'));
            add_action('admin_menu', array($class, 'adminMenu'));
            add_action('wp_ajax_' . self::PREFIX, array($class, 'adminAjax'));
            add_action('current_screen', array($class, 'adminSendHeaders'));
            add_action('admin_enqueue_scripts', array($class, 'adminEnqueueResources'));
            add_action('save_post', array($class, 'savePost'), 10, 2);
            add_action('media_buttons', array($class, 'mediaButtons'), 11);

            // Front-end AJAX recognized as admin
            add_action('wp_ajax_nopriv_' . self::PREFIX, array($class, 'frontendAjax'));
        } else {
            // Frontend actions
            add_action('init', array($class, 'frontendInit'));
            add_action('wp_enqueue_scripts',  array($class, 'frontendEnqueueResources'));
            add_action('send_headers', array($class, 'frontendSendHeaders'));
            add_action('wp', array($class, 'processPostView'));
            add_action('wp_head', array($class, 'doPendingRedirect'));
            // Support shortcodes in text widgets
            add_filter('widget_text', 'do_shortcode');

            add_shortcode(self::SHORTCODE_NAME, array($class, 'processShortcode'));
            // Backwards compatibility
            add_shortcode('urgency-countdown', array($class, 'processShortcode'));

            //if (!get_option('ucd_ajax_mode')) {
                add_filter('the_content', array($class, 'filterContent'));
            //}
        }

        self::addLibraryIncludePath();

        // Detect an update
        if (self::VERSION != get_option(self::PREFIX . '_version')) {
            self::loadApplication();
            Ucd_Application::getService('Setup')->install();
        }
    }

    static public function activate()
    {
        if (version_compare(PHP_VERSION, '5.0.0', '<')) {
            // Display Error Message
            self::throwInstallError(sprintf('Sorry, but %s requires PHP 5.0 or newer. Your version is %s. Please, ask your web host to upgrade to PHP 5.0.',
                self::TITLE, phpversion()));
        }

        // Requires Wordpress 3.3+
        global $wp_version;
        if (version_compare($wp_version, '3.3', '<' )) {
            self::throwInstallError(sprintf('Sorry, but %s requires Wordpress 3.3 or newer. Your version is %s. Please, upgrade to the latest version of Wordpress.',
                self::TITLE, $wp_version));
        }

        self::loadApplication();
        Ucd_Application::getService('Setup')->install();
    }

    static public function throwInstallError($message)
    {
        if(function_exists('deactivate_plugins')) {
            deactivate_plugins(plugin_basename(__FILE__), true);
        }

        trigger_error($message, E_USER_ERROR);
    }

    /**
    * Plug-in deactivation hook
    */
    static public function deactivate()
    {
    }

    static public function addLibraryIncludePath()
    {
        $includePaths = explode(PATH_SEPARATOR, get_include_path());
        $path = realpath(dirname(__FILE__) . '/library');
        if (!in_array($path, $includePaths)) {
            foreach ($includePaths as $key=>$item) {
                if ('.' == $item) {
                    unset($includePaths[$key]);
                    break;
                }
            }
            array_unshift($includePaths, $path);
            array_unshift($includePaths, '.');
            set_include_path(implode(PATH_SEPARATOR, $includePaths));
        }
    }

    static public function loadApplication()
    {
        self::addLibraryIncludePath();

        // Force en_US locale
        require_once 'UcdZend/Registry.php';
        UcdZend_Registry::set('UcdZend_Locale', 'en_US');

        require_once(self::$_pluginPath . '/application/Application.php');
    }

    static public function processShortcode($params)
    {
        return self::getInstance()->dispatch('shortcode', 'index', $params);
    }

    /**
    * On a single post view records user visit and detects if a redirect needed
    */
    static public function processPostView()
    {
        if (!is_singular()) {
            return;
        }

        $postId = get_the_ID();
        if (!$postId) {
            return;
        }

        if (!get_post_meta($postId, self::PREFIX . '_enabled', TRUE)) {
            return;
        }

        self::loadApplication();
        Ucd_Application::getService('Countdown')->processPostView($postId);
    }

    static public function doPendingRedirect()
    {
        if (!is_null(self::$_pendingRedirectUrl)) {
            $url = self::$_pendingRedirectUrl;
            require dirname(__FILE__) . '/application/views/scripts/frontendRedirect.phtml';
            self::$_pendingRedirectUrl = NULL;
        }
    }

    static public function mediaButtons($editorId)
    {
        $screen = get_current_screen();
        if (!$screen || 'post' != $screen->base || 'content' != $editorId) {
            return;
        }

        self::getInstance()->dispatch('metabox', 'media-buttons');
    }

    static public function frontendInit()
    {
    }

    static public function adminInit()
    {
    }

    static public function autoCheckUpdates()
    {
        $updater = self::$_updater;
        $state = $updater->getUpdateState();

        $shouldCheck = (empty($state)
            || !isset($state->lastCheck)
            || ((time() - $state->lastCheck) >= (self::UPDATE_CHECK_UNTERVAL * 3600)))
            && !wp_next_scheduled(self::$_updaterCronHook);

        if ($shouldCheck) {
            wp_schedule_single_event(time(), self::$_updaterCronHook);
            spawn_cron();
        }
    }

    /**
    * admin_menu hook
    */
    static public function adminMenu()
    {
        self::getInstance()
            ->_registerAdminPages()
            ->_registerMetaBoxes();
    }

    static public function adminAjax()
    {
        self::getInstance()->dispatchAjax();
    }

    static public function frontendAjax()
    {
        $_POST['controller'] = 'frontend-ajax';

        self::getInstance()->dispatchAjax();
    }

    static public function savePost($postId, $post)
    {
        // Ignore new posts, autosave and revisions:
        if (wp_is_post_autosave($postId)
            || wp_is_post_revision($postId)
            || 'auto-draft' == $post->post_status
            || 'trash' == $post->post_status
            || (isset($_POST['action']) && 'autosave' == $_POST['action'])
            || 'nav_menu_item' == $post->post_type
        ) {
            return;
        }
        if (isset($_POST[self::PREFIX . '_metabox'])
            && is_array($_POST[self::PREFIX . '_metabox'])
        ) {
            self::getInstance()->dispatch('metabox', 'save-post', array(
                $postId,
                $_POST[self::PREFIX . '_metabox'],
            ));
        }
    }

    static public function adminSendHeaders()
    {
        global $hook_suffix;

        if (in_array($hook_suffix, array(
            'toplevel_page_' . self::SLUG,
            'post.php',
            'post-new.php'))
        ) {
            // Prevent security issues in Chrome
            header('X-XSS-Protection: 0', TRUE);
        }
    }

    static public function frontendSendHeaders()
    {
        // Tag the visitor with cookie
        $cookieName = self::PREFIX . '_' . self::COOKIE_BASE_NAME;

        if (isset($_COOKIE[$cookieName])
            && self::isValidCookieValue($_COOKIE[$cookieName])
        ) {
            $cookieValue = $_COOKIE[$cookieName];
        } else {
            $cookieValue = uniqid();
            $_COOKIE[$cookieName] = $cookieValue; // for later usage in Ucd_Service_Countdown
        }

        setcookie($cookieName, $cookieValue,
            time() + self::COOKIE_LIFETIME,
            COOKIEPATH, COOKIE_DOMAIN);
    }

    static protected function isValidCookieValue($value)
    {
        return 13 == strlen($value);
    }

    static public function adminEnqueueResources($hook)
    {
        $mainHook = 'toplevel_page_' . self::SLUG;
        /*
        $optionsHook = str_replace('_', '-', self::SLUG) . '_page_'
            . self::SLUG . '-options';
        */

        if (!in_array($hook, array(
            $mainHook,
            'post.php',
            'post-new.php'))
        ) {
            return;
        }

        // Color picker
        wp_enqueue_script('farbtasticmod',
            self::getPluginBaseUrl() . 'js/jquery/farbtasticmod.js',
            array('jquery'),
            '1.3.1');

        // Timepicker
        wp_enqueue_script('timepicker',
            self::getPluginBaseUrl() . 'js/jquery/timepicker.js',
            array('jquery-ui-datepicker'),
            '1.3');
        wp_enqueue_style(self::SLUG . '-jquery-ui-datepicker',
            self::getPluginBaseUrl() . 'css/jquery-ui-datepicker.css',
            array(),
            self::VERSION);

        // Preview
        self::_enqueueWidgetResources();

        // Admin core
        wp_enqueue_script(self::SLUG . '-admin-base',
            self::getPluginBaseUrl() . 'js/admin/base.js',
            array('jquery', 'jquery-ui-core', 'jquery-effects-slide',
                'farbtasticmod', 'jquery-ui-datepicker'),
            self::VERSION);
        wp_enqueue_style(self::SLUG . '-admin',
            self::getPluginBaseUrl() . 'css/admin.css',
            array('farbtastic'),
            self::VERSION);

        // Help
        wp_enqueue_script(self::SLUG . '-help',
            self::getPluginBaseUrl() . 'js/help.js',
            array('jquery'),
            self::VERSION);
        wp_enqueue_style(self::SLUG . '-help',
            self::getPluginBaseUrl() . 'css/help.css',
            array(),
            self::VERSION);

        // Page specific resources
        switch ($hook) {
            case $mainHook:
                wp_enqueue_script(self::SLUG . '-admin-main',
                    self::getPluginBaseUrl() . 'js/admin/main.js',
                    array(self::SLUG . '-admin-base'),
                    self::VERSION);
                break;

            case 'post.php':
            case 'post-new.php':
                wp_enqueue_script(self::SLUG . '-admin-metabox',
                    self::getPluginBaseUrl() . 'js/admin/metabox.js',
                    array(self::SLUG . '-admin-base'),
                    self::VERSION);
                break;
        }

        //$class = get_class();

        // Custom editor styles
        //add_filter('mce_css', array($class, 'editorCss'));
    }

    /**
    * wp_enqueue_scripts hook
    */
    static public function frontendEnqueueResources()
    {
        self::_enqueueWidgetResources();

        wp_enqueue_script(self::SLUG . '-frontend',
            self::getPluginBaseUrl() . 'js/frontend.js',
            array('jquery'),
            self::VERSION);
        wp_localize_script(self::SLUG . '-frontend', self::PREFIX, array(
            'ajaxurl' => admin_url('admin-ajax.php'),
        ));
    }

    static protected function _enqueueWidgetResources()
    {
        wp_enqueue_script(self::SLUG . '-countdown',
            self::getPluginBaseUrl() . 'js/jquery/countdown.js',
            array('jquery'),
            self::VERSION);
        wp_enqueue_style(self::SLUG . '-countdown',
            self::getPluginBaseUrl() . 'css/countdown.css',
            array(),
            self::VERSION);
    }

    static public function filterContent($value)
    {
        self::loadApplication();

        return Ucd_Application::getModel('Block_Widget')
            ->setPostId(get_the_ID())
            ->filterContent($value);
    }

    /**
    * Singleton pattern
    *
    * @return Ucd_WpPlugin Singleton instance
    */
    static public function getInstance()
    {
        if (!self::$_instance) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    static public function getPluginBaseUrl()
    {
        return self::$_pluginBaseUrl;
    }

    static public function getPluginPath()
    {
        return self::$_pluginPath;
    }

    static public function getTablePrefix()
    {
        return self::$_tablePrefix;
    }

    static public function setPendingRedirectUrl($value)
    {
        self::$_pendingRedirectUrl = $value;
    }

//------------------------------------------------------------------------------
// Object members
//------------------------------------------------------------------------------

    /**
    * Register plug-in admin pages and menus
    */
    protected function _registerAdminPages()
    {
        add_menu_page(self::TITLE, self::MENU_LABEL,
            self::CAPABILITY,
            self::SLUG,
            array($this, 'dispatchAdmin'),
            self::getPluginBaseUrl() . 'images/icon-16.png'
        );

        add_submenu_page(self::SLUG, self::TITLE,
            'Admin', self::CAPABILITY,
            self::SLUG,
            array($this, 'dispatchAdmin')
        );

        return $this;
    }

    /**
    * Register plug-in admin metaboxes
    */
    protected function _registerMetaBoxes()
    {
        $postTypes = get_post_types(array('show_ui' => TRUE, 'show_in_nav_menus' => TRUE));
        foreach($postTypes as $type) {
            add_meta_box(self::PREFIX . '_main',
                self::TITLE, array($this, 'dispatch__metabox__index'), $type,
                'normal', 'high');
        }

        return $this;
    }

    /**
    * Intercepts WP requests
    */
    public function __call($method, $arguments)
    {
        if ('dispatch__' == substr($method, 0, 10)) {
            $parts = explode('__', $method);
            $controller = @$parts[1];
            $action = @$parts[2];

            $controller = $this->_prepareMvcName($controller);
            $action = $this->_prepareMvcName($action);

            $this->dispatch($controller, $action, $arguments);
        }
        else {
            throw new Exception("Method {$method} not found.");
        }
    }

    protected function _prepareMvcName($name)
    {
        return str_replace('_', '-', $name);
    }

    /**
    * Dispatches user request to WP Module
    *
    * @param string $controller Controller name
    * @param string $action Action name
    */
    public function dispatch($controller='index', $action='index',
        $params = NULL)
    {
        self::loadApplication();
        return Ucd_Application::dispatch($controller, $action, $params);
    }

    public function dispatchAdmin()
    {
        $controller = @$_GET['controller'];
        if (empty($controller)) {
            $controller = 'admin';
        }
        $action = @$_GET['action'];
        if (empty($action)) {
            $action = 'index';
        }

        $this->dispatch($controller, $action);
    }

    public function dispatchAjax()
    {
        $controller = @$_POST['controller'];
        if (empty($controller)) {
            $controller = 'index';
        }
        $action = @$_POST['controller_action'];
        if (empty($action)) {
            $action = 'index';
        }

        $this->dispatch($controller, $action);

        exit();
    }
}
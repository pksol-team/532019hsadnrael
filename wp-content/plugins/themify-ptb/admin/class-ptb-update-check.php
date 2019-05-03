<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

class PTB_Update_Check {

    var $name;

    /**
     * @var string $nicename Human-readable name of the plugin.
     */
    var $nicename = '';

    /**
     * @var string $nicename_short Human-readable name of the plugin where 'Builder' or other prefixes have been removed.
     */
    var $nicename_short = '';

    /**
     * @var string $update_type Whether this is a 'plugin' update or an 'addon' update.
     */
    var $update_type = '';
    var $version;
    var $versions_url;
    var $package_url;
    private static $prompt = false;

    public function __construct($name, $version, $slug) {
        
        // New name parameter
        $this->name = $name['name'];
        $this->nicename = $name['nicename'];
        $this->update_type = $name['update_type'];
       
        $this->nicename_short = $this->nicename;
       
        $this->version = $version;
        $this->slug = $slug;
        $this->versions_url = 'http://themify.me/versions/versions.xml';
        $this->package_url = "http://themify.me/files/{$this->name}/{$this->name}.zip";
        add_action('admin_notices', array($this, 'check_version'), 3);
        add_action('admin_enqueue_scripts', array($this, 'enqueue'));
        if (!self::$prompt) {
            add_action('admin_footer', array($this, 'prompt'));
            self::$prompt = true;
        }
        if (isset($_GET['action'])) {
            add_action('admin_notices', array($this, 'themify_ptb_updater'), 3);
        }

        if (defined('WP_DEBUG') && WP_DEBUG) {
            delete_transient("{$this->name}_new_update");
            delete_transient("{$this->name}_check_update");
        }

        //Executes themify_updater function using wp_ajax_ action hook
        add_action('wp_ajax_themify_ptb_validate_login', array($this, 'themify_ptb_validate_login'));
    }

    public function prompt() {
        ?>
        <div class="ptb_alert"></div>
        <!-- prompts -->
        <div class="ptb-promt-box">
            <div class="show-login">
                <form id="ptb_update_form" method="post" action="<?php echo admin_url('admin.php?page=ptb-cpt&action=upgrade&login=true'); ?>">
                    <p class="prompt-msg"><?php _e('Enter your Themify login info to upgrade', 'ptb'); ?></p>
                    <p><label><?php _e('Username', 'ptb'); ?></label> <input type="text" name="username" class="username" value=""/></p>
                    <p><label><?php _e('Password', 'ptb'); ?></label> <input type="password" name="password" class="password" value=""/></p>
                    <input type="hidden" value="true" name="login" />
                    <p class="pushlabel"><input name="login" type="submit" value="Login" class="button themify-ptb-upgrade-login" /></p>
                </form>
            </div>
            <div class="show-error">
                <p class="error-msg"><?php _e('There were some errors updating the theme', 'ptb'); ?></p>
            </div>
        </div>
        <div class="ptb_promt_overlay"></div>
        <!-- /prompts -->
        <?php
    }

    public function check_version() {
        $notifications = '';

        // Check update transient
        $current = get_transient("{$this->name}_check_update"); // get last check transient
       
        $timeout = 60;
        $time_not_changed = isset($current->lastChecked) && $timeout > ( time() - $current->lastChecked );
        $newUpdate = get_transient("{$this->name}_new_update"); // get new update transient

        if (is_object($newUpdate) && $time_not_changed) {
            if (version_compare($this->version, $newUpdate->version, '<')) {
                $notifications .= sprintf(__('<p class="update update-nag %s">%s version %s is now available. <a href="%s" title="" class="%s" target="%s" data-plugin="%s" data-package_url="%s" data-nicename_short="%s" data-update_type="%s">Update now</a> or view the <a href="%s" title="" class="ptb_changelogs" target="_blank" data-changelog="%s">changelog</a> for details.</p>', 'ptb'), esc_attr($newUpdate->login), $this->nicename, $newUpdate->version, esc_url($newUpdate->url), esc_attr($newUpdate->class), esc_attr($newUpdate->target), esc_attr($this->slug), esc_attr($this->package_url), esc_attr($this->nicename_short), esc_attr($this->update_type), esc_url('//themify.me/changelogs/' . $this->name . '.txt'), esc_url('//themify.me/changelogs/' . $this->name . '.txt')
                );
                echo '<div class="notifications">' . $notifications . '</div>';
            }
            return;
        }

        // get remote version
        $remote_version = $this->get_remote_version();

        // delete update checker transient
        delete_transient("{$this->name}_check_update");

        $class = "";
        $target = "";
        $url = "#";

        $new = new stdClass();
        $new->login = 'login';
        $new->version = $remote_version;
        $new->url = $url;
        $new->class = 'themify-ptb-upgrade-plugin';
        $new->target = $target;

        if (version_compare($this->version, $remote_version, '<')) {
            set_transient('themify_builder_new_update', $new);
            $notifications .= sprintf(__('<p class="update update-nag %s">%s version %s is now available. <a href="%s" title="" class="%s" target="%s" data-plugin="%s" data-package_url="%s" data-nicename_short="%s" data-update_type="%s">Update now</a> or view the <a href="%s" title="" class="ptb_changelogs" target="_blank" data-changelog="%s">changelog</a> for details.</p>', 'ptb'), esc_attr($new->login), $this->nicename, $new->version, esc_url($new->url), esc_attr($new->class), esc_attr($new->target), esc_attr($this->slug), esc_attr($this->package_url), esc_attr($this->nicename_short), esc_attr($this->update_type), esc_url('//themify.me/changelogs/' . $this->name . '.txt'), esc_url('//themify.me/changelogs/' . $this->name . '.txt')
            );
        }

        // update transient
        $this->set_update();

        echo '<div class="notifications">' . $notifications . '</div>';
    }

    public function get_remote_version() {
        $version = '';

        $response = wp_remote_get($this->versions_url);
        if (is_wp_error($response)) {
            return $version;
        }

        $body = wp_remote_retrieve_body($response);
        if (is_wp_error($body) || empty($body)) {
            return $version;
        }

        $xml = new DOMDocument;
        $xml->loadXML(trim($body));
        $xml->preserveWhiteSpace = false;
        $xml->formatOutput = true;
        $xpath = new DOMXPath($xml);
        $query = "//version[@name='" . $this->name . "']";
        $elements = $xpath->query($query);
        if ($elements->length) {
            foreach ($elements as $field) {
                $version = $field->nodeValue;
            }
        }

        return $version;
    }

    public function set_update() {
        $current = new stdClass();
        $current->lastChecked = time();
        set_transient("{$this->name}_check_update", $current);
    }

    public function is_update_available() {
        $newUpdate = get_transient("{$this->name}_new_update"); // get new update transient

        if (false === $newUpdate) {
            $new_version = $this->get_remote_version($this->name);
        } else {
            $new_version = $newUpdate->version;
        }
        return version_compare($this->version, $new_version, '<');
    }

    public function enqueue() {
        $translation_array = array(
                    'invalid_login' => __('Invalid username or password.<br/>Contact <a target="_blank" href="//themify.me/contact">Themify</a> for login issues.', 'ptb'),
                    'unsuscribed' => __('Your membership might be expired. Login to <a target="_blank" href="//themify.me/member">Themify</a> to check.', 'ptb'),
                );
        wp_localize_script('themify-ptb-upgrader', 'ptb_upgrader', $translation_array);     
        wp_enqueue_script('themify-ptb-upgrader', plugin_dir_url(__FILE__) . 'js/ptb-upgrader.js', array('jquery'), $this->version, true);
    }

    /**
     * Validate login credentials against Themify's membership system
     */
    function themify_ptb_validate_login() {
        $response = wp_remote_post(
            'http://themify.me/files/themify-login.php', array(
            'timeout' => 300,
            'headers' => array(),
            'body' => array(
                'amember_login' => $_POST['username'],
                'amember_pass' => $_POST['password']
            )
                )
        );

        //Was there some error connecting to the server?
        if (is_wp_error($response)) {
            die('Error ' . $response->get_error_code() . ': ' . $response->get_error_message($response->get_error_code()));
        }

        //Connection to server was successful. Test login cookie
        $amember_nr = false;
        foreach ($response['cookies'] as $cookie) {
            if ($cookie->name == 'amember_nr') {
                $amember_nr = true;
            }
        }
        if (!$amember_nr) {
            die('invalid');
        }

        $subs = json_decode($response['body'], true);
        $sub_match = 'false';

        foreach ($subs as $key => $value) {
            if (isset($_POST['nicename_short']) && ( stripos($value['title'], isset($_POST['nicename_short'])) !== false ) || $_POST['nicename_short'] == $value['title']) {
                $sub_match = 'true';
                break;
            }
            if (stripos($value['title'], 'Master Club') !== false || stripos($value['title'], 'PTB Bundle') !== false) {
                $sub_match = 'true';
                break;
            }
        }
        die("$sub_match");
    }

    /**
     * Updater called through wp_ajax_ action
     */
    function themify_ptb_updater() {
     
        $url = isset($_POST['package_url']) ? esc_attr($_POST['package_url']) : null;
        $plugin_slug = isset($_POST['plugin']) ? esc_attr($_POST['plugin']) : null;
        $name = isset($_POST['nicename_short']) ? esc_attr($_POST['nicename_short']) : null;
        if (!$url || !$plugin_slug || !$name)
            return;

        //If login is required
        if ($_GET['login'] === 'true') {

            $response = wp_remote_post(
                    'http://themify.me/files/themify-login.php', array(
                'timeout' => 300,
                'headers' => array(),
                'body' => array(
                    'amember_login' => $_POST['username'],
                    'amember_pass' => $_POST['password']
                )
                    )
            );

            //Was there some error connecting to the server?
            if (is_wp_error($response)) {
                die('Error ' . $response->get_error_code() . ': ' . $response->get_error_message($response->get_error_code()));
            }

            //Connection to server was successful. Test login cookie
            $amember_nr = false;
            foreach ($response['cookies'] as $cookie) {
                if ($cookie->name == 'amember_nr') {
                    $amember_nr = true;
                }
            }
            if (!$amember_nr) {
                _e('You are not a Themify Member.', 'ptb');
                die();
            }

            $subs = json_decode($response['body'], true);
            $sub_match = false;

            foreach ($subs as $key => $value) {
                if (isset($_POST['nicename_short']) && ( stripos($value['title'], isset($_POST['nicename_short'])) !== false ) || $_POST['nicename_short'] == $value['title']) {
                    $sub_match = true;
                    break;
                }
                if (stripos($value['title'], 'Master Club') !== false || stripos($value['title'], 'PTB Bundle') !== false) {
                    $sub_match = true;
                    break;
                }
            }
            if (!$sub_match) {
                _e('Your membership does not include this product.', 'ptb');
                die();
            }
        }

        //remote request is executed after all args have been set
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once plugin_dir_path(__FILE__) . '/class-ptb-upgrader.php';

        $upgrader = new PTB_Upgrader(new Plugin_Upgrader_Skin(
                array(
            'plugin' => $plugin_slug,
            'title' => $name
                )
        ));
        $response_cookies = ( isset($response) && isset($response['cookies']) ) ? $response['cookies'] : '';
        $upgrader->upgrade($plugin_slug, $url, $response_cookies);

        //if we got this far, everything went ok!	
        die();
    }

}

// class end

/**
 * Links to show after update.
 * 
 * @since 1.0.0
 * 
 * @param array $update_actions List of action => link markup for each link to output.
 * @param string $plugin Plugin slug composed of folder and main file.
 */
function themify_ptb_upgrade_complete($update_actions, $plugin) {
    if ($plugin == 'themify-ptb/post-type-builder.php') {
        if (isset($update_actions['themify_complete'])) {
            unset($update_actions['themify_complete']);
        }
        if (isset($update_actions['activate_plugin'])) {
            unset($update_actions['activate_plugin']);
        }
        $update_actions['plugins_page'] = '<a href="' . esc_url(add_query_arg('page', 'ptb-cpt', self_admin_url('admin.php'))) . '" title="' . __('Return to Post Type Builder Settings', 'ptb') . '" target="_parent">' . __('Return to Post Type Builder', 'ptb') . '</a>';
    }
    return $update_actions;
}

add_filter('update_plugin_complete_actions', 'themify_ptb_upgrade_complete', 10, 2);

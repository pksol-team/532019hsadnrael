<?php
class Swift_Performance_Plugin_Organizer {

      public $active_plugins;

      /**
       * Create instance and load rules
       */
      public function __construct(){
            global $swift_performance_plugin_organizer;
            $swift_performance_plugin_organizer = get_option('swift_performance_plugin_organizer', array());

            $rules = (isset($swift_performance_plugin_organizer['rules']) ? array_filter($swift_performance_plugin_organizer['rules']) : array());
            $rules = apply_filters('swift-performance-plugin-rules', $rules);
            if (!empty($rules)){
                  $this->apply_rules($rules);
            }

            add_action('admin_init', array($this, 'save'));
      }

      /**
       * Apply rules
       * @param array ruleset
       */
      public function apply_rules($ruleset){
            if (isset($_GET['subpage']) && $_GET['subpage'] == 'plugin-organizer'){
                  return;
            }
            $this->active_plugins = get_option('active_plugins');
            foreach ($this->active_plugins as $key => $plugin) {
                  $skip = false;

                  // Check exceptions first
                  if (isset($ruleset[$plugin]['exception']) && is_array($ruleset[$plugin]['exception'])){
                        foreach ($ruleset[$plugin]['exception'] as $mode => $rules) {
                              foreach ($rules as $rule){
                                    if (self::check_rule($mode, $rule)){
                                          $skip = true;
                                          break;
                                    }
                              }
                        }
                  }
                  // Skip plugin
                  if ($skip){
                        continue;
                  }

                  // Check disable rules
                  if (isset($ruleset[$plugin]['disable']) && is_array($ruleset[$plugin]['disable'])){
                        foreach ($ruleset[$plugin]['disable'] as $mode => $rules) {
                              foreach ($rules as $rule){
                                    if (self::check_rule($mode, $rule)){
                                          unset($this->active_plugins[$key]);
                                          break;
                                    }
                              }
                        }
                  }
            }

            add_filter('option_active_plugins', array($this, 'active_plugins'));
      }

      /**
       * Filter active plugins
       */
      public function active_plugins(){
            return $this->active_plugins;
      }

      /**
       * Save settings
       */
      public function save(){
            if (!isset($_POST['swift-save-plugin-organizer-nonce']) || !wp_verify_nonce($_POST['swift-save-plugin-organizer-nonce'], 'swift-save-plugin-organizer') || !current_user_can('manage_options')){
                  return;
            }

            global $swift_performance_plugin_organizer;

            $rules = (isset($_POST['plugin-organizer-rules']) ? array_filter($_POST['plugin-organizer-rules']) : array());
            $swift_performance_plugin_organizer['rules'] = $rules;
            update_option('swift_performance_plugin_organizer', $swift_performance_plugin_organizer);
            Swift_Performance::early_loader();
      }

      public static function check_rule($mode, $rule){
            switch ($mode) {
                  case 'frontend':
                        return !is_admin();
                        break;
                  case 'url-match':
                        if (substr($rule,0,1) == '#' && substr($rule,strlen($rule)-1) == '#'){
                              if (preg_match($rule, parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))){
                                    return true;
                              }
                        }
                        else {
                              if (strpos(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), $rule) !== false){
                                    return true;
                              }
                        }
                        break;
                  case 'admin':
                        if (is_admin()){
                              $disabled_pages = array();
                              foreach ($rule as $pages){
                                    $disabled_pages = array_merge($disabled_pages, explode(',', $pages));
                              }
                              if (!empty($disabled_pages)){
                                    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                                    $pagenow = (!preg_match('~\.php$~', $path) ? 'index.php' : basename($path));
            				if (in_array($pagenow, $disabled_pages)){
            					return true;
            				}
            			}
                        }
                        return false;
                        break;
                  case 'ajax':
                        return (defined('DOING_AJAX') && DOING_AJAX);
                        break;
                  case 'ajax-action':
                        return (defined('DOING_AJAX') && DOING_AJAX && isset($_REQUEST['action']) && $_REQUEST['action'] == $rule);
                        break;
                  case 'user-role':
                        foreach ($rule as $role){
                              if ($role == 'not-logged-in' && !Swift_Performance::is_user_logged_in()){
                                    return true;
                              }
                              else if ($role == 'logged-in' && Swift_Performance::is_user_logged_in()){
                                    return true;
                              }
                              else {
                                    if (isset($_COOKIE[LOGGED_IN_COOKIE]) && !empty($_COOKIE[LOGGED_IN_COOKIE])){
                                          @list($login, ) = explode('|', $_COOKIE[LOGGED_IN_COOKIE]);
                                          if (!empty($login)){
                                                global $wpdb;
                                                $roles = maybe_unserialize($wpdb->get_var($wpdb->prepare("SELECT meta_value FROM {$wpdb->usermeta} LEFT JOIN {$wpdb->users} ON user_id = ID WHERE user_login = %s AND meta_key LIKE '{$wpdb->prefix}capabilities'", $login)));
                                                if ( isset($roles[$role]) ) {
                                                    return true;
                                                }
                                          }
                                    }
                              }
                        }
                        return false;
                        break;
                  case 'query-string':
                        if (substr($rule,0,1) == '#' && substr($rule,strlen($rule)-1) == '#'){
                              if (preg_match($rule, parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY))){
                                    return true;
                              }
                        }
                        else {
                              if (strpos(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $rule) !== false){
                                    return true;
                              }
                        }
                        break;
                  case 'cookie':
                        if (!isset($_COOKIE)){
                              return false;
                        }
                        foreach ((array)$_COOKIE as $key => $value){
                              $cookie = "{$key}={$value}";
                              if (is_string($cookie) && substr($rule,0,1) == '#' && substr($rule,strlen($rule)-1) == '#'){
                                    if (preg_match($rule, $cookie)){
                                          return true;
                                    }
                              }
                              else {
                                    if (is_string($cookie) && strpos($cookie, $rule) !== false){
                                          return true;
                                    }
                              }
                        }
                        break;
                  case 'desktop':
                        return (Swift_Performance::check_option('mobile-support', 1) || Swift_Performance::check_option('enable-caching', 1, '!=')) && !Swift_Performance_Plugin_Organizer::is_mobile();
                  case 'mobile':
                        return (Swift_Performance::check_option('mobile-support', 1) || Swift_Performance::check_option('enable-caching', 1, '!=')) && Swift_Performance_Plugin_Organizer::is_mobile();
            }
      }

      /**
       * Get rule modes
       * @return array
       */
      public static function get_rule_modes(){
            return array('frontend','url-match','admin','ajax', 'ajax-action','user-role','query-string','cookie','desktop','mobile');
      }

      /**
       * Get flatted plugin rules array
       * @param string $slug
       * @return array
       */
      public static function get_rules($slug){
            global $swift_performance_plugin_organizer;
            $rules = array();
            if (isset($swift_performance_plugin_organizer['rules'][$slug])){
                  foreach (self::get_rule_modes() as $mode) {
                        $rules = self::get_rule_objects($slug, $mode, $rules);
                  }
            }
            return $rules;
      }


      /**
       * Get rule object for plugin for given mode
       * @param string $slug
       * @param string $mode
       * @param array $rules
       * @return array
       */
      public static function get_rule_objects($slug, $mode, $rules){
            global $swift_performance_plugin_organizer;
            // Exceptions
            if (isset($swift_performance_plugin_organizer['rules'][$slug]['exception'][$mode])){
                  foreach ((array)$swift_performance_plugin_organizer['rules'][$slug]['exception'][$mode] as $rule){
                        $rules[] = array(
                              'slug'      => $slug,
                              'type'      => 'exception',
                              'mode'      => $mode,
                              'rule'      => $rule
                        );
                  }
            }

            // Disable rules
            if (isset($swift_performance_plugin_organizer['rules'][$slug]['disable'][$mode])){
                  foreach ((array)$swift_performance_plugin_organizer['rules'][$slug]['disable'][$mode] as $rule){
                        $rules[] = array(
                              'slug'      => $slug,
                              'type'      => 'disable',
                              'mode'      => $mode,
                              'rule'      => $rule
                        );
                  }
            }
            return $rules;
      }

      /**
       * Get plugin rules
       * @param string $slug
       * @return array
       */
      public static function get_formatted_rule($rule, $id = "%RANDID%"){
            $html = '';
            $is_editable = !in_array($rule['mode'], array('frontend', 'ajax', 'desktop', 'mobile'));
            $html .= ($rule['type'] == 'exception' ? '<i class="fa fa-check"></i>' : '<i class="fa fa-ban"></i>');
            $html .= '<label>'.self::get_formatted_mode_name($rule['mode']).'</label>';
            if ($is_editable){
                  $html .= '<div class="rule-summary">'.(is_array($rule['rule']) ? implode(', ', array_map(array(__CLASS__, 'get_rule_label'), $rule['rule'])) : $rule['rule']).'</div>';
            }
            $html .= self::get_editor($rule, $id);
            if ($is_editable){
                  $html .= '<div class="swift-button-container swift-plugin-rule-actions"><a href="#" class="cancel-editing swift-btn swift-btn-gray">' . esc_html__('Cancel', 'swift-performance') . '</a> <a href="#" class="swift-performance-edit-plugin-rule swift-btn swift-btn-green">' . esc_html__('Save changes', 'swift-performance') . '</a></div>';
            }
            $html .= '<ul class="action-container">';
            if ($is_editable){
                  $html .= '<li><a href="#" class="swift-performance-edit-plugin-rule"><i class="fa fa-pencil"></i></a></li>';
            }
            $html .= '<li><a href="#" class="swift-performance-remove-plugin-rule"><i class="fa fa-times"></i></a></li>';
            $html .= '</ul>';

            return $html;
      }

      /**
       * Get mode formatted name
       */
      public static function get_formatted_mode_name($mode){
            switch ($mode) {
                  case 'frontend':
                        return esc_html__('Frontend', 'swift-performance');
                        break;
                  case 'url-match':
                        return esc_html__('URL Match', 'swift-performance');
                        break;
                  case 'admin':
                        return esc_html__('Admin pages', 'swift-performance');
                        break;
                  case 'ajax':
                        return esc_html__('All AJAX request', 'swift-performance');
                        break;
                  case 'ajax-action':
                        return esc_html__('AJAX Action', 'swift-performance');
                        break;
                  case 'user-role':
                        return esc_html__('User Role', 'swift-performance');
                        break;
                  case 'query-string':
                        return esc_html__('Query String', 'swift-performance');
                        break;
                  case 'cookie':
                        return esc_html__('Cookie', 'swift-performance');
                        break;
                  case 'desktop':
                        return esc_html__('Desktop', 'swift-performance');
                        break;
                  case 'mobile':
                        return esc_html__('Mobile', 'swift-performance');
                        break;
                  default:
                        return esc_html($mode);
                        break;
            }
      }

      /**
       * Get the admin pages array
       * @return array
       */
      public static function get_admin_pages(){
            return array(
                   'index.php'                                            => esc_html__('Dashboard', 'swift-performance'),
                   'edit.php'                                             => esc_html__('Posts/Pages List Table', 'swift-performance'),
                   'post.php,post-new.php'                                => esc_html__('Posts/Pages Editor', 'swift-performance'),
                   'upload.php,media-new.php'                             => esc_html__('Media', 'swift-performance'),
                   'edit-comments.php,comment.php'                        => esc_html__('Comments', 'swift-performance'),
                   'nav-menus.php'                                        => esc_html__('Menus', 'swift-performance'),
                   'widgets.php'                                          => esc_html__('Widgets', 'swift-performance'),
                   'theme-editor.php,plugin-editor.php'                   => esc_html__('Theme/Plugin Editor', 'swift-performance'),
                   'users.php,user-new.php,user-edit.php,profile.php'     => esc_html__('Users', 'swift-performance'),
                   'tools.php'                                            => esc_html__('Tools', 'swift-performance'),
                   'options-general.php'                                  => esc_html__('Settings', 'swift-performance'),
            );
      }

      /**
       * Get rule translated label based on key
       * @param string $key
       * @return string
       */
      public static function get_rule_label($key){
            global $wp_roles;

            $admin_pages      = self::get_admin_pages();
            $roles            = $wp_roles->get_names();
            if (isset($admin_pages[$key])){
                  return $admin_pages[$key];
            }
            else if (isset($roles[$key])){
                  return $roles[$key];
            }
            return $key;
      }

      /**
       * Get editor for rule
       * @param array $mode
       * @return string generated HTML
       */
      public static function get_editor($rule, $id){
            switch ($rule['mode']) {
                  case 'frontend':
                  case 'desktop':
                  case 'mobile':
                  case 'ajax':
                        return '<input type="hidden" name="plugin-organizer-rules['.esc_attr($rule['slug']).']['.esc_attr($rule['type']).']['.esc_attr($rule['mode']).']['.$id.']" value="1">';
                        break;
                  case 'admin':
                        foreach (self::get_admin_pages() as $key => $name){
                              $options[] = '<option value="'.esc_attr($key).'" '.(in_array($key, (array)$rule['rule']) ? ' selected="selected"' : '').'>'.esc_html($name).'</option>';
                        }
                        return '<select multiple name="plugin-organizer-rules['.esc_attr($rule['slug']).']['.esc_attr($rule['type']).']['.esc_attr($rule['mode']).']['.$id.'][]">'.implode("\n",$options).'</select>';
                        break;
                  case 'user-role':
                        $options   = array();
                        $options[] = '<option value="not-logged-in"'.(in_array('not-logged-in', (array)$rule['rule']) ? ' selected="selected"' : '').'>'.esc_html__('Not logged in', 'swift-performance').'</option>';
                        $options[] = '<option value="logged-in"'.(in_array('logged-in', (array)$rule['rule']) ? ' selected="selected"' : '').'>'.esc_html__('Logged in', 'swift-performance').'</option>';
                        global $wp_roles;
            	 	foreach ($wp_roles->get_names() as $key => $name){
                              $options[] = '<option value="'.esc_attr($key).'" '.(in_array($key, (array)$rule['rule']) ? ' selected="selected"' : '').'>'.esc_html($name).'</option>';
                        }
                        return '<select multiple name="plugin-organizer-rules['.esc_attr($rule['slug']).']['.esc_attr($rule['type']).']['.esc_attr($rule['mode']).']['.$id.'][]">'.implode("\n",$options).'</select>';
                        break;
                  case 'ajax-action':
                  case 'url-match':
                  case 'query-string':
                  case 'cookie':
                  default:
                        return '<input type="text" name="plugin-organizer-rules['.esc_attr($rule['slug']).']['.esc_attr($rule['type']).']['.esc_attr($rule['mode']).']['.$id.']" value="'.esc_attr($rule['rule']).'">';
                        break;
            }
      }

      /**
       * Bypass wp_is_mobile function for early use
       * Test if the current browser runs on a mobile device (smart phone, tablet, etc.)
       *
       * @return bool
       */
      public static function is_mobile() {
      	if ( empty($_SERVER['HTTP_USER_AGENT']) ) {
      		$is_mobile = false;
      	} elseif ( strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false // many mobile devices (all iPhone, iPad, etc.)
      		|| strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false
      		|| strpos($_SERVER['HTTP_USER_AGENT'], 'Silk/') !== false
      		|| strpos($_SERVER['HTTP_USER_AGENT'], 'Kindle') !== false
      		|| strpos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') !== false
      		|| strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false
      		|| strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mobi') !== false ) {
      			$is_mobile = true;
      	} else {
      		$is_mobile = false;
      	}

      	return $is_mobile;
      }

}

return new Swift_Performance_Plugin_Organizer();

?>

<?php

/**
 * Luv Framework Fields Class
 */

class Luv_Framework_Option_Fields extends Luv_Framework_Fields {

      public $ajax_save_endpoint = 'luv_framework_save_options';

      public function __construct($args){
            $this->args = apply_filters('luv_framework_meta_args',Luv_Framework::extend($args, array(
                  'option_name'     => '',
                  'menu'            => 'luv-framework',
                  'submenu'         => '',
                  'menu_title'      => 'Luv Framework',
                  'page_title'      => 'Luv Framework',
                  'capability'      => 'manage_options',
                  'icon'            => '',
                  'position'        => 0,
                  'sections'        => array(),
                  'layout'          => 'left',
                  'ajax'            => false,
                  'exim'            => true
            )));

            $this->option_name = apply_filters('luv_framework_option_name', sanitize_key($this->args['option_name']));

            parent::__construct($args);

            if ($this->args['exim']){
                  $this->args['sections']['luv-export-import'] = array(
                        'title' => esc_html__('Export/Import', 'swift-performance'),
      			'icon' => 'fas fa-sync',
      			'subsections' => array(
      				'export' => array(
      					'title' => esc_html__('Export', 'swift-performance'),
      					'fields' => array(
      						array(
      							'id'         => 'export-settings',
      							'type'       => 'export',
                                                'non-stored' => true
      						),
                                    )
                              ),
                              'import' => array(
      					'title' => esc_html__('Import', 'swift-performance'),
      					'fields' => array(
      						array(
      							'id'         => 'import-settings',
      							'type'       => 'import',
                                                'non-stored' => true
      						),
                                    )
                              )
                        )
                  );
            }

            // Create menu page
            add_action('admin_menu', function(){
                  if (empty($this->args['submenu'])){
                        add_menu_page($this->args['page_title'], $this->args['menu_title'], $this->args['capability'], $this->args['menu'], array($this, 'render'), $this->args['icon'], $this->args['position']);
                  }
                  else {
                        add_submenu_page($this->args['menu'], $this->args['page_title'], $this->args['menu_title'], $this->args['capability'], $this->args['submenu'], array($this, 'render'));
                  }
            });

            // Create global options array
            if (!empty($this->option_name)){
                  if (apply_filters('luv_framework_get_options',true, $this)){
                        if (!did_action('init')){
                              add_action('init', array($this, 'get_options'), 1);
                        }
                        else {
                              $this->get_options();
                        }
                  }
            }


            add_action('admin_init', array($this, 'save_options'));
            add_action('wp_ajax_luv_framework_save_options', array($this, 'save_options'));
            add_action('wp_ajax_luv_framework_import', array($this, 'import_options'));
            add_action('admin_enqueue_scripts', array($this, 'pre_enqueue_assets'));

            parent::$instances['options'][$this->option_name] = $this;
      }

      public function pre_enqueue_assets($hook){
            $menu = (isset($this->args['submenu']) ? $this->args['submenu'] : $this->args['menu']);
            if (apply_filters('luv_framework_enqueue_assets', site_url($_SERVER['REQUEST_URI']) == menu_page_url($menu, false), $hook, $this)) {
                  // Enqueue styles
                  $this->enqueue_styles();

                  // Enqueue scripts
                  add_action('admin_footer', array($this, 'enqueue_scirpts'), 0);

                  // Print modal
                  add_action('admin_footer', array($this, 'print_modal'));
            }
      }

      public function get_options(){
            if (!isset($GLOBALS[$this->option_name])){
                  $GLOBALS[$this->option_name] = get_option($this->option_name, array());
            }
            $this->prepare_fields();

            return $GLOBALS[$this->option_name];
      }

      public function get_defaults(){
            $fields = array();

            foreach ((array)$this->args['sections'] as $section){
                  if (isset($section['fields'])){
                        foreach ((array)$section['fields'] as $field){
                              $fields[] = $field;
                        }
                  }

                  if (isset($section['subsections'])){
                        foreach ((array)$section['subsections'] as $subsection){
                              if (isset($subsection['fields'])){
                                    foreach ((array)$subsection['fields'] as $field) {
                                          $fields[] = $field;
                                    }
                              }
                        }
                  }
            }

            foreach ($fields as $field) {
                  // Set value
                  $options[$field['id']] = (isset($field['default']) ? $field['default'] : NULL);
            }

            return $options;
      }

      public function prepare_fields(){
            // Run only if fields array is empty
            if (!empty($this->fields)){
                  return;
            }

            $new_option = false;
            $fields     = array();

            foreach ((array)$this->args['sections'] as $section){
                  if (isset($section['fields'])){
                        foreach ((array)$section['fields'] as $field){
                              $fields[] = $field;
                        }
                  }

                  if (isset($section['subsections'])){
                        foreach ((array)$section['subsections'] as $subsection){
                              if (isset($subsection['fields'])){
                                    foreach ((array)$subsection['fields'] as $field) {
                                          $fields[] = $field;
                                    }
                              }
                        }
                  }
            }

            foreach ($fields as $field) {
                  // Ignore fields with empty id
                  if (empty($field['id'])){
                        continue;
                  }

                  // Ignore fields with already used id
                  if (isset($this->fields[$field['id']])){
                        continue;
                  }

                  $this->fields[$field['id']] = $field;

                  // Skip non-stored fields
                  if (isset($field['non-stored']) && $field['non-stored']){
                        continue;
                  }

                  // Set value
                  if (!empty($this->option_name)){
                        if (array_key_exists($field['id'], $GLOBALS[$this->option_name])){
                              $this->fields[$field['id']]['value'] = $GLOBALS[$this->option_name][$field['id']];
                        }
                        else {
                              $new_option = true;
                              $this->fields[$field['id']]['value'] = $GLOBALS[$this->option_name][$field['id']] = (isset($field['default']) ? $field['default'] : NULL);
                        }
                  }
                  else {
                        $this->fields[$field['id']]['value'] = (Luv_Framework_Option_Fields::option_exists($this->prefix . $field['id']) ? get_option($this->prefix . $field['id']) : (isset($field['default']) ? $field['default'] : NULL));
                  }
            }

            if ($new_option){
                  update_option($this->option_name, $GLOBALS[$this->option_name]);

                  do_action('luv_framework_' . $this->option_name .  '_saved', $GLOBALS[$this->option_name], $this);
                  do_action('luv_framework_options_saved', $GLOBALS[$this->option_name], $this);
            }
      }

      public function render(){
            if (apply_filters('luv_framework_render_options', true, $this)){

                  $page_title = $this->args['page_title'];

                  do_action('luv_framework_before_framework_outer');

                  echo '<div class="luv-framework-outer">';

                  include LUV_FRAMEWORK_PATH . 'templates/tpl.header.php';

                  $this->fieldset_head = '<input type="hidden" name="luv_framework_option_name" value="'.$this->option_name.'">'.
                  wp_nonce_field('luv-framework-options', 'luv_framework_nonce['.sanitize_title($this->args['menu'] . $this->args['submenu']).']', true, false);

                  // Print sections
                  $this->render_sections();

                  echo '</div>';
            }
      }

      /**
       * Save options
       * @param int $post_id
       */
      public function save_options($post_id){
            // Skip if it is not save action
            if (!isset($_POST['luv-action']) || $_POST['luv-action'] != 'save'){
                  return;
            }

            // Check the user's permissions.
            if (!current_user_can($this->args['capability'])){
                  wp_send_json(array(
                        'result'     => 'error',
                        'message'   => __('You don\'t have permission for this action.', 'luv-framework')
                  ));
            }

            // Verify meta box nonce
            if (!wp_verify_nonce($_POST['luv_framework_nonce'][sanitize_title($this->args['menu'] . $this->args['submenu'])], 'luv-framework-options')){
                  wp_send_json(array(
                        'result'     => 'error',
                        'message'   => __('Session expired. Please refresh the page.', 'luv-framework')
                  ));
            }

            do_action('luv_framework_validate_fields', $this);

            if (!empty($this->option_name)){
                  $options = $this->get_options();
                  foreach ($this->defined_fields as $field){
                        $value = (isset($_POST[$this->prefix . $field['id']]) ? stripslashes_deep($_POST[$this->prefix . $field['id']]) : '');
                        if (is_string($options[$field['id']]) && is_string($value) && $value == md5($options[$field['id']])){
                              continue;
                        }

                        if (!$this->has_error($field)){
                              if ($options[$field['id']] != $value){
                                    /**
                                     * Action called when field was changed
                                     * @param mixed $old_value
                                     * @param mixed $new_value
                                     */
                                    do_action('luv_framework_field_changed_' . $field['id'], $options[$field['id']], $value);
                              }
                              $options[$field['id']] = $value;
                        }

                        // Remove empty elements from array
                        if ((!isset($field['keep_empty']) || $field['keep_empty'] == false) && is_array($options[$field['id']])){
                              $options[$field['id']] = array_filter($options[$field['id']]);
                        }
                  }
                  update_option($this->option_name, $options);

                  do_action('luv_framework_' . $this->option_name .  '_saved', $options, $this);
                  do_action('luv_framework_options_saved', $options, $this);
            }
            else {
                  foreach ($this->defined_fields as $field){
                        $key = $this->prefix . $field['id'];
                        $value = (isset($_POST[$key]) ? $_POST[$key] : '');

                        if ($value == md5(get_option($key))){
                              continue;
                        }

                        // Remove empty elements from array
                        if (isset($field['filter_empty']) && $field['filter_empty'] && is_array($value)){
                              $value = array_filter($value);
                        }

                        $autoload = (!isset($field['autoload']) ? true : $field['autoload']);
                        update_option($key, $value, $autoload);
                  }
            }

            // Send response
            if (!empty($this->validation_issues)){
                  wp_send_json(array(
                        'result'    => 'error',
                        'issues'    => $this->validation_issues,
                        'message'   => esc_html__('Settings saved. Some fields has validation issues.', 'luv-framework')
                  ));
            }
            else {
                  wp_send_json(array(
                        'result'    => 'success',
                        'message'   => esc_html__('Settings saved.', 'luv-framework')
                  ));
            }

      }

      public function import_options(){
            // Check the user's permissions.
            if (!current_user_can($this->args['capability'])){
                  wp_send_json(array(
                        'result'     => 'error',
                        'message'   => __('You don\'t have permission for this action.', 'luv-framework')
                  ));
            }

            // Verify meta box nonce
            if (!wp_verify_nonce($_POST['nonce'], 'luv-framework-fields-ajax')){
                  wp_send_json(array(
                        'result'     => 'error',
                        'message'   => __('Session expired. Please refresh the page.', 'luv-framework')
                  ));
            }

            $options    = $this->get_defaults();
            $imported   = json_decode(stripslashes_deep($_POST['settings']), true);

            if (!is_array($imported)){
                  wp_send_json(array(
                        'result'     => 'error',
                        'message'   => __('Uploaded file was corrupted.', 'luv-framework')
                  ));
            }

            foreach ($imported as $key => $value){
                  $options[$key] = $value;
            }

            $options = apply_filters('luv_framework_import_options', $options);

            update_option($this->option_name, $options);

            do_action('luv_framework_' . $this->option_name . '_import');
            do_action('luv_framework_import');

            do_action('luv_framework_' . $this->option_name .  '_saved', $options, $this);
            do_action('luv_framework_options_saved', $options, $this);

            wp_send_json(array(
                  'result'    => 'success',
                  'message'   => __('Settings was imported successfully.', 'luv-framework')
            ));

            die;
      }

      /**
       * Check if option exists
       * @param string $option_name
       * @return boolean
       */
      public static function option_exists($option_name){
            global $wpdb;
            return ($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name = %s", $option_name)) > 0);
      }
}

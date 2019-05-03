<?php

/**
 * Luv Framework Fields Class
 */

class Luv_Framework_Fields {

      // Static array hold instances
      public static $instances = array();

      // Unique id for links
      public $unique_id;

      // Field prefix
      public $prefix;

      // Arguments for fieldset
      public $args = array();

      // Fields for generating metabox
      public $fields = array();

      // Fieldset head
      public $fieldset_head = '';

      // Fields for saving metabox
      public $defined_fields = array();

      // Array containing errors/warnings
      public $validation_issues = array();

      // Dependencies array
      public $dependencies = array();

      // Contains used codemirror modules
      public $codemirror = array();

      // Is Font Awesome used
      public $fontawesome = false;

      public function __construct($args){
            do_action('luv_framework_before_fields_init', $this);

            $this->unique_id  = 'lf' . hash('crc32', serialize($this->args));
            $this->prefix     = apply_filters('luv_framework_field_prefix', '_luv_');
            $this->classes    = array('luv-framework-container ' . $this->args['layout']);

            if (isset($this->args['class'])){
                  $this->classes[] = $this->args['class'];
            }

            if (!isset($this->args['sections']) || empty($this->args['sections'])){
                  return;
            }

            foreach ($this->args['sections'] as $key => $section){
                  $this->args['sections'][$key]['fields'] = (isset($this->args['sections'][$key]['fields']) ? $this->args['sections'][$key]['fields'] : array());

                  $this->defined_fields = array_merge($this->defined_fields, $this->args['sections'][$key]['fields']);

                  if (isset($section['subsections'])){
                        foreach ((array)$section['subsections'] as $subsection) {
                              if (isset($subsection['fields'])){
                                    $this->defined_fields = array_merge($this->defined_fields, $subsection['fields']);
                              }

                              if (isset($subsection['icon']) && !preg_match('~\.(jpe?g|gif|png|svg)~', $subsection['icon']) && preg_match('~fa-~', $subsection['icon'])){
                                    $this->fontawesome = true;
                              }
                        }
                  }

                  if (isset($section['icon']) && !preg_match('~\.(jpe?g|gif|png|svg)~', $section['icon']) && preg_match('~fa-~', $section['icon'])){
                        $this->fontawesome = true;
                  }
            }

            // Collect used plugins and libraries
            foreach ((array)$this->defined_fields as $defined_field){
                  if (isset($defined_field['type']) && $defined_field['type'] == 'editor' && isset($defined_field['mode'])){
                        $mode = Luv_Framework::sanitize_file_name($defined_field['mode']);
                        $this->codemirror[$mode] = $mode;
                  }
            }

            // Validate fields
            add_action('luv_framework_validate_fields', array($this, 'validate_fields'));

            // Export
            add_action('admin_init', function(){
                  if (isset($_GET['luv-action'])){
                        if ($_GET['luv-action'] == 'export'){
                              header('Content-disposition: attachment; filename=swift-performance-settings.json');
                              header('Content-type: application/json');
                              wp_send_json(apply_filters('luv_framework_export_array', $this->get_options()));
                        }
                  }
            });

            do_action('luv_framework_after_fields_init', $this);
      }

      /**
       * Render sections
       */
      public function render_sections(){
            $headers = $body = array();

            do_action('luv_framework_before_render_sections', $this);

            $has_active = false;
            foreach ((array)$this->args['sections'] as $id => $section) {
                  // Subsections
                  if (isset($section['subsections'])){
                        $classes = $sub_body = $sub_headers = array();
                        $is_section_hidden = true;
                        $first_sub_section_id = '';

                        // Custom classes
                        if (isset($section['class'])){
                              $classes = array_merge($classes, (array)$section['class']);
                        }

                        foreach ((array)$section['subsections'] as $sid => $subsection) {
                              $sub_classes = array('subsection');
                              $is_subsection_hidden = true;

                              if (!isset($subsection['fields'])){
                                    continue;
                              }

                              // Custom classes
                              if (isset($subsection['class'])){
                                    $sub_classes = array_merge($sub_classes, (array)$subsection['class']);
                              }

                              ob_start();
                              $body_inner = '';
                              foreach ((array)$subsection['fields'] as $field) {
                                    $is_field_hidden = $this->render_field($this->fields[$field['id']]);
                                    if ($is_field_hidden === false){
                                          $is_section_hidden = $is_subsection_hidden = false;
                                    }
                              }
                              $body_inner = ob_get_clean();

                              if ($is_subsection_hidden){
                                    $sub_classes[] = 'luv-hidden';
                              }
                              else if (!$has_active){
                                    $classes[] = 'active';
                                    $sub_classes[] = 'active';
                                    $has_active = true;
                              }

                              $classes[] = 'has-child';

                              // Subsection icon
                              $icon = '';
                              if (isset($subsection['icon'])){
                                    if (preg_match('~\.(jpe?g|gif|png|svg)~', $subsection['icon'])){
                                          $icon = '<span class="luv-framework-section-icon"><img src="'.esc_attr($subsection['icon']).'"></span>';
                                    }
                                    else {
                                          $icon = '<span class="luv-framework-section-icon"><i class="'.esc_attr($subsection['icon']).'"></i></span>';
                                    }
                              }

                              $first_sub_section_id = (empty($first_sub_section_id) ? $id . '-' . $sid : $first_sub_section_id);

                              $sub_body[] = '<li class="luv-framework-tab '.implode(' ', array_unique($sub_classes)).'" id="' . $this->unique_id . '-' . esc_attr($id . '-' . $sid) . '" data-parent="' . $this->unique_id . '-' . esc_attr($id) . '">' . $body_inner . '</li>';
                              $sub_headers[] = '<li id="' . $this->unique_id . '-' . esc_attr($id . '-' . $sid) . '-header" class="'.implode(' ', array_unique($sub_classes)).'"><a class="luv-framework-tab-title" href="#' . $this->unique_id . '-' . esc_attr($id . '-' . $sid) . '">' . $icon . esc_html($subsection['title']) . '</a></li>';

                        }

                        // Section icon
                        $icon = '';
                        if (isset($section['icon'])){
                              if (preg_match('~\.(jpe?g|gif|png|svg)~', $section['icon'])){
                                    $icon = '<span class="luv-framework-section-icon"><img src="'.esc_attr($section['icon']).'"></span>';
                              }
                              else {
                                    $icon = '<span class="luv-framework-section-icon"><i class="'.esc_attr($section['icon']).'"></i></span>';
                              }
                        }

                        $body[] = '<li class="luv-framework-tab '.implode(' ', array_unique($classes)).'" id="' . $this->unique_id . '-' . esc_attr($id) . '"><ul>' . implode('',$sub_body) . '</ul></li>';
                        $headers[] = '<li id="' . $this->unique_id . '-' . esc_attr($id) . '-header" class="'.implode(' ', array_unique($classes)).'"><a class="luv-framework-tab-title" href="#' . $this->unique_id . '-' . esc_attr($first_sub_section_id) . '">' . $icon . esc_html($section['title']) . '</a><ul>'.implode('', $sub_headers).'</ul></li>';
                  }
                  else {
                        $classes = array();
                        $is_section_hidden = true;

                        ob_start();
                        $body_inner = '';
                        foreach ((array)$section['fields'] as $field) {
                              $is_field_hidden = $this->render_field($this->fields[$field['id']]);
                              if ($is_field_hidden === false){
                                    $is_section_hidden = false;
                              }
                        }
                        $body_inner = ob_get_clean();


                        if ($is_section_hidden){
                              $classes[] = 'luv-hidden';
                        }
                        else if (!$has_active){
                              $classes[] = 'active';
                              $has_active = true;
                        }

                        // Section icon
                        $icon = '';
                        if (isset($section['icon'])){
                              if (preg_match('~\.(jpe?g|gif|png|svg)~', $section['icon'])){
                                    $icon = '<span class="luv-framework-section-icon"><img src="'.esc_attr($section['icon']).'"></span>';
                              }
                              else {
                                    $icon = '<span class="luv-framework-section-icon"><i class="'.esc_attr($section['icon']).'"></i></span>';
                              }
                        }

                        $body[] = '<li class="luv-framework-tab '.implode(' ', $classes).'" id="' . $this->unique_id . '-' . esc_attr($id) . '">' . $body_inner . '</li>';
                        $headers[] = '<li id="' . $this->unique_id . '-' . esc_attr($id) . '-header" class="'.implode(' ', $classes).'"><a class="luv-framework-tab-title" href="#' . $this->unique_id . '-' . esc_attr($id) . '">' . $icon . esc_html($section['title']) . '</a></li>';
                  }
            }

            echo '<div id="fieldset-' . esc_attr($this->unique_id) . '" class="'.apply_filters('luv-framework-container-classes', implode(' ', $this->classes), $this->args, $this).'">';
            echo $this->fieldset_head;
            echo '<ul class="' . apply_filters('luv-framework-section-header-classes','luv-framework-section-header', $this->args, $this).'">';
            echo implode("\n", $headers);
            echo '</ul>';
            echo '<ul class="' . apply_filters('luv-framework-section-body-classes','luv-framework-section-body', $this->args, $this).'">';
            echo implode("\n", $body);
            echo '</ul>';
            echo '</div>';

            do_action('luv_framework_after_render_sections', $this);
      }

      /**
       * Render field
       * @param array $field
       * @return boolean is field hidden
       */
      public function render_field($field){

            do_action('luv_framework_render_field', $field, $this);

            $field = Luv_Framework::extend($field, array(
                  'type' => 'text',
            ));

            $prefix           = $this->prefix;
            $classes          = array('luv-framework-field-container');
            $multi_field      = in_array($field['type'], array('multi-text', 'checkbox')) || ($field['type'] == 'dropdown' && isset($field['multiple']) && $field['multiple'] == true);
            $name             = $prefix . $field['id'] . ( $multi_field ? '[]' : '');

            // Check dependency
            $hidden = $this->is_field_hidden($field);

            // Add hidden class
            if ($hidden){
                  $classes[] = 'luv-hidden';
            }

            // Add custom classes
            if (isset($field['class'])){
                  $classes = array_merge((array)$classes, (array)$field['class']);
            }

            $classes          = apply_filters('luv-framework-field-classes', $classes, $field);
            $label            = (isset($field['title']) ? $field['title'] : '');
            $description      = (isset($field['desc']) ? $field['desc'] : '');
            $info             = (isset($field['info']) ? $field['info'] : '');
            $default          = isset($field['default']) ? Luv_Framework_Fields::b64_json_encode(($multi_field ? (array)$field['default'] : $field['default'])) : '';

            switch ($field['type']) {
                  case 'text':
                        $file = trailingslashit(apply_filters('luv_framework_templates_dir', LUV_FRAMEWORK_PATH . 'templates')) . 'tpl.text.php';
                        break;
                  case 'hidden':
                        $file = trailingslashit(apply_filters('luv_framework_templates_dir', LUV_FRAMEWORK_PATH . 'templates')) . 'tpl.hidden.php';
                        break;
                  case 'license':
                        $file = trailingslashit(apply_filters('luv_framework_templates_dir', LUV_FRAMEWORK_PATH . 'templates')) . 'tpl.license.php';
                        break;
                  case 'multi-text':
                        $file = trailingslashit(apply_filters('luv_framework_templates_dir', LUV_FRAMEWORK_PATH . 'templates')) . 'tpl.multi-text.php';
                        break;
                  case 'key-value':
                        $file = trailingslashit(apply_filters('luv_framework_templates_dir', LUV_FRAMEWORK_PATH . 'templates')) . 'tpl.key-value.php';
                        break;
                  case 'number':
                        $file = trailingslashit(apply_filters('luv_framework_templates_dir', LUV_FRAMEWORK_PATH . 'templates')) . 'tpl.number.php';
                        break;
                  case 'slider':
                        $file = trailingslashit(apply_filters('luv_framework_templates_dir', LUV_FRAMEWORK_PATH . 'templates')) . 'tpl.slider.php';
                        break;
                  case 'email':
                        $file = trailingslashit(apply_filters('luv_framework_templates_dir', LUV_FRAMEWORK_PATH . 'templates')) . 'tpl.email.php';
                        break;
                  case 'checkbox':
                        $file = trailingslashit(apply_filters('luv_framework_templates_dir', LUV_FRAMEWORK_PATH . 'templates')) . 'tpl.checkbox.php';
                        break;
                  case 'radio':
                        $file = trailingslashit(apply_filters('luv_framework_templates_dir', LUV_FRAMEWORK_PATH . 'templates')) . 'tpl.radio.php';
                        break;
                  case 'switch':
                        $file = trailingslashit(apply_filters('luv_framework_templates_dir', LUV_FRAMEWORK_PATH . 'templates')) . 'tpl.switch.php';
                        break;
                  case 'dropdown':
                        $file = trailingslashit(apply_filters('luv_framework_templates_dir', LUV_FRAMEWORK_PATH . 'templates')) . 'tpl.dropdown.php';
                        break;
                  case 'textarea':
                        $file = trailingslashit(apply_filters('luv_framework_templates_dir', LUV_FRAMEWORK_PATH . 'templates')) . 'tpl.textarea.php';
                        break;
                  case 'editor':
                        $mode = isset($field['mode']) ? Luv_Framework::sanitize_file_name($field['mode']) : 'text';
                        $file = trailingslashit(apply_filters('luv_framework_templates_dir', LUV_FRAMEWORK_PATH . 'templates')) . 'tpl.editor.php';
                        break;
                  case 'export':
                        $file = trailingslashit(apply_filters('luv_framework_templates_dir', LUV_FRAMEWORK_PATH . 'templates')) . 'tpl.export.php';
                        break;
                  case 'import':
                        $file = trailingslashit(apply_filters('luv_framework_templates_dir', LUV_FRAMEWORK_PATH . 'templates')) . 'tpl.import.php';
                        break;
                  case 'custom':
                        $file = trailingslashit(apply_filters('luv_framework_templates_dir', LUV_FRAMEWORK_PATH . 'templates')) . 'tpl.custom.php';
                        break;
                  default:
                        $file = apply_filters('luv_framework_default_field_template', trailingslashit(apply_filters('luv_framework_templates_dir', LUV_FRAMEWORK_PATH . 'templates')) . 'tpl.text.php', $field);
            }

            include apply_filters('luv_framework_'.$field['type'].'_field_template', $file, $field);

            return $hidden;
      }

      /**
       * Check is the given field hidden
       * @param array $field
       * @return boolean
       */
      public function is_field_hidden($field){
            $hidden = false;
            $prefix           = $this->prefix;
            $classes          = array('luv-framework-field-container');
            $multi_field      = in_array($field['type'], array('multi-text', 'checkbox')) || ($field['type'] == 'dropdown' && isset($field['multiple']) && $field['multiple'] == true);
            $name             = $prefix . $field['id'] . ( $multi_field ? '[]' : '');

            if (isset($field['required'])){
                  // Force array of arrays
                  if (!is_array($field['required'][0])){
                        $field['required'] = array($field['required']);
                  }

                  // Loop through on dependencies
                  foreach ($field['required'] as $dependency){
                        // Skip if dependency field is not defined
                        if (!isset($this->fields[$dependency[0]])){
                              continue;
                        }

                        // Add depencency to dependency array
                        $dependency_multi_field = in_array($this->fields[$dependency[0]]['type'], array('multi-text', 'checkbox')) || ($this->fields[$dependency[0]]['type'] == 'dropdown' && isset($this->fields[$dependency[0]]['multiple']) && $this->fields[$dependency[0]]['multiple'] == true);
                        $dependency_name        = $prefix . $this->fields[$dependency[0]]['id'] . ($dependency_multi_field ? '[]' : '');

                        $this->dependencies[$prefix . $field['id']][] = array(
                              'field' => $dependency_name,
                              'condition' => $dependency[1],
                              'value' => (isset($dependency[2]) ? $dependency[2] : NULL)
                        );

                        if (doing_action('luv_framework_render_field')){
                              $value = $this->fields[$dependency[0]]['value'];
                        }
                        else {
                              $value = (isset($_POST[$this->prefix . $this->fields[$dependency[0]]['id']]) ? stripslashes_deep($_POST[$this->prefix . $this->fields[$dependency[0]]['id']]) : NULL);
                        }

                        if ((isset($this->fields[$dependency[0]]['hidden']) && $this->fields[$dependency[0]]['hidden'] === true) || !Luv_Framework_Fields::check_dependency($value, $dependency[1], (isset($dependency[2]) ? $dependency[2] : NULL) )){
                              $hidden = true;
                              $this->fields[$field['id']]['hidden'] = true;
                        }
                  }
            }

            return $hidden;
      }

      /**
       * Validate fields
       */
      public function validate_fields(){
            $options = $this->get_options();
            foreach ($this->defined_fields as $field){

                  // Check dependency
                  $hidden = $this->is_field_hidden($field);

                  $value = (isset($_POST[$this->prefix . $field['id']]) ? $_POST[$this->prefix . $field['id']] : '');
                  if (is_string($options[$field['id']]) && is_string($value) && $value == md5($options[$field['id']])){
                        continue;
                  }

                  // Validate field
                  $result = true;
                  if (!$hidden && isset($field['validate_callback']) && is_callable($field['validate_callback'])){
                        $result = $field['validate_callback']($result, $value);
                  }

                  $result = apply_filters('luv_framework_validate_field_' . $field['id'], $result, $value);

                  // Add warning first to validation issues
                  if (is_array($result) && isset($result['warning'])){
                        $this->validation_issues[$field['id']] = array(
                              'type'      => 'warning',
                              'value'     => $result['warning']
                        );
                  }
                  // Add error to validation issues last (let override warnings if error occured)
                  if (is_array($result) && isset($result['error'])){
                        $this->validation_issues[$field['id']] = array(
                              'type'      => 'error',
                              'value'     => $result['error']
                        );
                  }

            }
      }

      /**
       * Check is validation error exists for the given field
       */
      public function has_error($field){
            if (isset($this->validation_issues[$field['id']]) && $this->validation_issues[$field['id']]['type'] == 'error'){
                  return true;
            }
            return false;
      }

      /**
       * Enqueue scripts
       */
      public function enqueue_scirpts(){
            wp_enqueue_script('luv-framework-fields', LUV_FRAMEWORK_URL . 'assets/js/fields.js', array(), false, true);
            wp_enqueue_script('luv-framework-modal', LUV_FRAMEWORK_URL . 'assets/js/modal.js', array(), false, true);
            wp_enqueue_script('luv-framework-nouislider', LUV_FRAMEWORK_URL . 'assets/js/nouislider.min.js', array(), false, true);
            wp_enqueue_script('wp-pointer');
            wp_enqueue_script('select2', LUV_FRAMEWORK_URL . 'assets/js/select2.min.js', array(), false, true);
            wp_localize_script('luv-framework-fields', 'luv_framework_fields', array('prefix' => $this->prefix, 'ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('luv-framework-fields-ajax'), 'dependencies' => $this->dependencies));
            if (!empty($this->codemirror)){
                  wp_enqueue_script('codemirror', LUV_FRAMEWORK_URL . 'plugins/codemirror/codemirror.js', array(), false, true);
                  foreach ($this->codemirror as $cm){
                        wp_enqueue_script('codemirror-' . $cm, LUV_FRAMEWORK_URL . 'plugins/codemirror/mode/'.$cm.'/'.$cm.'.js', array(), false, true);
                  }
            }
      }

      /**
       * Enqueue styles
       */
      public function enqueue_styles(){
            wp_enqueue_style('luv-framework-fields', LUV_FRAMEWORK_URL . 'assets/css/fields.css');
            wp_enqueue_style('luv-framework-modal', LUV_FRAMEWORK_URL . 'assets/css/modal.css');
            wp_enqueue_style('luv-framework-nouislider', LUV_FRAMEWORK_URL . 'assets/css/nouislider.min.css');
            wp_enqueue_style('wp-pointer');
            wp_enqueue_style('select2', LUV_FRAMEWORK_URL . 'assets/css/select2.min.css');
            wp_enqueue_style('font-awesome-5', LUV_FRAMEWORK_URL . 'assets/icons/fa5/css/all.min.css');

            if (!empty($this->codemirror)){
                  wp_enqueue_style('codemirror', LUV_FRAMEWORK_URL . 'plugins/codemirror/codemirror.css');
                  wp_enqueue_style('codemirror-theme', LUV_FRAMEWORK_URL . 'plugins/codemirror/theme/monokai.css');
            }
      }

      /**
       * Print modal container
       */
      public function print_modal(){
            include trailingslashit(apply_filters('luv_framework_templates_dir', LUV_FRAMEWORK_PATH . 'templates')) . 'tpl.modal.php';
      }

      /**
       * Base64 encode json
       * @param string $value
       * @return string
       */
      public static function b64_json_encode($value){
            return base64_encode(json_encode($value));
      }

      /**
       * Check depencency condition against field value
       * @param string $field
       * @param string $condition
       * @param string $value
       */
      public static function check_dependency($field, $condition, $value = NULL){
            $condition = strtoupper($condition);
            switch ($condition) {
                  case 'NOT_EMPTY':
                        return ($field !== '');
                  case 'EQUAL':
                  case 'EQ':
                  case '=':
                  case '==':
                        return ($field == $value);
                  case '===':
                        return ($field === $value);
                  case 'NOT_EQUAL':
                  case 'NE':
                  case '!=':
                        return ($field != $value);
                  case '!==':
                        return ($field !== $value);
                  case 'CONTAINS':
                        return (strpos($field, $value) !== false);
                  case 'IN':
                        return (in_array($value, (array)$field));
                  case 'NOT_IN':
                        return (!in_array($value, (array)$field));
            }
            return false;
      }
}

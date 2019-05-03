<?php

/**
 * Luv Framework Fields Class
 */

class Luv_Framework_Meta_Fields extends Luv_Framework_Fields {

      public $ajax_save_endpoint = 'luv_framework_save_meta';

      public function __construct($args){
            $this->args = apply_filters('luv_framework_meta_args',Luv_Framework::extend($args, array(
                  'post_type' => array('page', 'post'),
                  'name'      => 'Luv Framework',
                  'context'   => 'normal',
                  'priority'  => 'high',
                  'sections'  => array(),
                  'layout'    => 'left',
                  'ajax'      => false
            )));

            // Force post types to be an array
            $this->args['post_types'] = (is_array($this->args['post_type']) ? $this->args['post_type'] : preg_split('~,(\s*)?~', $this->args['post_type']));

            // Add extra class for metabox
            foreach ($this->args['post_types'] as $post_type){
                  add_filter('postbox_classes_' . sanitize_key($post_type) . '_' . sanitize_key($this->args['name']), array($this, 'meta_box_classes'));
            }

            parent::__construct($args);

            add_action('add_meta_boxes', array($this, 'add_meta_box'));
            add_action('save_post', array($this, 'save_post_meta'));
            add_action('wp_ajax_luv_framework_save_meta', array($this, 'save_post_meta'));
            add_action('admin_enqueue_scripts', array($this, 'pre_enqueue_assets'), 0);
      }

      public function pre_enqueue_assets(){
            global $pagenow, $post;
            if (in_array($pagenow, array('post.php', 'post-new.php')) && is_object($post) && property_exists($post, 'post_type') && in_array( $post->post_type, $this->args['post_types'])) {
                  add_action('admin_footer', array($this, 'enqueue_scirpts'));
                  add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
            }
      }

      /**
       * Add extra class to meta box
       * @param array $classes
       * @return array
       */
      public function meta_box_classes($classes){
            $classes[] = 'luv-framework-outer';
            return apply_filters('luv_framework_meta_box_classes', $classes);
      }

      public function add_meta_box($post_type){
            $post_types = (is_array($this->args['post_type']) ? $this->args['post_type'] : preg_split('~,(\s*)?~', $this->args['post_type']));
            if (in_array( $post_type, $post_types )) {
                  add_meta_box(sanitize_title($this->args['name']), $this->args['name'], array($this, 'render_meta_box'), $post_type, $this->args['context'], $this->args['priority']);
            }
      }

      public function render_meta_box($post){
            include LUV_FRAMEWORK_PATH . 'templates/tpl.header.php';

            $this->fieldset_head = '<input type="hidden" name="luv_framework_post_id" value="'.$post->ID.'">' .
            wp_nonce_field('luv-framework-meta', 'luv_framework_nonce['.sanitize_title($this->args['name']).']', true, false);

            if (isset($this->args['meta_key'])){
                  $meta = get_post_meta($post->ID, $this->args['meta_key'], true);
            }

            $fields = array();

            foreach ((array)$this->args['sections'] as $section){
                  foreach ((array)$section['fields'] as $field){
                        $fields[] = $field;
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
                        return;
                  }

                  // Ignore fields with already used id
                  if (isset($this->fields[$field['id']])){
                        return;
                  }

                  $this->fields[$field['id']] = $field;

                  if (isset($this->args['meta_key'])){
                        $this->fields[$field['id']]['value'] = (isset($meta[$field['id']]) ? $meta[$field['id']] : (isset($field['default']) ? $field['default'] : NULL));
                  }
                  else {
                        $this->fields[$field['id']]['value'] = (metadata_exists('post', $post->ID, $this->prefix . $field['id']) ? get_post_meta($post->ID, $this->prefix . $field['id'], true) : (isset($field['default']) ? $field['default'] : NULL));
                  }
            }

            $this->render_sections();
      }

      public function save_post_meta($post_id){
            // Handle ajax requests
            if (empty($post_id) && isset($_POST['luv_framework_post_id'])){
                  $post_id = $_POST['luv_framework_post_id'];
            }

            // Return if post_id empty
            if (empty($post_id)){
                  return;
            }

            // Check the user's permissions.
            if (!current_user_can('edit_post', $post_id)){
                  return;
            }

            // Verify meta box nonce
            if (!isset($_POST['luv_framework_nonce'][sanitize_title($this->args['name'])]) || !wp_verify_nonce($_POST['luv_framework_nonce'][sanitize_title($this->args['name'])], 'luv-framework-meta')){
                  return;
            }

            // return if autosave
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){
                  return;
            }

            if (isset($this->args['meta_key'])){
                  $meta = (array)get_post_meta($post_id, $this->args['meta_key'], true);
                  foreach ($this->defined_fields as $field){
                        $meta[$field['id']] = (isset($_POST[$this->prefix . $field['id']]) ? $_POST[$this->prefix . $field['id']] : '');

                        // Remove empty elements from array
                        if (isset($field['filter_empty']) && $field['filter_empty'] && is_array($meta[$field['id']])){
                              $meta[$field['id']] = array_filter($meta[$field['id']]);
                        }
                  }
                  update_post_meta($post_id, $this->args['meta_key'], $meta);
            }
            else {
                  foreach ($this->defined_fields as $field){
                        $key = $this->prefix . $field['id'];
                        $value = (isset($_POST[$key]) ? $_POST[$key] : '');

                        // Remove empty elements from array
                        if (isset($field['filter_empty']) && $field['filter_empty'] && is_array($value)){
                              $value = array_filter($value);
                        }

                        update_post_meta($post_id, $key, $value);
                  }
            }
      }
}

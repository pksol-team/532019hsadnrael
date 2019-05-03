<?php
/**
 * Custom meta box class of type Textarea
 *
 * @link       http://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/includes
 */

/**
 * Custom meta box class of type Textarea
 *
 *
 * @package    PTB
 * @subpackage PTB/includes
 * @author     Themify <ptb@themify.me>
 */
class PTB_CMB_Textarea extends PTB_CMB_Base {



    /**
     * Adds the custom meta type to the plugin meta types array
     *
     * @since 1.0.0
     *
     * @param array $cmb_types Array of custom meta types of plugin
     *
     * @return array
     */
    public function filter_register_custom_meta_box_type($cmb_types) {
        $cmb_types[$this->get_type()] = array(
            'name' => __('Textarea', 'ptb')
        );
        return $cmb_types;
    }

    /**
     * @param string $id the id template
     * @param array $languages
     */
    public function action_template_type($id, array $languages) {
        ?>
        <div class="ptb_cmb_input_row">
            <label for="<?php echo $id; ?>_default_value" class="ptb_cmb_input_label">
                <?php _e("Default Value", 'ptb'); ?>
            </label>
            <div class="ptb_cmb_input">
                <?php if (count($languages) > 1): ?>
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
                            <input type="text" id="<?php echo $id; ?>_default_value_<?php echo $code ?>"/>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="ptb_cmb_input_row">
            <label for="<?php echo $id; ?>_editor" class="ptb_cmb_input_label">
                <?php _e("Use Html Editor", 'ptb'); ?>
            </label>
            <div class="ptb_cmb_input">
                <input type="checkbox" id="<?php echo $id; ?>_editor" name="<?php echo $id; ?>_editor" value="1"/>
            </div>
        </div>
        <?php
    }

    /**
     * Renders the meta boxes for themplates
     *
     * @since 1.0.0
     *
     * @param string $id the metabox id
     * @param string $type the type of the page(Arhive or Single)
     * @param array $args Array of custom meta types of plugin
     * @param array $data saved data
     * @param array $languages languages array
     */
    public function action_them_themplate($id, $type, $args, $data = array(), array $languages = array()) {
        
    }

    /**
     * Renders the meta boxes  in public
     *
     * @since 1.0.0
     *
     * @param array $args Array of custom meta types of plugin
     * @param array $data themplate data
     * @param string $meta_data post data
     * @param string $lang language code
     */
    public function action_public_themplate(array $args, array $data, $meta_data, $lang = false, $is_single = false) {
       echo self::format_text($meta_data[$args['key']],$is_single);
    }

    public function render_post_type_meta($post, $meta_key, $args) {

        $value = get_post_meta($post->ID, 'ptb_' . $meta_key, true);
        if (!$value) {
            $value = $this->get_default_value($post->ID, $meta_key, $args['defaultValue']);
        }
        ?>
        <?php if (isset($args['editor']) && $args['editor']): ?>
            <?php wp_editor($value, 'ptb_' . $meta_key, array('textarea_name' => $meta_key, 'drag_drop_upload' => true, 'media_buttons' => TRUE)); ?>
        <?php else: ?>
            <textarea id="<?php echo $meta_key; ?>" name="<?php echo $meta_key; ?>" rows="5" cols="40"><?php echo esc_textarea($value); ?></textarea>
        <?php endif; ?>
        <?php
    }

}

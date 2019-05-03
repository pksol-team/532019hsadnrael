<?php
/**
 * Custom meta box class of type Radio Button
 *
 * @link       http://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/includes
 */

/**
 * Custom meta box class of type Radio Button
 *
 *
 * @package    PTB
 * @subpackage PTB/includes
 * @author     Themify <ptb@themify.me>
 */
class PTB_CMB_Radio_Button extends PTB_CMB_Base {

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
            'name' => __('Radio Button', 'ptb')
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
            <label for="<?php echo $id; ?>_options" class="ptb_cmb_input_label">
                <?php _e("Options", 'ptb'); ?>
            </label>
            <fieldset class="ptb_cmb_input">
                <ul id="<?php echo $id; ?>_options_wrapper" class="ptb_cmb_options_wrapper">

                    <li class="<?php echo $id; ?>_option_wrapper ptb_cmb_option">
                        <span class="ti-split-v ptb_cmb_option_sort"></span>
                        <?php if (count($languages) > 1): ?>
                            <ul class="ptb_language_tabs">
                                <?php foreach ($languages as $code => $lng): ?>
                                    <li <?php if (isset($lng['selected'])): ?>class="ptb_active_tab_lng"<?php endif; ?>>
                                        <a class="ptb_lng_<?php echo $code ?>" title="<?php echo $lng['name'] ?>" href="#"></a></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <ul class="ptb_language_fields">
                            <?php foreach ($languages as $code => $lng): ?>
                                <li <?php if (isset($lng['selected'])): ?>class="ptb_active_lng"<?php endif; ?>>
                                    <input name="<?php echo $id; ?>_options_<?php echo $code ?>[]" type="text"/>&nbsp;&nbsp;
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <input type="radio" name="<?php echo $id; ?>_default_selected" class="ptb_cmb_option_default_selected"/>
                        <span  class="<?php echo $id; ?>_default_selected_label ptb_cmb_option_default_selected_label"><?php _e('Default Selected', 'ptb') ?></span>
                        <span class="<?php echo $id; ?>_remove remove ti-close"></span>
                    </li>
                </ul>
                <div id="<?php echo $id; ?>_add_new" class="ptb_cmb_option_add">
                    <span class="ti-plus"></span>
                    <?php _e("Add new", 'ptb'); ?>
                </div>
            </fieldset>
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
     * @param boolean $is_single single page
     */
    public function action_public_themplate(array $args, array $data, $meta_data, $lang = false, $is_single = false) {
        if ($meta_data && !empty($args['options'])) {
            foreach ($args['options'] as $opt) {
                if (isset($meta_data[$args['key']]) && $opt['id'] == $meta_data[$args['key']]) {
                    $this->get_text($opt[$lang], 'radio_button');
                    break;
                }
            }
        }
    }

    /**
     * Renders the meta boxes on post edit dashboard
     *
     * @since 1.0.0
     *
     * @param WP_Post $post
     * @param string $meta_key the same as meta box internal id
     * @param array $args
     */
    public function render_post_type_meta($post, $meta_key, $args) {

        $wp_meta_key = sprintf('%s_%s', $this->get_plugin_name(), $meta_key);
        $value = get_post_meta($post->ID, $wp_meta_key, true);
        ?>
        <fieldset>
            <?php
            foreach ($args['options'] as $option) {
                $label = PTB_Utils::get_label($option);
                ?>
                <label>
                    <input type="radio" id="<?php echo $option['id']; ?>" name="<?php echo $meta_key; ?>"
                           value="<?php echo $option['id']; ?>" <?php empty($value) ? checked($option['selected'], true) : checked($option['id'], $value); ?>>
                    <span><?php echo $label; ?></span>
                </label>
                <?php
            }
            ?>
        </fieldset>
        <?php
    }

}

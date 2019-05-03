<?php
/**
 * Custom meta box class of type Link Button
 *
 * @link       http://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/includes
 */

/**
 * Custom meta box class of type Link Button
 *
 *
 * @package    PTB
 * @subpackage PTB/includes
 * @author     Themify <ptb@themify.me>
 */
class PTB_CMB_Link_Button extends PTB_CMB_Base {

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
            'name' => __('Link Button', 'ptb')
        );

        return $cmb_types;
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
        $name = sprintf('%s[]', $meta_key);
        ?>
        <div class="ptb_table_row">
            <input name="<?php echo $name; ?>" type="text" value="<?php echo!empty($value) ? $value[0] : '' ?>" class="ptb_table_cell ptb_post_cmb_link_button_text"/>
            <div class="ptb_table_cell ptb_table_fill">
                <div class="ptb_table ptb_table_fill">
                    <span class="ptb_table_cell ptb_post_cmb_link_button_link_label"><?php _e('Link', 'ptb') ?><span class="ti-arrow-right"></span></span>
                    <input name="<?php echo $name; ?>" type="text" value="<?php echo!empty($value) ? $value[1] : '' ?>"/>
                </div>
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
        $pluginame = $this->get_plugin_name();
        $colors = array(
            'white' => __('White', 'ptb'),
            'yellow' => __('Yellow', 'ptb'),
            'orange' => __('Orange', 'ptb'),
            'blue' => __('Blue', 'ptb'),
            'green' => __('Green', 'ptb'),
            'red' => __('Red', 'ptb'),
            'black' => __('Black', 'ptb'),
            'purple' => __('Purple', 'ptb'),
            'gray' => __('Gray', 'ptb'),
            'light-yellow' => __('Light-yellow', 'ptb'),
            'light-green' => __('Light-green', 'ptb'),
            'pink' => __('Pink', 'ptb'),
            'lavender' => __('Lavender', 'ptb')
        );
        $size = array(
            'small' => __('Small', 'ptb'),
            'normal' => __('Normal', 'ptb'),
            'medium' => __('Medium', 'ptb'),
            'large' => __('Large', 'ptb'),
            'xlarge' => __('Xlarge', 'ptb')
        );
        $styles = array(
            'flat' => __('Flat', 'ptb'),
            'rect' => __('Rect', 'ptb'),
            'rounded' => __('Rounded', 'ptb'),
            'embossed' => __('Embossed', 'ptb'),
            'outline' => __('Outline', 'ptb'),
            'none' => __('None', 'ptb')
        );
        $links = array('lightbox' => __('Lightbox', 'ptb'), 'new_window' => __('New Window'), '0' => __('Same window', 'ptb'));
        ?>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>_link_bgcolor"><?php _e('Button Color', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input">
                <div class="ptb_custom_select">
                    <select class="ptb_maybe_disabled" id="ptb_<?php echo $id ?>_link_bgcolor" name="[<?php echo $id ?>][color]">
                        <?php foreach ($colors as $color => $name): ?>
                            <option class="shortcode ptb_link_button <?php echo $color ?>" <?php if (isset($data['color']) && $data['color'] === $color): ?>selected="selected"<?php endif; ?> value="<?php echo $color ?>"><?php echo $name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php _e('OR', 'ptb') ?>
                <input class="ptb_color_picker ptb_maybe_disabled" type="text" name="[<?php echo $id ?>][custom_color]" <?php if (isset($data['custom_color']) && $data['custom_color']): ?>data-value="<?php echo $data['custom_color'] ?>"<?php endif; ?> />
            </div>
        </div>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>_link_size"><?php _e('Font Size', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input">
                <div class="ptb_custom_select">
                    <select id="ptb_<?php echo $id ?>_link_size" name="[<?php echo $id ?>][size]">
                        <?php foreach ($size as $s => $name): ?>
                            <option <?php if (isset($data['size']) && $data['size'] === $s): ?>selected="selected"<?php endif; ?> value="<?php echo $s ?>"><?php echo $name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>_link_icon"><?php _e('Icon', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input">
                <input type="text" name="[<?php echo $id ?>][icon]" value="<?php echo isset($data['icon']) && $data['icon'] ? $data['icon'] : '' ?>" id="ptb_<?php echo $id ?>_link_icon" />
                <a title="<?php _e('Icon Picker', 'ptb') ?>" href="<?php echo plugin_dir_url(dirname(__FILE__)) ?>admin/themify-icons/list.html" class="ptb_custom_lightbox"><?php _e('Icon', 'ptb') ?></a>
            </div>
        </div>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>_link_color"><?php _e('Font Color', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input">
                <input class="ptb_color_picker" type="text" id="ptb_<?php echo $id ?>_link_color" name="[<?php echo $id ?>][text_color]" <?php if (isset($data['text_color']) && $data['text_color']): ?>data-value="<?php echo $data['text_color'] ?>"<?php endif; ?> />
            </div>
        </div>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>_link_style"><?php _e('Styles', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input ptb_change_disable" data-disabled="none">
                <?php foreach ($styles as $key => $name): ?>
                    <input value="<?php echo $key ?>" <?php if ($key !== 'none'): ?>class="ptb_maybe_disabled"<?php endif; ?> id="ptb_<?php echo $id ?>_styles_<?php echo $key ?>" type="checkbox" name="[<?php echo $id ?>][styles][]"
                    <?php
                    if (isset($data['styles']) && !empty($data['styles'])) :
                        if ((is_array($data['styles']) && in_array($key, $data['styles'])) || $key === $data['styles']) {
                            echo 'checked="checked"';
                        }
                    endif;
                    ?> />
                    <label for="ptb_<?php echo $id ?>_styles_<?php echo $key ?>"><?php echo $name ?></label>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[lightbox]"><?php _e('Open in', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input">
                <?php foreach ($links as $l => $n): ?>
                    <input type="radio" id="ptb_<?php echo $id ?>_radio_<?php echo $l ?>"
                           name="[<?php echo $id ?>][link_link]" value="<?php echo $l ?>"
                           <?php if ((!isset($data['link_link']) && $l === 'new_window') || ( isset($data['link_link']) && $data['link_link'] == "$l")): ?>checked="checked"<?php endif; ?>/>
                    <label for="ptb_<?php echo $id ?>_radio_<?php echo $l ?>"><?php echo $n ?></label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php if ($id !== 'permalink'): ?>
            <div class="ptb_back_active_module_row">
                <div class="ptb_back_active_module_label">
                    <label for="ptb_<?php echo $id ?>[default_link]"><?php _e('Default Link Text', 'ptb') ?></label>
                </div>
                <?php self::module_language_tabs($id, $data, $languages, 'default_link'); ?>
            </div>
        <?php endif; ?>
        <?php
    }

    /**
     * Renders the meta boxes  in public
     *
     * @since 1.0.0
     *
     * @param array $args Array of custom meta types of plugin
     * @param array $data themplate data
     * @param array $meta_data post data
     * @param string $lang language code
     * @param boolean $is_single single page
     */
    public function action_public_themplate($args, $data, $meta_data, $lang = false, $is_single = false) {
        if (!empty($meta_data)) {
            $meta_data = $meta_data[$args['key']];
            if (!isset($meta_data[1]) || !trim($meta_data[1])) {
                return;
            }
            $class = $style = array();
            $none = true;
            if (isset($data['custom_color']) && $data['custom_color']) {
                $style[] = 'background-color:' . $data['custom_color'] . ' !important;';
            } elseif (isset($data['color'])) {
                $class[] = $data['color'];
            }
            if (isset($data['icon']) && $data['icon']) {
                $class[] = 'fa';
                $class[] = $data['icon'];
            }
            if (isset($data['link_link']) && $data['link_link'] == 'lightbox') {
                $class[] = 'ptb_lightbox';
            }
            if (isset($data['size']) && $data['size']) {
                $class[] = $data['size'];
            }
            if (isset($data['styles']) && !empty($data['styles'])) {
                if (!is_array($data['styles'])) {
                    $data['styles'] = array($data['styles']);
                }
                $none = array_search('none', $data['styles']) === FALSE;
                $class[] = $none ? implode(' ', $data['styles']) : 'none';
            }
            if (isset($data['text_color']) && $data['text_color']) {
                $style[] = 'color:' . $data['text_color'] . ' !important;';
            }
            if (!$meta_data[0] && isset($data['default_link'])) {
                $meta_data[0] = PTB_Utils::get_label($data['default_link']);
            }
            ?>
            <div class="ptb_link">
                <a  <?php if ($none && !empty($style)): ?>style="<?php esc_attr_e(implode(' ', $style)) ?>"<?php endif; ?> 
                                                          class="ptb_link_button <?php if ($none && !empty($class)): ?>shortcode <?php esc_attr_e(implode(' ', $class)) ?><?php endif; ?>" 
                                                          <?php if (isset($data['link_link']) && $data['link_link'] === 'new_window'): ?>target="_blank"<?php endif; ?>
                                                          href="<?php echo $meta_data[1] ?>"><?php echo $meta_data[0] ?>
                </a>
            </div>
            <?php
        }
    }

}

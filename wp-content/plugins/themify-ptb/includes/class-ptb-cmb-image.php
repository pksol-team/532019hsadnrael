<?php
/**
 * Custom meta box class of type Image
 *
 * @link       http://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/includes
 */

/**
 * Custom meta box class of type Image
 *
 *
 * @package    PTB
 * @subpackage PTB/includes
 * @author     Themify <ptb@themify.me>
 */
class PTB_CMB_Image extends PTB_CMB_Base {

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
            'name' => __('Image', 'ptb')
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
        <div class="ptb_post_cmb_image_button_wrapper">
            <div class="ptb_post_cmb_image_wrapper">
                <a href="#" id="image_<?php echo $meta_key; ?>" class="ptb_post_cmb_image" <?php echo isset($value[1]) ? sprintf('style="background-image:url(%s)"', $value[1]) : ''; ?>>
                    <span class="ti-plus"></span>
                </a>
            </div>
            <input type="hidden" name="<?php echo $name; ?>" value="<?php echo isset($value[0]) ? esc_attr($value[0]) : ''; ?>"/>
            <input type="text" name="<?php echo $name; ?>" value="<?php echo isset($value[1]) ? esc_attr($value[1]) : ''; ?>"/>
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
        ?>

        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label><?php _e('Image Dimension', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input">
                <input id="ptb_<?php echo $id ?>_width" type="text" class="ptb_xsmall" name="[<?php echo $id ?>][width]"
                       <?php if (isset($data['width'])): ?>value="<?php echo $data['width'] ?>"<?php endif; ?> />
                <label for="ptb_<?php echo $id ?>_width"><?php _e('Width', 'ptb') ?></label>
                <input id="ptb_<?php echo $id ?>_height" type="text" class="ptb_xsmall" name="[<?php echo $id ?>][height]"
                       <?php if (isset($data['height'])): ?>value="<?php echo $data['height'] ?>"<?php endif; ?> />
                <label for="ptb_<?php echo $id ?>_height"><?php _e('Height', 'ptb') ?>(px)</label>
            </div>
        </div>
        
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>_permalink"><?php _e('Use Permalink', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input ptb_change_disable" data-disabled="1" data-action="show">
                <input value="1" <?php if (isset($data['permalink'])): ?>checked="checked"<?php endif; ?>  id="ptb_<?php echo $id ?>_permalink" type="checkbox" name="[<?php echo $id ?>][permalink]" />
                <input class="ptb_maybe_disabled" style="width: 94.8%;" placeholder="<?php _e('Or Custom Url', 'ptb') ?>" <?php if (isset($data['custom_url'])): ?>value="<?php echo esc_url($data['custom_url']) ?>"<?php endif; ?> type="text" id="ptb_<?php echo $id ?>_custom_url" name="[<?php echo $id ?>][custom_url]" />
            </div>
        </div>
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
    public function action_public_themplate(array $args, array $data, array $meta_data, $lang = false, $is_single = false) {
        $url = false;
        ?>
        <div class="ptb_image">
            <?php if (isset($meta_data[0])): ?>
                <?php $url = is_numeric($meta_data[0])?wp_get_attachment_url($meta_data[0]):esc_url($meta_data[0]); ?>
            <?php elseif (isset($meta_data[1])): ?>
                <?php $url = $meta_data[1]; ?>
            <?php endif; ?>
            <?php if($url):?>
                <?php $url = PTB_CMB_Base::ptb_resize($url, $data['width'], $data['height']); ?>
                <?php if ($url): ?>
                    <?php $link = isset($data['permalink'])?$meta_data['post_url']:(isset($data['custom_url']) && $data['custom_url']?esc_url($data['custom_url']):false);?>
                    <?php if($link):?>
                        <a href="<?php echo $link?>">
                    <?php endif;?>
                            <figure class="ptb_post_image clearfix">
                                <img src="<?php echo $url ?>" />
                            </figure>
                    <?php if($link):?>
                        </a>
                    <?php endif;?>
                <?php endif; ?>
            <?php endif;?>
        </div>
        <?php
    }

}

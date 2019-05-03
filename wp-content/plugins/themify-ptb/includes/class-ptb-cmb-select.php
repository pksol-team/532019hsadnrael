<?php
/**
 * Custom meta box class of type Select
 *
 * @link       http://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/includes
 */

/**
 * Custom meta box class of type Select
 *
 *
 * @package    PTB
 * @subpackage PTB/includes
 * @author     Themify <ptb@themify.me>
 */
class PTB_CMB_Select extends PTB_CMB_Base {

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
            'name' => __('Select', 'ptb')
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
            <label for="<?php echo $id; ?>_multiple_selects" class="ptb_cmb_input_label">
                <?php _e("Multiple Selects", 'ptb'); ?>
            </label>
            <fieldset class="ptb_cmb_input">
                <label for="<?php echo $id; ?>_multiple_selects_yes" title="Yes">
                    <input type="radio" id="<?php echo $id; ?>_multiple_selects_yes"
                           name="<?php echo $id; ?>_multiple_selects" value="Yes" />
                    <span><?php _e('Yes', 'ptb') ?></span>
                </label>&nbsp;&nbsp;
                <label for="<?php echo $id; ?>_multiple_selects_no" title="No">
                    <input type="radio" id="<?php echo $id; ?>_multiple_selects_no"
                           name="<?php echo $id; ?>_multiple_selects" value="No" />
                    <span><?php _e('No', 'ptb') ?></span>
                </label><br>
            </fieldset>
        </div>

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
                                        <a class="ptb_lng_<?php echo $code ?>" title="<?php echo $lng['name'] ?>" href="#"></a>
                                    </li>
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
        ?>
        <?php if ($args['multipleSelects']): ?>
            <div class="ptb_back_active_module_row">
                <div class="ptb_back_active_module_label">
                    <label for="ptb_<?php echo $id ?>[display_paragraph]"><?php _e('Display multiple items as', 'ptb') ?></label>
                </div>
                <div class="ptb_back_active_module_input ptb_change_disable" data-disabled="list,paragraph" data-action="1">
                    <input type="radio" id="ptb_<?php echo $id ?>[display_list]"
                           name="[<?php echo $id ?>][display]" value="list"
                           <?php if (!$data || ( $data && $data['display'] == 'list' )): ?>checked="checked"<?php endif; ?>  />
                    <label for="ptb_<?php echo $id ?>[display_list]"><?php _e('List', 'ptb') ?></label>
                    <input type="radio" id="ptb_<?php echo $id ?>[display_paragraph]"
                           name="[<?php echo $id ?>][display]" value="paragraph"
                           <?php if ($data && $data['display'] == 'paragraph'): ?>checked="checked"<?php endif; ?> />
                    <label for="ptb_<?php echo $id ?>[display_paragraph]"><?php _e('Paragraph', 'ptb') ?></label>
                    <input type="radio" id="ptb_<?php echo $id ?>[display_one_line]"
                           name="[<?php echo $id ?>][display]" value="one_line"
                           <?php if ($data && $data['display'] == 'one_line'): ?>checked="checked"<?php endif; ?> />
                    <label for="ptb_<?php echo $id ?>[display_one_line]"><?php _e('One line', 'ptb') ?></label>
                </div>
            </div>
            <div class="ptb_back_active_module_row ptb_maybe_disabled">
                <div class="ptb_back_active_module_label">
                    <label for="ptb_<?php echo $id ?>[seperator]"><?php _e('Seperator', 'ptb') ?></label>
                </div>
                <div class="ptb_back_active_module_input">
                    <input type="text" id="ptb_<?php echo $id ?>[seperator]"
                        name="[<?php echo $id ?>][seperator]" value="<?php echo  !empty($data['seperator'])? $data['seperator']:''?>" />
                </div>
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
    public function action_public_themplate(array $args, array $data, array $meta_data, $lang = false, $is_single = false) {
        if (!$meta_data || empty($args['options'])) {
            return false;
        }
        
        if (!isset($data['display'])) {
            $data['display'] = 'list';
        }
        if(!is_array($meta_data[$args['key']])){
            $meta_data[$args['key']] = array($meta_data[$args['key']]);
        }
        $options = array();
        foreach ($args['options'] as $opt) {
            if (in_array($opt['id'], $meta_data[$args['key']])) {
                $options[] = $opt[$lang];
            }
        }
        if (!empty($args['multipleSelects'])){
            $seperator = $data['display']==='one_line' && !empty($data['seperator'])?$data['seperator']:false;
            $this->get_repeateable_text($data['display'], $options,$seperator);
        }
        else{
            $this->get_text(current($options), 'select');
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
        if (!is_array($value)) {
            $value = array($value);
        }
        $name = sprintf('%s[]', $meta_key);
        ?>
        <select <?php echo $args['multipleSelects'] ? 'multiple' : ''; ?> name="<?php echo $name; ?>">
            <?php foreach ($args['options'] as $option): ?>
                <option value="<?php echo $option['id']; ?>" <?php echo !empty($value) && in_array($option['id'], $value) ? 'selected' : ''; ?>><?php echo PTB_Utils::get_label($option); ?></option>
            <?php endforeach; ?>
        </select>
        <?php
    }

}

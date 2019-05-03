<?php

class PTB_Form_PTT_Single extends PTB_Form_PTT_Them {

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     *
     * @param string $plugin_name
     * @param string $version
     * @param PTB_Options $options the plugin options instance
     * @param string themplate_id
     *
     */
    public function __construct($plugin_name, $version, $themplate_id=false) {
        parent::__construct($plugin_name, $version, $themplate_id);
    }

    /**
     * Single layout parametrs
     *
     * @since 1.0.0
     */
    public function add_fields($data = array()) {
        $fieldname = $this->get_field_name('navigation_post');
        $field_id_yes = $this->get_field_id('navigation_post_yes');
        $field_id_no = $this->get_field_id('navigation_post_no');
        
        $fieldname_category = $this->get_field_name('same_category');
        $field_id_cateogry_yes = $this->get_field_id('same_category_yes');
        $field_id_cateogry_no = $this->get_field_id('same_category_no');
        $fieldname_tax = $this->get_field_name('same_tax');
        
        $ptt = $this->get_ptt();
        $post_taxonomies = $this->options->get_cpt_cmb_taxonomies($ptt['post_type']);
        ?>
        <div class="ptb_lightbox_row_wrapper">
            <div class="ptb_lightbox_row ptb_navigate_post">
                <div class="ptb_lightbox_label"><?php _e('Post navigation', 'ptb'); ?></div>
                 <div class="ptb_lightbox_input ptb_change_disable" data-action="1" data-disabled="0">
                    <input 
                        <?php if (!isset($data[$fieldname]) || ( isset($data[$fieldname]) && $data[$fieldname] == '1' )): ?>checked="checked"<?php endif; ?>
                        type="radio" name="<?php echo $fieldname ?>" value="1" id="<?php echo $field_id_yes ?>"/>
                    <label for="<?php echo $field_id_yes; ?>"><?php _e('Yes', 'ptb'); ?></label>
                    <input 
                        <?php if (isset($data[$fieldname]) && $data[$fieldname] == '0'): ?>checked="checked"<?php endif; ?>
                        type="radio" name="<?php echo $fieldname ?>" value="0" id="<?php echo $field_id_no; ?>"/>
                    <label for="<?php echo $field_id_no ?>"><?php _e('No', 'ptb'); ?></label>
                </div>
            </div>
            <?php if(!empty($post_taxonomies)):?>
                <?php natcasesort($post_taxonomies);?>
                <div class="ptb_lightbox_row ptb_category_post ptb_maybe_disabled">
                    <div class="ptb_lightbox_label"><?php _e('Same Category', 'ptb'); ?></div>
                     <div class="ptb_lightbox_input">
                        <input 
                            <?php if (!isset($data[$fieldname_category]) || !empty($data[$fieldname_category])): ?>checked="checked"<?php endif; ?>
                            type="radio" name="<?php echo $fieldname_category ?>" value="1" id="<?php echo $field_id_cateogry_yes ?>"/>
                        <label for="<?php echo $field_id_cateogry_yes; ?>"><?php _e('Yes', 'ptb'); ?></label>
                        <select name="<?php echo $fieldname_tax?>">
                            <?php foreach($post_taxonomies as $tax):?>
                               <option <?php if (isset($data[$fieldname_tax]) && $data[$fieldname_tax] === $tax): ?>selected="selected"<?php endif; ?> value="<?php echo $tax?>"><?php echo $tax?></option>
                            <?php endforeach;?>
                        </select>
                        <input 
                            <?php if (isset($data[$fieldname_category]) && $data[$fieldname_category] == '0' ): ?>checked="checked"<?php endif; ?>
                            type="radio" name="<?php echo $fieldname_category ?>" value="0" id="<?php echo $field_id_cateogry_no; ?>"/>
                        <label for="<?php echo $field_id_cateogry_no ?>"><?php _e('No', 'ptb'); ?></label>
                    </div>
                </div>
            <?php endif;?>
        </div>
        <?php
    }

}

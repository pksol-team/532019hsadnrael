<?php

class PTB_Form_PTT_Archive extends PTB_Form_PTT_Them {

    public static $layouts = array('list-post' => 'checked', 'grid3' => '', 'grid2' => '', 'grid4' => '');
    private static $sorttypes = array('text', 'radio_button', 'select', 'image', 'textarea', 'checkbox', 'number');
    public static $sortfields = array('date' => '', 'id' => '', 'author' => '', 'title' => '', 'name' => '', 'modified' => '', 'rand' => '', 'comment_count' => '', 'menu_order' => '');

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
    public function __construct($plugin_name, $version, $themplate_id = false) {
        parent::__construct($plugin_name, $version, $themplate_id);
        self::get_sort_fields();
    }

    public static function get_sort_fields(array $metaboxes = array()) {
        self::$sortfields['date'] = __('Date', 'ptb');
        self::$sortfields['id'] = __('Id', 'ptb');
        self::$sortfields['author'] = __('Author', 'ptb');
        self::$sortfields['title'] = __('Title', 'ptb');
        self::$sortfields['name'] = __('Name', 'ptb');
        self::$sortfields['modified'] = __('Modified', 'ptb');
        self::$sortfields['rand'] = __('Random', 'ptb');
        self::$sortfields['comment_count'] = __('Comment count', 'ptb');
        self::$sortfields['menu_order'] = __('Menu Order', 'ptb');
        if(!empty($metaboxes)){
            $sort_types = self::get_sort_types();
            foreach ($metaboxes as $key => $m) {
                if (in_array($m['type'], $sort_types) &&
                        (($m['type'] === 'number' && empty($m['number_showrange'])) ||
                        ($m['type'] === 'text' && empty($m['repeatable'])) ||
                        ($m['type'] === 'select' && empty($m['multipleSelects'])) ||
                        ($m['type'] !== 'checkbox')
                        )) {
                    self::$sortfields[$key] = PTB_Utils::get_label($m['name']);
                }
            }
        }
        return apply_filters('ptb_sort_fields', self::$sortfields, $metaboxes);
    }
    
   

    public static function get_sort_types() {
        return self::$sorttypes;
    }

    /**
     * Archive layout parametrs
     *
     * @since 1.0.0
     */
    public function add_fields($data = array()) {
        $fieldname = $this->get_field_name('layout_post');
        $ptt = $this->get_ptt();
        $sorting_fileds = self::get_sort_fields($this->options->get_cpt_cmb_options($ptt['post_type']));
        ?>
        <div class="ptb_lightbox_row ptb_layout_post ">
            <div class="ptb_lightbox_label"><?php _e('Post Layout', 'ptb'); ?></div>
            <div class="ptb_lightbox_input">
                <?php foreach (self::$layouts as $l => $ch): ?>
                    <?php $id = $this->get_field_id('layout_post_' . $l); ?>
                    <input id="<?php echo $id ?>" type="radio" value="<?php echo $l; ?>" name="<?php echo $fieldname ?>" <?php if ((!$data && $ch ) || ( isset($data[$fieldname]) && $data[$fieldname] === $l )): ?>checked="checked"<?php endif; ?>/>
                    <label for="<?php echo $id ?>" class="ptb_grid ptb_grid_<?php echo $l; ?>"></label>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="ptb_lightbox_row ptb_offset_post">
            <div class="ptb_lightbox_label"><?php _e('Post per page', 'ptb'); ?></div>
            <div class="ptb_lightbox_input">
                <?php $fieldname = $this->get_field_name('offset_post'); ?>
                <input type="text" name="<?php echo $fieldname ?>" <?php if ($data && isset($data[$fieldname])): ?>value="<?php echo $data[$fieldname] ?>"<?php endif; ?>/>
                <small><?php _e('Leave blank to leave default setting (WordPress > Settings > Reading)', 'ptb'); ?></small>
            </div>
        </div>
        <div class="ptb_lightbox_row ptb_orderby_post">
            <div class="ptb_lightbox_label"><?php _e('Order By', 'ptb'); ?></div>
            <div class="ptb_lightbox_input">
                <div class="ptb_custom_select">
                    <?php $fieldname = $this->get_field_name('orderby_post'); ?>
                    <select name="<?php echo $fieldname ?>">
                        <?php foreach ($sorting_fileds as $key => $sort): ?>
                            <option <?php if (isset($data[$fieldname]) && $data[$fieldname] === $key): ?>selected="selected" <?php endif; ?> value="<?php echo esc_attr($key) ?>"><?php echo esc_attr($sort) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="ptb_lightbox_row ptb_order_post">
            <div class="ptb_lightbox_label"><?php _e('Order', 'ptb'); ?></div>
            <div class="ptb_lightbox_input">
                <div class="ptb_custom_select">
                    <?php $fieldname = $this->get_field_name('order_post'); ?>
                    <select name="<?php echo $fieldname ?>">
                        <option <?php if (isset($data[$fieldname]) && $data[$fieldname] == 'desc'): ?>selected="selected"<?php endif; ?> value="desc"><?php _e('Descending', 'ptb'); ?></option>
                        <option <?php if (isset($data[$fieldname]) && $data[$fieldname] == 'asc'): ?>selected="selected"<?php endif; ?> value="asc"><?php _e('Ascending', 'ptb'); ?></option>
                    </select>
                </div>
            </div>
        </div>
        <?php
        $fieldname = $this->get_field_name('pagination_post');
        $field_id_yes = $this->get_field_id('pagination_post_yes');
        $field_id_no = $this->get_field_id('pagination_post_no');
        ?>
        <div class="ptb_lightbox_row ptb_pagination_post">
            <div class="ptb_lightbox_label"><?php _e('Pagination', 'ptb'); ?></div>
            <div class="ptb_lightbox_input">
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
        <?php
    }

}

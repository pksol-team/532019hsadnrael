<?php
/**
 * Widget API: PTB_Widget_Recent_Posts class
 *
 * @package PTB
 * @subpackage PTB/includes
 * @since 1.2.8
 */

/**
 * Core class used to implement a PTB Recent Posts widget.
 *
 * @since 1.2.8
 *
 * @see WP_Widget
 */
class PTB_Widget_Recent_Posts extends WP_Widget {

    /**
     * Sets up a new PTB Recent Posts widget instance.
     *
     * @since 1.2.8
     * @access public
     */
    public function __construct() {
        $widget_ops = array(
            'classname' => 'ptb_recent_entries',
            'description' => __('PTB Recent Posts.','ptb')
        );
        parent::__construct('ptb-recent-posts', __('PTB Recent Posts','ptb'), $widget_ops);
    }

    /**
     * Outputs the content for the current PTB Recent Posts widget instance.
     *
     * @since 1.2.8
     * @access public
     *
     * @param array $args     Display arguments including 'before_title', 'after_title',
     *                        'before_widget', and 'after_widget'.
     * @param array $instance Settings for the current PTB Recent Posts widget instance.
     */
    public function widget($args, $instance) {
        if(!empty($instance['type'])){
            if (!isset($args['widget_id'])) {
                $args['widget_id'] = $this->id;
            }

            $title = !empty($instance['title'])?esc_attr($instance['title']):'';
            $title = apply_filters('widget_title',  $title, $instance, $this->id_base);
            unset($instance['title']);
            $shortcode = '[ptb';
            foreach ($instance as $k=>$v){
                if(!empty($v)){
                    if(is_array($v)){
                        $v = implode(',',$v);
                    }
                    $shortcode.=' '.$k.'="'.esc_attr($v).'"';
                }
            }
            $shortcode.=']'; 
            $content = do_shortcode($shortcode);
            if ($content){
                echo $args['before_widget'];
                if ($title) {
                    echo $args['before_title'] . $title . $args['after_title'];
                }
               echo $content,$args['after_widget'];
            }
        }
    }
    
    /**
     * Handles updating the settings for the current PTB Recent Posts widget instance.
     *
     * @since 1.2.8
     * @access public
     *
     * @param array $new_instance New settings for this instance as input by the user via
     *                            WP_Widget::form().
     * @param array $old_instance Old settings for this instance.
     * @return array Updated settings to save.
     */
    public function update($new_instance, $old_instance) {
        if(empty($new_instance['type'])){
            $new_instance = $old_instance;
        }
        else{
            $ptb_options = PTB::get_option();
            $t = $ptb_options->get_post_type_template_by_type($new_instance['type']);
            if(!$t || !$t->has_archive()){
                $new_instance = $old_instance;
            }
        }
        return $new_instance;
    }

    /**
     * Outputs the settings form for the PTB Recent Posts widget.
     *
     * @since 1.2.8
     * @access public
     *
     * @param array $instance Current settings.
     */
    public function form($instance) {
        $type = isset($instance['type']) ? esc_attr($instance['type']) : '';
        $ptb_options = PTB::get_option();
        static $themplates = null;
        if (is_null($themplates)) {
            $themplates = $ptb_options->get_post_type_templates();
        }
        $first = false;
        ?>  
        <?php if (!empty($themplates)): ?>
            <?php
            wp_enqueue_style(PTB::get_plugin_name());
            wp_enqueue_script('ptb-widget-js', plugin_dir_url(dirname(__DIR__)) . 'admin/js/ptb-widget.js', array('jquery'), PTB::get_plugin_name(), true);
            ?>
            <p>
                <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'ptb' ); ?>:</label>
                <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo !empty($instance['title'])?esc_attr($instance['title']):''; ?>" /></p>

            <div class="ptb_recent_widget_wrapper ptb_cmb_item_wrapper">
                <div class="ptb_cmb_item_body">
                    <div class="ptb_cmb_input_row">
                        <label class="ptb_cmb_input_label" for="<?php echo $this->get_field_id('post_type'); ?>"><?php _e('Post Type', 'ptb'); ?>:</label>
                        <div class="ptb_cmb_input">
                            <?php unset($instance['title']);?>
                            <select data-type="<?php echo $type?>" data-data="<?php echo $type ? esc_attr(wp_json_encode($instance)) : '' ?>" data-name="<?php echo $this->get_field_name('#name#'); ?>" data-id="<?php echo $this->get_field_id('#name#'); ?>" class="ptb_widget_resent_posts" id="<?php echo $this->get_field_id('post_type'); ?>" name="<?php echo $this->get_field_name('type'); ?>">
                                <?php foreach ($themplates as $post_themes): ?>
                                    <?php if ($post_themes->has_archive()): ?>
                                        <?php
                                        $post_type = $post_themes->get_post_type();
                                        $custom_post = $ptb_options->get_custom_post_type($post_type);
                                        if(!$first){
                                            $first = $post_type;
                                        }
                                        ?>
                                        <option <?php if ($type === $post_type): ?>selected="selected"<?php endif; ?> value="<?php echo $post_type ?>"><?php echo PTB_Utils::get_label($custom_post->plural_label) ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                            <div class="spinner"></div>
                        </div>
                    </div>
                    <?php 
                    if(!$type && $first){
                        $type = $first;
                    }?>
                    <div class="ptb_recent_widget_items">
                        <?php if($type):?>
                            <?php $data = $ptb_options->get_shortcode_data($type);?>
                            <?php foreach($data['data'] as $k=>$v):?>
                                <?php 
                                    $value = !empty($instance[$k])?$instance[$k]:false;
                                    $id =  $this->get_field_id($k);
                                    $name = $this->get_field_name($k);
                                ?>
                                 <div class="ptb_cmb_input_row">
                                    <label class="ptb_cmb_input_label" for="<?php echo $id?>"><?php echo $v['label']?>:</label>
                                    <div class="ptb_cmb_input">
                                        <?php switch($v['type']):
                                                case 'listbox':?>
                                                <select id="<?php echo $id?>" name="<?php echo $name?>">
                                                    <?php foreach($v['values'] as $opt):?>
                                                       <option <?php if($value===$opt['value']):?>selected="selected"<?php endif;?> value="<?php echo $opt['value']?>"><?php echo $opt['text']?></option>
                                                    <?php endforeach;?>
                                                </select>
                                        <?php break;
                                                case 'textbox':?>
                                                <input value="<?php echo $value?>" id="<?php echo $id?>" name="<?php echo $name?>" type="text"/>
                                        <?php break;
                                                case 'radio':?>
                                                <input value="1" <?php if($value || empty($instance)):?>checked="checked"<?php endif;?> id="<?php echo $id?>" name="<?php echo $name?>" type="checkbox"/>
                                        <?php break;?>
                                        <?php endswitch;?>
                                    </div>
                                 </div>
                            <?php endforeach;?>
                        <?php if(!empty($data['tax']['data'])):?>
                            <?php foreach($data['tax']['data'] as $k=>$v):?>
                                <?php 

                                    if($v['type']!=='multiselect'){
                                        continue;
                                    }
                                    $name = $this->get_field_name($v['name']);
                                    $values = !empty($instance[$v['name']])?$instance[$v['name']]:array();
                                    $id =  $this->get_field_id($v['name']);
                                   
                                ?>
                                <div class="ptb_cmb_input_row">
                                    <label class="ptb_cmb_input_label" for="<?php echo $id?>"><?php echo $v['label']?>:</label>
                                    <div class="ptb_cmb_input">
                                        <select multiple="multiple" name="<?php echo $name?>[]" id="<?php echo $id?>">
                                            <?php if(!empty($v['values'])):?>
                                                <?php foreach($v['values'] as $opt):?>
                                                    <option <?php if(is_array($values) && in_array($opt['value'],$values)):?>selected="selected"<?php endif;?> value="<?php echo $opt['value']?>"><?php echo $opt['text']?></option>
                                                <?php endforeach;?>
                                            <?php endif;?>
                                        </select>
                                    </div>
                                </div>
                                <?php endforeach;?>
                        <?php endif;?>
                        <?php endif;?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php
    }

}

<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    PTB
 * @subpackage PTB/public
 * @author     Themify <ptb@themify.me>
 */
class PTB_Public {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @var      string $plugin_name The name of the plugin.
     * @var      string $version The version of this plugin.
     */
    private static $options = false;
    private static $template = false;
    private static $render_instance = false;
    public static $render_content = false;
    public static $shortcode = false;
    private static $post_ids = array();

    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        self::$options = PTB::get_option();
        add_shortcode($this->plugin_name, array($this, 'ptb_shortcode'));
        add_filter( 'widget_text', array($this,'get_ptb_shortcode'),10,1);
        add_filter('widget_posts_args',array($this,'disable_ptb'),10,1);
    }

    /**
     * Register the Javascript/Stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in PTB_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The PTB_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        $plugin_url = plugin_dir_url(__FILE__);
        wp_enqueue_script($this->plugin_name . '-lightbox', $plugin_url . 'js/lightbox.min.js', array('jquery'), '2.1.1', false);
        $translation_ = array(
            'url' => $plugin_url,
            'ver' => $this->version
        );

        wp_register_script($this->plugin_name, $plugin_url . 'js/ptb-public.js', array($this->plugin_name . '-lightbox'), $this->version, false);
        wp_localize_script($this->plugin_name, 'ptb', $translation_);
        global $wp_styles;
        $is_fontawesome_loaded = false;
       
        $srcs = array_map('basename', (array) wp_list_pluck($wp_styles->registered, 'src'));
        foreach($srcs as $handler=>$sr){
            if((strpos($sr,'font-awesome')!==false || strpos($sr,'fontawesome')!==false) &&  in_array($handler,$wp_styles->queue)){
                $is_fontawesome_loaded = true;
                break;
            }
        }
        if (!$is_fontawesome_loaded) {
            wp_enqueue_style('themify-font-icons-css2', plugin_dir_url(dirname(__FILE__)) . 'admin/themify-icons/font-awesome.min.css', array(), $this->version, 'all');
        }
        wp_enqueue_style($this->plugin_name . '-colors', plugin_dir_url(dirname(__FILE__)) . 'admin/themify-icons/themify.framework.css', array(), $this->version, 'all');
        wp_enqueue_style($this->plugin_name, $plugin_url . 'css/ptb-public.css', array(), $this->version, 'all');
        wp_enqueue_style($this->plugin_name . '-lightbox', $plugin_url . 'css/lightbox.css', array(), '0.9.9', 'all');
        wp_enqueue_script($this->plugin_name);
    }

    /**
     * Register the ajax url
     *
     * @since    1.0.0
     */
    public static function define_ajaxurl() {
        ?>
        <script type="text/javascript">
            ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
        </script>
        <?php
    }

    public function ptb_filter_wp_head() {
        $option = PTB::get_option();
        $custom_css = $option->get_custom_css();
        if ($custom_css) {
            echo '<!-- PTB CUSTOM CSS --><style type="text/css">' . $custom_css . '</style><!--/PTB CUSTOM CSS -->';
        }
    }

    public function ptb_filter_body_class($classes) {

        $post_type = get_post_type();
        $templateObject = self::$options->get_post_type_template_by_type($post_type);
        if (is_null($templateObject)) {
            return $classes;
        }

        $single = $templateObject->has_single() && is_singular($post_type);
        $archive = !$single && self::$template && $templateObject->has_archive();
        if ($archive) {
            $classes[] = $this->plugin_name . '_archive';
            $classes[] = $this->plugin_name . '_archive_' . $post_type;
        } elseif ($single) {
            $classes[] = $this->plugin_name . '_single';
            $classes[] = $this->plugin_name . '_single_' . $post_type;
        }

        return $classes;
    }
    
     public function get_ptb_shortcode($text){
        if($text && has_shortcode($text, $this->plugin_name)){
           $text = PTB_CMB_Base::format_text($text);
           $text = do_shortcode($text);
        }
        return $text;
    }

    /** 		
     * @param $title		
     * @param null $id		
     * 		
     * @return string		
     */
    public function ptb_filter_post_type_title($title, $id = null) {

        if ($id !== get_the_ID() || self::$shortcode) {
            return $title;
        }
        $post_type = get_post_type();
        $templateObject = self::$options->get_post_type_template_by_type($post_type);
        return isset($templateObject) && ((is_singular($post_type) && $templateObject->has_single()) || (self::$template && $templateObject->has_archive())) ? '' : $title;
    }

    /* 		
     * @since 1.0.0		
     * 		
     * @param $content		
     * 		
     * @return string		
     */

    public function ptb_filter_post_type_content_post($content) {
        if (self::$shortcode) {
            return $content;
        }
        global $post;
        $post_type = $post->post_type;
        $templateObject = self::$options->get_post_type_template_by_type($post_type);

        if (is_null($templateObject) || self::$render_content) {
            self::$render_content = false;
            return $content;
        } elseif (self::$render_instance == 'excerpt') {
            return !self::$render_content ? $content : '&nbsp;';
        }
        self::$render_instance = 'content';
        $single = $templateObject->has_single() && is_singular($post_type);
        $archive = !$single && self::$template;
        if ($single || $archive) {
            $id = get_the_ID();
            $cmb_options = $post_support = $post_taxonomies = array();
            self::$options->get_post_type_data($post_type, $cmb_options, $post_support, $post_taxonomies);
            $post_meta = array_merge(array(), get_post_custom(), get_post('', ARRAY_A));
            $post_meta['post_url'] = get_permalink();
            $post_meta['taxonomies'] = !empty($post_taxonomies) ? wp_get_post_terms($id, array_values($post_taxonomies)) : array();
            $themplate = new PTB_Form_PTT_Them($this->plugin_name, $this->version);
            $themplate_layout = $single ? $templateObject->get_single() : $templateObject->get_archive();

            if (isset($themplate_layout['layout']) && ($single || !in_array($id, self::$post_ids))) {
                self::$post_ids[] = $id;
                return $themplate->display_public_themplate($themplate_layout, $post_support, $cmb_options, $post_meta, $post_type, $single);
            }
        }

        return $content;
    }

    /* 		
     * @since 1.0.0		
     * 		
     * @param $exceprt		
     * 		
     * @return string		
     */

    public function ptb_filter_post_type_exceprt_post($content) {
        if (self::$shortcode || !self::$template ) {
            return $content;
        }
        global $post;
        $post_type = $post->post_type;
        $templateObject = self::$options->get_post_type_template_by_type($post_type);

        if (is_null($templateObject) || self::$render_content) {
            if (self::$render_instance != 'content') {
                self::$render_content = false;
            }
            return $content;
        } elseif (self::$render_instance == 'content' || is_singular($post_type)) {
            return '&nbsp;';
        }
        self::$render_instance = 'excerpt';
        if ($templateObject->has_archive()) {
            $id = get_the_ID();
            $cmb_options = $post_support = $post_taxonomies = array();
            self::$options->get_post_type_data($post_type, $cmb_options, $post_support, $post_taxonomies);
            $post_meta = array_merge(array(), get_post_custom(), get_post('', ARRAY_A));
            $post_meta['post_url'] = get_permalink();
            $post_meta['taxonomies'] = !empty($post_taxonomies) ? wp_get_post_terms($id, array_values($post_taxonomies)) : array();
            $themplate = new PTB_Form_PTT_Them($this->plugin_name, $this->version);
            $themplate_layout = $templateObject->get_archive();

            if (isset($themplate_layout['layout']) && !in_array($id, self::$post_ids)) {
                self::$post_ids[] = $id;
                echo '<article id="post-' . $id . '" class="' . implode(' ', get_post_class(array('ptb_post', 'clearfix', 'ptb_is_excerpt', 'post'))) . '">';
                echo $themplate->display_public_themplate($themplate_layout, $post_support, $cmb_options, $post_meta, $post_type, false);
                echo '</article>';
                return '&nbsp;';
            }
        }

        return $content;
    }

    public function ptb_filter_post_type_class($classes) {
        if (!self::$shortcode) {
            if (!self::$template) {
                $post_type = get_post_type();
                $templateObject = self::$options->get_post_type_template_by_type($post_type);
                $single = isset($templateObject) && $templateObject->has_single() && is_singular($post_type);
            } else {
                $single = true;
            }
            if ($single) {
                $classes[] = 'ptb_post';
                $classes[] = 'clearfix';
            }
        }
        return $classes;
    }

    public function ptb_filter_post_type_start() {
        if (self::$template && !self::$shortcode) {
            self::$post_ids = array();
                if(!is_category()){
            $grid = self::$template->get_archive();
                    $grid = 'ptb_'.$grid[self::$options->prefix_ptt_id . 'layout_post'];
                }else{
                    $grid = '';
        }
                echo '<div class="ptb_loops_wrapper ' . $grid . ' clearfix">';
    }
    }

    public function ptb_filter_post_type_end() {
        if (self::$template && !self::$shortcode) {
            echo '</div>';
        }
    }

    public function ptb_post_thumbnail($html) {
        if (!self::$shortcode) {
            $post_type = get_post_type();
            $templateObject = self::$options->get_post_type_template_by_type($post_type);
            return isset($templateObject) && ((is_singular($post_type) && $templateObject->has_single()) || (self::$template && $templateObject->has_archive())) ? '' : $html;
        }
        return $html;
    }

    /** 		
     * @param WP_Query $query		
     * 		
     * @return WP_Query		
     */
    public function ptb_filter_cpt_category_archives(&$query) {
        if(!empty($query->query['ptb_disable'])){
            self::$template = false;
            return $query;
        }
        if (!self::$shortcode   && !is_singular() && !is_feed($query) && ($query->is_category() || $query->is_post_type_archive()  || $query->is_tag() || $query->is_tax()) && (!isset($query->query_vars['suppress_filters']) || $query->query_vars['suppress_filters'])) {
            self::$template = false;
            $post_type = false; 
            if ($query->is_post_type_archive() && isset($query->query['post_type'])) {

                $args = array();
                $t = self::$options->get_post_type_template_by_type($query->query['post_type']);
                if ($t && $t->has_archive()) {
                    self::$template = $t;
                    $post_type = $query->query['post_type'];
                    $args[] = $query->query['post_type'];
                }
            } elseif (!empty($query->tax_query->queries)) {
                $tax = $query->tax_query->queries;
                $tax = current($tax);
                $tax = $tax['taxonomy'];
                $taxonomy = get_taxonomy($tax);
                unset($tax);
                if ($taxonomy) {
                    $args = $taxonomy->object_type;
                    if ($args) {
                        array_reverse($args);
                        foreach ($args as $type) {
                            $t = self::$options->get_post_type_template_by_type($type);
                            if ($t && $t->has_archive()) {
                                self::$template = $t;
                                $post_type = $type;
                                break;
                            }
                        }
                    }
                }
            }
            if (self::$template) {
                $archive = self::$template->get_archive();
               
                if ($archive['ptb_ptt_pagination_post'] > 0) {
                    if ($archive['ptb_ptt_offset_post'] > 0) {
                        $query->set('posts_per_page', intval($archive['ptb_ptt_offset_post']));
                    }
                } else {
                    $query->set('nopaging', 1);
                    $query->set('no_found_rows', 1);
                } 
                if (isset(PTB_Form_PTT_Archive::$sortfields[$archive['ptb_ptt_orderby_post']])) {
                    $query->set('orderby', $archive['ptb_ptt_orderby_post']);
                } else {
                    $cmb_options = self::$options->get_cpt_cmb_options($post_type);
                    if(isset($cmb_options[$archive['ptb_ptt_orderby_post']])){
                        $sort = $cmb_options[$archive['ptb_ptt_orderby_post']]['type']==='number' && empty($cmb_options[$archive['ptb_ptt_orderby_post']]['range'])?'meta_value_num':'meta_value';
                        $query->set('orderby', $sort);
                        $query->set('meta_key', $this->plugin_name . '_' . $archive['ptb_ptt_orderby_post']);
                    }
                }
                $query->set('order', $archive['ptb_ptt_order_post']);
                $query->set('post_type', $args);
                if ($query->is_main_query()) {
                    $query->set('suppress_filters', true); //wpml filter	
                }
            }
        } elseif (!self::$shortcode && $query->is_main_query() && is_search()) {
            $post_types = self::$options->get_custom_post_types();
            if (!empty($post_types)) {
                $searchable_types = array('post', 'page');
                 foreach ($post_types as $type) {
                    if(empty($type->ad_exclude_from_search)){
                        $searchable_types[] = $type->slug;
                    }
                }
                $query->set('post_type', $searchable_types);
            }
        }

        return $query;
    }

    /**
     * @since 1.0.0
     *
     * @param $atts
     *
     * @return string|void
     */
    public function ptb_shortcode($atts) {

        $post_types = explode(',', esc_attr($atts['type']));
        $type = current($post_types);
        $template = self::$options->get_post_type_template_by_type($type);
        if (null == $template) {
            return;
        }
        unset($atts['type']);
        // WP_Query arguments
        $args = array(
            'orderby' => 'date',
            'order' => 'DESC',
            'post_type' => $type,
            'post_status' => 'publish',
            'nopaging' => false,
            'style' => 'list-post',
            'post__in' => isset($atts['ids']) && $atts['ids'] ? explode(',', $atts['ids']) : '',
            'posts_per_page' => isset($atts['posts_per_page']) && intval($atts['posts_per_page']) > 0 ? $atts['posts_per_page'] : get_option('posts_per_page'),
            'paged' => isset($atts['paged']) && $atts['paged'] > 0 ? intval($atts['paged']) : (is_front_page() ? get_query_var('page', 1) : get_query_var('paged', 1)),
            'logic' => 'AND'
        );
        if (isset($atts['offset']) && intval($atts['offset']) > 0) {
            $args['offset'] = intval($atts['offset']);
        }
        $args = wp_parse_args($atts, $args);
        unset($atts);
        if (!$args['paged'] || !is_numeric($args['paged'])) {
            $args['paged'] = 1;
        }
        if(empty($args['pagination'])){
            $args['no_found_rows'] = 1;
        }
        if (isset($args['post_id']) && is_numeric($args['post_id'])) {
            $args['p'] = $args['post_id'];
            $args['style'] = '';
        } else {
            $taxes = $conditions = $meta = array();
            $post_taxonomies = $cmb_options = $post_support = array();
            self::$options->get_post_type_data($type,$cmb_options,$post_support,$post_taxonomies);
            if(isset($post_support['category'])){
                $post_taxonomies[] = 'category';
            }
            foreach ($args as $key => $value) {
                if(!is_array($value)){
                    $value = trim($value);
                }
                if($value || $value=='0'){
                    if (strpos($key, 'ptb_tax_') === 0) {
                        $origk = str_replace('ptb_tax_', '', $key);
                        if(in_array($origk,$post_taxonomies)){
                            $taxes[] = array(
                                'taxonomy' => sanitize_key($origk),
                                'field' => 'slug',
                                'terms' => explode(',', $value),
                                'operator'=>!empty($args[$origk.'_operator'])?$args[$origk.'_operator']:'IN',
                                'include_children'=>!empty($args[$origk.'_children'])?false:true,
                            );
                        }
                        unset($args[$key],$args[$origk.'_operator'],$args[$origk.'_children']);
                    }
                    elseif(strpos($key, 'ptb_meta_') === 0){
                        $origk = sanitize_key(str_replace('ptb_meta_', '', $key));
                        if(!isset($cmb_options[$origk]) && strpos($origk,'_exist')!==false){
                            $origk = str_replace('_exist', '', $origk);
                        }
                        if(isset($cmb_options[$origk]) || isset($args[$origk.'_from']) || isset($args[$origk.'_to'])){
                            if(!empty($args[$key.'_exist'])){
                                $meta[$origk] = array(
                                    'key'=>'ptb_' . $origk,
                                    'compare'=>'EXISTS'
                                );
                            }
                            else{
                                $cmb = $cmb_options[$origk];
                                $mtype = isset($args[$origk.'_from']) || isset($args[$origk.'_to'])?'number':$cmb['type'];
                                switch($mtype){
                                    case 'checkbox':
                                    case 'select':
                                    case 'radio_button':
                                        if(empty($cmb['options'])){
                                            continue 2;
                                        }
                                        if($mtype==='select'|| $mtype==='checkbox'){
                                            $value = explode(',',$value);
                                            $args['post__in'] = self::parse_multi_query($value, $type, $origk,$args['post__in']);
                                            
                                            if(!$args['post__in']){
                                                return '';
                                            }
                                        }
                                        else{
                                            if(!isset($cmb['options'][$value])){
                                                return '';
                                            }
                                            $meta[$origk] = array(
                                                'key'=>'ptb_' . $origk,
                                                'compare'=>'=',
                                                'value'=>$value
                                            );
                                        }
                                      
                                    break;
                                    case 'text':
                                        $slike = !empty($args[$origk.'_slike']);
                                        $elike = !empty($args[$origk.'_elike']);
                                       
                                        if(!$cmb['repeatable']){
                                            $meta[$origk] = array(
                                                'key'=>'ptb_' . $origk,
                                                'compare'=>'=',
                                                'value'=>$value
                                            );
                                            if($slike && $elike){
                                                $meta[$origk]['compare'] = 'LIKE';
                                            }
                                            elseif($slike){
                                                $meta[$origk]['compare'] = 'REGEXP';
                                                $meta[$origk]['value'] = '^'.$meta[$origk]['value'];
                                            }
                                            elseif($elike){
                                                $meta[$origk]['compare'] = 'REGEXP';
                                                $meta[$origk]['value'] = $meta[$origk]['value'].'$';
                                            }
                                        }
                                        else{
                                            $post_id = self::parse_multi_query(explode(',',$value), $type, $origk,$args['post__in'],true);
                                            if(empty($post_id)){
                                                return '';
                                            }
                                            foreach($post_id as $i=>$p){
                                                $m = get_post_meta($p,'ptb_'.$origk,true);
                                                if(empty($m)){
                                                    unset($post_id[$i]);
                                                }
                                                else{
                                                    if(!is_array($m)){
                                                        $m = array($m);
                                                    }
                                                    if(!$slike && !$elike && !in_array($value,$m)){// compare =
                                                        unset($post_id[$i]);
                                                    }
                                                    else{//compare like %s%,%s or s%
                                                        $find = false;
                                                        $reg = $slike?'/^'.$value.'/iu': '/'.$value.'$/iu';
                                                        foreach($m as $m1){
                                                            if($slike && $elike){
                                                                if(strpos($m1,$value)!==false){//compare  %s%
                                                                    $find = true;
                                                                    break;
                                                                }
                                                            }
                                                            else{
                                                                if(preg_match($reg, $m1)){
                                                                    $find = true;
                                                                    break;
                                                                }
                                                            }
                                                        }
                                                        if(!$find){
                                                            unset($post_id[$i]);
                                                        }
                                                    }
                                                }
                                            }
                                            if(empty($post_id)){
                                                return '';
                                            }
                                            $args['post__in'] = $post_id;
                                        }
                                        unset($args[$origk.'_elike'],$args[$origk.'_slike']);
                                    break;
                                    case 'number':
                                        if(empty($cmb['range']) && !isset($meta[$origk])){
                                            $from_val =  isset($args[$origk.'_from']) && is_numeric($args[$origk.'_from'])?$args[$origk.'_from']:false;
                                            $to_val = isset($args[$origk.'_to']) &&  is_numeric($args[$origk.'_to'])?$args[$origk.'_to']:false;
                                            $from_sign = $from_val && !empty($args[$origk.'_from_sign'])?html_entity_decode($args[$origk.'_from_sign'],ENT_QUOTES, 'UTF-8'):false;
                                            $to_sign =  $to_val && $from_sign!=='=' && !empty($args[$origk.'_to_sign']) ?html_entity_decode($args[$origk.'_to_sign'],ENT_QUOTES, 'UTF-8'):false;
                                            $meta[$origk] = array(
                                                'key'=>'ptb_' . $origk,
                                                'compare'=>'=',
                                                'value'=>$from_val,
                                                'type'=>'DECIMAL'
                                            );
                                            if($from_sign!=='='){
                                                if($from_sign==='>=' && $to_sign==='<='){
                                                    $meta[$origk]['compare'] = 'BETWEEN';
                                                     $meta[$origk]['value'] = array($from_val,$to_val);
                                                }
                                                elseif($from_sign==='>' || $from_sign==='>='){
                                                    $meta[$origk]['compare'] = $from_sign;
                                                }
                                                if($to_sign==='<' || $to_sign==='<='){
                                                    $meta[$origk.'_to'] =  $meta[$origk];
                                                    $meta[$origk.'_to']['compare'] = $to_sign; 
                                                    $meta[$origk.'_to']['value'] = $to_val;
                                                }
                                            }
                                        }
                                         unset($args[$origk.'_to_sign'],$args[$origk.'_from_sign'],$args[$origk.'_from'],$args[$origk.'_to']);
                                    break;
                                    default:
                                        $meta[$origk] = array(
                                            'key'=>'ptb_' . $origk,
                                            'compare'=>'=',
                                            'value'=>$value
                                        );
                                    break;
                                }
                            }
                        }
                    }
                    elseif(strpos($key, 'ptb_field_') === 0){
                        $origk = sanitize_key(str_replace(array('ptb_field_','_exist','_from','_to'), array('','','',''), $key));
                          
                        if(isset($post_support[$origk]) || isset($args['ptb_field_'.$origk.'_from']) || isset($args['ptb_field_'.$origk.'_to'])){
                            $slike = !empty($args[$origk.'_slike'])?'%':'';
                            $elike = !empty($args[$origk.'_elike'])?'%':'';
                          
                          
                            switch($origk){
                                case 'thumbnail':
                                    $meta['field_'.$origk] = array(
                                        'key'=>'_thumbnail_id',
                                        'compare'=>'EXISTS'
                                );
                                break;
                                case 'title':
                                case 'editor':
                                case 'excerpt':
                                    
                                    if(!empty($args['ptb_field_'.$origk.'_exist'])){
                                        if($origk==='editor'){
                                            $origk = 'content';
                                        }
                                        $conditions[$origk] = " `post_$origk` !='' ";
                                    }
                                    else{
                                        if($origk==='editor'){
                                            $origk = 'content';
                                        }
                                        $conditions[$origk] = '`post_'.$origk.'` LIKE '."'".$slike.esc_sql($value).$elike."'";
                                    }
                                break;
                                case 'author':
                                    $args['author__in'] = explode(',',$value);
                                break;
                                case 'comment_count':
                               
                                    if(!empty($args['ptb_field_'.$origk.'_exist'])){
                                        $conditions[$origk] = "`comment_count`>'0'";
                                    }
                                    elseif(!isset($conditions[$origk])){
                                        $query_comment = array();
                                        $from_val = isset($args['ptb_field_'.$origk.'_from']) && is_numeric($args['ptb_field_'.$origk.'_from'])?(int)$args['ptb_field_'.$origk.'_from']:false;
                                        $to_val = isset($args['ptb_field_'.$origk.'_to']) &&  is_numeric($args['ptb_field_'.$origk.'_to'])?(int)$args['ptb_field_'.$origk.'_to']:false;
                                        $from_sign = $from_val && !empty($args[$origk.'_from_sign'])?html_entity_decode($args[$origk.'_from_sign'],ENT_QUOTES, 'UTF-8'):false;
                                        $to_sign =  $to_val && $from_sign!=='=' && !empty($args[$origk.'_to_sign']) ?html_entity_decode($args[$origk.'_to_sign'],ENT_QUOTES, 'UTF-8'):false;
                                        
                                        if($from_sign){
                                            if(in_array($from_sign,array('>','>=','='))){
                                                $query_comment[]='`comment_count`'.$from_sign."'".$from_val."'";
                                            }
                                        }
                                        if($to_sign){
                                            if(in_array($from_sign,array('>','>='))){
                                                $query_comment[]='`comment_count`'.$to_sign."'".$to_val."'";
                                            }
                                        }
                                        if(!empty($query_comment)){
                                            $conditions[$origk] = implode(' AND ',$query_comment);
                                        }
                                    }
                                break;
                            }
                            unset($args[$origk.'_elike'],$args[$origk.'_slike']);
                        }
                    }
                }
            }
            if(!empty($conditions)){
                if(!empty($args['post__in'])){
                    $conditions[]='ID IN(' . implode(',', $args['post__in']) . ')';
                }
                $conditions = implode(' AND ',$conditions);
                global $wpdb;
                $result_query = $wpdb->get_results("SELECT ID FROM $wpdb->posts WHERE `post_status`='publish' AND post_type='". esc_sql($type) . "' AND $conditions" );
                if(empty($result_query)){
                    return '';
                }
                $args['post__in'] = array();
                foreach($result_query as $p){
                    $args['post__in'][] = $p->ID;
                }
            }
            if (!empty($taxes)) {
                $args['tax_query'] = $taxes;
                $args['tax_query']['relation'] = $args['logic'];
                unset($args['logic']);
            }
            if (!empty($meta)) {
                $args['meta_query'] = $meta;
                $args['meta_query']['relation'] = 'AND';
            }
            if (!isset(PTB_Form_PTT_Archive::$sortfields[$args['orderby']])) {
               
                if(isset($cmb_options[$args['orderby']])){
                    $args['meta_key'] = 'ptb_' . $args['orderby']; 
                    $args['orderby']  = $cmb_options[$args['orderby']]['type']==='number' && empty($cmb_options[$args['orderby']]['range'])?'meta_value_num':'meta_value';
                }
            }
        }
        self::$shortcode = true;
        if (isset($args['offset']) && !$args['offset']) {
            unset($args['offset']);
        }
        $style = $args['style'];
        unset($args['style']);
        // The Query
        $query = new WP_Query(apply_filters('themify_ptb_shortcode_query', $args));

        // The Loop
        if ($query->have_posts()) {
            $themplate = new PTB_Form_PTT_Them($this->plugin_name, $this->version);
            $themplate_layout = isset($args['p']) ? $template->get_single() : $template->get_archive();
            $cmb_options = $post_support = $post_taxonomies = array();
            self::$options->get_post_type_data($type, $cmb_options, $post_support, $post_taxonomies);

            $terms = array();
            $html= '<div class="ptb_loops_wrapper ptb_loops_shortcode clearfix ptb_' .$style . '">';
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $post_meta = array();
                $class = array('ptb_post', 'clearfix');
                $post_meta['post_url'] = get_permalink();
                $post_meta['taxonomies'] = !empty($post_taxonomies) ? wp_get_post_terms($post_id, array_values($post_taxonomies)) : array();
                if (isset($args['post_filter']) && !empty($post_meta['taxonomies'])) {
                    foreach ($post_meta['taxonomies'] as $p) {
                        $class[] = 'ptb-tax-' . $p->term_id;
                        $terms[] = $p->term_id;
                    }
                }
                $post_meta = array_merge($post_meta, get_post_custom(), get_post('', ARRAY_A));
                $html .= '<article id="post-' . $post_id . '" class="' . implode(' ', get_post_class($class)) . '">';
                $html .= $themplate->display_public_themplate($themplate_layout, $post_support, $cmb_options, $post_meta, $type, false);
                $html .= '</article>';
            }
            $html .= '</div>';
            if (isset($args['pagination']) && $query->max_num_pages > 1) {
                $html.='<div class="ptb_pagenav">';
                $html .= paginate_links(array(
                    'total' => $query->max_num_pages,
                    'current' => $args['paged']
                ));
                $html.='</div>';
            }
            if (isset($args['post_filter']) && !isset($args['post_id']) && !empty($terms)) {
                $terms = array_unique($terms);
                $query_terms = get_terms($post_taxonomies, array('hide_empty' => 1, 'hierarchical' => 1, 'pad_counts' => false));

                if (!empty($query_terms)) {
                    $cats = array();
                    foreach ($query_terms as $cat) {
                        if ($cat->parent == 0 || in_array($cat->term_id, $terms)) {
                            $cats[$cat->parent][$cat->term_id] = $cat->name;
                        }
                    }
                    unset($query_terms);
                    foreach ($cats[0] as $pid => &$parent) {
                        if (!isset($cats[$pid]) && !in_array($pid, $terms)) {
                            unset($cats[0][$pid]);
                        }
                    }

                    $filter = '';
                    foreach ($cats[0] as $tid => $cat) {

                        $filter.='<li data-tax="' . $tid . '"><a onclick="return false;" href="' . get_term_link(intval($tid)) . '">' . $cat . '</a>';
                        if (isset($cats[$tid])) {
                            $filter.='<ul class="ptb-post-filter-child">';
                            $filter.=$this->get_Child($cats[$tid], $cats);
                            $filter.='</ul>';
                        }
                        $filter.='</li>';
                    }
                    $html = '<ul class="ptb-post-filter">' . $filter . '</ul>' . $html;
                }
            }
            // Restore original Post Data
            $query->reset_postdata();
            self::$shortcode = false;
            return $html;
        }
        self::$shortcode = false;
        return '';
    }
    
    public static function parse_multi_query(array $value,$type,$k,$post_id=array(),$like=false){
        $like = $like?"meta_value LIKE '%%s%'":"LOCATE('%s',`meta_value`)>0";
        global $wpdb;
        foreach($value as $v){
            $v =  trim($v);
            $condition = str_replace('%s',$v,$like);
            $condition.=!empty($post_id) ? ' AND post_id IN(' . implode(',', $post_id) . ')' : '';
            
       
            $get_values = $wpdb->get_results("SELECT `post_id` FROM `{$wpdb->postmeta}` WHERE `meta_key` = 'ptb_$k' AND $condition");
            if(empty($get_values)){
                return false;
            }

            $ids = array();
            foreach ($get_values as $val) {
                $ids[] = $val->post_id;
            }
            $ids = implode(',', $ids);
            $get_posts = $wpdb->get_results("SELECT `ID` FROM `{$wpdb->posts}` WHERE  ID IN({$ids}) AND `post_type` = '$type' AND `post_status`='publish'");
            if(empty($get_posts)){
                return false;
            }

            foreach ($get_posts as $p) {
                $post_id[] = $p->ID;
            }
            $post_id = array_unique($post_id);
        }
        return $post_id;
    }

    public function single_lightbox() {
        if (!empty($_GET) && isset($_GET['id']) && is_numeric($_GET['id'])) {
            $id = intval($_GET['id']);
            $post = get_post($id);
            if ($post && $post->post_status === 'publish') {
                $short_code = '[ptb post_id=' . $id . ' type=' . $post->post_type . ']';
                echo '<div class="ptb_single_lightbox">' . do_shortcode($short_code) . '</div>';
            }
            wp_die();
        }
    }

    private function get_Child($term, $cats) {
        $filter = '';
        foreach ($term as $tid => $cat) {
            $filter.='<li data-tax="' . $tid . '"><a onclick="return false;" href="' . get_term_link(intval($tid)) . '">' . $cat . '</a></li>';
            if (isset($cats[$tid])) {
                $filter.=$this->get_Child($cats[$tid], $cats);
            }
        }
        return $filter;
    }
    
    public function disable_ptb($args){
        $args['ptb_disable'] = 1;
        return $args;
    }
}

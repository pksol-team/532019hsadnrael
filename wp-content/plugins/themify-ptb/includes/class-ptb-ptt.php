<?php

/**
 * Post Type Template class.
 *
 * This class helps to manipulate with Post Type Templates
 *
 * @link       http://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/includes
 */

/**
 * Post Type Template class.
 *
 * This class helps to manipulate with Post Type Templates
 *
 * @since      1.0.0
 * @package    PTB
 * @subpackage PTB/includes
 * @author     Themify <ptb@themify.me>
 */
class PTB_Post_Type_Template {

    const ID = 'id';
    const NAME = 'name';
    const POST_TYPE = 'post_type';
    const ARCHIVE = 'archive';
    const SINGLE = 'single';

    /**
     * The last css grid classname of arhive themplate
     *
     * @since    1.0.0
     * @access   public
     * @var      string $gridclass
     */
    public static $gridclass = false;

    /**
     * The css grid counter e.g grid3,grid
     *
     * @since    1.0.0
     * @access   public
     * @var      int $gridcounter
     */
    public static $gridcounter = 0;

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
     * The id of post type template.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $id The id of post type template.
     */
    private $id;

    /**
     * The name of template.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $name The name of post type template.
     */
    private $name;

    /**
     * The post type of template.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $post_type The post type of template.
     */
    private $post_type;

    /**
     * Archive template settings
     *
     * @since    1.0.0
     * @access   private
     * @var      array $archive archive template settings.
     */
    private $archive;

    /**
     * Single template settings
     *
     * @since    1.0.0
     * @access   private
     * @var      array $single single template settings.
     */
    private $single;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @var      string $plugin_name The name of the plugin.
     * @var      string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

        $this->archive = array();
        $this->single = array();
    }

    /**
     * Serialization of post type template class for storing in WP options.
     * This function mainly used by PTB_Options class.
     *
     * @since   1.0.0
     *
     * @return array Serialized array of post type template
     */
    public function serialize($ptt_id = false) {

        return apply_filters('ptb_template_serialize', array(
            self::NAME => $this->name,
            self::POST_TYPE => $this->post_type,
            self::ARCHIVE => $this->archive,
            self::SINGLE => $this->single
                ), $ptt_id);
    }

    /**
     * De-serialization of post type template class from options.
     * This function mainly used by PTB_Options class and
     * should be called right after constructor.
     *
     * @since   1.0.0
     *
     * @param array $source Serialized options of post type template
     *
     */
    public function deserialize($source) {

        $this->name = isset($source[self::NAME]) ? $source[self::NAME] : '';
        $this->post_type = isset($source[self::POST_TYPE]) ? $source[self::POST_TYPE] : '';
        $this->archive = isset($source[self::ARCHIVE]) ? $source[self::ARCHIVE] : array();
        $this->single = isset($source[self::SINGLE]) ? $source[self::SINGLE] : array();
    }

    /**
     * Getter of template id
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_id() {

        return $this->id;
    }

    /**
     * Setter of template id
     *
     * @since 1.0.0
     *
     * @param string $id
     */
    public function set_id($id) {

        $this->id = $id;
    }

    /**
     * Getter of template name
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_name() {

        return $this->name;
    }

    /**
     * Setter of template name
     *
     * @since 1.0.0
     *
     * @param string $name
     */
    public function set_name($name) {

        $this->name = $name;
    }

    /**
     * Setter of template post type
     *
     * @since 1.0.0
     * @return string
     */
    public function get_post_type() {

        return $this->post_type;
    }

    /**
     * Getter of template post type
     *
     * @since 1.0.0
     *
     * @param string $post_type
     */
    public function set_post_type($post_type) {

        $this->post_type = $post_type;
    }

    /**
     * @return array
     */
    public function get_archive() {

        return $this->archive;
    }

    /**
     * @param array $archive
     */
    public function set_archive($archive) {

        $this->archive = $archive;
    }

    /**
     * @return array
     */
    public function get_single() {

        return $this->single;
    }

    /**
     * @param array $single
     */
    public function set_single($single) {

        $this->single = $single;
    }

    /**
     * @return bool
     */
    public function has_archive() {

        return !empty($this->archive);
    }

    /**
     * @return bool
     */
    public function has_single() {

        return !empty($this->single);
    }

}

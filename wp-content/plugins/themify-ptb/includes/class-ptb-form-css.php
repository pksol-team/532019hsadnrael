<?php

class PTB_Form_Css {

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $plugin_name The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $version The current version of the plugin.
     */
    protected $version;
    protected $slug_admin_css;

    /**
     * The options management class of the the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      PTB_Options $options Manipulates with plugin options
     */
    protected $options;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     *
     * @param string $plugin_name
     * @param string $version
     * @param PTB_Options $options the plugin options instance
     *
     */
    public function __construct($plugin_name, $version, $options) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->options = $options;
    }

    public function add_settings_fields($slug_admin_css) {

        $this->slug_admin_css = $slug_admin_css;
        add_settings_section('', '', array($this, 'import_section_cb'), $this->slug_admin_css);
    }

    public function import_section_cb() {
        $custom_css = $this->options->get_custom_css();
        ?>
        <form  method="post" action="options.php" enctype="multipart/form-data" id="<?php echo $this->plugin_name ?>_form_css">
            <?php settings_fields('ptb_plugin_options'); ?>
            <textarea name="ptb_plugin_options[<?php echo $this->plugin_name . '_css' ?>]"><?php if ($custom_css): ?><?php echo esc_attr($custom_css); ?><?php endif; ?></textarea>
            <?php submit_button(__('Save', 'ptb')); ?>
        </form>
        <?php
    }

    /**
     * @param array $input The inputs array of custom taxonomy
     *
     * @since    1.0.0
     */
    public function process_options($input) {
        $value = sanitize_text_field($input[$this->plugin_name . '_css']);
        $this->options->set_custom_css($value);
    }

}

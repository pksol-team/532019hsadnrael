<?php
namespace Getresponse\WordPress;

/**
 * Class GetResponse
 * @package Getresponse\WordPress
 */
class GetResponse {

    /** @var bool */
    public $enable_log = true;
    /** @var string */
    public $plugin_name;
    /** @var string */
    public $plugin_dir;
    /** @var string  */
    public $basename;
    /** @var string */
    public $traceroute_host = 'api.getresponse.com';
    /** @var string  */
    public $contact_form_url = "https://app.getresponse.com/feedback.html?devzone=yes&lang=en";
    /** @var string */
    public $log_path = '';
    /** @var Settings */
    public $settings = null;
    /** @var FlashMessages */
    private $flashMessages;
    /** @var string  */
    public $settings_page = 'gr-integration.php';
    /** @var BuddyPress */
    public $buddypress = null;
    /** @var DbHandler  */
    public $db = null;
    /** @var DbValidator */
    public $db_validator = null;
    /** @var GrWidget  */
    public $int_widget = null;
    /** @var null  */
    public $asset_path = null;
    /** @var ContactForm7 */
    public $contactForm7 = null;
    /** @var NinjaForms */
    public $ninjaForms = null;
    /** @var CoreAdmin */
    public $gr_core_admin = null;

    /**
     * Get instance of GetResponse class.
     * @return GetResponse
     */
    public static function instance() {

        static $instance;

        if ( null === $instance ) {
            $instance = new GetResponse();
            $instance->setup_globals();
            $instance->setup_actions();

            $instance->ninjaForms = new NinjaForms();
            $instance->contactForm7 = new ContactForm7();
            $instance->gr_core_admin = new CoreAdmin();
            $instance->buddypress = new BuddyPress();
            $instance->db = new DbHandler();
            $instance->db_validator = new DbValidator();
            $instance->settings = new Settings();
            $instance->flashMessages = new FlashMessages();
        }

        return $instance;
    }

    /**
     * Setup environment globals variables.
     */
    private function setup_globals() {

        $this->plugin_dir = plugin_dir_path( __FILE__ );

        $this->basename    = basename( $this->plugin_dir ) . '/gr-loader.php';
        $this->plugin_name = 'GetResponse';
        $this->log_path    = $this->plugin_dir . 'log.txt';

        $url              = untrailingslashit( plugins_url( '/', __FILE__ ) );
        $this->asset_path = $url . '/gr-assets';
    }

    /**
     * Setup actions.
     *
     * There is only actions required in the whole plugin (admin + front site).
     */
    private function setup_actions() {
        add_action('wp', array( &$this, 'gr_lpc' ), 1 );
        add_action( 'plugins_loaded', array( &$this, 'gr_langs' ) );
        add_shortcode( 'grwebform', array( &$this, 'show_webform_short_code' ) );
        add_action( 'wp_head', array( &$this, 'add_tracking_code' ) );
        add_action( 'wp_footer', array( &$this, 'tracking_code_set_user_id' ) );
    }

    public function add_tracking_code() {
        $api = ApiFactory::create_api();

        if ( empty( $api ) ) {
            return;
        }

        $service = new TrackingCodeService( $api );

        if ( ! $service->get_status() ) {
            return;
        }

        $tracking_code = $service->get_tracking_code();

        if ( ! empty( $tracking_code ) ) {
            echo $tracking_code['snippet'];
        }
    }

    public function tracking_code_set_user_id() {

        $api = ApiFactory::create_api();

        if ( empty( $api ) ) {
            return;
        }

        $service       = new TrackingCodeService( $api );
        $tracking_code = $service->get_tracking_code();

        if ( empty( $tracking_code ) ) {
            return;
        }

        $user = wp_get_current_user();

        if ( ! empty( $user->user_email ) ) {
            echo '<script type="text/javascript">
				if(window.addEventListener){
				  window.addEventListener("load", function() { gaSetUserId("' . $user->user_email . '"); })
				}else{
				  window.attachEvent("onload", function() { gaSetUserId("' . $user->user_email . '"); } )
				}
			</script>';
        }
    }

    /**
     * Show WebFrom short code.
     * @param array $atts.
     * @return string
     */
    public function show_webform_short_code( $atts ) {

        $params = shortcode_atts( array(
            'url'           => 'null',
            'css'           => 'on',
            'center'        => 'off',
            'center_margin' => '200',
            'variant'       => ''
        ), $atts );

        $div_start = $div_end = '';
        if ( $params['center'] == 'on' ) {
            $div_start = '<div style="margin-left: auto; margin-right: auto; width: ' . $params['center_margin'] . 'px;">';
            $div_end   = '</div>';
        }

        $css = ( $params['css'] == "off" ) ? htmlspecialchars( "&css=1" ) : "";

        $variant_maps      = array( 'A' => 0, 'B' => 1, 'C' => 2, 'D' => 3, 'E' => 4, 'F' => 5, 'G' => 6, 'H' => 7 );
        $params['variant'] = strtoupper( $params['variant'] );
        $variant           = ( in_array( $params['variant'],
            array_keys( $variant_maps ) ) ) ? htmlspecialchars( "&v=" . $variant_maps[ $params['variant'] ] ) : "";

        $params['url'] = $this->replace_https_to_http_if_ssl_on( $params['url'] );

        return $div_start . '<script type="text/javascript" src="' . $params['url'] . $css . $variant . '"></script>' . $div_end;
    }

    /**
     * Replace https prefix in url if ssl is off
     *
     * @param $url
     *
     * @return mixed
     */
    public function replace_https_to_http_if_ssl_on( $url ) {
        return ( ! empty( $url ) && ! is_ssl() && strpos( $url, 'https' ) === 0 ) ? str_replace( 'https', 'http',
            $url ) : $url;
    }

    /**
     * Check, if file exists.
     *
     * * @param $template string template file source.
     *
     * @return bool
     */
    public function locate_template( $template ) {

        $path = $this->plugin_dir . 'gr-templates/' . $template;

        if ( is_file( $path ) && file_exists( $path ) ) {
            return true;
        }

        return false;
    }

    /**
     * Log data to file.
     *
     * * @param $data
     *
     */
    public function log( $data ) {

        if ( false === $this->enable_log ) {
            return;
        }

        if ( false === is_file( gr()->log_path ) ) {
            return;
        }

        if ( $fh = fopen( gr()->log_path, 'w' ) ) {

            if ( null === $data ) {
                $message = "\n";
            } else {
                $message = "\n" . date( 'Y-m-d H:i:s' ) . ' ' . (string) $data;
            }

            fwrite( $fh, $message, 1024 );
            fclose( $fh );
        }
    }

    /**
     * Load template file.
     *
     * @param string $template template file source.
     * @param array $params array of variables.
     */
    public function load_template( $template, $params = array() ) {
        $path = $this->plugin_dir . 'gr-templates/' . $template;

        if ( false === empty( $params ) ) {
            extract( $params, EXTR_OVERWRITE );
        }

        require( $path );
    }

    /**
     * @param string $message
     */
    public function add_error_message($message)
    {
        $this->flashMessages->addErrorMessage($message);
    }

    /**
     * @return array
     */
    public function getErrorMessages()
    {
        return $this->flashMessages->getErrorMessages();
    }

    /**
     * @param string $message
     */
    function add_success_message($message)
    {
        $this->flashMessages->addSuccessMessage($message);
    }

    /**
     * @return array
     */
    public function getSuccessMessages()
    {
        return $this->flashMessages->getSuccessMessages();
    }

    /**
     * Check requirements.
     *
     */
    public function check_requirements() {

        if ( false === $this->valid_curl_extension() ) {
            return false;
        }

        return true;
    }

    /**
     * Check, if curl extension is available.
     */
    public function valid_curl_extension() {
        if ( extension_loaded( 'curl' ) && is_callable( 'curl_init' ) ) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function is_connected_to_getresponse() {
        $api_key = gr_get_option( 'api_key' );

        if ( empty( $api_key ) ) {
            return false;
        }

        return true;
    }

    public function is_active_woocommerce_checkout() {
        return (bool) get_option( 'woocommerce_checkout_on' );
    }

    public function disconnect_integration() {

        foreach ( wp_load_alloptions() as $option => $value ) {
            if ( strpos( $option, 'gr_' ) === 0 ) {
                delete_option( $option );
            }
        }

        delete_option('widget_getresponse-widget');
        wp_cache_flush();

    }

    /**
     * Check, if plugin is active.
     */
    public function is_woocommerce_plugin_active() {

        $plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );

        foreach ($plugins as $plugin) {
            if( preg_match('/woocommerce\.php/', $plugin)) {
                return true;
            }
        }

        return false;
    }

    public function gr_langs()
    {

    }

    public function gr_lpc()
    {
        $service = new LandingPageService(ApiFactory::create_api());

        if (false === $this->is_connected_to_getresponse()) {
            return;
        }

        $slug = urldecode($this->get_actual_path());
        $url = explode( '?', $slug );
        $page = $service->get_page_by_slug( trim( $url[0], '/' ) );

        if ( empty( $page ) ) {
            return;
        }

        if (isset($url[1])) {
            $page['url'] .= '?' . $url[1];
        }

        if ( !empty( $page ) && $page['status'] === '1') {
            $this->display_iframe( $page['title'], $page['url'] );
        }
    }

    /**
     * @return string
     */
    public function get_actual_path()
    {
        return trim(
            strtr (
                home_url( add_query_arg( null, null ) ),
                array( home_url() => '' )
            ),
            '/ '
        );
    }

    /**
     * @param string $title
     * @param string $url
     */
    public function display_iframe($title, $url )
    {
        http_response_code(200);
        ob_start();
        echo '<html><title>' . $title . '</title><style>*{margin:0;padding:0;}body,html{height:100vh}iframe{height:100vh;width:100vw;border:none;}</style><meta name="viewport" content="width=device-width, initial-scale=1.0"><iframe src="' . $url . '"></iframe></html>';
        ob_end_flush();
        die;
    }
}
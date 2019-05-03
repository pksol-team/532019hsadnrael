<?php

/**
 * Utility class with various static functions
 *
 * This class helps to manipulate with arrays
 *
 * @link       http://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/includes
 */

/**
 * Utility class of various static functions
 *
 * This class helps to manipulate with arrays
 *
 * @since      1.0.0
 * @package    PTB
 * @subpackage PTB/includes
 * @author     Themify <ptb@themify.me>
 */
class PTB_Utils {

    /**
     * This function add the value to array if it's already not in array
     *
     * @since      1.0.0
     *
     * @param mixed $value The value to add
     * @param array $array The reference of array
     *
     * @return bool Returns true if value added to array and false if value already in array
     */
    public static function add_to_array($value, &$array) {

        if (!in_array($value, $array)) {
            $array[] = $value;
            return true;
        }

        return false;
    }

    /**
     * This function remove the value from array if it's in array
     *
     * @since      1.0.0
     *
     * @param mixed $value The value to remove
     * @param array $array The reference of array
     *
     * @return bool Returns true if value removed from array and false if value does not exist in array
     */
    public static function remove_from_array($value, &$array) {

        $key = array_search($value, $array);

        if (false !== $key) {

            unset($array[$key]);

            return true;
        }

        return false;
    }

    /**
     * Divides array into segments provided in argument
     *
     * @since 1.0.0
     *
     * @param $array
     * @param int $segmentCount
     *
     * @return array|bool
     */
    public static function array_divide($array, $segmentCount = 2) {
        $dataCount = count($array);
        if ($dataCount === 0) {
            return false;
        }
        $segmentLimit = ceil($dataCount / $segmentCount);
        $outputArray = array_chunk($array, $segmentLimit);

        return $outputArray;
    }

    /**
     * Log array to wp debug file
     *
     * @param array $array
     */
    public static function Log_Array($array) {

        error_log(print_r($array, true));
    }

    /**
     * Log to wp debug file
     *
     * @param string $value
     */
    public static function Log($value) {

        error_log(print_r($value, true));
    }

    /**
     * Returns the current language code
     *
     * @since 1.0.0
     *
     * @return string the language code, e.g. "en"
     */
    public static function get_current_language_code() {
        
        static $language_code = false;
        if($language_code){
            return $language_code;
        }
        if (defined('ICL_LANGUAGE_CODE')) {

            $language_code = ICL_LANGUAGE_CODE;
        } elseif (function_exists('qtrans_getLanguage')) {

            $language_code = qtrans_getLanguage();
        }
        if (!$language_code) {
            $language_code = substr(get_bloginfo('language'), 0, 2);
        }
        $language_code = strtolower(trim($language_code));
        return $language_code;
    }

    /**
     * Returns the site languages
     *
     * @since 1.0.0
     *
     * @return array the languages code, e.g. "en",name e.g English
     */
    public static function get_all_languages() {

        static $languages = array();
        if(!empty($languages)){
            return $languages;
        }
        if (defined('ICL_LANGUAGE_CODE')) {
            $lng = self::get_current_language_code();
            if ($lng == 'all') {
                $lng = self::get_default_language_code();
            }
            $all_lang = icl_get_languages('skip_missing=0&orderby=KEY&order=DIR&link_empty_to=str');
            foreach ($all_lang as $key => $l) {
                if ($lng == $key) {
                    $languages[$key]['selected'] = true;
                }
                $languages[$key]['name'] = $l['native_name'];
            }
        } elseif (function_exists('qtrans_getLanguage')) {

            $languages = qtrans_getSortedLanguages();
        } else {

            $all_lang = self::get_default_language_code();
            $languages[$all_lang]['name'] = '';
            $languages[$all_lang]['selected'] = true;
        }
        return $languages;
    }

    /**
     * Returns the current language code
     *
     * @since 1.0.0
     *
     * @return string the language code, e.g. "en"
     */
    public static function get_default_language_code() {

        global $sitepress;
        static $language_code=false;
        if($language_code!==false){
            return $language_code;
        }
        if (isset($sitepress)) {

            $language_code = $sitepress->get_default_language();
        }

        $language_code = empty($language_code) ? substr(get_bloginfo('language'), 0, 2) : $language_code;
        $language_code = strtolower(trim($language_code));
        return $language_code;
    }

    public static function get_label($label) {
        if (!is_array($label)) {
            return esc_attr($label);
        }
        static $lng=false;
        if($lng===false){
            $lng = self::get_current_language_code();
        }
        $value = '';
        if (isset($label[$lng]) && $label[$lng]) {
            $value = $label[$lng];
        } else {
            static $default_lng=false;
            if($default_lng===false){
                $default_lng = self::get_default_language_code();
            }
            $value = isset($label[$default_lng]) && $label[$default_lng] ? $label[$default_lng] : current($label);
        }
        return esc_attr($value);
    }
    
    
    public static function get_reserved_terms(){

            return array(
                    'attachment',
                    'attachment_id',
                    'author',
                    'author_name',
                    'calendar',
                    'cat',
                    'category',
                    'category__and',
                    'category__in',
                    'category__not_in',
                    'category_name',
                    'comments_per_page',
                    'comments_popup',
                    'custom',
                    'customize_messenger_channel',
                    'customized',
                    'cpage',
                    'day',
                    'debug',
                    'embed',
                    'error',
                    'exact',
                    'feed',
                    'hour',
                    'link_category',
                    'm',
                    'minute',
                    'monthnum',
                    'more',
                    'name',
                    'nav_menu',
                    'nonce',
                    'nopaging',
                    'offset',
                    'order',
                    'orderby',
                    'p',
                    'page',
                    'page_id',
                    'paged',
                    'pagename',
                    'pb',
                    'perm',
                    'post',
                    'post__in',
                    'post__not_in',
                    'post_format',
                    'post_mime_type',
                    'post_status',
                    'post_tag',
                    'post_type',
                    'posts',
                    'posts_per_archive_page',
                    'posts_per_page',
                    'preview',
                    'robots',
                    's',
                    'search',
                    'second',
                    'sentence',
                    'showposts',
                    'static',
                    'subpost',
                    'subpost_id',
                    'tag',
                    'tag__and',
                    'tag__in',
                    'tag__not_in',
                    'tag_id',
                    'tag_slug__and',
                    'tag_slug__in',
                    'taxonomy',
                    'tb',
                    'term',
                    'terms',
                    'theme',
                    'title',
                    'type',
                    'w',
                    'withcomments',
                    'withoutcomments',
                    'year'
            );
    }
    public static function start_session() {
        if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
            if (session_status() == PHP_SESSION_NONE) {
		 session_start();
    }
        } elseif (session_id() == '') {
            session_start();
        }
    }
    
    public static function set_cookie($name, $value, $expire=2592000,$admin=true, $secure = false){
        $_COOKIE[$name] = $value;
        $path = $admin?ADMIN_COOKIE_PATH:COOKIEPATH;
        setcookie( $name, $value, $expire, $path ? $path : '/', COOKIE_DOMAIN, $secure );
    }
    
    
    /**
     * Get full list of currency codes.
     * @return array
     */
    public static function get_currencies() {
        return array_unique(
                apply_filters('ptb_currencies', array(
            'AED' => __('United Arab Emirates Dirham', 'ptb'),
            'ARS' => __('Argentine Peso', 'ptb'),
            'AUD' => __('Australian Dollars', 'ptb'),
            'BDT' => __('Bangladeshi Taka', 'ptb'),
            'BRL' => __('Brazilian Real', 'ptb'),
            'BGN' => __('Bulgarian Lev', 'ptb'),
            'CAD' => __('Canadian Dollars', 'ptb'),
            'CLP' => __('Chilean Peso', 'ptb'),
            'CNY' => __('Chinese Yuan', 'ptb'),
            'COP' => __('Colombian Peso', 'ptb'),
            'CZK' => __('Czech Koruna', 'ptb'),
            'DKK' => __('Danish Krone', 'ptb'),
            'DOP' => __('Dominican Peso', 'ptb'),
            'EUR' => __('Euros', 'ptb'),
            'HKD' => __('Hong Kong Dollar', 'ptb'),
            'HRK' => __('Croatia kuna', 'ptb'),
            'HUF' => __('Hungarian Forint', 'ptb'),
            'ISK' => __('Icelandic krona', 'ptb'),
            'IDR' => __('Indonesia Rupiah', 'ptb'),
            'INR' => __('Indian Rupee', 'ptb'),
            'NPR' => __('Nepali Rupee', 'ptb'),
            'ILS' => __('Israeli Shekel', 'ptb'),
            'JPY' => __('Japanese Yen', 'ptb'),
            'KIP' => __('Lao Kip', 'ptb'),
            'KRW' => __('South Korean Won', 'ptb'),
            'MYR' => __('Malaysian Ringgits', 'ptb'),
            'MXN' => __('Mexican Peso', 'ptb'),
            'NGN' => __('Nigerian Naira', 'ptb'),
            'NOK' => __('Norwegian Krone', 'ptb'),
            'NZD' => __('New Zealand Dollar', 'ptb'),
            'PYG' => __('Paraguayan Guaraní', 'ptb'),
            'PHP' => __('Philippine Pesos', 'ptb'),
            'PLN' => __('Polish Zloty', 'ptb'),
            'GBP' => __('Pounds Sterling', 'ptb'),
            'RON' => __('Romanian Leu', 'ptb'),
            'RUB' => __('Russian Ruble', 'ptb'),
            'SGD' => __('Singapore Dollar', 'ptb'),
            'ZAR' => __('South African rand', 'ptb'),
            'SEK' => __('Swedish Krona', 'ptb'),
            'CHF' => __('Swiss Franc', 'ptb'),
            'TWD' => __('Taiwan New Dollars', 'ptb'),
            'THB' => __('Thai Baht', 'ptb'),
            'TRY' => __('Turkish Lira', 'ptb'),
            'UAH' => __('Ukrainian Hryvnia', 'ptb'),
            'USD' => __('US Dollars', 'ptb'),
            'VND' => __('Vietnamese Dong', 'ptb'),
            'EGP' => __('Egyptian Pound', 'ptb')
                        )
                )
        );
    }

    /**
     * Get Currency symbol.
     * @param string $currency
     * @return string
     */
    public static function get_currency_symbol($currency) {
        static $return = array();
        if(empty($return[$currency])){

            switch ($currency) {
                case 'AED' :
                    $currency_symbol = 'د.إ';
                    break;
                case 'AUD' :
                case 'ARS' :
                case 'CAD' :
                case 'CLP' :
                case 'COP' :
                case 'HKD' :
                case 'MXN' :
                case 'NZD' :
                case 'SGD' :
                case 'USD' :
                    $currency_symbol = '&#36;';
                    break;
                case 'BDT':
                    $currency_symbol = '&#2547;&nbsp;';
                    break;
                case 'BGN' :
                    $currency_symbol = '&#1083;&#1074;.';
                    break;
                case 'BRL' :
                    $currency_symbol = '&#82;&#36;';
                    break;
                case 'CHF' :
                    $currency_symbol = '&#67;&#72;&#70;';
                    break;
                case 'CNY' :
                case 'JPY' :
                case 'RMB' :
                    $currency_symbol = '&yen;';
                    break;
                case 'CZK' :
                    $currency_symbol = '&#75;&#269;';
                    break;
                case 'DKK' :
                    $currency_symbol = 'DKK';
                    break;
                case 'DOP' :
                    $currency_symbol = 'RD&#36;';
                    break;
                case 'EGP' :
                    $currency_symbol = 'EGP';
                    break;
                case 'EUR' :
                    $currency_symbol = '&euro;';
                    break;
                case 'GBP' :
                    $currency_symbol = '&pound;';
                    break;
                case 'HRK' :
                    $currency_symbol = 'Kn';
                    break;
                case 'HUF' :
                    $currency_symbol = '&#70;&#116;';
                    break;
                case 'IDR' :
                    $currency_symbol = 'Rp';
                    break;
                case 'ILS' :
                    $currency_symbol = '&#8362;';
                    break;
                case 'INR' :
                    $currency_symbol = 'Rs.';
                    break;
                case 'ISK' :
                    $currency_symbol = 'Kr.';
                    break;
                case 'KIP' :
                    $currency_symbol = '&#8365;';
                    break;
                case 'KRW' :
                    $currency_symbol = '&#8361;';
                    break;
                case 'MYR' :
                    $currency_symbol = '&#82;&#77;';
                    break;
                case 'NGN' :
                    $currency_symbol = '&#8358;';
                    break;
                case 'NOK' :
                    $currency_symbol = '&#107;&#114;';
                    break;
                case 'NPR' :
                    $currency_symbol = 'Rs.';
                    break;
                case 'PHP' :
                    $currency_symbol = '&#8369;';
                    break;
                case 'PLN' :
                    $currency_symbol = '&#122;&#322;';
                    break;
                case 'PYG' :
                    $currency_symbol = '&#8370;';
                    break;
                case 'RON' :
                    $currency_symbol = 'lei';
                    break;
                case 'RUB' :
                    $currency_symbol = '&#1088;&#1091;&#1073;.';
                    break;
                case 'SEK' :
                    $currency_symbol = '&#107;&#114;';
                    break;
                case 'THB' :
                    $currency_symbol = '&#3647;';
                    break;
                case 'TRY' :
                    $currency_symbol = '&#8378;';
                    break;
                case 'TWD' :
                    $currency_symbol = '&#78;&#84;&#36;';
                    break;
                case 'UAH' :
                    $currency_symbol = '&#8372;';
                    break;
                case 'VND' :
                    $currency_symbol = '&#8363;';
                    break;
                case 'ZAR' :
                    $currency_symbol = '&#82;';
                    break;
                default :
                    $currency_symbol = '';
                    break;
            }
            $return[$currency] = apply_filters('ptb_currency_symbol', $currency_symbol, $currency);
        }
        return $return[$currency];
    }

    /**
     * Get full list of currency codes.
     * @return array
     */
    public static function get_currency_position() {
        return array_unique(
                apply_filters('ptb_currency_position', array(
                        'left' => __('Left (£99.99)', 'ptb'),
                        'right' => __('Right (99.99£)', 'ptb'),
                        'left_space' => __('Left with space (£ 99.99)', 'ptb'),
                        'right_space' => __('Right with space (99.99 £)', 'ptb')
                                    )
                )
        );
    }
    
    /**
     * Get the price format depending on the currency position
     *
     * @return string
     */
    public static function  get_price_format($currency_pos, $currency, $price) {
       
        switch ($currency_pos) {
            case 'left' :
                $format = '%1$s%2$s';
                break;
            case 'right' :
                $format = '%2$s%1$s';
                break;
            case 'left_space' :
                $format = '%1$s&nbsp;%2$s';
                break;
            case 'right_space' :
                $format = '%2$s&nbsp;%1$s';
                break;
        }
        $format = apply_filters('ptb_price_format', $format, $currency, $currency_pos);
        return sprintf($format, self::get_currency_symbol($currency), $price);
    }
    
    /**
     * Check if ajax request
     *
     * @param void
     *
     * return boolean
     */
    public static function is_ajax() {
        static $is_ajax = null;
        if(is_null($is_ajax)){
            $is_ajax = defined('DOING_AJAX') || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
        }
        return $is_ajax;
    }
}

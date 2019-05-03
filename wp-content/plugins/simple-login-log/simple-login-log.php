<?php
/*
  Plugin Name: Simple Login Log
  Plugin URI: http://simplerealtytheme.com
  Description: This plugin keeps a log of WordPress user logins. Offers user filtering and export features.
  Author: Max Chirkov
  Version: 1.1.3
  Author URI: http://SimpleRealtyTheme.com
 */

//TODO: add cleanup method on uninstall

if( !class_exists( 'SimpleLoginLog' ) )
{

 class SimpleLoginLog
 {
    private $db_ver = "1.3";
    public $table = 'simple_login_log';
    private $log_duration = null; //days
    private $opt_name = 'simple_login_log';
    private $opt = false;
    private $login_success = 1;
    public $data_labels = array();

     private $values;

    function __construct()
    {
        global $wpdb;

        if ( is_multisite() )
        {
            // get main site's table prefix
            $main_prefix = $wpdb->get_blog_prefix(1);
            $this->table = $main_prefix . $this->table;
        }
        else
        {
            // non-multisite - regular table name
            $this->table = $wpdb->prefix . $this->table;
        }
        $this->opt = get_option($this->opt_name);

        //Get plugin's DB version
        $this->installed_ver = get_option( "sll_db_ver" );

        add_action( 'admin_menu', array($this, 'sll_admin_menu') );
        add_action('admin_init', array($this, 'settings_api_init') );
        add_action('admin_head', array($this, 'screen_options') );

        //check if db needs to be upgraded after plugin update was completed
        add_action('plugins_loaded', array($this, 'update_db_check') );

        //Init login actions
        add_action( 'init', array($this, 'init_login_actions') );

        //Init CSV Export
        add_action('admin_init', array($this, 'init_csv_export') );
        add_action('admin_init', array($this, 'delete_all') );

        //Style the log table
        add_action( 'admin_head', array($this, 'admin_header') );

        //Initialize scheduled events (when some one visits site in front-end)
        add_action( 'wp', array($this, 'init_scheduled_events') );
        add_action('truncate_sll', array($this, 'cron') );

        //Load Locale
        add_action('plugins_loaded', array($this, 'load_locale'), 10 );

        //For translation purposes
        $this->data_labels = array(
            'Successful'        => __('Successful', 'sll'),
            'Failed'            => __('Failed', 'sll'),
            'Login'             => __('Login', 'sll'),
            'User Agent'        => __('User Agent', 'sll'),
            'Login Redirect'    => __('Login Redirect', 'sll'),
            'id'                => __('#', 'sll'),
            'uid'               => __('User ID', 'sll'),
            'user_login'        => __('Username', 'sll'),
            'user_role'         => __('User Role', 'sll'),
            'name'              => __('Name', 'sll'),
            'time'              => __('Time', 'sll'),
            'ip'                => __('IP Address', 'sll'),
            'login_result'      => __('Login Result', 'sll'),
            'data'              => __('Data', 'sll'),
        );

        //Deactivation hook
        register_deactivation_hook(__FILE__, array($this, 'deactivation') );

    }


     function set($name, $value)
     {
         $this->values[$name] = $value;
     }


     function get($name)
     {
         return (isset($this->values[$name])) ? $this->values[$name] : false;
     }


    function load_locale()
    {
            load_plugin_textdomain( 'sll', false, basename(dirname(__FILE__)) . '/languages/' );
    }


    function cron()
    {
        SimpleLoginLog::truncate_log();
    }


    function screen_options()
    {

        //execute only on login_log page, othewise return null
        $page = ( isset($_GET['page']) ) ? esc_attr($_GET['page']) : false;
        if( 'login_log' != $page )
            return;

        $current_screen = get_current_screen();

        //define options
        $per_page_field = 'per_page';
        $per_page_option = $current_screen->id . '_' . $per_page_field;

        //Save options that were applied
        if( isset($_REQUEST['wp_screen_options']) && isset($_REQUEST['wp_screen_options']['value']) )
        {
            update_option( $per_page_option, esc_html($_REQUEST['wp_screen_options']['value']) );
        }

        //prepare options for display

        //if per page option is not set, use default
        $per_page_val = get_option($per_page_option, 20);
        $args = array('label' => __('Records', 'sll'), 'default' => $per_page_val );

        //display options
        add_screen_option($per_page_field, $args);
        $_per_page = get_option('users_page_login_log_per_page');

        //needs to be initialized early enough to pre-fill screen options section in the upper (hidden) area.
        $this->log_table = new SLL_List_Table;
    }


    function init_login_actions()
    {
        //condition to check if "log failed attempts" option is selected

        //Action on successful login
        add_action( 'wp_login', array($this, 'login_success') );

        //Action on failed login
        if( isset($this->opt['failed_attempts']) ){
            add_action( 'wp_login_failed', array($this, 'login_failed') );
        }

    }


    function login_success( $user_login )
    {
        $this->login_success = 1;
        $this->login_action( $user_login );
    }


    function login_failed( $user_login )
    {
        $this->login_success = 0;
        $this->login_action( $user_login );
    }


    function init_scheduled_events()
    {

        $log_duration = get_option('simple_login_log');

        if ( $log_duration && !wp_next_scheduled( 'truncate_sll' ) )
        {
            $start = time();
            wp_schedule_event($start, 'daily', 'truncate_sll');
        } elseif( !$log_duration || 0 == $log_duration)
        {
            $timestamp = wp_next_scheduled( 'truncate_sll' );
            (!$timestamp) ? false : wp_unschedule_event($timestamp, 'truncate_sll');

        }
    }


    function deactivation()
    {
        wp_clear_scheduled_hook('truncate_sll');

        //clean up old cron jobs that no longer exist
        wp_clear_scheduled_hook('truncate_log');
        wp_clear_scheduled_hook('SimpleLoginLog::truncate_log');
    }


    function truncate_log()
    {
        global $wpdb;

        $opt = get_option('simple_login_log');
        $log_duration = (int)$opt['log_duration'];

        if( 0 < $log_duration ){
            $sql = $wpdb->prepare( "DELETE FROM {$this->table} WHERE time < DATE_SUB(CURDATE(),INTERVAL %d DAY)", $log_duration);
            $wpdb->query($sql);
        }

    }


     function delete_all()
     {
         global $wpdb;

         $nonce = isset($_REQUEST['_wpnonce']) ? $_REQUEST['_wpnonce'] : false;

         if (!wp_verify_nonce($nonce, 'delete_sll'))
         {
             return;
         }
         else
         {
             $sql = "DELETE FROM {$this->table}";

             if ($wpdb->query($sql))
             {
                 $this->set('deleted', true);
             }
         }
     }



    /**
    * Runs via plugin activation hook & creates a database
    */
    function install()
    {
        global $wpdb;

        if( $this->installed_ver != $this->db_ver )
        {
            //if table does't exist, create a new one
            if( !$wpdb->get_row("SHOW TABLES LIKE '{$this->table}'") ){
                $sql = "CREATE TABLE  " . $this->table . "
                    (
                        id INT( 11 ) NOT NULL AUTO_INCREMENT ,
                        uid INT( 11 ) NOT NULL ,
                        user_login VARCHAR( 60 ) NOT NULL ,
                        user_role VARCHAR( 255 ) NOT NULL ,
                        time DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL ,
                        ip VARCHAR( 100 ) NOT NULL ,
                        login_result VARCHAR (1) ,
                        data LONGTEXT NOT NULL ,
                        PRIMARY KEY ( id ) ,
                        INDEX ( uid, ip, login_result )
                    );";

                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                dbDelta($sql);

                update_option( "sll_db_ver", $this->db_ver );
            }
        }


    }


    /**
    * Checks if the installed database version is the same as the db version of the current plugin
    * calls the version specific function if upgrade is required
    */
    function update_db_check()
    {
        if ( get_site_option( 'sll_db_ver' ) != $this->db_ver )
        {
            switch( $this->db_ver )
            {
                case "1.1":
                    $this->db_update_1_1();
                    break;
                case "1.2":
                    $this->db_update_1_2();
                    break;
                case "1.3":
                    $this->db_update_1_3();
                    break;
            }
        }
    }


    /**
    * DB version specific updates
    */
    function db_update_1_1()
    {
        /* this version adds a new field "login_result"
         * check if this field exists
         */
        global $wpdb;

        $sql = "SELECT * FROM {$this->table} LIMIT 1";
        $fields = $wpdb->get_row($sql, 'ARRAY_A');

        if( !$fields ){
            $this->install();
            return;
        }

        $field_names = array_keys( $fields );

        if( !array_search('login_result', $field_names) )
        {
            //add the new field since it doesn't exist
            $sql = "ALTER TABLE {$this->table} ADD COLUMN login_result varchar(1) NOT NULL AFTER ip, ADD INDEX (login_result);";
            $insert = $wpdb->query( $sql );

            //update version record if it has been updated
            if( false !== $insert )
                update_option( "sll_db_ver", $this->db_ver );

        }

    }


    function db_update_1_2()
    {
        /* this version adds a new field "user_role"
         * check if this field exists
         */
        global $wpdb;

        $sql = "SELECT * FROM {$this->table} LIMIT 1";
        $fields = $wpdb->get_row($sql, 'ARRAY_A');

        if( !$fields ){
            $this->install();
            return;
        }

        $field_names = array_keys( $fields );

        if( !array_search('user_role', $field_names) )
        {
            //add the new field since it doesn't exist
            $sql = "ALTER TABLE {$this->table} ADD COLUMN user_role varchar(30) NOT NULL AFTER user_login;";
            $insert = $wpdb->query( $sql );

            //update version record if it has been updated
            if( false !== $insert )
                update_option( "sll_db_ver", $this->db_ver );

        }
    }


     function db_update_1_3()
     {
         /**
          * modifies column data length for user_role
          */
         global $wpdb;

         $sql = "SELECT * FROM {$this->table} LIMIT 1";
         $fields = $wpdb->get_row($sql, 'ARRAY_A');

         if( !$fields ){
             $this->install();
             return;
         }

         $sql = "ALTER TABLE {$this->table} MODIFY user_role varchar(255) NOT NULL;";
         $insert = $wpdb->query( $sql );

         //update version record if it has been updated
         if( false !== $insert )
             update_option( "sll_db_ver", $this->db_ver );

     }


    //Initializing Settings
    function settings_api_init()
    {
        add_settings_section('simple_login_log', __('Simple Login Log', 'sll'), array($this, 'sll_settings'), 'general');
        add_settings_field('field_log_duration', __('Truncate Log Entries', 'sll'), array($this, 'field_log_duration'), 'general', 'simple_login_log');
        add_settings_field('field_log_failed_attempts', __('Log Failed Attempts', 'sll'), array($this, 'field_log_failed_attempts'), 'general', 'simple_login_log');
        register_setting( 'general', 'simple_login_log' );

    }


    function sll_admin_menu()
    {
        add_submenu_page( 'users.php', __('Simple Login Log', 'sll'), __('Login Log', 'sll'), 'list_users', 'login_log', array($this, 'log_manager') );
    }


    function sll_settings()
    {
        //content that goes before the fields output
    }


    function field_log_duration()
    {
        $duration = (null !== $this->opt['log_duration']) ? $this->opt['log_duration'] : $this->log_duration;
        $output = '<input type="text" value="' . $duration . '" name="simple_login_log[log_duration]" size="10" class="code" /> ' . __('days and older.', 'sll');
        echo $output;
        echo "<p>" . __("Leave empty or enter 0 if you don't want the log to be truncated.", 'sll') . "</p>";

        //since we're on the General Settings page - update cron schedule if settings has been updated
        if( isset($_REQUEST['settings-updated']) ){
            wp_clear_scheduled_hook('truncate_sll');
            //$this->init_scheduled_events();
        }
    }


    function field_log_failed_attempts()
    {
        $failed_attempts = ( isset($this->opt['failed_attempts']) ) ? $this->opt['failed_attempts'] : false;
        echo '<input type="checkbox" name="simple_login_log[failed_attempts]" value="1" ' . checked( $failed_attempts, 1, false ) . ' /> ' . __('Logs failed attempts where user name and password are entered. Will not log if at least one of the mentioned fields is empty.', 'sll');
    }


    function admin_header()
    {
        $page = ( isset($_GET['page']) ) ? esc_attr($_GET['page']) : false;
        if( 'login_log' != $page )
            return;

        echo '<style type="text/css">';
        echo 'table.users { table-layout: auto; }';
        echo '</style>';
    }


    //Catch messages on successful login
    function login_action($user_login)
    {

        $userdata = get_user_by('login', $user_login);

        $uid = ($userdata && $userdata->ID) ? $userdata->ID : 0;

        $data[$this->data_labels['Login']] = ( 1 == $this->login_success ) ? $this->data_labels['Successful'] : $this->data_labels['Failed'];
        if ( isset( $_REQUEST['redirect_to'] ) ) { $data[$this->data_labels['Login Redirect']] = esc_attr( $_REQUEST['redirect_to'] ); }
        $data[$this->data_labels['User Agent']] = esc_attr( $_SERVER['HTTP_USER_AGENT'] );

        $serialized_data = serialize($data);

        //get user role
        $user_role = '';
        if( $uid ){
            $user = new WP_User( $uid );
            if ( !empty( $user->roles ) && is_array( $user->roles ) ) {
                $user_role = implode(', ', $user->roles);
            }
        }


        $values = array(
            'uid'           => $uid,
            'user_login'    => $user_login,
            'user_role'     => $user_role,
            'time'          => current_time('mysql'),
            'ip'            => isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? esc_attr($_SERVER['HTTP_X_FORWARDED_FOR']) : esc_attr($_SERVER['REMOTE_ADDR']),
            'login_result'  => $this->login_success,
            'data'          => $serialized_data,
            );

        $format = array('%d', '%s', '%s', '%s', '%s', '%s', '%s');

        $this->save_data($values, $format);
    }


    function save_data($values, $format)
    {
        global $wpdb;

        $wpdb->insert( $this->table, $values, $format );
    }


    function make_where_query()
    {
        $where = false;
        if( isset($_GET['filter']) && '' != $_GET['filter'] )
        {
            $filter = esc_attr( $_GET['filter'] );
            $where['filter'] = "(user_login LIKE '%{$filter}%' OR ip LIKE '%{$filter}%')";
        }
        if( isset($_GET['user_role']) && '' != $_GET['user_role'] )
        {
            $user_role = esc_attr( $_GET['user_role'] );
            $where['user_role'] = "user_role LIKE '%{$user_role}%'";
        }
        if( isset($_GET['result']) && '' != $_GET['result'] )
        {
            $result = esc_attr( $_GET['result'] );
            $where['result'] = "login_result = '{$result}'";
        }
        if( isset($_GET['datefilter']) && '' != $_GET['datefilter'] )
        {
            $datefilter = esc_attr( $_GET['datefilter'] );
            $year = substr($datefilter, 0, 4);
            $month = substr($datefilter, -2);
            $where['datefilter'] = "YEAR(time) = {$year} AND MONTH(time) = {$month}";
        }

        return $where;
    }


    function getLimit()
    {
        return ' LIMIT ' . get_option('users_page_login_log_per_page', 20);
    }


    function log_get_data($orderby = false, $order = false, $limit = 0, $offset = 0)
    {
        global $wpdb;

        $orderCol = array(
            'uid' => 'uid',
            'user_login' => 'user_login',
            'time' => 'time',
            'ip' => 'ip'
        );
        $orderDir = array(
            'asc' => 'ASC',
            'desc'=> 'DESC'
        );

        $where = '';

        $orderby = isset($orderCol[$orderby]) ? $orderCol[$orderby] : 'time';
        $order   = isset($orderDir[$order]) ? $orderDir[$order] : 'DESC';

        $where = $this->make_where_query();

        if( is_array($where) && !empty($where) )
            $where = ' WHERE ' . implode(' AND ', $where);

        $sql = "SELECT * FROM $this->table" . $where . " ORDER BY {$orderby} {$order} " . 'LIMIT ' . $limit . ' OFFSET ' . $offset;

        $data = $wpdb->get_results($sql, 'ARRAY_A');

        return $data;
    }


    function log_manager()
    {

        $log_table = $this->log_table;

        $log_table->prepare_items();

        echo '<div class="wrap srp">';
            echo '<h2>' . __('Login Log', 'sll') . '</h2>';

            if ($this->get('deleted'))
            {
                echo '<div class="updated"><p>All records were deleted.</p></div>';
            }

            echo '<div class="tablenav top">';
                echo '<div class="alignleft actions">';
                    echo $this->date_filter();
                echo '</div>';

                $username = ( isset($_GET['filter']) ) ? esc_attr($_GET['filter']) : false;
                echo '<form method="get" class="alignright">';
                    echo '<p class="search-box">';
                        echo '<input type="hidden" name="page" value="login_log" />';
                        echo '<label>' . __('Username:', 'sll') . ' </label><input type="text" name="filter" class="filter-username" value="' . $username . '" /> <input class="button" type="submit" value="' . __('Filter User', 'sll') . '" />';
                        echo '<br />';
                    echo '</p>';
                echo '</form>';
            echo '</div>';
            echo '<div class="tablenav top">';

                //if log failed attempts is set in the settings, then output views filter
                if( isset($this->opt['failed_attempts']) ){
                    echo '<div class="alignleft actions">';
                            $log_table->views();
                    echo '</div>';
                }

                echo '<div class="alignright actions">';
                $mode = ( isset($_GET['mode']) ) ? esc_attr($_GET['mode']) : false;
                $log_table->view_switcher($mode);
                echo '</div>';
            echo '</div>';

            $log_table->display();

            echo '<form method="get" id="export-login-log">';
            if ( function_exists('wp_nonce_field') )
                wp_nonce_field('ssl_export_log');

            echo '<input type="hidden" name="page" value="login_log" />';
            echo '<input type="hidden" name="download-login-log" value="true" />';
            echo '<p class="submit">';
            echo '<input type="submit" name="submit" id="submit" class="button" value="Export Log to CSV">';
            echo '&nbsp;&nbsp;<a id="delete-all" href="' . wp_nonce_url('users.php?page=login_log&action=delete', 'delete_sll') . '" onclick="return confirm(\'IMPORTANT: All User Log records will be deleted.\')">Delete All</a>';
            echo '</p>';
            echo '</form>';
            //if filtered results - add export filtered results button
            $where = false;
            if( isset( $_GET['filter'] ) || isset( $_GET['user_role'] ) || isset( $_GET['datefilter'] ) || isset( $_GET['result'] ) )
            {
                $where = array();
                foreach($_GET as $k => $v)
                {
                    $where[$k] = @esc_attr($v);
                }
                echo '<form method="get" id="export-login-log">';
                if ( function_exists('wp_nonce_field') )
                    wp_nonce_field('ssl_export_log');

                echo '<input type="hidden" name="page" value="login_log" />';
                echo '<input type="hidden" name="download-login-log" value="true" />';
                echo '<input type="hidden" name="where" value="' . esc_attr(serialize($where)) . '" />';
                submit_button( __('Export Current Results to CSV', 'sll'), 'secondary' );
                echo '</form>';

            }

        echo '</div>';
    }


    function date_filter()
    {
        global $wpdb;
        $sql = "SELECT DISTINCT YEAR(time) as year, MONTH(time)as month FROM {$this->table} ORDER BY YEAR(time), MONTH(time) desc";
        $results = $wpdb->get_results($sql);

        if(!$results)
            return;


        $option = '';
        foreach($results as $row)
        {
            //represent month in double digits
            $timestamp = mktime(0, 0, 0, $row->month, 1, $row->year);
            $month = (strlen($row->month) == 1) ? '0' . $row->month : $row->month;
            $datefilter = ( isset($_GET['datefilter']) ) ? $_GET['datefilter'] : false;
            $option .= '<option value="' . $row->year . $month . '" ' . selected($row->year . $month, $datefilter, false) . '>' . date('F', $timestamp) . ' ' . $row->year . '</option>';
        }

        $output = '<form method="get">';
        $output .= '<input type="hidden" name="page" value="login_log" />';
        $output .= '<select name="datefilter"><option value="">' . __('View All', 'sll') . '</option>' . $option . '</select>';
        $output .= '<input class="button" type="submit" value="' . __('Filter', 'sll') . '" />';
        $output .= '</form>';
        return $output;
    }


    function init_csv_export()
    {
        //Check if download was initiated

        $download = (isset($_GET['download-login-log'])) ? esc_attr($_GET['download-login-log']) : false;

        if($download)
        {
            check_admin_referer( 'ssl_export_log' );

            $where = ( isset($_GET['where']) && '' != $_GET['where'] ) ? $_GET['where'] : false;
            $where = maybe_unserialize( stripcslashes($where) );

            if( is_array($where) && !empty($where) )
            {
                foreach($where as $k => $v)
                {
                    $_GET[$k] = esc_attr($v);
                }
            }

            $this->export_to_CSV( $this->make_where_query() );
        }
    }


    function export_to_CSV($where = false){
        global $wpdb;

        //if $where is set, then contemplate WHERE sql query
        if( $where ){

            if( is_array($where) && !empty($where) )
                $where = ' WHERE ' . implode(' AND ', $where);

        }

        $sql = "SELECT * FROM {$this->table}{$where}";
        $data = $wpdb->get_results($sql, 'ARRAY_A');

        if(!$data)
            return;

        //date string to suffix the file nanme: month - day - year - hour - minute
        $suffix = date('n-j-y_H-i');

        // send response headers to the browser
        header( 'Content-Type: text/csv' );
        header( 'Content-Disposition: attachment;filename=login_log_' . $suffix . '.csv');
        $fp = fopen('php://output', 'w');

        $i = 0;
        foreach($data as $row){
            $tmp = unserialize($row['data']);
            //output header row
            if(0 == $i)
            {
                fputcsv( $fp, array_keys($row) );
            }
            $row_data = (!empty($tmp)) ? array_map(create_function('$key, $value', 'return $key.": ".$value." | ";'), array_keys($tmp), array_values($tmp)) : array();
            $row['data'] = implode($row_data);
            fputcsv($fp, $row);
            $i++;
        }

        fclose($fp);
        die();
    }

 }

}

if( class_exists( 'SimpleLoginLog' ) )
{
    $sll = new SimpleLoginLog;
    //Register for activation
    register_activation_hook( __FILE__, array(&$sll, 'install') );

}

if(!class_exists('WP_List_Table'))
{
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class SLL_List_Table extends WP_List_Table
{
    private $sllData;
    protected $data_labels;

    function __construct()
    {
        global $sll, $_wp_column_headers;

        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'user',     //singular name of the listed records
            'plural'    => 'users',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );

        $this->data_labels = $sll->data_labels;
    }


    function set($name, $value)
    {
        $this->sllData[$name] = $value;
    }


    function get($name)
    {
        return (isset($this->sllData[$name])) ? $this->sllData[$name] : false;
    }


    function column_default($item, $column_name)
    {
        $item = apply_filters('sll-output-data', $item);

        //unset existing filter and pagination
        $args = wp_parse_args( parse_url($_SERVER["REQUEST_URI"], PHP_URL_QUERY) );
        unset($args['filter']);
        unset($args['paged']);

        switch($column_name){
            case 'id':
            case 'uid':
            case 'time':
            case 'ip':
                return $item[$column_name];
            case 'user_login':
                return "<a href='" . add_query_arg( array('filter' => $item[$column_name]), menu_page_url('login_log', false) ) . "' title='" . __('Filter log by this name', 'sll') . "'>{$item[$column_name]}</a>";
            case 'name':
                $user_info = get_userdata($item['uid']);
                return ( is_object($user_info) ) ? $user_info->first_name .  " " . $user_info->last_name : false;
            case 'login_result':
                if ( '' == $item[$column_name]) return '';
                return ( '1' == $item[$column_name] ) ? __($this->data_labels['Successful'], 'sll') : '<div class="login-failed">' . __($this->data_labels['Failed'], 'sll') . '</div>';
            case 'user_role':
                if( !$item['uid'] )
                    return;

                global $wp_roles;

                $user = new WP_User( $item['uid'] );
                if ( !empty( $user->roles ) && is_array( $user->roles ) )
                {
                    foreach($user->roles as $role)
                    {

                        $roleName = isset($wp_roles->roles[$role]['name']) ? $wp_roles->roles[$role]['name'] : $role;

                        $roles[] = "<a href='" . add_query_arg( array('user_role' => $role), menu_page_url('login_log', false) ) . "' title='" . __('Filter log by User Role', 'sll') . "'>{$roleName}</a>";
                    }
                    return implode(', ', $roles);
                }
                break;
            case 'data':
                $data = unserialize($item[$column_name]);
                if(is_array($data))
                {
                    $output = '';
                    foreach($data as $k => $v)
                    {
                        $output .= $k .': '. $v .'<br />';
                    }

                    $output = ( isset($_GET['mode']) && 'excerpt' == $_GET['mode'] ) ? $output : substr($output, 0, 50) . '...';

                    if( isset($data[$this->data_labels['Login']]) && $data[$this->data_labels['Login']] == $this->data_labels['Failed'] ){
                        return '<div class="login-failed">' . $output . '</div>';
                    }
                    return $output;
                }
                break;
            default:
                return $item[$column_name];
        }
    }


    function get_columns()
    {
        global $status;
        $columns = array(
            'id'            => __('#', 'sll'),
            'uid'           => __('User ID', 'sll'),
            'user_login'    => __('Username', 'sll'),
            'user_role'     => __('User Role', 'sll'),
            'name'          => __('Name', 'sll'),
            'time'          => __('Time', 'sll'),
            'ip'            => __('IP Address', 'sll'),
            'login_result'  => __('Login Result', 'sll'),
            'data'          => __('Data', 'sll'),
        );
        return $columns;
    }


    function get_sortable_columns()
    {
        $sortable_columns = array(
            //'id'    => array('id',true),     //doesn't sort correctly
            'uid'           => array('uid',false),
            'user_login'    => array('user_login', false),
            'time'          => array('time',true),
            'ip'            => array('ip', false),
        );
        return $sortable_columns;
    }


    function get_views()
    {
        //creating class="current" variables
        if( !isset($_GET['result']) ){
            $all = 'class="current"';
            $success = '';
            $failed = '';
        }else{
            $all = '';
            $success = ( '1' == $_GET['result'] ) ? 'class="current"' : '';
            $failed = ( '0' == $_GET['result'] ) ? 'class="current"' : '';
        }



        //if date filter is set, adjust views label to reflect the date
        $date_label = false;
        if( isset($_GET['datefilter']) && !empty($_GET['datefilter']) ){
            $year = substr($_GET['datefilter'], 0, 4);
            $month = substr($_GET['datefilter'], -2);
            $timestamp = mktime(0, 0, 0, $month, 1, $year);
            $date_label = date('F', $timestamp) . ' ' . $year . ' ';
        }

        //get args from the URL
        $args = wp_parse_args( parse_url($_SERVER["REQUEST_URI"], PHP_URL_QUERY) );
        //the only arguments we can pass are mode and datefilter
        $param = false;
        if( isset($args['mode']) )
            $param['mode'] = $args['mode'];

        if( isset($args['datefilter']) )
            $param['datefilter'] = $args['datefilter'];

        //creating base url for the views links
        $menu_page_url = menu_page_url('login_log', false);
        ( is_array($param) && !empty($param) ) ? $url = add_query_arg( $param, $menu_page_url) : $url = $menu_page_url;

        //definition for views array
        $views = array(
            'all' => $date_label . __('Login Results', 'sll') . ': <a ' . $all . ' href="' . $url . '">' . __('All', 'sll') . '</a>' . '(' .$this->get('allTotal') . ')',
            'success' => '<a ' . $success . ' href="' . $url . '&result=1">' . __('Successful', 'sll') . '</a> (' . $this->get('successTotal') . ')',
            'failed' => '<a ' . $failed . ' href="' . $url . '&result=0">' . __('Failed', 'sll') . '</a>' . '(' . $this->get('failedTotal') . ')',
        );

        return $views;
    }


    function prepare_items()
    {
        global $wpdb, $sll;

        //get number of successful and failed logins so we can display them in parentheces for each view

        //building a WHERE SQL query for each view
        $where = $sll->make_where_query();
        //we only need the date filter, everything else need to be unset
        if( is_array($where) && isset($where['datefilter']) ){
            $where = array( 'datefilter' =>  $where['datefilter'] );
        }else{
            $where = false;
        }

        $where3 = $where2 = $where1 = $where;
        $where2['login_result'] = "login_result = '1'";
        $where3['login_result'] = "login_result = '0'";

        if(is_array($where1) && !empty($where1)){
            $where1 = 'WHERE ' . implode(' AND ', $where1);
        }
        $where2 = 'WHERE ' . implode(' AND ', $where2);
        $where3 = 'WHERE ' . implode(' AND ', $where3);

        $sql1 = "SELECT count(*) FROM {$sll->table} {$where1}";
        $allTotal = $wpdb->get_var($sql1);
        $sql2 = "SELECT count(*) FROM {$sll->table} {$where2}";
        $successTotal = $wpdb->get_var($sql2);
        $sql3 = "SELECT count(*) FROM {$sll->table} {$where3}";
        $failedTotal = $wpdb->get_var($sql3);

        $this->set('allTotal', $allTotal);
        $this->set('successTotal', $successTotal);
        $this->set('failedTotal', $failedTotal);

        $screen = get_current_screen();

        /**
         * First, lets decide how many records per page to show
         */
        $per_page_option = $screen->id . '_per_page';
        $per_page = get_option($per_page_option, 20);
        $per_page = ($per_page != false) ? $per_page : 20;

        $offset = $per_page * ($this->get_pagenum() - 1);

        $orderby = (isset($_REQUEST['orderby']) && !empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : false;
        $order = (isset($_REQUEST['order']) && !empty($_REQUEST['order'])) ? $_REQUEST['order'] : false;

        $this->items = $sll->log_get_data($orderby, $order, $per_page, $offset);

        /**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        $columns = $this->get_columns();
        $hidden_cols = get_user_option( 'manage' . $screen->id . 'columnshidden' );
        $hidden = ( $hidden_cols ) ? $hidden_cols : array();
        $sortable = $this->get_sortable_columns();


        /**
         * REQUIRED. Finally, we build an array to be used by the class for column
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array($columns, $hidden, $sortable);
        $columns = get_column_headers( $screen );


        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */
        //$this->process_bulk_action();


        /**
         * Instead of querying a database, we're going to fetch the example data
         * property we created for use in this plugin. This makes this example
         * package slightly different than one you might build on your own. In
         * this example, we'll be using array manipulation to sort and paginate
         * our data. In a real-world implementation, you will probably want to
         * use sort and pagination data to build a custom query instead, as you'll
         * be able to use your precisely-queried data immediately.
         */
//        $data = $this->items;


        /**
         * This checks for sorting input and sorts the data in our array accordingly.
         *
         * In a real-world situation involving a database, you would probably want
         * to handle sorting by passing the 'orderby' and 'order' values directly
         * to a custom query. The returned data will be pre-sorted, and this array
         * sorting technique would be unnecessary.
         */
//        function usort_reorder($a,$b)
//        {
//            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'time'; //If no sort, default to title
//            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc
//            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
//            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
//        }
//        usort($data, 'usort_reorder');


        /***********************************************************************
         * ---------------------------------------------------------------------
         * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
         *
         * In a real-world situation, this is where you would place your query.
         *
         * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
         * ---------------------------------------------------------------------
         **********************************************************************/


        /**
         * REQUIRED for pagination. Let's figure out what page the user is currently
         * looking at. We'll need this later, so you should always include it in
         * your own package classes.
         */
        $current_page = $this->get_pagenum();

        /**
         * REQUIRED for pagination. Let's check how many items are in our data array.
         * In real-world use, this would be the total number of items in your database,
         * without filtering. We'll need this later, so you should always include it
         * in your own package classes.
         */

        if (isset($_GET['result']) && $_GET['result'] == '1')
        {
            $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $sll->table {$where2}");
        }
        else if(isset($_GET['result']) && $_GET['result'] == '0')
        {
            $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $sll->table {$where3}");
        }
        else
        {
            $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $sll->table");
        }


        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to
         */
//        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);



        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where
         * it can be used by the rest of the class.
         */
//        $this->items = $data;


        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );

    }

}
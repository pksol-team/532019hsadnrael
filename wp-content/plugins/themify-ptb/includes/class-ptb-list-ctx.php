<?php

if (!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/screen.php' );
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * The List table class which extends WP_List_Table Wordpress Admin core class
 *
 * @link       http://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/includes
 */

/**
 * The List table class which extends WP_List_Table Wordpress Admin core class
 *
 * @since      1.0.0
 * @package    PTB
 * @subpackage PTB/includes
 * @author     Themify <ptb@themify.me>
 */
class PTB_List_Table_CTX extends WP_List_Table {

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The string used to uniquely identify this plugin.
     */
    private $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of the plugin.
     */
    private $version;

    /**
     * The plugin options object.
     *
     * @since    1.0.0
     * @access   private
     * @var      PTB_Options $version The options object.
     */
    private $options;

    /**     * ***********************************************************************
     * Normally we would be querying data from a database and manipulating that
     * for use in your list table. For this example, we're going to simplify it
     * slightly and create a pre-built array. Think of this as the data that might
     * be returned by $wpdb->query().
     *
     * @var array
     * ************************************************************************ */
    private $data;

    /**     * ***********************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We
     * use the parent reference to set some default configs.
     * *************************************************************************
     *
     * @param string $plugin_name
     * @param string $version
     * @param PTB_Options $options
     */
    function __construct($plugin_name, $version, $options) {
        global $status, $page;

        //Set parent defaults
        parent::__construct(array(
            'singular' => 'ptb_taxonomy', //singular name of the listed records
            'plural' => 'ptb_taxonomies', //plural name of the listed records
            'ajax' => false              //does this table support ajax?
        ));

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->options = $options;

        $this->prepare_data();
    }

    private function prepare_data() {

        $this->data = array();
        $custom_taxonomies = $this->options->get_custom_taxonomies();

        foreach ($custom_taxonomies as $ctx) {

            sort($ctx->attach_to);

            $ctx_data = array();
            $ctx_data['ID'] = $ctx->id;
            $ctx_data['singular_label'] = PTB_Utils::get_label($ctx->singular_label);
            $registered_text = !$this->options->is_custom_taxonomy_registered($ctx->id)?'('.__('Unregistered','ptb').')':'';
            $ctx_data['singular_label'].=' <span class="ptb_unregistered_title">'.$registered_text.'</span>';
            $ctx_data['slug'] = $ctx->slug;
            $ctx_data['is_hierarchical'] = $ctx->is_hierarchical;
            $ctx_data['attach_to'] = implode(', ', $ctx->attach_to);
            $this->data[] = $ctx_data;
        }
    }

    /**     * ***********************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title()
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as
     * possible.
     *
     * Since we have defined a column_title() method later on, this method doesn't
     * need to concern itself with any column with a name of 'title'. Instead, it
     * needs to handle everything else.
     *
     * For more detailed insight into how columns are handled, take a look at
     * WP_List_Table::single_row_columns()
     *
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     *
     * @return string Text or HTML to be placed inside the column <td>
     * ************************************************************************ */
    function column_default($item, $column_name) {
        switch ($column_name) {
            case 'slug':
            case 'is_hierarchical':
            case 'attach_to':
                //case 'taxonomies':
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    /**     * ***********************************************************************
     * Recommended. This is a custom column method and is responsible for what
     * is rendered in any column with a name/slug of 'title'. Every time the class
     * needs to render a column, it first looks for a method named
     * column_{$column_title} - if it exists, that method is run. If it doesn't
     * exist, column_default() is called instead.
     *
     * This example also illustrates how to implement rollover actions. Actions
     * should be an associative array formatted as 'slug'=>'link html' - and you
     * will need to generate the URLs yourself. You could even ensure the links
     *
     *
     * @see WP_List_Table::::single_row_columns()
     *
     * @param array $item A singular item (one full row's worth of data)
     *
     * @return string Text to be placed inside the column <td> (movie title only)
     * ************************************************************************ */
    function column_singular_label($item) {

        //Build row actions
        static $nonce_dialog = NULL;
        static $nonce_register = NULL;
        static $nonce_copy = NULL;
        if(is_null($nonce_register)){
            $nonce_dialog = wp_create_nonce('ptb_remove_dialog');
            $nonce_register = wp_create_nonce('ptb_register');
            $nonce_copy =  wp_create_nonce('ptb_ctx_copy');
        }
        $is_registed = $this->options->is_custom_taxonomy_registered($item['ID']);
        if($is_registed){
            $key = 'unregister';
            $register_text =__('Unregister', 'ptb');  
        }
        else{
            $key = 'register';
            $register_text =__('Register', 'ptb');  
        }
        $ajax_url = admin_url( 'admin-ajax.php' );
        $actions = array(
            'edit' => sprintf(
                    '<a href="%1$s" title="%2$s" class="ptb_ctx_edit">%2$s</a>',add_query_arg(array('page'=>$_REQUEST['page'],'slug'=>$item['ID'],'action'=>'edit'), admin_url('admin.php')), __('Edit', 'ptb')
            ),
            'copy' => sprintf(
                    '<a href="%1$s" title="%2$s" class="ptb_ctx_copy">%2$s</a>', add_query_arg(array('slug'=>$item['ID'],'action'=>'ptb_copy','nonce'=>$nonce_copy,'type'=>'ctx'),$ajax_url), __('Duplicate', 'ptb')
            ),
            'delete' => sprintf(
                    '<a href="%1$s" title="" onclick="return false;" data-class="ptb_remove_dialog_lightbox" class="ptb_ctx_delete ptb_custom_lightbox">%2$s</a>', add_query_arg(array('slug'=>$item['ID'],'action'=>'ptb_remove_dialog','nonce'=>$nonce_dialog,'type'=>'ctx'),$ajax_url), __('Delete', 'ptb')
            ),
            $key=>sprintf(
                '<a href="%1$s" title="%2$s" onclick="return false;" class="ptb_'.$key.'">%2$s</a>', add_query_arg(array('slug'=>$item['ID'],'action'=>'ptb_register','nonce'=>$nonce_register,'type'=>'ctx'),$ajax_url), $register_text
            )
        );

        //Return the title contents
        return sprintf('%1$s %2$s',
                /* $1%s */ $item['singular_label'],
                /* $3%s */ $this->row_actions($actions)
        );
    }

    function column_is_hierarchical($item) {

        return $item['is_hierarchical'] ? __('Yes', 'ptb') : __('No', 'ptb');
    }

    function column_attach_to($item) {

        return $item['attach_to'];
    }

    /**     * ***********************************************************************
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
     *
     * @see WP_List_Table::::single_row_columns()
     *
     * @param array $item A singular item (one full row's worth of data)
     *
     * @return string Text to be placed inside the column <td> (movie title only)
     * ************************************************************************ */
    function column_cb($item) {
        return sprintf(
                '<input type="checkbox" name="%1$s[]" value="%2$s" />',
                /* $1%s */ $this->_args['singular'], //Let's simply repurpose the table's singular label ("movie")
                /* $2%s */ $item['ID']                //The value of the checkbox should be the record's id
        );
    }

    /**     * ***********************************************************************
     * Optional. If you need to include bulk actions in your list table, this is
     * the place to define them. Bulk actions are an associative array in the format
     * 'slug'=>'Visible Title'
     *
     * If this method returns an empty value, no bulk action will be rendered. If
     * you specify any bulk actions, the bulk actions box will be rendered with
     * the table automatically on display().
     *
     * Also note that list tables are not automatically wrapped in <form> elements,
     * so you will need to create those manually in order for bulk actions to function.
     *
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     * ************************************************************************ */
    function get_bulk_actions() {
        $actions = array(
            'delete' => 'Delete'
        );

        return $actions;
    }

    /**     * ***********************************************************************
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     *
     * @global WPDB $wpdb
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     * ************************************************************************ */
    function prepare_items() {

        /**
         * First, lets decide how many records per page to show
         */
        $per_page = 10;


        /**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();


        /**
         * REQUIRED. Finally, we build an array to be used by the class for column
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array($columns, $hidden, $sortable);


        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */
        $this->process_bulk_action();


        /**
         * Instead of querying a database, we're going to fetch the example data
         * property we created for use in this plugin. This makes this example
         * package slightly different than one you might build on your own. In
         * this example, we'll be using array manipulation to sort and paginate
         * our data. In a real-world implementation, you will probably want to
         * use sort and pagination data to build a custom query instead, as you'll
         * be able to use your precisely-queried data immediately.
         */
        $data = $this->data;

        /**
         * This checks for sorting input and sorts the data in our array accordingly.
         *
         * In a real-world situation involving a database, you would probably want
         * to handle sorting by passing the 'orderby' and 'order' values directly
         * to a custom query. The returned data will be pre-sorted, and this array
         * sorting technique would be unnecessary.
         */
        function usort_reorder($a, $b) {
            $orderby = (!empty($_REQUEST['orderby']) ) ? $_REQUEST['orderby'] : 'slug'; //If no sort, default to title
            $order = (!empty($_REQUEST['order']) ) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ( $order === 'asc' ) ? $result : - $result; //Send final sort direction to usort
        }

        usort($data, 'usort_reorder');


        /*         * *********************************************************************
         * ---------------------------------------------------------------------
         * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
         *
         * In a real-world situation, this is where you would place your query.
         *
         * For information on making queries in WordPress, see this Codex entry:
         * http://codex.wordpress.org/Class_Reference/wpdb
         *
         * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
         * ---------------------------------------------------------------------
         * ******************************************************************** */


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
        $total_items = count($data);


        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to
         */
        $data = array_slice($data, ( ( $current_page - 1 ) * $per_page), $per_page);


        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where
         * it can be used by the rest of the class.
         */
        $this->items = $data;


        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args(array(
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page' => $per_page, //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items / $per_page)   //WE have to calculate the total number of pages
        ));
    }

    /**     * ***********************************************************************
     * REQUIRED! This method dictates the table's columns and titles. This should
     * return an array where the key is the column slug (and class) and the value
     * is the column's title text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
     *
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     *
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     * ************************************************************************ */
    function get_columns() {
        $columns = array(
            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'singular_label' => 'Name',
            'slug' => 'Slug',
            'is_hierarchical' => 'Hierarchical',
            'attach_to' => 'Attached to'
        );

        return $columns;
    }

    /**     * ***********************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle),
     * you will need to register it here. This should return an array where the
     * key is the column that needs to be sortable, and the value is db column to
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
     *
     * This method merely defines which columns should be sortable and makes them
     * clickable - it does not handle the actual sorting. You still need to detect
     * the ORDERBY and ORDER querystring variables within prepare_items() and sort
     * your data accordingly (usually by modifying your query).
     *
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     * ************************************************************************ */
    function get_sortable_columns() {
        $sortable_columns = array(
            'singular_label' => array('singular_label', false), //true means it's already sorted
            'slug' => array('slug', false),
                //'supports'       => array( 'supports', false )
        );

        return $sortable_columns;
    }

    /**     * ***********************************************************************
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     *
     * @see $this->prepare_items()
     * ************************************************************************ */
    function process_bulk_action() {

        //Detect when a bulk action is being triggered...
        if ('delete' === $this->current_action() && current_user_can('manage_options')) {

            if (isset($_REQUEST[$this->_args['singular']])) {

                $taxonomy_ids = $_REQUEST[$this->_args['singular']];
                $taxonomy_ids = array_values($taxonomy_ids);

                $deleted = 0;

                foreach ($taxonomy_ids as $tax_id) {

                    if ($this->options->remove_custom_taxonomy($tax_id)) {
                        $deleted ++;
                    }
                }

                if ($deleted > 0) {
                    $this->options->update();
                    $this->prepare_data();

                    if ($deleted === 1) {
                        $message = sprintf(
                                __('%1$s custom taxonomy successfully removed.', 'ptb'), implode(', ', $taxonomy_ids)
                        );
                    } else {
                        $message = sprintf(
                                __('%1$s custom taxonomies successfully removed.', 'ptb'), implode(', ', $taxonomy_ids)
                        );
                    }

                    add_settings_error($this->plugin_name . '_notices', '', $message, 'updated');
                }
            }
        }
    }

}

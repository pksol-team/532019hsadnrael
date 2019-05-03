<?php
/**
 * The metabox-specific functionality of the plugin.
 *
 * @link       http://wpoptimiser.com
 * @since      1.0.0
 *
 * @package    WPOptimiser
 * @subpackage WPOptimiser/admin
 */

/**
 * The metabox-specific functionality of the plugin.
 *
 * @package 	WPOptimiser
 * @subpackage 	WPOptimiser/admin
 */
class WPOptimiser_Admin_Metaboxes {

	/**
	 * The post meta data
	 *
	 * @since 		1.0.0
	 * @access 		private
	 * @var 		string 			$meta    			The post meta data.
	 */
	private $meta;

	/**
	 * The ID of this plugin.
	 *
	 * @since 		1.0.0
	 * @access 		private
	 * @var 		string 			$plugin_name 		The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since 		1.0.0
	 * @access 		private
	 * @var 		string 			$version 			The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 		1.0.0
	 * @param 		string 			$plugin_name	The name of this plugin.
	 * @param 		string 			$version 			The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->set_meta();

	}

	/**
	 * Registers metaboxes with WordPress
	 *
	 * @since 	1.0.0
	 * @access 	public
	 */
	public function add_metaboxes() {

		// add_meta_box( $id, $title, $callback, $screen, $context, $priority, $callback_args );

		add_meta_box(
			'wpoptimiser_lazy_load',
			apply_filters( $this->plugin_name . '-metabox-title-lazy-load', esc_html__( 'WP Optimiser' ) ),
			array( $this, 'metabox' ),
			array('post', 'page', 'wpeo_optinpage'),
			'side',
			'default',
			array(
				'file' => 'lazy-load'
			)
		);

		$this->set_meta();

	} // add_metaboxes()

	/**
	 * Check each nonce. If any don't verify, $nonce_check is increased.
	 * If all nonces verify, returns 0.
	 *
	 * @since 		1.0.0
	 * @access 		public
	 * @return 		int 		The value of $nonce_check
	 */
	private function check_nonces( $posted ) {

		$nonces 		= array();
		$nonce_check 	= 0;

		$nonces[] 		= 'lazy_load';

		foreach ( $nonces as $nonce ) {

			if ( !isset( $posted[$nonce] ) ) { $nonce_check++; }
			if ( isset( $posted[$nonce] ) && ! wp_verify_nonce( $posted[$nonce], $this->plugin_name ) ) { $nonce_check++; }

		}

		return $nonce_check;

	} // check_nonces()

	/**
	 * Returns an array of the all the metabox fields and their respective types
	 *
	 * @since 		1.0.0
	 * @access 		public
	 * @return 		array 		Metabox fields and types
	 */
	private function get_metabox_fields() {

		$fields = array();

		$fields[] = array( 'wpoptimiser-lazy-load', 'select' );

    $fields = apply_filters($this->plugin_name . '_get_metabox_fields', $fields);

		return $fields;

	} // get_metabox_fields()

	/**
	 * Calls a metabox file specified in the add_meta_box args.
	 *
	 * @since 	1.0.0
	 * @access 	public
	 * @return 	void
	 */
	public function metabox( $post, $params ) {

		if ( ! is_admin() ) { return; }

		include( plugin_dir_path( __FILE__ ) . 'partials/wpoptimiser-admin-metabox-' . $params['args']['file'] . '.php' );

    do_action($this->plugin_name  . '_after_metabox_html', $this->meta);

	} // metabox()

	private function sanitizer( $data ) {

		if ( empty( $data ) ) { return; }

		$return 	= '';

		$one	= trim( $data );
		$two	= stripslashes( $one );
		$return	= htmlspecialchars( $two );

		return $return;

	} // sanitizer()

	/**
	 * Sets the class variable $options
	 */
	public function set_meta() {

		global $post;

		if ( empty( $post ) ) { return; }

		//wp_die( '<pre>' . print_r( $post->ID ) . '</pre>' );

		$this->meta = get_post_custom( $post->ID );

	} // set_meta()

	/**
	 * Saves metabox data
	 *
	 * @since 	1.0.0
	 * @access 	public
	 * @param 	int 		$post_id 		The post ID
	 * @param 	object 		$object 		The post object
	 * @return 	void
	 */
	public function validate_meta( $post_id, $object ) {

		// wp_die( '<pre>' . print_r( $_POST ) . '</pre>' );

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return $post_id; }
		if ( ! current_user_can( 'edit_post', $post_id ) ) { return $post_id; }

		$nonce_check = $this->check_nonces( $_POST );

		if ( 0 < $nonce_check ) { return $post_id; }

		$metas = $this->get_metabox_fields();

		foreach ( $metas as $meta ) {

			$name = $meta[0];
			$new_value = $this->sanitizer( $_POST[$name] );

			update_post_meta( $post_id, $name, $new_value );

		} // foreach

	} // validate_meta()

} // class

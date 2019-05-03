<?php

class Swift_Performance_Meta_Boxes {

	/**
	 * Construct Swift_Performance_Meta_Boxes
	 */
	public function __construct(){
		// Post types
 		$post_types = array();
 		foreach (Swift_Performance::get_post_types() as $post_type){
 			add_action( 'add_meta_boxes_' . $post_type, array(__CLASS__, 'add_meta_box'));
 		}

		// Save meta box
		add_action( 'save_post', array(__CLASS__, 'save_post'));
	}

	/**
	 * Add meta box
	 * @param WP_Post $post
	 */
	public static function add_meta_box($post){
		add_meta_box( 'swift_performance_meta_box', __( 'Swift Performance', 'swift-perfromance' ), function($post){
	            $settings = get_post_meta(get_the_ID(), 'swift-performance', true);

	            $include_scripts  = (isset($settings['include-scripts']) ? array_filter($settings['include-scripts']) : array());
	            $include_styles   = (isset($settings['include-styles']) ? array_filter($settings['include-styles']) : array());

	            $include_scripts  = array_unique(array_merge($include_scripts, (array)Swift_Performance::get_option('include-scripts')));
	            $include_styles = array_unique(array_merge($include_styles, (array)Swift_Performance::get_option('include-styles')));

	            wp_nonce_field('swift-meta-box', 'swift_meta_box_nonce');
	      	include_once SWIFT_PERFORMANCE_DIR . 'modules/asset-manager/templates/meta-box.tpl.php';
	      }, null, 'normal', 'low' );
	}

	/**
	 * Save plugin post meta
	 * @param int $post_id
	 */
	public static function save_post($post_id){
	      // Check nonce
	      if ( !isset( $_POST['swift_meta_box_nonce'] ) || !wp_verify_nonce( $_POST['swift_meta_box_nonce'], 'swift-meta-box' ) ){
			return;
		}

	      // Don't save on autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
			return;
		}

	      $settings = (isset($_POST['swift-performance']) ? $_POST['swift-performance'] : array());
	      update_post_meta($post_id, 'swift-performance', $settings);

	}

}

return new Swift_Performance_Meta_Boxes();
?>

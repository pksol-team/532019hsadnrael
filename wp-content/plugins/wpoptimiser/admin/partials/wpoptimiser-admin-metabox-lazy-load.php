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

wp_nonce_field( $this->plugin_name, 'lazy_load' );

$atts 					= array();
$atts['class'] 			= 'widefat';
$atts['description'] 	= '';
$atts['id'] 			  = 'wpoptimiser-lazy-load';
$atts['label'] 			= 'Enable Lazy Load';
$atts['name'] 			= 'wpoptimiser-lazy-load';
$atts['type'] 			= 'select';
$atts['value'] 			= 'S';
$atts['aria'] 			= '';
if ( ! empty( $this->meta[$atts['id']][0] ) ) {
	$atts['value'] = $this->meta[$atts['id']][0];
}
$atts['selections'] = Array(Array('label' => 'Use Default Settings', 'value' => 'S'), Array('label' => 'Yes - Turn ON  Lazy Load', 'value' => 'Y'), Array('label' => 'No - Turn OFF Lazy Load', 'value' => 'N'));
?>
<p>
<?php

include( plugin_dir_path( __FILE__ ) . $this->plugin_name . '-admin-field-select.php' );

?>
</p>

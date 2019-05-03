<?php
/**
 * Kick off the site profiler if its been initiated
 *
 *
 * @link       http://wpoptimiser.com
 * @since      1.0.0
 *
 * @package    WPOptimiser
 * @subpackage WPOptimiser/includes
 */


/**
 * Start the Profiling if profiling enabled
 *
 */
if ( function_exists( 'get_option' ) && !isset( $GLOBALS['wpopti_profiler'] ) && basename( __FILE__ ) !=  basename( $_SERVER['SCRIPT_FILENAME'] ) ) {

	$opts = get_option( 'wpopti_profiler_options' );

	if ( isset($opts['profiling_enabled']) && $opts['profiling_enabled'] !== false ) {

    // if ( strtok(phpversion(),'.') >= 7) {
    //   require plugin_dir_path( dirname( __FILE__ ) )  . 'includes/streamwrapper.php';
    //   FileStreamWrapper3::init(__DIR__);;
    // }
    include_once realpath( dirname( __FILE__ ) ) . '/site-profiler.php';

    // declare( ticks = 1 ); // Capture every user function call
		$GLOBALS['wpopti_profiler'] = new WPOptimiser_Site_Profiler(); // Go
	}
	unset( $opts );
}

/**
 * Get the user's IP
 * @return string
 */
function wpopti_profiler_get_ip() {
	static $ip = '';
	if ( !empty( $ip ) ) {
		return $ip;
	} else {
		if ( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif ( !empty ( $_SERVER['HTTP_X_REAL_IP'] ) ) {
			$ip = $_SERVER['HTTP_X_REAL_IP'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}
}
?>

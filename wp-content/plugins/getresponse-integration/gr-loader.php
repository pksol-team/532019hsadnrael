<?php

/**
 * Plugin Name: GetResponse for WordPress
 * Plugin URI: http://wordpress.org/extend/plugins/getresponse-integration/
 * Description: GetResponse for Wordpress lets you add site visitors to your contact list, update contact information, track site visits, and pass ecommerce data to **GetResponse**. It helps you keep your list growing and ensures you have the contact information and ecommerce data to plan successful marketing campaigns.
 * Version: 5.3.7
 * Author: GetResponse
 * Author URI: http://getresponse.com/
 * Author: GR Integration Team ;)
 * License: GPL2
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

define('GR_PLUGIN_VERSION', '5.3.7');

use Getresponse\WordPress\GetResponse;

defined( 'ABSPATH' ) || exit;

require_once dirname( __FILE__ ) . '/vendor/autoload.php';

/**
 * Class GetResponse
 */


/**
 * Function responsible for returning one instance of GetResponse Instance.
 *
 * @return GetResponse
 */
function gr() {
	return GetResponse::instance();
}

if ( defined( 'ABSPATH' ) and defined( 'WPINC' ) ) {
	if ( empty( $GLOBALS['GetResponseIntegration'] ) ) {
		$GLOBALS['GetResponseIntegration'] = gr();
	}
}

/**
 * Function to check if file exists.
 *
 * @param $template string file source.
 * @return bool
 */
function gr_locate_template( $template ) {
	return gr()->locate_template( $template );
}

/**
 * Load template file.
 *
 * @param string $template string file path.
 * @param array $params array of variables.
 */
function gr_load_template( $template, $params = array() ) {

	$is_template = gr_locate_template( $template );

	if ( false === $is_template ) {
		return;
	}

	gr()->load_template( $template, $params );
}

/**
 * Get prefix.
 *
 * @param $val
 * @return string
 */
function gr_prefix( $val = null ) {
	return 'gr_integrations_' . $val;
}

/**
 * Get option value with prefix.
 *
 * @param string $value value.
 * @param null $default default settings.
 *
 * @return mixed
 */
function gr_get_option( $value, $default = null ) {
	$result = get_option( gr_prefix( $value ), $default );

	if ( false == empty( $result ) ) {
		return $result;
	}

	return null;
}

/**
 * Get value if exists in global or database.
 *
 * @param string $name - name of variable.
 * @return string|null
 */
function gr_get_value( $name ) {
	if ( isset( $_GET[ $name ] ) ) {
		return $_GET[ $name ];
	}

	if ( isset( $_POST[ $name ] ) ) {
		return $_POST[ $name ];
	}

	$value = get_option( gr_prefix( $name ) );

	if ( false == empty ( $value ) ) {
		return $value;
	}

	return null;
}

/**
 * Update value with GetResponse prefix.
 *
 * @param $name
 * @param $value
 *
 */
function gr_update_option( $name, $value ) {
	update_option( gr_prefix( $name ), $value );
}

/**
 * Delete value with GetResponse prefix.
 *
 * @param $name
 *
 */
function gr_delete_option( $name ) {
	delete_option( gr_prefix( $name ) );
}

/**
 * Get value from global $_GET array.
 *
 * @param $name
 * @return null
 */
function gr_get( $name ) {

	if ( false == isset( $_GET[ $name ] ) ) {
		return null;
	}

	if ( is_string( $_GET[ $name ] ) && 0 === strlen( $_GET[ $name ] ) ) {
		return null;
	}

	return $_GET[ $name ];
}

/**
 * Get value from global $_GET array.
 *
 * @param $name
 * @return null
 */
function gr_post( $name ) {

	if ( false == isset( $_POST[ $name ] ) ) {
		return null;
	}

	if ( is_string( $_POST[ $name ] ) && 0 === strlen( $_POST[ $name ] ) ) {
		return null;
	}

	if ( is_array( $_POST[ $name ] ) && 0 === count( $_POST[ $name ] ) ) {
		return null;
	}

	return $_POST[ $name ];
}

/**
 * Log to file.
 *
 * @param $val
 *
 */
function gr_log( $val ) {
	if ( true === is_array( $val ) ) {
		$val = serialize( $val );
	}
	gr()->log( $val );
}

/**
 * @param string $key
 * @param mixed $data
 * @param int $time
 *
 */
function gr_cache_set( $key, $data, $time ) {
	wp_cache_set( $key, $data, 'getresponse', time() + $time );
}

/**
 * @param string $key
 *
 * @return mixed
 */
function gr_cache_get( $key ) {
	return wp_cache_get( $key, 'getresponse' );
}

function gr_cache_delete( $key ) {
	wp_cache_delete( $key, 'getresponse' );
}

function gr_log_to_file( $data ) {
	$data = PHP_EOL . date( 'Y-m-d H:i:s' ) . ' - ' . print_r( $data, 1 );
	file_put_contents( dirname( __FILE__ ) . '/log.txt', $data, FILE_APPEND );
}

/**
 * Url to error page.
 *
 * @return string
 */
function error_url() {
	return admin_url( add_query_arg( array( 'page' => 'page=gr-integration-error' ), 'admin.php' ) );
}

require_once dirname( __FILE__ ) . '/install.php';
register_activation_hook( __FILE__, 'install_getresponse_tables' );
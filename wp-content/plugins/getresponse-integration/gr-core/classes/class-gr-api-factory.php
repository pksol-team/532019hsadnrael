<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class ApiFactory
 * @package Getresponse\WordPress
 */
class ApiFactory {

	/**
	 * @return Api|null
	 */
	public static function create_api() {

		$api_key = gr_get_option( 'api_key' );
		$url     = gr_get_option( 'api_url' );
		$domain  = gr_get_option( 'api_domain' );

		if ( empty( $api_key ) ) {
			return null;
		}

		return new Api( $api_key, $url, $domain );
	}
}

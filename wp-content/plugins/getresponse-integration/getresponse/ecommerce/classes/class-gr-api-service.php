<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class ApiService
 * @package Getresponse\WordPress
 */
class ApiService {

	/**
	 * @param string $api_key
	 * @param string $url
	 * @param string $domain
	 * @param int $is_mx_account
	 *
	 * @throws ApiException
	 */
	public function connect( $api_key, $url, $domain, $is_mx_account ) {

		$api = new Api( $api_key, $url, $domain );

		$account_details = $api->connect();

		gr_update_option( 'api_key', $api_key );
		gr_update_option( 'api_url', $url );
		gr_update_option( 'api_domain', $domain );
		gr_update_option( 'getresponse_360_account', $is_mx_account );

		$account_service = new AccountService( $api );
		$account_service->update_account_details( $account_details );
	}
}

<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class AccountService
 * @package Getresponse\WordPress
 */
class AccountService {

	const CACHE_KEY = 'gr_account';
	const CACHE_TIME = 300;

	/** @var Api */
	private $api;

	/**
	 * @param Api $api
	 */
	public function __construct( $api ) {
		$this->api = $api;
	}

    /**
     * @param $account
     */
	public function update_account_details($account) {

		if ( empty( $account ) ) {
			return;
		}

		gr_update_option( 'account_first_name', $account['firstName'] );
		gr_update_option( 'account_last_name', $account['lastName'] );
		gr_update_option( 'account_email', $account['email'] );
		gr_update_option( 'account_company_name', $account['companyName'] );
		gr_update_option( 'account_state', $account['state'] );
		gr_update_option( 'account_city', $account['city'] );
		gr_update_option( 'account_street', $account['street'] );
		gr_update_option( 'account_zip_code', $account['zipCode'] );

		if ( isset( $account['countryCode'] ) && isset( $account['countryCode']['countryCode'] ) ) {

			$country_name = Countries::get_country_by_code( $account['countryCode']['countryCode'] );
			gr_update_option( 'account_country_name', $country_name );
		}
	}
}

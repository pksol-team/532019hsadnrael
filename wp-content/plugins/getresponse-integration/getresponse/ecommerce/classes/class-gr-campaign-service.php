<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class CampaignService
 * @package Getresponse\WordPress
 */
class CampaignService {

	const CACHE_KEY = 'campaigns';
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
	 * @return array
	 * @throws ApiException
	 */
	public function get_campaigns() {

		$campaigns = gr_cache_get( self::CACHE_KEY );

		if ( false === $campaigns ) {

			$campaigns = $this->api->get_campaigns();

			if ( empty( $campaigns ) ) {
				return array();
			}

			gr_cache_set( self::CACHE_KEY, $campaigns, self::CACHE_TIME );
		}

		return $campaigns;
	}
}

<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class TrackingCodeService
 * @package Getresponse\WordPress
 */
class TrackingCodeService {
	const CACHE_TIME = 300;
	const CACHE_KEY = 'gr_features';

	/** @var Api */
	private $api;

	/**
	 * @param Api $api
	 */
	public function __construct( $api ) {
		$this->api = $api;
	}

	/**
	 * @param bool $status
	 */
	public function update( $status ) {
		gr_update_option( 'tracking_code_status', (int) $status );
	}

	/**
	 * @return bool
	 */
	public function get_status() {
		return (bool) gr_get_option( 'tracking_code_status' );
	}

	/**
	 * @return array
	 */
	public function get_tracking_code() {
		return gr_get_option( 'tracking_code' );
	}

	/**
	 * @throws ApiException
	 */
	public function get_tracking_code_from_api() {
		$tracking_code = $this->api->get_tracking_code();

		if ( ! empty( $tracking_code ) ) {
			$tracking_code = (array) reset( $tracking_code );
			gr_update_option( 'tracking_code', $tracking_code );
		}
	}

	/**
	 * @return bool
	 * @throws ApiException
	 */
	public function get_feature_tracking_status() {

		$features = gr_cache_get( self::CACHE_KEY );

		if ( false === $features ) {

			$features = $this->api->get_features();
			gr_cache_set( self::CACHE_KEY, $features, self::CACHE_TIME );
		}

		if ( isset( $features['feature_tracking'] ) && 1 === (int) $features['feature_tracking'] ) {
			return true;
		}

		return false;
	}
}

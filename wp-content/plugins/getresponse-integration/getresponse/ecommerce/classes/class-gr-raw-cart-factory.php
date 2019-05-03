<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class RawCartFactory
 * @package Getresponse\WordPress
 */
class RawCartFactory {

	/**
	 * @param $store_id
	 * @param $customer_id
	 * @param $total_price
	 * @param $external_id
	 * @param $tax_price
	 * @param $products
	 * @param $url
	 * @param $currency
	 *
	 * @return RawCart
	 */
	public static function create_from_params(
	    $store_id,
		$customer_id,
		$total_price,
		$external_id,
		$tax_price,
		$products,
		$url,
		$currency
	) {
		return new RawCart(
		    $store_id,
			$customer_id,
			$total_price,
			$external_id,
			$tax_price,
			$products,
			$url,
			$currency
		);
	}
}

<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class OrderWithCartFactory
 * @package Getresponse\WordPress
 */
class OrderWithCartFactory {

	/**
	 * @param string $store_id
	 * @param string $cart_id
	 * @param string $contact_id
	 * @param int $order_id
	 *
	 * @return Order
	 */
	public static function create_from_params(
		$store_id,
		$cart_id,
		$contact_id,
		$order_id
	) {
		return new Order(
			$store_id,
			$cart_id,
			$contact_id,
			$order_id
		);
	}
}
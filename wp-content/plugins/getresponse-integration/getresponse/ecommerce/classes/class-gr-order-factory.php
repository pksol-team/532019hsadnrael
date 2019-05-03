<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class OrderFactory
 * @package Getresponse\WordPress
 */
class OrderFactory {

    /**
     * @param string $store_id
     * @param string $cart_id
     * @param string $contact_id
     * @param int $order_id
     * @param bool $skip_automation
     * @return Order
     */
	public static function create_from_params(
		$store_id,
		$cart_id,
		$contact_id,
		$order_id,
        $skip_automation = false
	) {
		return new Order(
			$store_id,
			$cart_id,
			$contact_id,
			$order_id,
            $skip_automation
		);
	}
}

<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class OrderService
 * @package Getresponse\WordPress
 */
class OrderService {

	/** @var Api */
	private $api;

	/**
	 * @param Api $api
	 */
	public function __construct( $api ) {
		$this->api = $api;
	}

	/**
	 * @param Order $order
	 *
	 * @return array
	 * @throws ApiException
	 */
	public function upsert_order($order)
    {
		$orders_map  = new OrdersMap();
		$gr_order_id = $orders_map->get_gr_order_id(
			$order->get_store_id(),
			$order->get_order_id()
		);

		if (!empty($gr_order_id)) {

			return $this->update_gr_order(
				$gr_order_id,
				$order->get_store_id(),
				OrderBuilder::createFromWoocommerceOrder(
					$order->get_store_id(),
					null,
					$order->get_contact_id(),
					$order->get_order_id()
				),
                $order->get_skip_automation()
			);
		}

		if (null !== $order->get_cart_id()) {
            $gr_cart = $this->api->get_carts(
                $order->get_store_id(),
                array('query' => array('externalId' => $order->get_cart_id()))
            );
            $gr_cart = (array)reset($gr_cart);
            $gr_cart_id = $gr_cart['cartId'];
        } else {
            $gr_cart_id = null;
        }

		$order_data = $this->create_gr_order(
			$order->get_store_id(),
            $gr_cart_id,
			$order->get_contact_id(),
			\WC_Order_Factory::get_order($order->get_order_id()),
            $order->get_skip_automation()
		);

		if (isset($order_data['cartId'])) {
            $this->api->remove_cart( $order->get_store_id(), $order_data['cartId']);
        }

        return $order_data;
	}

    /**
     * @param string $gr_order_id
     * @param string $store_id
     * @param array $params
     * @param bool $skip_automation
     * @return array
     */
	public function update_gr_order($gr_order_id, $store_id, $params, $skip_automation = false) {
		return $this->api->update_order(
			$gr_order_id,
			$store_id,
			$params,
            $skip_automation
		);
	}

	/**
	 * @param string $store_id
	 * @param string $gr_cart_id
	 * @param int $contact_id
	 * @param \WC_Order $order
	 * @param bool $skip_automation
	 *
	 * @return array
	 * @throws ApiException
	 */
	public function create_gr_order($store_id, $gr_cart_id, $contact_id, $order, $skip_automation = false) {

		$gr_order = $this->api->create_order(
			$store_id,
			OrderBuilder::createFromWoocommerceOrder(
				$store_id,
				$gr_cart_id,
				$contact_id,
				$order->get_id()
			),
            $skip_automation
		);

		$orders_map = new OrdersMap();
		$orders_map->add_order( $store_id, $gr_order['orderId'], $order->get_id() );

		return $gr_order;
	}

	/**
	 * @param int $store_id
	 * @param int $order_id
	 *
	 * @return array
	 * @throws ApiException
	 */
	public function get_gr_order( $store_id, $order_id ) {

		return $this->api->get_order( $store_id, $order_id );
	}
}

<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class CartFactory
 * @package Getresponse\WordPress
 */
class CartFactory {

	/**
	 * @param int $store_id
	 * @param string $contact_id
	 * @param \WC_Order $order
	 * @param string $cart_id
	 *
	 * @return Cart
	 * @throws ApiException
	 */
	public static function create_from_order( $store_id, $contact_id, $order, $cart_id ) {
		$product_map     = new ProductsMap();
		$product_factory = new \WC_Product_Factory();
		$product_service = new ProductService(ApiFactory::create_api());
		$variants        = array();

		/** @var \WC_Order_Item_Product $item */
		foreach ( $order->get_items() as $item ) {

			$gr_product_id = $product_map->get_gr_product_id( $store_id, $item->get_product_id() );

			/** @var \WC_Product_Simple $product */
			$product = $product_factory->get_product( $item->get_product_id() );

			if (empty($gr_product_id)) {
				$product_service->add_product($store_id, $product);
			}

			$gr_product = $product_service->get_gr_product($store_id, $gr_product_id);

			$first_variant = (array) reset( $gr_product['variants'] );

			$variants[] = new CartVariant(
				$first_variant['variantId'],
				null,
				$product->get_price(),
				0,
				$item->get_quantity()
			);
		}

		return new Cart(
		    $store_id,
			$contact_id,
			$order->get_total(),
			$cart_id,
			$order->get_total_tax(),
			$variants,
			$order->get_view_order_url(),
			$order->get_currency()
		);
	}

	/**
	 * @param int $store_id
	 * @param int $customer_id
	 * @param float $total_price
	 * @param int $external_id
	 * @param float $tax_price
	 * @param CartVariant[] $products
	 * @param string $url
	 * @param string $currency
	 *
	 * @return Cart
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
		return new Cart(
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

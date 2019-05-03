<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class OrderBuilder
 * @package Getresponse\WordPress
 */
class OrderBuilder {

	/**
	 * @param string $store_id
	 * @param string $cart_id
	 * @param string $contact_id
	 * @param  int $order_id
	 * @return array
	 * @throws ApiException
     * @throws EcommerceException
	 */
	public static function createFromWoocommerceOrder( $store_id, $cart_id, $contact_id, $order_id ) {

		$product_service = new ProductService(ApiFactory::create_api());
		$order = \WC_Order_Factory::get_order($order_id);
		$productMap = new ProductsMap();
		$variantsMap = new VariantsMap();
		$product_factory = new \WC_Product_Factory();
		$variants = array();

		/** @var \WC_Order_Item_Product $item */
		foreach ($order->get_items() as $item) {

            /** @var \WC_Product_Simple $product */
            $product = $product_factory->get_product($item->get_product_id());

            if (false === $product) {
                throw EcommerceException::createForIncorrectOrder($order->get_id());
            }

			$gr_product_id = $productMap->get_gr_product_id($store_id, $item->get_product_id());

            if (empty($gr_product_id)) {
                $product_service->add_product($store_id, $product);
            } else {
                $product_service->get_gr_product($store_id, $gr_product_id);
            }

            $productId = $item->get_product_id();
            $variantId = $item->get_variation_id();

            $grVariantId = $variantsMap->get_gr_variant_id($store_id, empty($variantId) ? $productId : $variantId);

			$variants[] = array(
				'variantId' => $grVariantId,
				'quantity' => $item->get_quantity(),
				'price' => $product->get_price(),
				'priceTax' => 0
			);
		}

		$billing_address = AddressFactory::createAddress($order->get_address('billing'));

		$shipping_address = AddressFactory::createAddress($order->get_address('shipping'));

		$billing_country_code = $billing_address->get_country_code();
		if (empty($billing_country_code)) {
			$billing_address = array();
		} else {
			$billing_address = $billing_address->to_array();
		}

		$shipping_country_code = $shipping_address->get_country_code();
		if (empty($shipping_country_code)) {
			$shipping_address = $billing_address;
		} else {
			$shipping_address = $shipping_address->to_array();
		}

		return array(
			'contactId' => $contact_id,
			'totalPrice' => $order->get_subtotal(),
			'totalPriceTax' => $order->get_total(false),
			'currency' => $order->get_currency(),
			'externalId' => $order->get_id(),
			'orderUrl' => $order->get_view_order_url(),
			'billingAddress' => $billing_address,
			'shippingAddress' => $shipping_address,
			'status' => $order->get_status(),
			'selectedVariants' => $variants,
			'cartId' => $cart_id,
			'processedAt' => $order->get_date_created()->date(DATE_ISO8601)
		);
	}
}

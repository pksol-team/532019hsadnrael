<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class CartService
 * @package Getresponse\WordPress
 */
class CartService {

	/** @var Api */
	private $api;

	/**
	 * @param Api $api
	 */
	public function __construct( $api ) {
		$this->api = $api;
	}

	/**
	 * @param Cart $cart
	 *
	 * @throws ApiException
	 */
	public function remove_cart( $cart ) {

		$existing_cart = $this->api->get_carts( $cart->get_store_id(),
			array( 'query' => array( 'externalId' => $cart->get_external_id() ) ) );

		if ( empty( $existing_cart ) ) {
			return;
		}

		$gr_cart = (array) reset( $existing_cart );
		$this->api->remove_cart( $cart->get_store_id(), $gr_cart['cartId'] );
	}

	/**
	 * @param Cart $cart
	 *
	 * @return array
	 * @throws ApiException
	 */
	public function upsert_cart($cart) {

		$existing_cart = $this->api->get_carts(
		    $cart->get_store_id(),
			array('query' => array('externalId' => $cart->get_external_id())
            )
		);

		if (empty( $existing_cart)) {
			return $this->create_gr_cart($cart->get_store_id(), $cart->for_api());
		}

		$existing_cart = (array)reset($existing_cart);

		return $this->api->update_cart($existing_cart['cartId'], $cart->get_store_id(), $cart->for_api());
	}

	public function generate_cart_id() {
		return md5( time() + rand( 0, 99999 ) );
	}

	/**
	 * @param string $store_id
	 * @param array $cart_products
	 *
	 * @return array
	 * @throws ApiException
	 * @throws EcommerceException
	 */
	public function build_variants_from_products( $store_id, $cart_products ) {

		$variants        = array();
		$product_service = new ProductService(ApiFactory::create_api());
		$product_factory = new \WC_Product_Factory();

		/** @var array $_product */
		foreach ( $cart_products as $_product ) {

			$product = $product_factory->get_product( $_product['product_id'] );

			$gr_product_id = $product_service->get_gr_product_id( $store_id, $_product['product_id'] );

			if ( empty( $gr_product_id ) ) {
				$gr_product = $product_service->add_product( $store_id, $product );
			} else {
				$gr_product = $product_service->get_gr_product( $store_id, $gr_product_id );
			}

			if ( empty( $gr_product ) ) {
				return array();
			}

			$variant = $product_service->add_product_variant(
				$store_id,
				$_product['variation_id'],
				$_product['quantity'],
				$product->get_type(),
				$gr_product['variants']
			);

			$variants[] = $variant;
		}

		return $variants;
	}

	/**
	 * @param string $store_id
	 * @param array $params
	 *
	 * @return array
	 * @throws ApiException
	 */
	public function create_gr_cart( $store_id, $params ) {

		return $this->api->create_cart( $store_id, $params );
	}
}

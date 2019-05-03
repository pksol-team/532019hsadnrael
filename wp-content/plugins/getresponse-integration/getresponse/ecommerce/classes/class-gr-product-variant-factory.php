<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class ProductVariantFactory
 * @package Getresponse\WordPress
 */
class ProductVariantFactory {

	/**
	 * @param \WC_Product_Variation $variant
	 *
	 * @return ProductVariant
	 */
	public static function create_from_woocommerce_variation( $variant ) {

		return new ProductVariant(
			$variant->get_name(),
			$variant->add_to_cart_url(),
			$variant->get_price(),
			null,
			$variant->get_stock_quantity(),
			$variant->get_sku(),
			$variant->get_description(),
			$variant->get_parent_id(),
			array()
		);
	}
}

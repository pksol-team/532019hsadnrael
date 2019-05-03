<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class CartVariantFactory
 * @package Getresponse\WordPress
 */
class CartVariantFactory {

	/**
	 * @param array $variant
	 *
	 * @return CartVariant
	 */
	public static function create_from_gr_variant($variant) {

		return new CartVariant(
			$variant['variantId'],
			$variant['url'],
			$variant['price'],
			$variant['priceTax'],
			$variant['quantity']
		);
	}
}

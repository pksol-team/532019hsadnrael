<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class CartHash
 * @package Getresponse\WordPress
 */
class CartHash {

	/**
	 * @param array $cart_data
	 *
	 * @return string
	 */
	public static function generate_hash_from_cart( $cart_data ) {

		$data = array();

		foreach ( $cart_data as $row ) {
			$data[] = array(
				'product_id'    => $row['product_id'],
				'variation_id'  => $row['variation_id'],
				'quantity'      => $row['quantity'],
				'line_total'    => $row['line_total'],
				'line_tax'      => $row['line_tax'],
				'line_subtotal' => $row['line_subtotal']
			);
		}

		return md5( serialize( $data ) );
	}
}

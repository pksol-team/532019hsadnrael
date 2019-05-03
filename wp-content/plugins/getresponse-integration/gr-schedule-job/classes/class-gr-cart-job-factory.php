<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class CartJobFactory
 * @package Getresponse\WordPress
 */
class CartJobFactory {

	/**
	 * @param string $action
	 * @param Cart $cart
	 *
	 * @return CartJob
	 */
	public static function crate_from_params( $action, $cart ) {
		return new CartJob( $action, $cart );
	}

}

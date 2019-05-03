<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class CartJob
 * @package Getresponse\WordPress
 */
class CartJob {

	/** @var string */
	private $action;

	/** @var Cart */
	private $cart;

	/**
	 * @param string $action
	 * @param Cart $cart
	 */
	public function __construct( $action, $cart ) {
		$this->action = $action;
		$this->cart   = $cart;
	}

	/**
	 * @return string
	 */
	public function get_action() {
		return $this->action;
	}

	/**
	 * @return Cart
	 */
	public function get_cart() {
		return $this->cart;
	}
}

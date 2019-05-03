<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class Session
 * @package Getresponse\WordPress
 */
class Session {

	private $prefix = 'gr_';

	const GR_CART_ID = 'gr_cart_id';

    /**
	 * @param string $key
	 * @param string $value
	 */
	public function set($key, $value) {

		$_SESSION[$this->prefix.$key] = $value;
	}

	/**
	 * @param string $key
	 *
	 * @return string
	 */
	public function get($key) {
		return isset($_SESSION[$this->prefix.$key]) ? $_SESSION[$this->prefix.$key] : null;
	}
}

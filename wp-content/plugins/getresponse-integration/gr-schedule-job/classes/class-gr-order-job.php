<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class OrderJob
 * @package Getresponse\WordPress
 */
class OrderJob {

	/** @var string */
	private $action;

	/** @var Order */
	private $order;

	/**
	 * @param string $action
	 * @param Order $order
	 */
	public function __construct( $action, $order ) {
		$this->action = $action;
		$this->order    = $order;
	}

	/**
	 * @return string
	 */
	public function get_action() {
		return $this->action;
	}

	/**
	 * @return Order
	 */
	public function get_order() {
		return $this->order;
	}
}

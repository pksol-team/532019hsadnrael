<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class Order
 * @package Getresponse\WordPress
 */
class Order implements ScheduleJobInterface {

	/** @var string */
	private $store_id;
	/** @var string */
	private $cart_id;
	/** @var string */
	private $contact_id;
	/** @var int */
	private $order_id;
	/** @var bool */
	private $skip_automation;

    /**
     * @param string $store_id
     * @param string $cart_id
     * @param string $contact_id
     * @param int $order_id
     * @param bool $skip_automation
     */
	public function __construct($store_id, $cart_id, $contact_id, $order_id, $skip_automation = false) {
		$this->store_id = $store_id;
		$this->cart_id = $cart_id;
		$this->contact_id = $contact_id;
		$this->order_id = $order_id;
		$this->skip_automation = $skip_automation;
	}

	/**
	 * @return string
	 */
	public function get_store_id() {
		return $this->store_id;
	}

	/**
	 * @return string
	 */
	public function get_cart_id() {
		return $this->cart_id;
	}

	/**
	 * @return string
	 */
	public function get_contact_id() {
		return $this->contact_id;
	}

	/**
	 * @return int
	 */
	public function get_order_id() {
		return $this->order_id;
	}

    /**
     * @return bool
     */
	public function get_skip_automation() {
	    return $this->skip_automation;
    }

	/**
	 * @return array
	 */
	public function for_schedule_job() {
		return array(
			'store_id' => $this->store_id,
			'cart_id' => $this->cart_id,
			'contact_id' => $this->contact_id,
			'order_id' => $this->order_id,
            'skip_automation' => $this->skip_automation,
		);
	}
}

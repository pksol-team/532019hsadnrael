<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class ScheduleJobCartPayload
 * @package Getresponse\WordPress
 */
class ScheduleJobCartPayload {

	/** @var int */
	private $store_id;

	/** @var int */
	private $external_id;

	/** @var int */
	private $customer_id;

	/** @var array */
	private $products;

	/** @var float */
	private $total_price;

	/** @var float */
	private $total_tax_price;

	/** @var string */
	private $currency;

	/** @var string */
	private $url;

	/**
	 * @param int $store_id
	 * @param int $external_id
	 * @param int $customer_id
	 * @param array $products
	 * @param float $total_price
	 * @param float $total_tax_price
	 * @param string $currency
	 * @param string $url
	 */
	public function __construct(
		$store_id,
		$external_id,
		$customer_id,
		$products,
		$total_price,
		$total_tax_price,
		$currency,
		$url
	) {
		$this->store_id        = $store_id;
		$this->external_id     = $external_id;
		$this->customer_id     = $customer_id;
		$this->products        = $products;
		$this->total_price     = $total_price;
		$this->total_tax_price = $total_tax_price;
		$this->currency        = $currency;
		$this->url             = $url;
	}

	/**
	 * @return int
	 */
	public function get_store_id() {
		return $this->store_id;
	}

	/**
	 * @return int
	 */
	public function get_external_id() {
		return $this->external_id;
	}

	/**
	 * @return int
	 */
	public function get_customer_id() {
		return $this->customer_id;
	}

	/**
	 * @return array
	 */
	public function get_products() {
		return $this->products;
	}

	/**
	 * @return float
	 */
	public function get_total_price() {
		return $this->total_price;
	}

	/**
	 * @return float
	 */
	public function get_total_tax_price() {
		return $this->total_tax_price;
	}

	/**
	 * @return string
	 */
	public function get_currency() {
		return $this->currency;
	}

	/**
	 * @return string
	 */
	public function get_url() {
		return $this->url;
	}
}

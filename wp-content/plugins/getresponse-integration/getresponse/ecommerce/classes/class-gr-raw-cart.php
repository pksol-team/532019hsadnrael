<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class RawCart
 * @package Getresponse\WordPress
 */
class RawCart implements ScheduleJobInterface {

	/** @var string */
	private $store_id;

	/** @var string */
	private $contact_id;

	/** @var string */
	private $currency;

	/** @var float */
	private $total_price;

	/** @var string */
	private $cart_id;

	/** @var float */
	private $total_tax_price;

	/** @var string */
	private $cart_url;

	/** @var array */
	private $variants;

	/**
	 * @param string $store_id
	 * @param string $contact_id
	 * @param float $total_price
	 * @param string $cart_id
	 * @param float $total_tax_price
	 * @param array $variants
	 * @param string $currency
	 * @param string $cart_url
	 */
	public function __construct(
		$store_id,
		$contact_id,
		$total_price,
		$cart_id,
		$total_tax_price,
		$variants,
		$cart_url,
		$currency
	) {

        $grUrl = new Url($cart_url);
        if ($grUrl->isValid()) {
            $this->cart_url = $grUrl->getUrl();
        }

		$this->store_id        = $store_id;
		$this->contact_id      = $contact_id;
		$this->currency        = $currency;
		$this->total_price     = $total_price;
		$this->cart_id     = $cart_id;
		$this->total_tax_price = $total_tax_price;
		$this->variants        = $variants;
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
	public function get_contact_id() {
		return $this->contact_id;
	}

	/**
	 * @return string
	 */
	public function get_currency() {
		return $this->currency;
	}

	/**
	 * @return float
	 */
	public function get_total_price() {
		return $this->total_price;
	}

	/**
	 * @return string
	 */
	public function get_cart_id() {
		return $this->cart_id;
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
	public function get_cart_url() {
		return $this->cart_url;
	}

	/**
	 * @return array
	 */
	public function for_schedule_job() {

		return array(
			'store_id'        => $this->store_id,
			'customer_id'     => $this->contact_id,
			'currency'        => $this->currency,
			'total_price'     => $this->total_price,
			'products'        => $this->variants,
			'external_id'     => $this->cart_id,
			'total_tax_price' => $this->total_tax_price,
			'url'             => $this->cart_url
		);
	}
}

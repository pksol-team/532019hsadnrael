<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class Cart
 * @package Getresponse\WordPress
 */
class Cart implements ScheduleJobInterface {

	/** @var string */
	private $store_id;

	/** @var string */
	private $contact_id;

	/** @var string */
	private $currency;

	/** @var float */
	private $total_price;

	/** @var string */
	private $external_id;

	/** @var float */
	private $total_tax_price;

	/** @var string */
	private $cart_url;

	/** @var CartVariant[] */
	private $variants;

	/**
	 * @param string $store_id
	 * @param string $contact_id
	 * @param float $total_price
	 * @param string $external_id
	 * @param float $total_tax_price
	 * @param CartVariant[] $variants
	 * @param string $currency
	 * @param string $cart_url
	 */
	public function __construct(
		$store_id,
		$contact_id,
		$total_price,
		$external_id,
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
		$this->external_id     = $external_id;
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
	public function get_external_id() {
		return $this->external_id;
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
			'external_id'     => $this->external_id,
			'total_tax_price' => $this->total_tax_price,
			'url'             => $this->cart_url
		);
	}

    /**
     * @return array
     */
	public function for_api() {

		$variants = array();

		/** @var CartVariant $product */
		foreach ( $this->variants as $variant ) {
			$variants[] = $variant->to_array();
		}

		$params = array(
			'contactId'        => $this->contact_id,
			'currency'         => $this->currency,
			'totalPrice'       => $this->total_price,
			'selectedVariants' => $variants,
			'externalId'       => $this->external_id,
			'totalTaxPrice'    => $this->total_tax_price
		);

		if (!empty($this->cart_url)) {
		    $params['cartUrl'] = $this->cart_url;
        }

        return $params;
	}

	/**
	 * @param float $total_price
	 */
	public function set_total_price( $total_price ) {
		$this->total_price = $total_price;
	}

	public function calculate_prices() {
		$total_price = 0;
		/** @var CartVariant $variant */
		foreach ( $this->variants as $variant ) {
			$total_price += $variant->get_price() * $variant->get_quantity();
		}

		$this->total_price = $total_price;
	}
}

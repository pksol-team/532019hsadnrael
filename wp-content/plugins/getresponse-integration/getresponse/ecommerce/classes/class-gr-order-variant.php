<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class OrderVariant
 * @package Getresponse\WordPress
 */
class OrderVariant {

	/** @var string */
	private $id;

	/** @var string */
	private $url;

	/** @var float */
	private $price;

	/** @var float */
	private $price_tax;

	/** @var int */
	private $quantity;

	/** @var array */
	private $taxes;

	/**
	 * @param string $id
	 * @param string $url
	 * @param float $price
	 * @param float $price_tax
	 * @param int $quantity
	 * @param array $taxes
	 */
	public function __construct($id, $url, $price, $price_tax, $quantity, $taxes) {

        $grUrl = new Url($url);
        if ($grUrl->isValid()) {
            $this->url = $grUrl->getUrl();
        }

		$this->id        = $id;
		$this->price     = $price;
		$this->price_tax = $price_tax;
		$this->quantity  = $quantity;
		$this->taxes = $taxes;
	}

	/**
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function get_url() {
		return $this->url;
	}

	/**
	 * @return float
	 */
	public function get_price() {
		return $this->price;
	}

	/**
	 * @return float
	 */
	public function get_price_tax() {
		return $this->price_tax;
	}

	/**
	 * @return int
	 */
	public function get_quantity() {
		return $this->quantity;
	}

	/**
	 * @return array
	 */
	public function get_taxes() {
		return $this->taxes;
	}

	/**
	 * @return array
	 */
	public function to_array() {
		return array(
			'variantId' => $this->get_id(),
			'quantity' => $this->get_quantity(),
			'price' => $this->get_price(),
			'priceTax' => $this->get_price_tax(),
			'taxes' => $this->get_taxes()
		);
	}
}

<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class CartVariant
 * @package Getresponse\WordPress
 */
class CartVariant {

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

	/**
	 * @param string $id
	 * @param string $url
	 * @param float $price
	 * @param float $price_tax
	 * @param int $quantity
	 */
	public function __construct($id, $url, $price, $price_tax, $quantity) {

        $grUrl = new Url($url);
        if ($grUrl->isValid()) {
            $this->url = $grUrl->getUrl();
        }

		$this->id        = $id;
		$this->price     = $price;
		$this->price_tax = $price_tax;
		$this->quantity  = $quantity;
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
	 * @param int $quantity
	 */
	public function set_quantity($quantity) {
		$this->quantity = $quantity;
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
			'href' => $this->get_url()
		);
	}
}

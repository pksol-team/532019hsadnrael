<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class ProductVariant
 * @package Getresponse\WordPress
 */
class ProductVariant {

	/** @var string */
	private $name;

	/** @var string */
	private $url;

	/** @var float */
	private $price;

	/** @var float */
	private $price_tax;

	/** @var int */
	private $quantity;

	/** @var string */
	private $sku;

	/** @var string */
	private $external_id;

	/** @var string */
	private $description;

	/** @var array */
	private $images;

	/**
	 * @param string $name
	 * @param string $url
	 * @param float $price
	 * @param float $price_tax
	 * @param int $quantity
	 * @param string $sku
	 * @param string $description
	 * @param string $external_id
	 * @param array $images
	 */
	public function __construct( $name, $url, $price, $price_tax, $quantity, $sku, $description, $external_id, $images ) {

	    $grUrl = new Url($url);
	    if ($grUrl->isValid()) {
	        $this->url = $grUrl->getUrl();
        }

		$this->name      = $name;
		$this->price     = $price;
		$this->price_tax = $price_tax;
		$this->quantity  = $quantity;
		$this->sku       = $sku;
		$this->description = $description;
		$this->external_id = $external_id;
		$this->images = $images;
	}

	/**
	 * @return string
	 */
	public function get_name() {
		return $this->name;
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
	 * @return string
	 */
	public function get_sku() {
		return $this->sku;
	}

	/**
	 * @return string
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * @return string
	 */
	public function get_external_id() {
		return $this->external_id;
	}

	/**
	 * @return array
	 */
	public function to_array() {
		return array(
			'name' => $this->get_name(),
			'url' => $this->get_url(),
			'price' => $this->get_price(),
			'priceTax' => $this->get_price_tax(),
			'sku' => $this->get_sku(),
			'quantity' => $this->get_quantity(),
			'description' => $this->get_description(),
			'externalId' => $this->get_external_id(),
			'images' => $this->images
		);
	}
}

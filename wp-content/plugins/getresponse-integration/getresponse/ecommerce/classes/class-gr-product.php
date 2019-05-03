<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class Product
 * @package Getresponse\WordPress
 */
class Product {

	/** @var string */
	private $name;

	/** @var string */
	private $url;

	/** @var string */
	private $type;

	/** @var string */
	private $external_id;

	/** @var array[ProductVariant] */
	private $variants;

	private $categories;

	/**
	 * @param string $name
	 * @param string $url
	 * @param string $type
	 * @param string $external_id
	 * @param array $variants
	 * @param array $categories
	 */
	public function __construct($name, $url, $type, $external_id, array $variants, $categories) {

        $grUrl = new Url($url);
        if ($grUrl->isValid()) {
            $this->url = $grUrl->getUrl();
        }

		$this->name        = $name;
		$this->type        = $type;
		$this->external_id = $external_id;
		$this->variants    = $variants;
		$this->categories = $categories;
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
	 * @return string
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * @return string
	 */
	public function get_external_id() {
		return $this->external_id;
	}

	/**
	 * @return array[GR_Product_Variant]
	 */
	public function get_variants() {
		return $this->variants;
	}

	/**
	 * @return array
	 */
	public function to_array() {

		$variants = array();

		/** @var ProductVariant $variant */
		foreach ($this->variants as $variant) {
			$variants[] = $variant->to_array();
		}

		return array(
			'name' => $this->get_name(),
			'url' => $this->get_url(),
			'type' => $this->get_type(),
			'externalId' => $this->get_external_id(),
			'categories' => $this->categories,
			'variants' => $variants
		);
	}
}

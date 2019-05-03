<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class ProductJob
 * @package Getresponse\WordPress
 */
class ProductJob {

	/** @var int */
	private $product_id;

	/** @var int */
	private $variant_id;

	/** @var int */
	private $quantity;

	/**
	 * @param int $product_id
	 * @param int $variant_id
	 * @param int $quantity
	 */
	public function __construct( $product_id, $variant_id, $quantity ) {
		$this->product_id = $product_id;
		$this->variant_id = $variant_id;
		$this->quantity   = $quantity;
	}

	public function for_payload() {
		return array(
			'product_id' => $this->product_id,
			'variation_id' => $this->variant_id,
			'quantity' => $this->quantity
		);
	}
}

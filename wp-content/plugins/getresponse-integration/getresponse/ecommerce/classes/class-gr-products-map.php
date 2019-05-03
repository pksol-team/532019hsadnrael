<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class ProductsMap
 * @package Getresponse\WordPress
 */
class ProductsMap {

    /** @var \wpdb */
    private $wpdb;
    /** @var string */
    private $table;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table = $this->wpdb->prefix . 'gr_products_map';
    }

    /**
	 * @param string $storeId
	 * @param string $grProductId
	 * @param int $productId
	 */
	public function add_product($storeId, $grProductId, $productId)
    {
		$sql = "
        INSERT INTO 
		    " . $this->table . " (`store_id`, `gr_product_id`, `woocommerce_product_id`) 
        VALUES 
		    (%s, %s, %d)
		";

        $this->wpdb->query($this->wpdb->prepare($sql, array($storeId, $grProductId, $productId)));
	}

    /**
     * @param string $storeId
     */
	public function removeProductsByGrStoreId($storeId)
    {
        $this->wpdb->delete($this->table, array('store_id' => $storeId));
    }

	/**
	 * @param string $storeId
	 * @param int $productId
	 * @return string|null
	 */
	public function get_gr_product_id($storeId, $productId)
    {
		$sql = "
		SELECT 
		    `gr_product_id` 
        FROM 
            " . $this->table . " 
        WHERE 
            `store_id` = %s 
            AND `woocommerce_product_id` = %s
		";

		return $this->wpdb->get_var($this->wpdb->prepare($sql, array($storeId, $productId)));
	}

	/**
	 * @param int $storeId
	 * @param string $grProductId
	 * @return string|null
	 */
	public function get_product_by_gr_id($storeId, $grProductId)
    {
		$sql = "
		SELECT 
		    `woocommerce_variant_id` 
        FROM 
            " . $this->table . "
        WHERE 
            `store_id` = %s 
            AND `gr_variant_id` = %s
		";

		return $this->wpdb->get_var($this->wpdb->prepare($sql, array($storeId, $grProductId)));
	}
}

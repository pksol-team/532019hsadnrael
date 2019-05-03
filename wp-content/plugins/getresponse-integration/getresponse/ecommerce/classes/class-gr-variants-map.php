<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class VariantsMap
 * @package Getresponse\WordPress
 */
class VariantsMap {

    /** @var \wpdb */
    private $wpdb;
    /** @var string */
    private $table;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table = $this->wpdb->prefix . 'gr_variants_map';
    }

	/**
	 * @param int $storeId
	 * @param int $variantId
	 * @param string $grVariantId
	 */
	public function add_variant($storeId, $variantId, $grVariantId)
    {
		$sql = "
		INSERT INTO 
		    " . $this->table . " (`store_id`, `woocommerce_variant_id`, `gr_variant_id`) 
		VALUES
		        (%s, %s, %s)
		";

		$this->wpdb->query($this->wpdb->prepare($sql, array($storeId, $variantId, $grVariantId)));
	}

    /**
     * @param string $storeId
     */
    public function removeVariantsByGrStoreId($storeId)
    {
        $this->wpdb->delete($this->table, array('store_id' => $storeId));
    }

	/**
	 * @param string $storeId
	 * @param int $variantId
	 * @return string|null
	 */
	public function get_gr_variant_id($storeId, $variantId)
    {
		$sql = "
		SELECT 
		    `gr_variant_id` 
        FROM 
            " . $this->table . " 
        WHERE 
            `store_id` = %s 
            AND `woocommerce_variant_id` = %s
		";

		return $this->wpdb->get_var($this->wpdb->prepare($sql, array($storeId, $variantId)));
	}
}

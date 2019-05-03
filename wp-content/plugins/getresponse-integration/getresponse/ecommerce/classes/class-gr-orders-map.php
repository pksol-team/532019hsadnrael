<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class OrdersMap
 * @package Getresponse\WordPress
 */
class OrdersMap {

    /** @var \wpdb */
    private $wpdb;
    /** @var string */
    private $table;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table = $this->wpdb->prefix . 'gr_orders_map';
    }


    /**
	 * @param string $storeId
	 * @param string $grOrderId
	 * @param int $woocommerceOrderId
	 */
	public function add_order($storeId, $grOrderId, $woocommerceOrderId)
    {
		$sql = "
        INSERT INTO 
            " . $this->table . "  (`store_id`, `gr_order_id`, `woocommerce_order_id`) 
		VALUES 
            (%s, %s, %s)
		";

		$this->wpdb->query($this->wpdb->prepare($sql, array($storeId, $grOrderId, $woocommerceOrderId)));
	}

    /**
     * @param string $storeId
     */
    public function removeOrdersByGrStoreId($storeId)
    {
        $this->wpdb->delete($this->table, array('store_id' => $storeId));
    }

	/**
	 * @param string $storeId
	 * @param int $woocommerceOrderId
	 * @return string|null
	 */
	public function get_gr_order_id($storeId, $woocommerceOrderId)
    {
		$sql = "
		    SELECT 
		        `gr_order_id` 
            FROM 
                " . $this->table . "
            WHERE 
                `store_id` = %s 
                AND `woocommerce_order_id` = %s
		";

		return $this->wpdb->get_var($this->wpdb->prepare($sql, array($storeId, $woocommerceOrderId)));
	}
}

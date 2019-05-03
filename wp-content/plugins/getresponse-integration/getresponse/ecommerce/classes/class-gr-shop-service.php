<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class ShopService
 * @package Getresponse\WordPress
 */
class ShopService {

	const CACHE_KEY = 'gr_shops';
	const CACHE_TIME = 300;

	/** @var Api */
	private $api;
    /** @var GrCache */
	private $cache;
    /** @var ProductsMap */
	private $productsMap;
    /** @var OrdersMap */
	private $ordersMap;
	/** @var VariantsMap */
	private $variantsMap;
    /** @var WoocommerceService */
	private $woocommerceService;

    /**
     * @param Api $api
     * @param GrCache $cache
     * @param ProductsMap $productsMap
     * @param OrdersMap $ordersMap
     * @param VariantsMap $variantsMap
     * @param WoocommerceService $woocommerceService
     */
	public function __construct(Api $api, GrCache $cache, ProductsMap $productsMap, OrdersMap $ordersMap, VariantsMap $variantsMap, WoocommerceService $woocommerceService)
    {
		$this->api = $api;
		$this->cache = $cache;
		$this->productsMap = $productsMap;
		$this->ordersMap = $ordersMap;
		$this->variantsMap = $variantsMap;
		$this->woocommerceService = $woocommerceService;
	}

	/**
	 * @return array
	 * @throws ApiException
	 */
	public function get_shops()
    {
		$shops = $this->cache->getValue(self::CACHE_KEY);

		if (false === $shops) {
			$shops = $this->api->get_shops();

			if (empty($shops)) {
				return array();
			}
			$this->cache->setValue(self::CACHE_KEY, $shops, self::CACHE_TIME);
		}

		return $shops;
	}

	/**
	 * @param string $storeId
	 * @throws ApiException
	 */
	public function delete_shop($storeId)
    {
		$this->api->delete_shop($storeId);
        $this->productsMap->removeProductsByGrStoreId($storeId);
        $this->ordersMap->removeOrdersByGrStoreId($storeId);
        $this->variantsMap->removeVariantsByGrStoreId($storeId);

        if ($this->woocommerceService->is_ecommerce_enabled()) {
            if ($storeId == $this->woocommerceService->get_ecommerce_store()) {
                $this->woocommerceService->disconnectEcommerce();
            }
        }

        $this->cache->deleteKey(self::CACHE_KEY);
	}

	/**
	 * @param string $shopName
	 * @param string $locale
	 * @param string $currency
	 * @throws ApiException
	 */
	public function add_shop($shopName, $locale, $currency)
    {
		$this->api->add_shop($shopName, $locale, $currency);
        $this->cache->deleteKey(self::CACHE_KEY);
	}
}

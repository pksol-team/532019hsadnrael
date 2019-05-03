<?php

namespace Getresponse\WordPress\Tests;

use Getresponse\WordPress\Api;
use Getresponse\WordPress\GrCache;
use Getresponse\WordPress\OrdersMap;
use Getresponse\WordPress\ProductsMap;
use Getresponse\WordPress\ShopService;
use Getresponse\WordPress\VariantsMap;
use Getresponse\WordPress\WoocommerceService;

/**
 * Class ShopServiceTest
 * @package Getresponse\WordPress\Tests
 */
class ShopServiceTest  extends BaseTestCase
{
    /** @var ShopService */
    private $shopService;
    /** @var Api | \PHPUnit_Framework_MockObject_MockObject */
    private $apiMock;
    /** @var GrCache | \PHPUnit_Framework_MockObject_MockObject */
    private $cacheMock;
    /** @var ProductsMap | \PHPUnit_Framework_MockObject_MockObject */
    private $productsMapMock;
    /** @var OrdersMap |  \PHPUnit_Framework_MockObject_MockObject */
    private $ordersMapMock;
    /** @var VariantsMap | \PHPUnit_Framework_MockObject_MockObject */
    private $variantsMapMock;
    /** @var WoocommerceService | \PHPUnit_Framework_MockObject_MockObject */
    private $woocommerceService;

    protected function setUp()
    {
        $this->apiMock = $this->createMockWithoutConstructor(Api::class);
        $this->cacheMock = $this->createMockWithoutConstructor(GrCache::class);
        $this->productsMapMock = $this->createMockWithoutConstructor(ProductsMap::class);
        $this->ordersMapMock = $this->createMockWithoutConstructor(OrdersMap::class);
        $this->variantsMapMock = $this->createMockWithoutConstructor(VariantsMap::class);
        $this->woocommerceService = $this->createMockWithoutConstructor(WoocommerceService::class);

        $this->shopService = new ShopService(
            $this->apiMock,
            $this->cacheMock,
            $this->productsMapMock,
            $this->ordersMapMock,
            $this->variantsMapMock,
            $this->woocommerceService
        );
    }

    /**
     * @test
     */
    public function shouldGetShopsFromCache()
    {
        $shops = ['shop1', 'shop2'];

        $this->cacheMock
            ->expects(self::once())
            ->method('getValue')
            ->with('gr_shops')
            ->willReturn($shops);

        $this->cacheMock
            ->expects(self::never())
            ->method('setValue');

        $this->apiMock
            ->expects(self::never())
            ->method('get_shops');

        self::assertEquals($shops, $this->shopService->get_shops());
    }

    /**
     * @test
     */
    public function shouldGetShopsFromApiAndSaveThemInCache()
    {
        $shops = ['shop1', 'shop2'];

        $this->cacheMock
            ->expects(self::once())
            ->method('getValue')
            ->with('gr_shops')
            ->willReturn(false);

        $this->cacheMock
            ->expects(self::once())
            ->method('setValue')
            ->with('gr_shops', $shops, 300);

        $this->apiMock
            ->expects(self::once())
            ->method('get_shops')
            ->willReturn($shops);

        self::assertEquals($shops, $this->shopService->get_shops());
    }

    /**
     * @test
     */
    public function shouldDeleteShopAndPurgeDbTables()
    {
        $storeId = 'a';

        $this->apiMock
            ->expects(self::once())
            ->method('delete_shop')
            ->with($storeId);

        $this->productsMapMock
            ->expects(self::once())
            ->method('removeProductsByGrStoreId')
            ->with($storeId);

        $this->ordersMapMock
            ->expects(self::once())
            ->method('removeOrdersByGrStoreId')
            ->with($storeId);

        $this->variantsMapMock
            ->expects(self::once())
            ->method('removeVariantsByGrStoreId')
            ->with($storeId);

        $this->cacheMock
            ->expects(self::once())
            ->method('deleteKey')
            ->with('gr_shops');

        $this->woocommerceService
            ->expects(self::once())
            ->method('is_ecommerce_enabled')
            ->willReturn(true);

        $this->woocommerceService
            ->expects(self::once())
            ->method('get_ecommerce_store')
            ->willReturn($storeId);

        $this->woocommerceService
            ->expects(self::once())
            ->method('disconnectEcommerce');

        $this->shopService->delete_shop($storeId);
    }

    /**
     * @test
     */
    public function shouldAddShop()
    {
        $name = 'name';
        $locale = 'PL_pl';
        $currency = 'PLN';

        $this->apiMock
            ->expects(self::once())
            ->method('add_shop')
            ->with($name, $locale, $currency);


        $this->cacheMock
            ->expects(self::once())
            ->method('deleteKey')
            ->with('gr_shops');

        $this->shopService->add_shop('name', 'PL_pl', 'PLN');
    }

}
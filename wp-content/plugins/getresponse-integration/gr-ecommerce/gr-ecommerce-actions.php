<?php

use Getresponse\WordPress\ApiException;
use Getresponse\WordPress\ApiFactory;
use Getresponse\WordPress\GrCache;
use Getresponse\WordPress\OrdersMap;
use Getresponse\WordPress\ProductsMap;
use Getresponse\WordPress\ShopService;
use Getresponse\WordPress\VariantsMap;
use Getresponse\WordPress\WoocommerceService;

defined('ABSPATH') || exit;

add_action('gr_settings_run', 'gr_delete_shop');
add_action('gr_settings_run', 'create_new_store');

function gr_delete_shop()
{
	if ('remove-shop' !== gr_get('action')) {
		return;
	}

	$shop_id = gr_get('id');

	if (empty($shop_id)) {
		gr()->add_error_message('We couldn’t delete this store. Please check if you’ve made any changes to the store ID');
		return;
	}

	try {
		$service = new ShopService(
		    ApiFactory::create_api(),
            new GrCache(),
            new ProductsMap(),
            new OrdersMap(),
            new VariantsMap(),
            new WoocommerceService(ApiFactory::create_api())
        );

		$service->delete_shop($shop_id);
        gr()->add_success_message('Store removed');
	} catch (ApiException $e) {
		gr()->add_error_message('We couldn’t delete this store. If the problem persists, please contact the GetResponse dev team.');
	}

	wp_redirect(admin_url(add_query_arg(array('page' => 'gr-integration-woocommerce'), 'admin.php')));
    exit;

}

/**
 * @throws \Exception
 */
function create_new_store()
{
	$submit = gr_post('add_shop');

	if ('Submit' !== $submit) {
		return;
	}

	$shop_name = gr_post('shop_name');

	if (strlen( $shop_name ) <= 3) {
		gr()->add_error_message( 'Your store name has to be at least 3 characters long' );
		return;
	}

	$language = 'EN';
	$currency = 'EUR';

	$locale = get_locale();

	if (strlen($locale) > 0) {
		$params = explode('_', $locale);
		$language = reset($params);
	}

	if (function_exists('get_woocommerce_currency')) {
		$currency = get_woocommerce_currency();
	}

	try {
		$service = new ShopService(
		    ApiFactory::create_api(),
            new GrCache(),
            new ProductsMap(),
            new OrdersMap(),
            new VariantsMap(),
            new WoocommerceService(ApiFactory::create_api())
        );
		$service->add_shop(
			$shop_name,
			strtoupper($language),
			$currency
		);
        gr()->add_success_message('GetResponse store created');
	} catch (ApiException $e) {
		gr()->add_error_message('Looks like we didn’t expect this technical problem. If it persists, please contact the GetResponse dev team');
	}

}

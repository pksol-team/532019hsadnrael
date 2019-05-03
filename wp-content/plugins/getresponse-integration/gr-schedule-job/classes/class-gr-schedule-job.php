<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class ScheduleJob
 * @package Getresponse\WordPress
 */
class ScheduleJob {

	const UPDATE_CART = 'update_cart';
	const REMOVE_CART = 'remove_cart';
	const CREATE_ORDER = 'update_order';
	const EXPORT_CUSTOMER = 'export_customer';
}

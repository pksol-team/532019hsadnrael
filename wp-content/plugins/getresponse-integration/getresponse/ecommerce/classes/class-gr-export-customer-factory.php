<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class ExportCustomerFactory
 * @package Getresponse\WordPress
 */
class ExportCustomerFactory
{
	/**
	 * @param string $campaign_id
	 * @param int $customer_id
	 * @param array $customs
	 * @param string $autoresponder_id
	 * @param string $store_id
	 *
	 * @return ExportCustomer
	 */
	public static function create_from_params(
		$campaign_id,
		$customer_id,
		$customs,
		$autoresponder_id,
		$store_id
	) {
		return new ExportCustomer(
			$campaign_id,
			$customer_id,
			$customs,
			$autoresponder_id,
			$store_id
		);
	}
}
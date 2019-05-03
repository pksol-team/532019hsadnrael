<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class ScheduleJobsBuilder
 * @package Getresponse\WordPress
 */
class ScheduleJobsBuilder {

	/**
	 * @param $jobs array
	 *
	 * @return array
	 */
	public function process_cart_jobs( $jobs ) {

		$virtual_carts = array();

		if ( empty( $jobs ) ) {
			return $virtual_carts;
		}

		/** @var CartJob $job */
		foreach ( $jobs as $job ) {

			if ( $job->get_action() === ScheduleJob::UPDATE_CART ) {
				$virtual_carts[ $job->get_cart()->get_store_id() ][ $job->get_cart()->get_contact_id() ][ $job->get_cart()->get_external_id() ] = $job;
			}

			if ( $job->get_action() == ScheduleJob::REMOVE_CART ) {
				unset( $virtual_carts[ $job->get_cart()->get_store_id() ][ $job->get_cart()->get_contact_id() ][ $job->get_cart()->get_external_id() ] );
			}

		}

		return $this->prepare_cart_response( $virtual_carts );

	}

	/**
	 * @param array $virtual_carts
	 *
	 * @return array
	 */
	private function prepare_cart_response( $virtual_carts ) {

		$results = array();

		foreach ( $virtual_carts as $store_id => $_data ) {
			foreach ( $_data as $customer_id => $_carts ) {
				foreach ( $_carts as $cart_id => $job ) {
					/** @var CartJob $job */
					$results[] = $job;
				}
			}
		}

		return $results;
	}
}

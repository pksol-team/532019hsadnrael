<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class ScheduleJobRepository
 * @package Getresponse\WordPress
 */
class ScheduleJobRepository {

	/**
	 * @param string $customer_id
	 * @param string $type
	 * @param string $payload
	 *
	 */
	public function add($customer_id, $type, $payload) {
		global $wpdb;

		$sql = "
		INSERT INTO ".$wpdb->prefix ."gr_schedule_jobs_queue
			(`customer_id`, `type`, `payload`) 
		VALUES 
		(%s, %s, %s)
		";

		$wpdb->query($wpdb->prepare($sql, array($customer_id, $type, $payload)));
	}

	/**
	 * @return array
	 */
	public function get_schedules(  ) {
		global $wpdb;

		$sql = "SELECT * FROM ".$wpdb->prefix ."gr_schedule_jobs_queue";

		return (array) $wpdb->get_results($sql);
	}

	/**
	 * @param int $id
	 */
	public function remove_job( $id ) {
		global $wpdb;

		$sql = "DELETE FROM ".$wpdb->prefix ."gr_schedule_jobs_queue WHERE `id` = %s";
		$wpdb->query($wpdb->prepare($sql, array($id)));
	}
}

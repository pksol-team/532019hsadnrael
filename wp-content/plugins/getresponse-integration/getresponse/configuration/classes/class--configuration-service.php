<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class Configuration
 * @package Getresponse\WordPress
 */
class Configuration {

	/**
	 * @param int $status
	 */
	public function update_cron_job_status($status) {
		global $wpdb;

		$sql = "INSERT INTO " . $wpdb->prefix . "gr_configuration (name, value) VALUES(%s, %s) 
		ON DUPLICATE KEY UPDATE name=%s, value=%s";

		$wpdb->query( $wpdb->prepare( $sql, array( 'is_cron_job_running', $status, 'is_cron_job_running', $status ) ) );
	}

	public function lock_cron_job() {
		$this->update_cron_job_status(1);
	}

	public function unlock_cron_job() {
		$this->update_cron_job_status(0);
	}

	/**
	 * @return bool
	 */
	public function is_cron_job_locked() {

		global $wpdb;

		$sql = "SELECT 
					`value` 
				FROM 
					" . $wpdb->prefix . "gr_configuration
				WHERE `name` = %s";

		return (bool) $wpdb->get_var( $wpdb->prepare( $sql, array( 'is_cron_job_running' ) ) );
	}
}
<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Interface ScheduleJobInterface
 * @package Getresponse\WordPress
 */
interface ScheduleJobInterface
{
	/**
	 * @return string
	 */
	public function get_contact_id();

	/**
	 * @return array
	 */
	public function for_schedule_job();
}

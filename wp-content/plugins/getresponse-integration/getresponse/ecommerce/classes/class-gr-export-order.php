<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class ExportOrder
 * @package Getresponse\WordPress
 */
class ExportOrder implements ScheduleJobInterface {

	/** @var string */
	private $campaign_id;

	/** @var int */
	private $contact_id;

	/** @var array */
	private $custom_fields;

	/** @var string */
	private $autoresponder_id;

	/**
	 * @param string $campaign_id
	 * @param int $customer_id
	 * @param array $custom_fields
	 * @param string $autoresponder_id
	 */
	public function __construct( $campaign_id, $customer_id, $custom_fields, $autoresponder_id ) {
		$this->campaign_id      = $campaign_id;
		$this->contact_id       = $customer_id;
		$this->custom_fields    = $custom_fields;
		$this->autoresponder_id = $autoresponder_id;
	}

	/**
	 * @return string
	 */
	public function get_campaign_id() {
		return $this->campaign_id;
	}

	/**
	 * @return int
	 */
	public function get_contact_id() {
		return $this->contact_id;
	}

	/**
	 * @return array
	 */
	public function get_custom_fields() {
		return $this->custom_fields;
	}

	/**
	 * @return string
	 */
	public function get_autoresponder_id() {
		return $this->autoresponder_id;
	}

	/**
	 * @return array
	 */
	public function for_schedule_job() {

		return array(
			'campaign_id'      => $this->campaign_id,
			'contact_id'       => $this->contact_id,
			'custom_fields'    => $this->custom_fields,
			'autoresponder_id' => $this->autoresponder_id
		);
	}
}

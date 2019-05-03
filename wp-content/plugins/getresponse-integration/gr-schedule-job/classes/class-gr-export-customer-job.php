<?php
namespace Getresponse\WordPress;


defined( 'ABSPATH' ) || exit;

/**
 * Class ExportCustomerJob
 * @package Getresponse\WordPress
 */
class ExportCustomerJob {

	/** @var string */
	private $action;

	/** @var ExportCustomer */
	private $export_customer;

	/**
	 * @param string $action
	 * @param ExportCustomer $export_customer
	 */
	public function __construct( $action, $export_customer ) {
		$this->action = $action;
		$this->export_customer    = $export_customer;
	}

	/**
	 * @return string
	 */
	public function get_action() {
		return $this->action;
	}

	/**
	 * @return ExportCustomer
	 */
	public function get_export_customer() {
		return $this->export_customer;
	}
}

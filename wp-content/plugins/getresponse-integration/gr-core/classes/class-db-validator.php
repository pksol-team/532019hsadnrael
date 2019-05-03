<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class DbValidator
 * @package Getresponse\WordPress
 */
class DbValidator {

	/**
	 * Check, if all required tables exists in database.
	 */
	public function validate() {
		$missing_tables = $this->get_missing_tables();

		if (0 === count($missing_tables)) {
			return;
		}

		include_once __DIR__ . '/../../install.php';
		install_getresponse_tables();

		$missing_tables = $this->get_missing_tables();

		if (0 < count($missing_tables)) {
			$this->display_error($missing_tables);
		}
	}

	/**
	 * @param array $missing_tables
	 */
	private function display_error($missing_tables) {
		if (1 === count($missing_tables)) {
			gr()->add_error_message('The plugin didn\'t install properly (the table '.$missing_tables[0].' is missing).<br /> Try reinstalling it. If the problem persists, contact our Support Team.');
		} else {
			gr()->add_error_message('The plugin didn\'t install properly. The tables '.join(' and ', $missing_tables).' are missing.<br /> Try reinstalling it. If the problem persists, contact our Support Team.');
		}
	}

	/**
	 * @return array
	 */
	private function get_missing_tables() {
		global $wpdb;

		$missing_tables = array();

		foreach (gr()->db->plugin_tables as $table) {
			$sql = "show tables like '" . $wpdb->prefix . $table . "'";
			$exists = $wpdb->get_row( $sql );

			if (NULL === $exists) {
				$missing_tables[] =  '<strong>' . $wpdb->prefix . $table . '</strong>';
			}
		}

		return $missing_tables;
	}
}

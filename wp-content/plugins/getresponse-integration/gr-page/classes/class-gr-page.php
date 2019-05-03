<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class Page
 * @package Getresponse\WordPress
 */
class Page {

	/**
	 * Return different between current date and registered date in days
	 *
	 * @param string $now current date.
	 * @param string $user_registered_date user date.
	 *
	 * @return bool
	 */
	public function get_date_diff( $now, $user_registered_date ) {
		$now       = strtotime( $now );
		$user_date = strtotime( $user_registered_date );
		$diff      = $now - $user_date;

		return floor( $diff / 3600 / 24 );
	}
}

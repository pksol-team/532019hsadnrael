<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class AddressFactory
 * @package Getresponse\WordPress
 */
class AddressFactory {

    /**
     * @param $address
     * @return Address
     */
	public static function createAddress( $address ) {
		return new Address(
			isset($address['country']) ? $address['country'] : '',
			isset($address['first_name']) ? $address['first_name'] : '',
			isset($address['last_name']) ? $address['last_name'] : '',
			isset($address['address_1']) ? $address['address_1'] : '',
			isset($address['address_2']) ? $address['address_2'] : '',
			isset($address['city']) ? $address['city'] : '',
			isset($address['postcode']) ? $address['postcode'] : '',
			isset($address['state']) ? $address['state'] : '',
			null,
			isset($address['phone']) ? $address['phone'] : '',
			isset($address['company']) ? $address['company'] : ''
		);
	}
}

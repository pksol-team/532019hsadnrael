<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class Address
 * @package Getresponse\WordPress
 */
class Address {

	/** @var string */
	private $country_code;

	/** @var string */
	private $first_name;

	/** @var string */
	private $last_name;

	/** @var string */
	private $address;

	/** @var string */
	private $address2;

	/** @var string */
	private $city;

	/** @var string */
	private $zip;

	/** @var string */
	private $province;

	/** @var string */
	private $province_code;

	/** @var string */
	private $phone;

	/** @var string */
	private $company;

	/**
	 * @param string $country_code
	 * @param string $first_name
	 * @param string $last_name
	 * @param string $address
	 * @param string $address2
	 * @param string $city
	 * @param string $zip
	 * @param string $province
	 * @param string $province_code
	 * @param string $phone
	 * @param string $company
	 */
	public function __construct(
		$country_code,
		$first_name,
		$last_name,
		$address,
		$address2,
		$city,
		$zip,
		$province,
		$province_code,
		$phone,
		$company
	) {
		$this->country_code = $country_code;
		$this->first_name = $first_name;
		$this->last_name = $last_name;
		$this->address = $address;
		$this->address2 = $address2;
		$this->city = $city;
		$this->zip = $zip;
		$this->province = $province;
		$this->province_code = $province_code;
		$this->phone = $phone;
		$this->company = $company;
	}

	/**
	 * @return string
	 */
	public function get_country_code() {
		return $this->country_code;
	}

	/**
	 * @return string
	 */
	public function get_first_name() {
		return $this->first_name;
	}

	/**
	 * @return string
	 */
	public function get_last_name() {
		return $this->last_name;
	}

	/**
	 * @return string
	 */
	public function get_address() {
		return $this->address;
	}

	/**
	 * @return string
	 */
	public function get_address2() {
		return $this->address2;
	}

	/**
	 * @return string
	 */
	public function get_city() {
		return $this->city;
	}

	/**
	 * @return string
	 */
	public function get_zip() {
		return $this->zip;
	}

	/**
	 * @return string
	 */
	public function get_province() {
		return $this->province;
	}

	/**
	 * @return string
	 */
	public function get_province_code() {
		return $this->province_code;
	}

	/**
	 * @return string
	 */
	public function get_phone() {
		return $this->phone;
	}

	/**
	 * @return string
	 */
	public function get_company() {
		return $this->company;
	}

	/**
	 * @return array
	 */
	public function to_array() {

		return array(
			'countryCode' => gr_country_code_converter($this->get_country_code()),
			'name' => $this->get_first_name().'-'.$this->get_last_name(),
			'firstName' => $this->get_first_name(),
			'lastName' => $this->get_last_name(),
			'address1' => $this->get_address(),
			'address2' => $this->get_address2(),
			'city' => $this->get_city(),
			'zip' => $this->get_zip(),
			'province' => $this->get_province(),
			'provinceCode' => $this->get_province_code(),
			'phone' => $this->get_phone(),
			'company' => $this->get_company()
		);
	}
}

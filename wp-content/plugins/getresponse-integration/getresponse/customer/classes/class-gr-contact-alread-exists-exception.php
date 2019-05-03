<?php

namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class ContactAlreadyExistsException
 * @package Getresponse\WordPress
 */
class ContactAlreadyExistsException extends ApiException {

    /**
     * @return ContactAlreadyExistsException
     */
	public static function throw_when_contact_already_exists() {
		return new self('Contact already exists');
	}
}

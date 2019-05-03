<?php
namespace Getresponse\WordPress;

/**
 * Class ApiException
 * @package Getresponse\WordPress
 */
class ApiException extends \Exception {

    /**
     * @return ApiException
     */
    public static function create_for_invalid_response_status() {
        return new self("The API key seems incorrect. Please check if you typed or 
        	pasted it correctly. If you recently generated a new key, please make 
        	sure you're using the right one.");
    }

    /**
     * @param $error_message
     * @return ApiException
     */
	public static function create_for_invalid_curl_response( $error_message ) {
    	return new self($error_message);
	}

    /**
     * @param $error_message
     * @param $error_code
     * @return ApiException
     */
	public static function create_for_invalid_api_response_code( $error_message, $error_code ) {
		return new self($error_message, $error_code);
	}
}

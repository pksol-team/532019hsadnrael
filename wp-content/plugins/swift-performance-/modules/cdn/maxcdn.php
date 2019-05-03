<?php
/**
 * MaxCDN REST Client Library
 *
 * @copyright 2012
 * @author Karlo Espiritu
 * @version 1.0 2012-09-21
*/
class Swift_Performance_MaxCDN {

	public $alias;

	public $key;

	public $secret;

	public $MaxCDNrws_url = 'https://rws.maxcdn.com';

    private $consumer;

	public function __construct($alias, $key, $secret, $options=null) {
		// OAuth class
		require_once 'oauth.inc.php';


		$this->alias  = $alias;
		$this->key    = $key;
		$this->secret = $secret;
		$this->consumer = new \Swift_Performance\OAuthConsumer($key, $secret, NULL);

	}

	private function execute($selected_call, $method_type, $params) {
		$query_str = '';

		// the endpoint for your request
		$endpoint = "$this->MaxCDNrws_url/$this->alias$selected_call";

		//parse endpoint before creating OAuth request
		$parsed = parse_url($endpoint);
		if (array_key_exists("parsed", $parsed))
		{
		    parse_str($parsed['query'], $params);
		}

		//generate a request from your consumer
		$req_req = \Swift_Performance\OAuthRequest::from_consumer_and_token($this->consumer, NULL, $method_type, $endpoint, $params);

		//sign your OAuth request using hmac_sha1
		$sig_method = new \Swift_Performance\OAuthSignatureMethod_HMAC_SHA1();
		$req_req->sign_request($sig_method, $this->consumer, NULL);

		if ($method_type == "POST" || $method_type == "PUT" || $method_type == "DELETE") {
		    $query_str = \Swift_Performance\OAuthUtil::build_http_query($params);
		}

		// make call
		$_result = wp_remote_request($req_req, array(
				'method'	=> $method_type,
				'headers'	=> array(
						'Expect:',
						'Content-Length: ' . strlen($query_str)
				),
				'body'		=> $query_str,
				'timeout'	=> 60,
				'user-agent' => 'PHP MaxCDN API Client'
		));

		$result = '';
		$headers = array();
		if (is_wp_error($_result)){
			$curl_error = $_result->get_error_message();
		}
		else{
			$result		= $_result['body'];
			$headers	= $_result['headers'];
		}

		// $json_output contains the output string
		$json_output = $result;

		// catch errors
		if(!empty($curl_error) || empty($json_output)) {
			throw new Exception("CURL ERROR: $curl_error, Output: $json_output, HTTP Code: {$headers['http_code']}");
		}

		return $json_output;
	}

	public function get($selected_call, $params = array()){

		return $this->execute($selected_call, 'GET', $params);
	}

	public function post($selected_call, $params = array()){
		return $this->execute($selected_call, 'POST', $params);
	}

	public function put($selected_call, $params = array()){
		return $this->execute($selected_call, 'PUT', $params);
	}

	public function delete($selected_call, $params = array()){
		return $this->execute($selected_call, 'DELETE', $params);
	}


}

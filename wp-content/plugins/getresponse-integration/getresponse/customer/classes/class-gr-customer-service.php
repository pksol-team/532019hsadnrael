<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class CustomerService
 * @package Getresponse\WordPress
 */
class CustomerService {

	const CACHE_KEY = 'gr_logged_in_customer';
	const CACHE_TIME = 300;
	const CUSTOM_TYPE = 'wordpress';
	const CONTACT_ALREADY_ADDED_EXCEPTION = 'Invalid response status: 409, Contact already added';

	/** @var Api */
	private $api;

	/**
	 * @param Api $api
	 */
	public function __construct( $api ) {
		$this->api = $api;
	}

	/**
	 * Add new contact to campaign.
	 *
	 * @param string $campaign campaign name.
	 * @param string $name client name.
	 * @param string $email client email.
	 * @param int $autoresponder_id cycle day.
	 * @param array $user_customs custom fields.
	 *
	 * @return array
	 *
	 * @throws ApiException
	 * @throws ContactAlreadyExistsException
	 */
	public function add_contact( $campaign, $name, $email, $autoresponder_id = null, $user_customs = array() ) {

		$autoresponder_service = new AutoresponderService(ApiFactory::create_api());
		$cycle_day = $autoresponder_service->get_autoresponder_cycle_day($autoresponder_id);

		if (empty($name)) {
			$name = $this->get_contact_name($email);
		}

		$user_customs['origin'] = self::CUSTOM_TYPE;

		$params = array(
			'email'     => $email,
			'campaign'  => array( 'campaignId' => $campaign ),
			'ipAddress' => $_SERVER['REMOTE_ADDR'],
		);

		if (false === empty($name)) {
			$params['name'] = $name;
		}

		if (!is_null($cycle_day)) {
			$params['dayOfCycle'] = $cycle_day;
		}

		$contact = $this->get_customer( $email, $campaign );

		$service = new CustomFieldsService( $this->api );

		$params['customFieldValues'] = $service->create_custom_fields( $user_customs );

		// If contact already exists in gr account.
		if ( ! empty( $contact ) && isset( $contact['contactId'] ) ) {
            return $this->api->update_contact( $contact['contactId'], $params );
        }

		try {
			return $this->api->add_contact( $params );
		} catch ( ApiException $e ) {
			if ( $e->getMessage() === self::CONTACT_ALREADY_ADDED_EXCEPTION ) {
				throw ContactAlreadyExistsException::throw_when_contact_already_exists();
			}
		}
	}

	/**
	 * @param string $email
	 *
	 * @return string
	 */
	private function get_contact_name( $email ) {
		preg_match( '/[\w]+/i', $email, $result );
		$name = isset( $result[0] ) ? $result[0] : '';

		return empty( $name ) ? 'Friend' : $name;
	}

	/**
	 * @param string $email
	 * @param string $campaign_id
	 *
	 * @return array
	 * @throws ApiException
	 */
	public function get_customer( $email, $campaign_id ) {

		$key = self::CACHE_KEY . $email . $campaign_id;

		$customer = gr_cache_get( $key );

		if ( false === $customer || empty($customer)) {

			$customer = $this->load_customer_from_api( $email, $campaign_id );

			if ( empty( $customer ) ) {
				return array();
			}

			gr_cache_set( $key, $customer, self::CACHE_TIME );
		}

		return $customer;
	}

	/**
	 * @param string $email
	 *
	 * @throws ApiException
	 */
	public function refresh_customer( $email ) {

		$customer = $this->load_customer_from_api( $email );
		gr_cache_set( self::CACHE_KEY . $email, $customer, self::CACHE_TIME );
	}

	/**
	 * @param string $email
	 * @param string $campaign_id
	 *
	 * @return array
	 * @throws ApiException
	 */
	private function load_customer_from_api( $email, $campaign_id = '' ) {

		$query = array( 'query' => array( 'email' => $email ) );

		if ( ! empty( $campaign_id ) ) {
			$query['query']['campaignId'] = $campaign_id;
		}

		$customers = $this->api->get_contacts( $query );

		if ( empty( $customers ) ) {
			return array();
		}

		return (array) reset( $customers );
	}

    /**
     * @param string $listId
     * @param string $name
     * @param string $email
     * @param string $autoresponderId
     * @param array $customs
     * @return string|null
     */
    public function createOrGetContact($listId, $name, $email, $autoresponderId, $customs)
    {
        try {
            $result = $this->add_contact(
                $listId, $name, $email, $autoresponderId, $customs
            );
        } catch (ContactAlreadyExistsException $e) {
            $result = $this->get_customer($email, $listId);
        }

        return isset($result['contactId']) ? $result['contactId'] : null;
    }
}

<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class BuddyPress
 * @package Getresponse\WordPress
 */
class BuddyPress {

	/**
	 * BuddyPress Max days limit.
	 *
	 * If user do not confirm activation
	 * link until this number of days
	 * will be removed from queue.
	 *
	 * @var string
	 */
	public $max_bp_unconfirmed_days = '30';

	/**
	 * Check, if plugin is active.
	 */
	public function is_active() {

		$plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );

		foreach ($plugins as $plugin) {
			if( preg_match('/bp-loader\.php/', $plugin)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check, if plugin is active.
	 */
	public function is_enabled() {

		if (false === $this->is_active()) {
			return false;
		}

		if ( '1' !== gr_get_option( 'bp_registration_on' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Add contact to campaign.
	 *
	 * @param $user
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function add_contact( $user ) {

		if ( null !== $user->activation_key ) {
			return false;
		}

		if ( null === gr_get_option( 'api_key' ) ) {
			return false;
		}

		$responder_id = null;
		$responder_status = gr_get_option( 'bp_registration_campaign_autoresponder_status' );

		if ($responder_status) {
			$responder_id = gr_get_option( 'bp_registration_campaign_autoresponder' );
		}

		try {
            $gr_customer = new CustomerService(ApiFactory::create_api());
            $gr_customer->add_contact(
                gr_get_option( 'bp_registration_campaign' ),
                $user->display_name,
                $user->user_email,
                $responder_id
            );
        } catch (ApiException $e) {}

		return true;
	}

    /**
     * @param string $email
     */
	public function store_email_in_waiting_list( $email ) {

		if ( empty( $email ) ) {
			return;
		}

		$emails = $this->get_waiting_emails();

		// If emails not found - add only this one.
		if ( empty( $emails ) ) {
			$new_emails = array( $email );
		} else {
			$new_emails = array_merge( $emails, array( $email ) );
		}

		gr_update_option( 'bp_registered_emails', serialize( $new_emails ) );
	}

	/**
	 * Get emails from waiting list.
	 *
	 * @return array
	 */
	public function get_waiting_emails() {

		$emails = gr_get_option( 'bp_registered_emails' );

		if ( empty( $emails ) ) {
			return array();
		}

		return (array) unserialize( $emails );
	}

	/**
	 * Remove contact from waiting list if required.
	 *
	 * * @param $user
	 *
	 */
	public function remove_user_from_waiting_list( $user ) {

		$emails = $this->get_waiting_emails();

		if ( empty( $emails ) ) {
			return;
		}

		foreach ( $emails as $k => $v ) {

			if ( $user->user_email == $v ) {
				unset( $emails[ $k ] );
			}
		}

		gr_update_option( 'bp_registered_emails', serialize( $emails ) );
	}

	/**
	 * Clear waiting list.
	 *
	 */
	public function clear_waiting_list() {

		$emails = $this->get_waiting_emails();

		if ( empty( $emails ) ) {
			return;
		}

		foreach ( $emails as $k => $v ) {

			$user = gr()->db->get_user_details_by_email( $v );

			$page = new Page();
			$diff = $page->get_date_diff( date( 'Y-m-d H:i:s' ), $user->user_registered );

			if ( $diff >= $this->max_bp_unconfirmed_days ) {
				unset( $k );
			}
		}

		gr_update_option( 'bp_registered_emails', serialize( $emails ) );
	}
}

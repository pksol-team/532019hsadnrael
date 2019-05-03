<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class NinjaForms
 * @package Getresponse\WordPress
 */
class NinjaForms {

    /**
     * Check, if Ninja Forms is active.
     * @return bool
     */
    public function is_active() {

        $plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );

	    foreach ($plugins as $plugin) {
		    if( preg_match('/\/ninja-forms\.php/', $plugin)) {
			    return true;
		    }
	    }

        return false;
    }

    /**
     * Check, if plugin is active.
     * @return bool
     */
    public function is_enabled() {

        if (false === $this->is_active()) {
            return false;
        }

        if ( 1 !== (int) gr_get_option( 'ninjaforms_registration_on' ) ) {
            return false;
        }

        return true;
    }

	/**
	 * Add contact to campaign.
	 *
	 * @param string $name
	 * @param string $email
	 *
	 * @throws \Exception
	 */
    public function add_contact( $name, $email ) {

    	$api = ApiFactory::create_api();

        $responder_id = null;
        $responder_status = gr_get_option( 'ninjaforms_registration_campaign_autoresponder_status' );

        if ($responder_status) {
	        $responder_id = gr_get_option( 'ninjaforms_registration_campaign_autoresponder' );
        }

	    $gr_customer = new CustomerService($api);
	    $gr_customer->add_contact(
            gr_get_option( 'ninjaforms_registration_campaign' ),
            $name,
            $email,
		    $responder_id
        );
    }
}
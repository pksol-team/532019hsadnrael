<?php

use Getresponse\WordPress\ApiException;
use Getresponse\WordPress\ApiFactory;
use Getresponse\WordPress\Configuration;
use Getresponse\WordPress\CustomerService;
use Getresponse\WordPress\CustomFieldsService;
use Getresponse\WordPress\EcommerceException;
use Getresponse\WordPress\ScheduleJobRepository;
use Getresponse\WordPress\ScheduleJobService;
use Getresponse\WordPress\WoocommerceService;

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'gr_register_session' );
add_action( 'woocommerce_after_checkout_billing_form', 'gr_add_checkbox_to_checkout_page', 5 );
add_action( 'woocommerce_ppe_checkout_order_review', 'gr_add_checkbox_to_checkout_page', 5 );
add_action( 'woocommerce_checkout_order_processed', 'gr_grab_email_from_checkout_page', 5, 2 );
add_action( 'woocommerce_ppe_do_payaction', 'gr_paypal_grab_email_from_checkout_page', 5, 1 );
add_action( 'gr_settings_run', 'gr_update_woocommerce_settings' );
add_action( 'gr_settings_run', 'gr_export_woocommerce_subscribers' );
add_action( 'woocommerce_register_form', 'gr_add_checkbox_to_registration_form', 5, 1 );
add_action( 'woocommerce_customer_save_address', 'gr_grab_email_from_update_address_details_page' );
add_action( 'woocommerce_save_account_details', 'gr_grab_email_from_edit_account' );
add_action( 'woocommerce_cart_updated', 'gr_update_cart', 10, 2 );
add_action( 'wp_login', 'gr_check_logged_user', 10, 2 );

add_action( 'woocommerce_order_status_pending', 'gr_update_order', 10, 2 );
add_action( 'woocommerce_order_status_processing', 'gr_update_order', 10, 2 );
add_action( 'woocommerce_order_status_failed', 'gr_update_order', 10, 2 );
add_action( 'woocommerce_order_status_on-hold', 'gr_update_order', 10, 2 );
add_action( 'woocommerce_order_status_completed', 'gr_update_order', 10, 2 );
add_action( 'woocommerce_order_status_refunded', 'gr_update_order', 10, 2 );
add_action( 'woocommerce_order_status_cancelled', 'gr_update_order', 10, 2 );

add_filter( 'cron_schedules', 'gr_schedule' );
add_action( 'getresponse_jobs', 'gr_handle_ecommerce' );

function gr_schedule( $schedules ) {
    $schedules['gr_schedule'] = [
		'interval' => 600, // ten minutes
		'display'  => __( '10 minutes' )
	];

	return $schedules;
}

function gr_set_cron_jobs_schedule() {
    $schedule_service = new ScheduleJobService(
        new ScheduleJobRepository(),
        new Configuration()
    );

    if (
        (1 == gr_get_option('woocommerce_ecommerce') && $schedule_service->is_schedule_enabled())
        || true === gr_get_option('export_schedule')
    ) {
        wp_schedule_event( time(), 'gr_schedule', 'getresponse_jobs' );

        if (true === gr_get_option('export_schedule')) {
            gr_update_option('export_schedule', false);
        }
    } else {
        wp_clear_scheduled_hook( 'getresponse_jobs' );
    }
}

function gr_handle_ecommerce() {
	$service = new ScheduleJobService(
		new ScheduleJobRepository(),
		new Configuration()
	);
	$service->handle_jobs();
}

function gr_register_session() {
	if ( is_user_logged_in() && ! session_id() ) {
		session_start();
	}
}

/**
 * @param int $order_id
 */
function gr_update_order( $order_id ) {

	$api = ApiFactory::create_api();

	if ( empty( $api ) ) {
		return;
	}

	$service = new WoocommerceService($api);

	$order = \WC_Order_Factory::get_order($order_id);
    $email = $order->get_billing_email();

	try {
		$service->update_order($order, $email);
	} catch (ApiException $e) {
    } catch (EcommerceException $e) {}
}

/**
 * @param string $user_login
 *
 * @throws \Exception
 */
function gr_check_logged_user( $user_login ) {

	if ( false === gr()->is_connected_to_getresponse() ) {
		return;
	}

	$user = new \WP_User( $user_login );
	$api = ApiFactory::create_api();

	if ( empty( $user ) || empty( $api ) ) {
		return;
	}

	try {
		$service = new CustomerService( $api );
		$service->refresh_customer( $user->user_email );
	} catch (\Exception $e) {}
}

function gr_update_cart() {

	if ( is_admin() || ! is_user_logged_in() ) {
		return;
	}

	$user = wp_get_current_user();
	$api = ApiFactory::create_api();

	if ( empty( $user ) || empty( $api ) ) {
		return;
	}

	try {
		$service = new WoocommerceService( $api );
		$service->update_cart( WC()->cart, $user->user_email );
	} catch ( \Exception $e ) {

	}
}

/**
 * Export customers to GetResponse
 */
function gr_export_woocommerce_subscribers() {

	if ( 'Export' !== gr_post( 'Export' ) ) {
		return;
	}

	if ( false === gr()->is_woocommerce_plugin_active() ) {
		return;
	}

	$campaign_to_export    = gr_post( 'campaign_id_to_export' );
	$export_ecommerce_data = (bool) gr_post( 'export_send_ecommerce_data' );
	$store_id              = gr_post( 'store_id' );

	if ( empty( $campaign_to_export ) ) {
		gr()->add_error_message( 'You need to select a contact list' );

		return;
	}

	if ( $export_ecommerce_data && empty( $store_id ) ) {
		gr()->add_error_message( 'You need to select a store' );

		return;
	}

	// clear cache before export
	wp_cache_flush();

	$customs     = array();
	$woocommerce = new WoocommerceService( ApiFactory::create_api() );

	if ( '1' === gr_post( 'export_customs' ) ) {
		$customs = (array) gr_post( 'custom_fields_to_export' );
	}

	$responder_id          = null;
	$autoresponder_enabled = 'on' === gr_post( 'campaign_id_to_export_autoresponder_enabled' ) ? 1 : 0;

	if ( $autoresponder_enabled ) {
		$responder_id = gr_post( 'campaign_id_to_export_selected_autoresponder' );
	}

	$use_schedule = (bool) gr_post( 'use_schedule' );

	if ($use_schedule) {
	    gr_update_option('export_schedule', true);
    }

	try {
		$woocommerce->export_customers(
			$campaign_to_export,
			$responder_id,
			$customs,
			$store_id,
			$use_schedule
		);

        gr_set_cron_jobs_schedule();

        gr()->add_success_message( __( 'Customer data exported' ) );

	} catch (\Exception $e ) {
		gr()->add_error_message( $e->getMessage() );
	}

}

/**
 * Update WooCommerce settings.
 */
function gr_update_woocommerce_settings() {

	if ( 'Save Changes' !== gr_post( 'WooCommerce' ) ) {
		return;
	}

    if ( false === gr()->is_woocommerce_plugin_active() ) {
		return;
	}

    $schedule_service = new ScheduleJobService(
		new ScheduleJobRepository(),
		new Configuration()
	);

	$checkout_campaign = $checkout_label = $checkout_campaign_autoresponder = $checkout_checked = $sync_order_data = $woocommerce_ecommerce_store = $woocommerce_ecommerce_campaign = $checkout_autoresponder_enabled = null;

	$checkout_enabled  = 'enabled' === gr_post( 'checkout_status' ) ? 1 : 0;
	$ecommerce_enabled = 'enabled' === gr_post( 'ecommerce_status' ) ? 1 : 0;

	gr_update_option( 'woocommerce_checkout_on', $checkout_enabled );
	gr_update_option( 'woocommerce_ecommerce', $ecommerce_enabled );

	if ( 1 === $checkout_enabled ) {
		$checkout_campaign = gr_post( 'checkout_campaign' );

		if ( empty( $checkout_campaign ) ) {
			gr()->add_error_message( 'You need to select a contact list' );

			return;
		}

		$checkout_campaign_autoresponder = gr_post( 'checkout_campaign_selected_autoresponder' );
		$checkout_checked                = gr_post( 'checkout_checked' );
		$sync_order_data                 = gr_post( 'sync_order_data' );
		$checkout_autoresponder_enabled  = 'on' === gr_post( 'checkout_campaign_autoresponder_enabled' ) ? 1 : 0;
		$checkout_label = gr_post( 'checkout_label' ) ;
	}

	if ( 1 === $ecommerce_enabled ) {
		$woocommerce_ecommerce_store = gr_post( 'woocommerce_ecommerce_store' );

		if ( empty( $woocommerce_ecommerce_store ) ) {
			gr()->add_error_message( 'You need to select a store' );

			return;
		}

		$woocommerce_ecommerce_campaign = gr_post( 'ecommerce_campaign' );

		if ( empty( $woocommerce_ecommerce_campaign ) ) {
			gr()->add_error_message( 'You need to select a contact list' );

			return;
		}
	}

    gr_update_option( 'checkout_label', $checkout_label );
	gr_update_option( 'checkout_campaign', $checkout_campaign );
	gr_update_option( 'checkout_campaign_autoresponder_status', $checkout_autoresponder_enabled );
	gr_update_option( 'checkout_campaign_autoresponder', $checkout_campaign_autoresponder );
	gr_update_option( 'checkout_checked', $checkout_checked );
	gr_update_option( 'sync_order_data', $sync_order_data );
	gr_update_option( 'woocommerce_ecommerce_store', $woocommerce_ecommerce_store );
	gr_update_option( 'woocommerce_ecommerce_campaign', $woocommerce_ecommerce_campaign );

	$schedule_service->update_schedule_status( 'enabled' === gr_post( 'ecommerce_schedule_status' ) ? 1 : 0 );

	$custom_fields = gr_post( 'checkout_custom_fields' );

	if ( empty( $custom_fields ) ) {

		foreach ( array_keys( WoocommerceService::$billing_fields ) as $value ) {
			gr_delete_option( $value );
		}
	} else {

		$service = new CustomFieldsService(ApiFactory::create_api());
		$invalid_customs = $service->validate_custom_fields( $custom_fields );

		if ( !empty( $invalid_customs ) ) {
			foreach ( $invalid_customs as $custom ) {
				gr()->add_error_message( 'The custom field ' . $custom . ' contains invalid characters. Use lowercase English alphabet, numbers, and underscore ("_")' );
			}

			return;
		} else {

			// Sync order data - custom fields.
			foreach ( WoocommerceService::$billing_fields as $value => $bf ) {
				if ( in_array( $value, array_keys( $custom_fields ) ) ) {
					gr_update_option( $value, $custom_fields[ $value ] );
				} else {
					gr_delete_option( $value );
				}
			}
		}
	}

    gr_set_cron_jobs_schedule();

    gr()->add_success_message( __( 'Settings saved', 'Gr_Integration' ) );
}

/**
 * @throws\Exception
 */
function gr_grab_email_from_edit_account() {

	if ( false === gr()->is_connected_to_getresponse() ) {
		return;
	}

	if ( true === gr()->is_active_woocommerce_checkout() ) {
		return;
	}

	if ( false === gr()->is_woocommerce_plugin_active() ) {
		return;
	}

	$api = ApiFactory::create_api();

	if ( empty( $api ) ) {
		return;
	}

	$name  = gr_post( 'account_first_name' ) . ' ' . gr_post( 'account_last_name' );
	$email = gr_post( 'account_email' );

	$responder_id     = null;
	$responder_status = gr_get_option( 'registration_checkout_autoresponder_enabled' );

	if ( $responder_status ) {
		$responder_id = gr_get_option( 'registration_campaign_autoresponder' );
	}

	try {
        $gr_customer = new CustomerService($api);
        $gr_customer->add_contact(
            gr_get_option('registration_checkout_campaign'),
            $name,
            $email,
            $responder_id
        );
    } catch (ApiException $e) {}
}

/**
 * @throws\Exception
 */
function gr_grab_email_from_update_address_details_page() {

	if ( false === gr()->is_connected_to_getresponse() ) {
		return;
	}

	if ( true === gr()->is_active_woocommerce_checkout() ) {
		return;
	}

	if ( false === gr()->is_woocommerce_plugin_active() ) {
		return;
	}

	$api = ApiFactory::create_api();

	if ( empty( $api ) ) {
		return;
	}

	$customs = array();

	$name = gr_post( 'billing_first_name' ) . ' ' . gr_post( 'billing_last_name' );

	if ( '1' === gr_get_option( 'sync_order_data' ) ) {
		foreach ( WoocommerceService::$billing_fields as $custom_name => $custom_field ) {

			$custom = gr_get_option( $custom_name );

			if ( $custom && null !== gr_post( $custom_field['value'] ) ) {
				$customs[ $custom ] = gr_post( $custom_field['value'] );
			}
		}
	}

	$responder_id     = null;
	$responder_status = gr_get_option( 'registration_checkout_autoresponder_enabled' );

	if ( $responder_status ) {
		$responder_id = gr_get_option( 'registration_campaign_autoresponder' );
	}

	try {
        $gr_customer = new CustomerService($api);
        $gr_customer->add_contact(
            gr_get_option('registration_checkout_campaign'),
            $name,
            gr_post('billing_email'),
            $responder_id,
            $customs
        );
    } catch (ApiException $e) {}
}

/**
 * @throws\Exception
 */
function gr_grab_email_from_checkout_page() {

	if ( false === gr()->is_connected_to_getresponse() ) {
		return;
	}

	if ( '1' !== gr_post( 'gr_checkout_checkbox' ) ) {
		return;
	}

	if ( true === gr()->is_active_woocommerce_checkout() ) {
		return;
	}

	if ( false === gr()->is_woocommerce_plugin_active() ) {
		return;
	}

	if ( null === gr_get_option( 'api_key' ) ) {
		return;
	}

	$customs = array();

	$name = gr_post( 'billing_first_name' ) . ' ' . gr_post( 'billing_last_name' );

	if ( '1' === gr_get_option( 'sync_order_data' ) ) {
		foreach ( WoocommerceService::$billing_fields as $custom_name => $custom_field ) {
			$custom = gr_get_option( $custom_name );

			if ( $custom && null !== gr_post( $custom_field['value'] ) ) {
				$customs[ $custom ] = gr_post( $custom_field['value'] );
			}
		}
	}

	$autoresponder_id = null;
	$autoresponder_status = gr_get_option('checkout_campaign_autoresponder_status');

	if ($autoresponder_status) {
		$autoresponder_id = gr_get_option( 'checkout_campaign_autoresponder' );
	}

	try {
		$gr_customer = new CustomerService( ApiFactory::create_api() );
		$gr_customer->add_contact(
			gr_get_option( 'checkout_campaign' ),
			$name,
			gr_post( 'billing_email' ),
			$autoresponder_id,
			$customs
		);
	} catch (Exception $e) {}

}

/**
 * Grab email from checkout form - paypal express
 * @throws\Exception
 */
function gr_paypal_grab_email_from_checkout_page() {

	if ( false === gr()->is_connected_to_getresponse() ) {
		return;
	}

	if ( true === gr()->is_active_woocommerce_checkout() ) {
		return;
	}

	if ( '1' !== gr_post( 'gr_checkout_checkbox' ) ) {
		return;
	}

	$api = ApiFactory::create_api();

	if ( empty( $api ) ) {
		return;
	}

	$responder_id     = null;
	$responder_status = gr_get_option( 'checkout_campaign_autoresponder_status' );

	if ( $responder_status ) {
		$responder_id = gr_get_option( 'checkout_campaign_autoresponder' );
	}

	try {
        $gr_customer = new CustomerService($api);
        $gr_customer->add_contact(
            gr_get_option('checkout_campaign'),
            'Friend',
            gr_post('billing_email'),
            $responder_id
        );
    } catch (ApiException $e) {}
}

/**
 * Add Checkbox to checkout form
 */
function gr_add_checkbox_to_checkout_page() {

	if ( false === gr()->is_connected_to_getresponse() ) {
		return;
	}

	if ( true === gr()->is_active_woocommerce_checkout() ) {
		return;
	}

	if ( false === gr()->is_woocommerce_plugin_active() ) {
		return;
	}

	if ( 1 !== (int) gr_get_option( 'woocommerce_checkout_on' ) ) {
		return;
	}

	gr_load_template( 'page/woocommerce/checkbox_checkout.php' );
}

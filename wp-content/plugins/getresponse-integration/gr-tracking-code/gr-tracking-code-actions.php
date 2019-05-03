<?php

defined( 'ABSPATH' ) || exit;

add_action( 'gr_settings_run', 'update_tracking_code' );

function update_tracking_code() {

	$submit = gr_post( 'save_tracking_code' );

	if ( 'Save Changes' !== $submit ) {
		return;
	}

	try {
		$status  = (bool) gr_post( 'tracking_code' );
		$service = new \Getresponse\WordPress\TrackingCodeService(\Getresponse\WordPress\ApiFactory::create_api() );
		$service->update( $status );

		if ( $status ) {

			$service->get_tracking_code_from_api();
		}
	} catch (\Getresponse\WordPress\ApiException $e) {
		gr()->add_error_message( 'Cannot get tracking code' );

		return;
	}

	gr()->add_success_message( $status ? 'Web Event Tracking enabled' : 'Web Event Tracking disabled' );
}

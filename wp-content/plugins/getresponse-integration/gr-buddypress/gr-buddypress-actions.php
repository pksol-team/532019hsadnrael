<?php

defined( 'ABSPATH' ) || exit;

// Add BuddyPress custom stylesheets.
add_action( 'wp_head', 'gr_add_buddypress_custom_stylesheet' );

// Add checkbox on registration page.
add_action( 'bp_before_registration_submit_buttons', 'gr_buddypress_add_checkbox_to_registration_form', 5 );

// Add email to waiting list.
add_action( 'bp_core_signup_user', 'gr_buddypress_serve_registration_form', 5, 1 );

// Add new contact to campaign and clear waiting list.
add_action( 'bp_core_activated_user', 'gr_buddypress_add_contact_from_activation_page', 5, 1 );

/**
 * Add custom css file.
 */
function gr_add_buddypress_custom_stylesheet() {

	if ( false === gr()->buddypress->is_enabled() ) {
		return;
	}

	echo '<link rel="stylesheet" id="gr-bp-css" href="' . gr()->asset_path . '/css/getresponse-bp-form.css" type="text/css" media="all">';
}

/**
 * Add contact to GetResponse on activation step.
 * @throws Exception
 */
function gr_buddypress_add_contact_from_activation_page() {

	if ( false === gr()->buddypress->is_enabled() ) {
		return;
	}

	$emails = gr()->buddypress->get_waiting_emails();

	if ( empty( $emails ) ) {
		return;
	}

	foreach ( $emails as $k => $v ) {

		$user = gr()->db->get_user_details_by_email( $v );

		if ( true === empty( $user ) ) {
			continue;
		}

		// Add contact to campaign.
		if ( gr()->buddypress->add_contact( $user ) ) {

			// Remove email from waitling list.
			gr()->buddypress->remove_user_from_waiting_list( $user );
		}
	}

	// Clear waiting list with expired emails.
	gr()->buddypress->clear_waiting_list();
}

/**
 * Add checkbox to BoddyPress registration page.
 */
function gr_buddypress_add_checkbox_to_registration_form() {

	if ( false === gr()->buddypress->is_enabled() ) {
		return;
	}

	gr_load_template( 'page/partials/bp_registration_form.php' );

}

/**
 * Grab BoddyPress registration form.
 */
function gr_buddypress_serve_registration_form() {

	if ( '1' !== gr_post( 'gr_bp_checkbox' ) ) {
		return;
	}

	gr()->buddypress->store_email_in_waiting_list( gr_post( 'signup_email' ) );
}

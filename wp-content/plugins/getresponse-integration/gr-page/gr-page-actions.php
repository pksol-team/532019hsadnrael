<?php

use Getresponse\WordPress\ApiException;

defined( 'ABSPATH' ) || exit;

add_action( 'comment_form', 'gr_checkbox_add_to_comments_form' );
add_action( 'comment_post', 'gr_grab_email_from_comment' );
add_action( 'register_form', 'gr_add_checkbox_to_registration_form' );
add_action( 'user_register', 'gr_grab_email_from_registration_form' );

/**
 * Add checkbox to new comment form.
 */
function gr_checkbox_add_to_comments_form() {

	if ( null === gr_get_option( 'api_key' ) ) {
		return;
	}

	if ( 1 !== (int) gr_get_option( 'comment_checkout_enabled' ) ) {
		return;
	}

	gr_load_template( 'page/partials/comment.php' );
}

/**
 * Grab email from comment.
 * @throws Exception
 */
function gr_grab_email_from_comment() {

	$api = \Getresponse\WordPress\ApiFactory::create_api();

	if ( empty( $api ) ) {
		return;
	}

	if ( '1' !== gr_post( 'gr_comment_checkbox' ) ) {
		return;
	}

    $responder_id     = null;
    $comment_checkout_autoresponder_enabled = gr_get_option( 'comment_checkout_autoresponder_enabled' );

    if ( $comment_checkout_autoresponder_enabled ) {
        $responder_id = gr_get_option( 'comment_checkout_selected_autoresponder' );
    }

    if ( true === is_user_logged_in() ) {

		$current_user = wp_get_current_user();
		$name = trim( $current_user->user_firstname . ' ' . $current_user->user_lastname );

		if ( strlen( $name ) === 0 ) {
			$name = $current_user->get( 'user_login' );
		}

		$email = $current_user->user_email;

	} else {

		if ( null === gr_post( 'email' ) || '' === gr_post( 'email' ) ) {
			return;
		}

		if ( null === gr_post( 'author' ) || '' === gr_post( 'author' ) ) {
			return;
		}

        $name = gr_post( 'author' );
        $email = gr_post( 'email' );
	}

	try {
        $gr_customer = new \Getresponse\WordPress\CustomerService($api);
        $gr_customer->add_contact(
            gr_get_option('comment_checkout_campaign'),
            $name,
            $email,
            $responder_id
        );
    } catch (ApiException $e) {}
}

/**
 * Add checkbox to registration form.
 */
function gr_add_checkbox_to_registration_form() {

	if ( null === gr_get_option( 'api_key' ) ) {
		return;
	}

	if ( 1 !== (int) gr_get_option( 'registration_checkout_enabled' ) ) {
		return;
	}

	if ( true === is_user_logged_in() ) {
		return;
	}

	gr_load_template( 'page/partials/registration_form.php' );
}

/**
 * Grab email from registration form.
 * @throws Exception
 */
function gr_grab_email_from_registration_form() {

	if ( null === gr_get_option( 'api_key' ) ) {
		return;
	}

	if ( '1' !== gr_post( 'gr_registration_checkbox' ) ) {
		return;
	}

	$api = \Getresponse\WordPress\ApiFactory::create_api();

	if ( empty( $api ) ) {
		return;
	}

	$email = $name = null;

	$_email = gr_post( 'user_email' );
	if ( false === empty( $_email ) ) {
		$email = gr_post( 'user_email' );
	}

	$_name = gr_post( 'user_login' );
	if ( false === empty( $_name ) ) {
		$name = gr_post( 'user_login' );
	}

	$_email = gr_post( 'email' );
	if ( false === empty( $_email ) ) {
		$email = gr_post( 'email' );
	}

	$_name = gr_post( 'username' );
	if ( false === empty( $_name ) ) {
		$name = gr_post( 'username' );
	}

	if ( false === empty( $email ) ) {

		$responder_id     = null;
		$responder_status = gr_get_option( 'registration_checkout_autoresponder_enabled' );

		if ( $responder_status ) {
			$responder_id = gr_get_option( 'registration_campaign_autoresponder' );
		}

		try {
            $gr_customer = new \Getresponse\WordPress\CustomerService($api);
            $gr_customer->add_contact(
                gr_get_option('registration_checkout_campaign'),
                $name,
                $email,
                $responder_id
            );
        } catch (ApiException $e) {}
	}
}

<?php

defined( 'ABSPATH' ) || exit;

// Add new contact to campaign.
add_action( 'wpcf7_before_send_mail', 'gr_add_contact_from_contact_form_7', 5, 1 );

/**
 * Add contact to GetResponse.
 */
function gr_add_contact_from_contact_form_7() {

    if ( false === gr()->contactForm7->is_enabled() ) {
        return;
    }

    $name = WPCF7_Submission::get_instance()->get_posted_data('your-name');
    $email = WPCF7_Submission::get_instance()->get_posted_data('email');

    $signup_to_newsletter = WPCF7_Submission::get_instance()->get_posted_data('signup-to-newsletter');

    if (is_array($signup_to_newsletter)) {
        $signup_to_newsletter = join('', $signup_to_newsletter);
    }

    if (empty($signup_to_newsletter) || empty($email)) {
        return;
    }

    try {
        gr()->contactForm7->add_contact($name, $email);
    } catch (Exception $e) {
        return;
    }
}

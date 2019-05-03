<?php

defined( 'ABSPATH' ) || exit;

// Ninja Forms submission action
add_action( 'ninja_forms_after_submission', 'gr_add_contact_from_ninja_forms' );

/**
 * Add contact to GetResponse.
 */
function gr_add_contact_from_ninja_forms( $form_data ) {

    $email = gr_get_email_address_from_nf( $form_data );

    if (
        false === gr()->ninjaForms->is_enabled()
        || empty($email)
        || 1 != $form_data['fields_by_key']['signup-to-newsletter']['value']
    ) {
        return;
    }

    try {
        gr()->ninjaForms->add_contact(gr_get_name_from_nf( $form_data ), $email);
    } catch (Exception $e) {
        return;
    }
}



function gr_get_email_address_from_nf( $form_data ) {
    foreach ($form_data['fields_by_key'] as $field) {
        if ( 'email' == $field['type'] ) {
            return $field['value'];
        }
    }

    return '';
}

function gr_get_name_from_nf( $form_data ) {
    if (isset($form_data['fields_by_key']['name'])) {
        return $form_data['fields_by_key']['name']['value'];
    }

    foreach ($form_data['fields_by_key'] as $field) {
        if ('firstname' == $field['type'] || false !== strpos($field['settings']['name'], 'firstname_')) {
            return $field['value'];
        }
    }

    return '';
}
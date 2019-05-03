<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Display Settings form.
 */

$cf7_registration_type = (int) gr_get_option( 'cf7_registration_on' );

?>
<table class="form-table">
    <tr>
        <th>
            <label><?php _e( 'Status', 'Gr_Integration' ); ?></label>
        </th>
        <td>
            <label for="contactFormNo">
                <input id="contactFormNo" type="radio" name="contactFormStatus" <?php if ( 1 !== $cf7_registration_type ) : ?>checked<?php endif ?> value="disabled" >
                Disabled
            </label>
            <label for="contactFormYes">
                <input id="contactFormYes" type="radio" name="contactFormStatus" <?php if ( 1 === $cf7_registration_type ) : ?>checked<?php endif ?> value="enabled">
                Enabled
            </label>
        </td>
    </tr>
    <?php gr_return_campaign_selector(
            'cf7_registration_campaign',
            gr_get_option( 'cf7_registration_campaign' ),
            (bool) gr_get_option( 'cf7_registration_campaign_autoresponder_status' ),
            gr_get_option( 'cf7_registration_campaign_autoresponder' ),
            !$cf7_registration_type
        )
    ?>
    <tr>
        <th>
            <label><?php _e( 'Opt-in checkbox', 'Gr_Integration' ); ?></label>
        </th>
        <td>
            <p>Here's how to edit your contact form template:</p>
            <ol>
                <li>Paste the following snippet into the contact form template. Customize the call to action to suit your brand identity and encourage people to sign up.
                    <p><code>&lt;label&gt;[acceptance signup-to-newsletter id:signup-to-newsletter class:signup-to-newsletter optional] Sign up to our newsletter! [/acceptance]&lt;/label&gt;</code></p>
                </li>
                <li>Change the name of the email input field to:
                    <p><code><label>Your Email (required) [email* email]</label></code></p>
                </li>
                <li>
                    Save the changes and <a href="https://contactform7.com/getting-started-with-contact-form-7/">add the form to your WordPress site</a>.
                </li>
            </ol>
        </td>
    </tr>
</table>

<script>
    var contactFormNoCheckbox = jQuery('#contactFormNo');
    var contactFormYesCheckbox = jQuery('#contactFormYes');

    var cf7_campaign = jQuery('#campaigns_for_cf7_registration_campaign');
    var cf7_responder_status = jQuery('#cf7_registration_campaign_autoresponder_enabled');
    var cf7_responder_id = jQuery('#responders_for_cf7_registration_campaign');

    contactFormNoCheckbox.on('click', function() {
        cf7_campaign.prop('selectedIndex',0);
        cf7_responder_id.prop('selectedIndex',0);
        cf7_responder_status.prop('checked', false);

        cf7_campaign.prop('disabled', true);
        cf7_responder_status.prop('disabled', true);
        cf7_responder_id.prop('disabled', true);
    });

    contactFormYesCheckbox.on('click', function() {
        cf7_campaign.prop('disabled', false);
        cf7_responder_status.prop('disabled', false);
    });
</script>

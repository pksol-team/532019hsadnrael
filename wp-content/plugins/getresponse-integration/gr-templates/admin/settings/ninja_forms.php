<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Display Settings form.
 */

$ninjaforms_registration_type = (int) gr_get_option( 'ninjaforms_registration_on' );

?>
<table class="form-table">
    <tr>
        <th>
            <label><?php _e( 'Status', 'Gr_Integration' ); ?></label>
        </th>
        <td>
            <label for="contactFormNfNo">
                <input id="contactFormNfNo" type="radio" name="contactNinjaFormStatus" <?php if ( 1 !== $ninjaforms_registration_type ) : ?>checked<?php endif ?> value="disabled" >
                Disabled
            </label>
            <label for="contactFormNfYes">
                <input id="contactFormNfYes" type="radio" name="contactNinjaFormStatus" <?php if ( 1 === $ninjaforms_registration_type ) : ?>checked<?php endif ?> value="enabled">
                Enabled
            </label>
        </td>
    </tr>
    <?php gr_return_campaign_selector(
            'ninjaforms_registration_campaign',
            gr_get_option( 'ninjaforms_registration_campaign' ),
            (bool) gr_get_option( 'ninjaforms_registration_campaign_autoresponder_status' ),
            gr_get_option( 'ninjaforms_registration_campaign_autoresponder' ),
            !$ninjaforms_registration_type
        )
    ?>
    <tr>
        <th>
            <label><?php _e( 'Opt-in checkbox', 'Gr_Integration' ); ?></label>
        </th>
        <td>
            <p><?php _e( 'You can add an opt-in box to your Ninja Forms you\'ve added to your pages in WordPress. When someone fills out a form, they can be automatically added to your GetResponse contact list. To add the box, you\'ll need to edit your form settings within the Ninja Forms plugin.', 'Gr_Integration' ); ?></p>
            <ol>
                <li><?php _e( 'Go to Ninja Forms>>Dashboard and click the form to which you want to add the opt-in box or start building a new form. Be sure the form includes the <strong>Email</strong> field. <strong>Note</strong>: If you want to use the <strong>First Name</strong> field, its <strong>Field key</strong> value needs to start with <strong>firstname_</strong>.', 'Gr_Integration' ); ?></li>
                <li><?php _e( 'In the form builder, click the <strong>Add field</strong> icon in the bottom right to open the fields drawer.', 'Gr_Integration' ); ?></li>
                <li><?php _e( 'Single-click or drag the <strong>Single Checkbox</strong> field to where you want it to appear in the form.', 'Gr_Integration' ); ?></li>
                <li><?php _e( 'Edit the field <strong>Label</strong> to the call-to-action you want everyone to see.', 'Gr_Integration' ); ?></li>
                <li><?php _e( 'Expand the <strong>Advanced</strong> section. For <strong>Checked calculation value</strong>, enter 1. For </strong>Unchecked calculation value</strong>, enter 0.', 'Gr_Integration' ); ?></li>
                <li><?php _e( 'Expand the <strong>Administration</strong> section. Then, change the <strong>Field key</strong> value to <strong>signup-to-newsletter</strong> to enable passing the contact information to GetResponse.', 'Gr_Integration' ); ?></li>
                <li><?php _e( 'Click <strong>Done</strong> to save your changes.', 'Gr_Integration' ); ?></li>
            </ol>
        </td>
    </tr>
</table>

<script>
    var contactFormNfNoCheckbox = jQuery('#contactFormNfNo');
    var contactFormNfYesCheckbox = jQuery('#contactFormNfYes');

    var ninjaforms_campaign = jQuery('#campaigns_for_ninjaforms_registration_campaign');
    var ninjaforms_responder_status = jQuery('#ninjaforms_registration_campaign_autoresponder_enabled');
    var ninjaforms_responder_id = jQuery('#responders_for_ninjaforms_registration_campaign');

    contactFormNfNoCheckbox.on('click', function() {
        ninjaforms_campaign.prop('selectedIndex',0);
        ninjaforms_responder_id.prop('selectedIndex',0);
        ninjaforms_responder_status.prop('checked', false);

        ninjaforms_campaign.prop('disabled', true);
        ninjaforms_responder_status.prop('disabled', true);
        ninjaforms_responder_id.prop('disabled', true);
    });

    contactFormNfYesCheckbox.on('click', function() {
        ninjaforms_campaign.prop('disabled', false);
        ninjaforms_responder_status.prop('disabled', false);
    });
</script>

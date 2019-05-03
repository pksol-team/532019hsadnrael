<?php

defined( 'ABSPATH' ) || exit;

$reg_checkout_enabled    = (int) gr_get_option( 'registration_checkout_enabled' );
?>

<h2><?php _e( 'Add Contacts upon Registration', 'Gr_Integration' ); ?></h2>
<p class="description gr_description">
    <?php _e( 'Add WordPress visitors to your selected GetResponse contact list upon registration.', 'Gr_Integration' ); ?>
</p>
<table class="form-table">
    <tr>
        <th>
            <label><?php _e( 'Status', 'Gr_Integration' ); ?></label>
        </th>
        <td>
            <label for="registerNo">
                <input id="registerNo" type="radio" name="register_status" <?php if ( 0 === $reg_checkout_enabled ) : ?>checked<?php endif ?> value="disabled">
                Disabled
            </label>
            <label for="registerYes">
                <input id="registerYes" type="radio" name="register_status" <?php if ( 1 === $reg_checkout_enabled ) : ?>checked<?php endif ?> value="enabled">
                Enabled
            </label>
        </td>
    </tr>
    <?php 
        gr_return_campaign_selector(
            'registration_checkout_campaign',
            gr_get_option( 'registration_checkout_campaign' ),
            gr_get_option( 'registration_checkout_autoresponder_enabled' ),
            gr_get_option( 'registration_campaign_autoresponder' ),
	        !$reg_checkout_enabled
        )
    ?>
    <tr>
        <th>
            <label><?php _e( 'Opt-in text', 'Gr_Integration' ); ?></label>
        </th>
        <td>
            <input
                <?php if (0 === $reg_checkout_enabled): ?>disabled="disabled"<?php endif?>
                name="registration_checkout_label"
                id="registration_checkout_label"
                type="text"
                class="regular-text ltr" 
                placeholder="<?php _e( 'Sign up to our newsletter!', 'Gr_Integration' ); ?>"
                value="<?php echo gr_get_option( 'registration_checkout_label', __( 'Sign up to our newsletter!', 'Gr_Integration' ) ) ?>"
            >
        </td>
    </tr>
    <tr>
        <th>
            <label>Opt-in checkbox</label>
        </th>
        <td>
            <label for="registration_checked">
                <input
	                <?php if (0 === $reg_checkout_enabled): ?>disabled="disabled"<?php endif?>
                    id="registration_checked"
                    type="checkbox"
                    name="registration_checked"
                    value="1"
                    <?php if ( gr_get_option( 'registration_checked' ) ) : ?>
                        checked="checked"
                    <?php endif ?>
                >
                <?php _e( 'Enable checkmark by default', 'Gr_Integration' ); ?>
            </label>
        </td>
    </tr>
</table>

<script>
    var registerNoCheckbox = jQuery('#registerNo');
    var registerYesCheckbox = jQuery('#registerYes');

    var register_campaign = jQuery('#campaigns_for_registration_checkout_campaign');
    var responder_status = jQuery('#registration_checkout_campaign_autoresponder_enabled');
    var responder_id = jQuery('#responders_for_registration_checkout_campaign');
    var additional_text = jQuery('#registration_checkout_label');
    var registration_checked = jQuery('#registration_checked');


    registerNoCheckbox.on('click', function() {

        register_campaign.prop('selectedIndex',0);
        responder_status.prop('checked', false);
        responder_id.prop('selectedIndex',0);
        registration_checked.prop('checked', false);

        register_campaign.prop("disabled", true);
        responder_status.prop("disabled", true);
        responder_id.prop("disabled", true);
        additional_text.prop("disabled", true);
        registration_checked.prop("disabled", true);

    });

    registerYesCheckbox.on('click', function() {

        register_campaign.prop("disabled", false);
        responder_status.prop("disabled", false);
        responder_id.prop("disabled", false);
        additional_text.prop("disabled", false);
        registration_checked.prop("disabled", false);

    });  
</script>
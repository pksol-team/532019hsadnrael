<?php

defined( 'ABSPATH' ) || exit;

/**
 * Display Settings form.
 */

$bp_registration_enabled = (int) gr_get_option( 'bp_registration_on' );
?>

<h2><?php _e( 'Add contacts via BuddyPress user registration form', 'Gr_Integration' ); ?></h2>
<p class="description gr_description">
    <?php _e( 'Add new registered and approved BuddyPress members to your GetResponse list when they check the opt-in box.', 'Gr_Integration' ); ?>
</p>
<table class="form-table">
    <tr>
        <th>
            <label><?php _e( 'Status', 'Gr_Integration' ); ?></label>
        </th>
        <td>
            <label for="bpRegisterNo">
                <input id="bpRegisterNo" type="radio" name="buddypress_status" <?php if ( 1 !== $bp_registration_enabled ) : ?>checked<?php endif ?> value="disabled">
                Disabled
            </label>
            <label for="bpRegisterYes">
                <input id="bpRegisterYes" type="radio" name="buddypress_status" <?php if ( 1 === $bp_registration_enabled ) : ?>checked<?php endif ?> value="enabled">
                Enabled
            </label>
        </td>
    </tr>
    <?php gr_return_campaign_selector(
        'bp_registration_campaign',
        gr_get_option( 'bp_registration_campaign' ),
        (int) gr_get_option( 'bp_registration_campaign_autoresponder_status' ),
        gr_get_option( 'bp_registration_campaign_autoresponder' ),
        !$bp_registration_enabled
    ) ?>
    <tr>
        <th>
            <label for="bp_registration_label">
                <?php _e( 'Opt-in text', 'Gr_Integration' ); ?>
            </label>
        </th>
        <td>
            <input
                <?php if (0 === $bp_registration_enabled) :?>disabled<?php endif ?>
                type="text"
                id="bp_registration_label"
                name="bp_registration_label"
                class="regular-text ltr"
                id="bp_registration_label"
                value="<?php echo gr_get_option( 'bp_registration_label',
                    __( 'Sign up to our newsletter!', 'Gr_Integration' ) ) ?>"
            >
        </td>
    </tr>
    <tr>
        <th>
            <label>Opt-in checkbox</label>
        </th>
        <td>
            <label for="bp_registration_checked">
                <input
	                <?php if (0 === $bp_registration_enabled) :?>disabled<?php endif ?>
                    type="checkbox"
                    name="bp_registration_checked"
                    id="bp_registration_checked"
                    value="1"
                <?php if ( '1' == gr_get_option( 'bp_registration_checked' ) ) : ?>
                    checked="checked"
                <?php endif ?> />
                <?php _e( 'Enable checkmark by default', 'Gr_Integration' ); ?>
            </label>
        </td>
    </tr>
</table>

<script>
    var bpRegisterNoCheckbox = jQuery('#bpRegisterNo');
    var bpRegisterYesCheckbox = jQuery('#bpRegisterYes');

    var bp_campaign = jQuery('#campaigns_for_bp_registration_campaign');
    var autoresponder_enabled = jQuery('#bp_registration_campaign_autoresponder_enabled');
    var autoresponder = jQuery('#responders_for_bp_registration_campaign');
    var label = jQuery('#bp_registration_label');
    var is_checked = jQuery('#bp_registration_checked');

    bpRegisterNoCheckbox.on('click', function() {
        var _this = jQuery(this);

        bp_campaign.prop('selectedIndex',0);
        autoresponder_enabled.prop('checked', false);
        autoresponder.prop('selectedIndex',0);
        is_checked.prop('checked', false);

        bp_campaign.prop("disabled", true);
        autoresponder_enabled.prop("disabled", true);
        autoresponder.prop("disabled", true);
        label.prop("disabled", true);
        is_checked.prop("disabled", true);

        if (!_this.is(":not(:checked)")) {
            bpRegisterYesCheckbox.attr('checked', false);
        }
    });

    bpRegisterYesCheckbox.on('click', function() {
        var _this = jQuery(this);

        bp_campaign.prop("disabled", false);
        autoresponder_enabled.prop("disabled", false);
        label.prop("disabled", false);
        is_checked.prop("disabled", false);

        if (!_this.is(":not(:checked)")) {
            bpRegisterNoCheckbox.attr('checked', false);
        }
    });
</script>

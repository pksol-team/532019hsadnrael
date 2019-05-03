<?php

/**
 * Subscribe via comment template.
 */
defined( 'ABSPATH' ) || exit;

$comment_type = (int) gr_get_option( 'comment_checkout_enabled' );
?>
<table class="form-table">
    <tr>
        <th>
            <label><?php _e( 'Status', 'Gr_Integration' ); ?></label>
        </th>
        <td>
            <label for="commentNo">
                <input id="commentNo" type="radio" name="comment_status" <?php if ( 0 === $comment_type ) : ?>checked<?php endif ?> value="disabled">
                Disabled
            </label>
            <label for="commentYes">
                <input id="commentYes" type="radio" name="comment_status" <?php if ( 1 === $comment_type ) : ?>checked<?php endif ?> value="enabled">
                Enabled
            </label>
        </td>
    </tr>
    <?php 
        gr_return_campaign_selector(
            'comment_checkout_campaign',
            gr_get_option( 'comment_checkout_campaign' ),
            (bool) gr_get_option( 'comment_checkout_autoresponder_enabled' ),
            gr_get_option( 'comment_checkout_selected_autoresponder' ),
            !$comment_type
        ) 
    ?>
    <tr>
        <th>
            <label><?php _e( 'Opt-in text', 'Gr_Integration' ); ?></label>
        </th>
        <td>
            <input
                <?php if (0 === $comment_type) :?>disabled<?php endif ?>
                name="comment_checkout_label"
                id="comment_checkout_label"
                type="text"
                class="regular-text ltr"
                placeholder="<?php _e( 'Sign up to our newsletter!', 'Gr_Integration' ); ?>"
                value="<?php echo gr_get_option( 'comment_checkout_label', __( 'Sign up to our newsletter!', 'Gr_Integration' ) ) ?>"
            >
        </td>
    </tr>
    <tr>
        <th>
            <label><?php _e( 'Opt-in checkbox', 'Gr_Integration' ); ?></label>
        </th>
        <td>
            <label for="comment_checked">
                <input
	                <?php if (0 === $comment_type) :?>disabled<?php endif ?>
                    id="comment_checked"
                    class="GR_checkbox"
                    type="checkbox"
                    name="comment_checked"
                    value="1"
                    <?php if ( gr_get_option( 'comment_checked' ) ) : ?>
                        checked="checked"
                    <?php endif ?>
                >
                <?php _e( 'Enable checkmark by default', 'Gr_Integration' ); ?>
            </label>
        </td>
    </tr>
</table>

<script>
    var commentNoCheckbox = jQuery('#commentNo');
    var commentYesCheckbox = jQuery('#commentYes');

    var comment_campaign = jQuery('#campaigns_for_comment_checkout_campaign');
    var comment_responder_status = jQuery('#comment_checkout_campaign_autoresponder_enabled');

    var comment_responder_id = jQuery('#responders_for_comment_checkout_campaign');
    var comment_label = jQuery('#comment_checkout_label');
    var comment_checked = jQuery('#comment_checked');

    commentNoCheckbox.on('click', function() {

        comment_campaign.prop('selectedIndex',0);
        comment_responder_status.prop('checked', false);
        comment_responder_id.prop('selectedIndex',0);
        comment_checked.prop('checked', false);

        comment_campaign.prop("disabled", true);
        comment_responder_status.prop("disabled", true);
        comment_responder_id.prop("disabled", true);
        comment_label.prop("disabled", true);
        comment_checked.prop("disabled", true);
    });

    commentYesCheckbox.on('click', function() {

        comment_campaign.prop("disabled", false);
        comment_responder_status.prop("disabled", false);
        comment_label.prop("disabled", false);
        comment_checked.prop("disabled", false);
    });  
</script>


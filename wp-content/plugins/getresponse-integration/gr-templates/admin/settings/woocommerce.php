<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

try {
    $service = new ShopService(
        ApiFactory::create_api(),
        new GrCache(),
        new ProductsMap(),
        new OrdersMap(),
        new VariantsMap(),
        new WoocommerceService(ApiFactory::create_api())
    );
    $shops   = $service->get_shops();
} catch (ApiException $e) {
    $shops = array();
}

if (count($shops) === 0) {
    gr()->add_error_message('To send ecommerce data, first create a GetResponse store.');
}

$checkout_checked  = (int) gr_get_option( 'woocommerce_checkout_on' );
$ecommerce_checked = (int) gr_get_option( 'woocommerce_ecommerce' );

$schedule_service = new ScheduleJobService(
	new ScheduleJobRepository(),
	new Configuration()
);
$schedule_status  = (bool) $schedule_service->is_schedule_enabled();

try {
	$campaign_service = new CampaignService(ApiFactory::create_api());
	$campaigns = $campaign_service->get_campaigns();
} catch (ApiException $e) {
	$campaigns = array();
}

/**
 * Display Settings form.
 */
?>

<?php gr_load_template( 'admin/settings/header.php' ); ?>

<h2 class="nav-tab-wrapper"><?php echo gr_get_admin_tabs( 'gr-integration-woocommerce' ) ?></h2>

<?php gr_load_template( 'admin/settings/partials/messages.php' ); ?>

<form method="post" action="<?php echo admin_url( 'admin.php?page=gr-integration-woocommerce' ); ?>">

	<?php if ( gr()->is_active_woocommerce_checkout() ): ?>
        <table class="form-table subscription-settings gr_box">
            <tbody>
            <tr>
                <td class="subscription-settings-cell">

                    <table class="form-table">
                        <tr>
                            <th>
                                <label>
									<?php _e( 'Subscribe via Checkout Page (WooCommerce)', 'Gr_Integration' ); ?>
                                </label>
                            </th>
                            <td>
								<?php _e( 'This section is disabled due to GetResponse WooCommerce plugin is active.',
									'Gr_Integration' ); ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            </tbody>
        </table>
	<?php else : ?>
    <table class="form-table subscription-settings gr_box">
        <tbody>
            <tr>
                <td class="subscription-settings-cell">
                    <h2><?php _e( 'Export Customer Data', 'Gr_Integration' ); ?></h2>
                    <p class="description gr_description">
    					<?php _e( 'Export your current customer information from WooCommerce to GetResponse.' ); ?>
                    </p>
                    <table class="form-table">
    					<?php gr_return_campaign_selector( 'campaign_id_to_export',
    						gr_get_value( 'campaign_id_to_export' ) ); ?>
                        <tr>
                            <th>
                                <label>
    								<?php _e( 'Ecommerce data', 'Gr_Integration' ); ?>
                                </label>
                            </th>
                            <td>
                                <label for="export_send_ecommerce_data">
                                    <input
                                        type="checkbox"
                                        name="export_send_ecommerce_data"
                                        value="1"
                                        id="export_send_ecommerce_data"
                                    />
    								<?php _e( 'Include ecommerce data in this export', 'Gr_Integration' ); ?>
                                </label>
                            </td>
                        </tr>

                        <tr id="export_store_select" style="display: none">
                            <th>
                                <label for="ecommerce">
    								<?php _e( 'Store', 'Gr_Integration' ); ?>
                                </label>
                            </th>
                            <td>
    							<?php gr_return_ecommerce_stores_selector( null, 'store_id' ); ?>
                            </td>
                        </tr>

                        <tr>
                            <th>
                                <label>
    				                <?php _e( 'Performance optimization', 'Gr_Integration' ); ?>
                                </label>
                            </th>
                            <td>
                                <label for="use_schedule">
                                    <input
                                            type="checkbox"
                                            name="use_schedule"
                                            id="use_schedule"
                                            value="1"
                                    />
    				                <?php _e( 'Use a time-based job scheduler for this export', 'Gr_Integration' ); ?>
                                </label><br /><br />
                                <small>To use this option, you need to have crontab access and root-level (administrative) access to the server. For help with running scheduled events, please contact our
                                    <a href="https://www.getresponse.com/features/support">Support Team</a></small>
                            </td>
                        </tr>

                        <tr>
                            <th>
                                <label>
    				                <?php _e( 'Contact info', 'Gr_Integration' ); ?>
                                </label>
                            </th>
                            <td>
                                <label for="export_customs">
                                    <input
                                            type="checkbox"
                                            name="export_customs"
                                            id="export_customs"
                                            value="1"
                                    />
    				                <?php _e( 'Update contact info', 'Gr_Integration' ); ?>
                                </label><br /><br />
                                <small>Select this option if you want to overwrite contact details that already exist in your GetResponse database. <br />Clear this option to keep existing data intact</small>

                            </td>
                        </tr>

                        <tr class="" id="customs_to_export" style="display: none;">
                            <th>
                                <label>
    								<?php _e( 'Contact info', 'Gr_Integration' ); ?>
                                </label>
                            </th>
                            <td colspan="2">
                                <div>
                                    <div class="gr-custom-field">
                                        <select
                                                class="jsNarrowSelect"
                                                name="custom_fields_to_export"
                                                id="custom_fields_to_export_select"
                                                multiple="multiple">
    										<?php foreach (WoocommerceService::$billing_fields as $value => $filed ) {
    											$field_name = $filed['name'];
    											echo '<option data-inputvalue="' . $field_name . '" value="' . $value . '" id="' . $filed['value'] . '"', ( $filed['default'] == 'yes' ) ? ' selected="selected"' : '', $filed['default'] == 'yes' ? ' disabled="disabled"' : '', '>', $filed['name'], '</option>';
    										} ?>
                                        </select>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <input
                                        type="submit"
                                        name="Export"
                                        class="button button-primary"
                                        value="<?php _e( 'Export', 'Gr_Integration' ); ?>"/>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="subscription-settings-cell">
                    <h2><?php _e( 'Add Contacts at Checkout', 'Gr_Integration' ); ?></h2>
                    <p class="description gr_description">
    					<?php _e( 'Add WooCommerce customers to your selected GetResponse contact list at checkout.' ); ?>
                    </p>
                    <table class="form-table">
                        <tr>
                            <th>
                                <label><?php _e( 'Status', 'Gr_Integration' ); ?></label>
                            </th>
                            <td>
                                <label for="checkoutNo">
                                    <input id="checkoutNo" type="radio" name="checkout_status"
    								       <?php if ( 1 !== $checkout_checked ) : ?>checked<?php endif ?> value="disabled">
                                    Disabled
                                </label>
                                <label for="checkoutYes">
                                    <input id="checkoutYes" type="radio" name="checkout_status"
    								       <?php if ( 1 === $checkout_checked ) : ?>checked<?php endif ?> value="enabled">
                                    Enabled
                                </label>
                            </td>
                        </tr>
    					<?php gr_return_campaign_selector(
    						'checkout_campaign',
    						gr_get_value( 'checkout_campaign' ),
    						gr_get_option( 'checkout_campaign_autoresponder_status' ),
    						gr_get_option( 'checkout_campaign_autoresponder' ),
    						! $checkout_checked
    					); ?>

                        <tr>
                            <th>
                                <label for="checkout_additional_text">
    								<?php _e( 'Opt-in text', 'Gr_Integration' ); ?>
                                </label>
                            </th>
                            <td>
                                <input
    								<?php if ( 0 === $checkout_checked ): ?>disabled="disabled" <?php endif ?>
                                    type="text"
                                    class="regular-text ltr"
                                    id="checkout_additional_text"
                                    name="checkout_label"
                                    placeholder="<?php _e( 'Sign up to our newsletter!', 'Gr_Integration' ); ?>"
                                    value="<?php echo gr_get_option( 'checkout_label', __( 'Sign up to our newsletter!', 'Gr_Integration' ) ) ?>"
                                />
                            </td>
                        </tr>

                        <tr>
                            <th>
                                <label>
    								<?php _e( 'Opt-in checkbox', 'Gr_Integration' ); ?>
                                </label>
                            </th>
                            <td>
                                <label for="sign_up_by_default">
                                    <input
    									<?php if ( 0 === $checkout_checked ): ?>disabled="disabled" <?php endif ?>
                                        id="sign_up_by_default"
                                        type="checkbox"
                                        name="checkout_checked"
                                        value="1"
    									<?php if ( '1' === gr_get_option( 'checkout_checked' ) ) : ?>
                                            checked="checked"
    									<?php endif ?>
                                    />
    								<?php _e( 'Enable checkmark by default', 'Gr_Integration' ); ?>
                                </label>
                            </td>
                        </tr>

                        <tr>
                            <th>
                                <label>
    								<?php _e( 'Contact info', 'Gr_Integration' ); ?>
                                </label>
                            </th>
                            <td>
                                <label for="sync_order_data">
                                    <input
    									<?php if ( 0 === $checkout_checked ): ?>disabled="disabled" <?php endif ?>
                                        type="checkbox"
                                        name="sync_order_data"
                                        id="sync_order_data"
                                        value="1"
    									<?php if ( '1' === gr_get_option( 'sync_order_data' ) ) : ?>
                                            checked="checked"
    									<?php endif ?>
                                    />
    								<?php _e( 'Update contact info', 'Gr_Integration' ); ?>
                                </label><br /><br />
                                <small>
    								<?php _e( 'Select this option if you want to overwrite contact details that already exist in your GetResponse database.<br /> Clear this option to keep existing data intact',
    									'Gr_Integration' ); ?>
                                </small>

                            </td>
                        </tr>
                        <tr class="hidden" id="customNameFields">
                            <th>
                                <label>
    								<?php _e( 'Update contact info', 'Gr_Integration' ); ?>
                                </label>
                            </th>
                            <td colspan="2">
                                <div>
                                    <div class="gr-custom-field">

                                        <select
                                                class="jsNarrowSelect"
                                                name="checkout_custom_fields"
                                                id="checkout_custom_fields"
                                                multiple="multiple">
    										<?php
    										foreach (WoocommerceService::$billing_fields as $value => $filed ) {
    											$custom     = gr_get_option( $value );
    											$field_name = ( $custom ) ? $custom : $filed['name'];
    											echo '<option data-inputvalue="' . $field_name . '" value="' . $value . '" id="' . $filed['value'] . '"', ( $filed['default'] == 'yes' || $custom ) ? ' selected="selected"' : '', $filed['default'] == 'yes' ? ' disabled="disabled"' : '', '>', $filed['name'], '</option>';
    										} ?>
                                        </select>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td class="subscription-settings-cell">
                    <h2>Send Ecommerce Data to GetResponse</h2>
                    <p class="description gr_description">For contacts in the target list, start sending ecommerce data from your online store to the selected GetResponse store.</p>
                    <table class="form-table">
                        <tr>
                            <th>
                                <label><?php _e( 'Status', 'Gr_Integration' ); ?></label>
                            </th>
                            <td>
                                <label for="ecommerceNo">
                                    <input id="ecommerceNo" type="radio" name="ecommerce_status"
    								       <?php if ( 1 !== $ecommerce_checked ) : ?>checked<?php endif ?> value="disabled">
                                    Disabled
                                </label>
                                <label for="ecommerceYes">
                                    <input id="ecommerceYes" type="radio" name="ecommerce_status"
    								       <?php if ( 1 === $ecommerce_checked ) : ?>checked<?php endif ?> value="enabled">
                                    Enabled
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="woocommerce_ecommerce_store">
    								<?php _e( 'Store', 'Gr_Integration' ); ?>
                                </label>
                            </th>
                            <td>
    							<?php gr_return_ecommerce_stores_selector(
    								gr_get_value( 'woocommerce_ecommerce_store' ),
    								'woocommerce_ecommerce_store',
    								! $ecommerce_checked
    							); ?>
                            </td>
                        </tr>

                        <tr>
                            <th>
                                <label>
    								<?php _e( 'Target list', 'Gr_Integration' ); ?>
                                </label>
                            </th>
                            <td>
                                <select
                                        name="ecommerce_campaign"
                                        id="ecommerce_campaign"
    									<?php if ( 0 === $ecommerce_checked ) : ?>disabled<?php endif ?>
                                >
                                    <option value="">Select a list</option>
    								<?php foreach ( $campaigns as $campaign ): ?>
                                        <option
    										<?php if ( gr_get_option( 'woocommerce_ecommerce_campaign' ) === $campaign['campaignId'] ): ?>selected="selected" <?php endif ?>
                                            value="<?php echo $campaign['campaignId'] ?>"><?php echo $campaign['name'] ?></option>
    								<?php endforeach ?>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <th>
                                <label>
                                    <?php _e( 'Performance optimization', 'Gr_Integration' ); ?>
                                </label>
                            </th>

                            <td>
                                <label for="send_ecommerce_data_disabled">
                                    <input
    									<?php if ( 0 === $ecommerce_checked ) : ?>disabled<?php endif ?>
    									<?php if ( false === $schedule_status ) : ?>checked="checked"<?php endif ?>
                                        type="radio"
                                        name="ecommerce_schedule_status"
                                        value="disabled"
                                        id="send_ecommerce_data_disabled"
                                    />
    								<?php _e( 'Disabled', 'Gr_Integration' ); ?>
                                </label>
                                <label for="send_ecommerce_data_enabled">
                                    <input
    			                        <?php if ( 0 === $ecommerce_checked ) : ?>disabled<?php endif ?>
    			                        <?php if ( true === $schedule_status ) : ?>checked="checked"<?php endif ?>
                                        type="radio"
                                        name="ecommerce_schedule_status"
                                        value="enabled"
                                        id="send_ecommerce_data_enabled"
                                    />
    		                        <?php _e( 'Enabled', 'Gr_Integration' ); ?>
                                </label>
                                <br /><br />
                                <small>To use this option, you need to have crontab access and root-level (administrative) access to the server. For help with running scheduled events, please contact our
                                    <a href="https://www.getresponse.com/features/support">Support Team</a></small>
                            </td>
                        </tr>

                    </table>
                </td>
            </tr>
            <tr>
                <td class="subscription-settings-cell" id="stores">
                    <h2>
                        <?php _e( 'Stores', 'Gr_Integration' ); ?>
                        <button id="addStoreBtn" class="button button-secondary add-store-btn">Add new</button>
                    </h2>
                    <p class="description gr_description">Create a GetResponse store to which ecommerce data will be sent. The store will appear in <strong>Advanced Search</strong>, marketing automation, and the <strong>Ecommerce</strong> module for product recommendations.</p>
                    <br>
                    <table id="storesTable" class="stores striped">
                        <tr>
                            <th>ID</th>
                            <th>Store name</th>
                            <th>Action</th>
                        </tr>

                        <?php foreach ($shops as $id => $shop): ?>
                        <tr>
                            <td><?php echo (++$id) ?></td>
                            <td><?php echo $shop['name'] ?></td>
                            <td>
                                <a onclick="return confirm('Please confirm that you want to delete this store')" href="<?php echo admin_url( 'admin.php?page=gr-integration-woocommerce&action=remove-shop&id=' .  $shop['shopId'] ); ?>">Delete</a>
                            </td>
                        </tr>

                        <?php endforeach ?>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="save_button" colspan="2">
                    <input
                            type="submit"
                            name="WooCommerce"
                            class="button button-primary"
                            value="<?php _e( 'Save Changes', 'Gr_Integration' ); ?>"
                    />
                <td>
            </tr>
		  <?php endif ?>
        </tbody>
    </table>
</form>
<script>
    /**
    *   Add and edit stores
    */

        var addStoreBtn = jQuery('#addStoreBtn');
        var storesTable = jQuery('#storesTable');

        addStoreBtn.on('click', function(event) {
            event.preventDefault();
            var storeForm = document.getElementById("storeForm");

            if (!storeForm) {
                createStoreForm();
            }
        });

        function createStoreForm() {

            var storeFormRow = '<tr><form id="storeForm" action="<?php echo admin_url( 'admin.php?page=gr-integration-ecommerce'); ?>" method="post">' +
                '<td class="center">' +
                    '<?php echo sizeof($shops) + 1 ?>' +
                '</td>' +
                '<td>' +
                    '<input id="store_name" type="text" placeholder="Enter a store name" name="shop_name" />' +
                '</td>' +
                '<td>' +
                    '<input class="button-primary" type="submit" name="add_shop" value="Submit" />' +
                '</td>' +
            '</form></tr>';

            storesTable.append(storeFormRow);
        }


    /**
     *   Handle custom fields mapping
     */

    var customs_to_export = jQuery('#customs_to_export'),
        exportCustomsCheckbox = jQuery('#export_customs'),
        sod = jQuery('#sync_order_data'),
        cfp = jQuery('#customNameFields'),
        export_send_ecommerce_data = jQuery('#export_send_ecommerce_data'),
        export_store_select = jQuery('#export_store_select');

    jQuery(export_send_ecommerce_data).change(function () {
        export_store_select.toggle('slow');
    });

    if (sod.prop('checked') === true) {
        cfp.show();
    }
    sod.change(function () {
        cfp.toggle('slow');
    });

    exportCustomsCheckbox.change(function () {
        customs_to_export.toggle('slow');
    });

    jQuery('.jsNarrowSelect').selectNarrowDown();

    /**
     *   Handle checkout page activation
     */

    var checkoutNoCheckbox = jQuery('#checkoutNo');
    var checkoutYesCheckbox = jQuery('#checkoutYes');

    var co_campaign = jQuery('#campaigns_for_checkout_campaign');
    var co_responder_status = jQuery('#checkout_campaign_autoresponder_enabled');
    var co_responder_id = jQuery('#responders_for_checkout_campaign');
    var co_label = jQuery('#checkout_additional_text');
    var co_customs = jQuery('#sync_order_data');
    var co_active_on_default = jQuery('#sign_up_by_default');


    checkoutNoCheckbox.on('click', function () {

        cfp.hide();
        co_campaign.prop('selectedIndex', 0);
        co_responder_status.prop('checked', false);
        co_responder_id.prop('selectedIndex', 0);
        co_customs.prop('checked', false);
        co_active_on_default.prop('checked', false);

        co_campaign.prop('disabled', true);
        co_responder_status.prop('disabled', true);
        co_responder_id.prop('disabled', true);
        co_label.prop('disabled', true);
        co_customs.prop('disabled', true);
        co_active_on_default.prop('disabled', true);

    });

    checkoutYesCheckbox.on('click', function () {

        co_campaign.prop('disabled', false);
        co_responder_status.prop('disabled', false);
        co_label.prop('disabled', false);
        co_customs.prop('disabled', false);
        co_active_on_default.prop('disabled', false);

    });

    /**
     *   Handle ecommerce activation
     */

    var ecommerceNoCheckbox = jQuery('#ecommerceNo');
    var ecommerceYesCheckbox = jQuery('#ecommerceYes');

    var ecommerce_store = jQuery('#woocommerce_ecommerce_store');
    var ecommerce_campaign = jQuery('#ecommerce_campaign');
    var ecommerce_cron_disabled = jQuery('#send_ecommerce_data_disabled');
    var ecommerce_cron_enabled = jQuery('#send_ecommerce_data_enabled');


    ecommerceNoCheckbox.on('click', function () {

        ecommerce_store.prop('selectedIndex', 0);
        ecommerce_campaign.prop('selectedIndex', 0);
        ecommerce_cron_disabled.prop('checked', false);
        ecommerce_cron_enabled.prop('checked', false);

        ecommerce_store.prop('disabled', true);
        ecommerce_campaign.prop('disabled', true);
        ecommerce_cron_enabled.prop('disabled', true);
        ecommerce_cron_disabled.prop('disabled', true);

    });

    ecommerceYesCheckbox.on('click', function () {
        console.log('click');
        ecommerce_store.prop('disabled', false);
        ecommerce_campaign.prop('disabled', false);
        ecommerce_cron_disabled.prop('disabled', false);
        ecommerce_cron_enabled.prop('disabled', false);
        ecommerce_cron_disabled.prop('checked', true);
    });

</script>

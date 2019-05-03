<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

$service = new TrackingCodeService(ApiFactory::create_api());
$status = $service->get_status();

try {
	$is_feature_enabled = $service->get_feature_tracking_status();
} catch (ApiException $e) {
	$is_feature_enabled = false;
}

/**
 * Display Ecommerce page.
 */
?>

<?php gr_load_template( 'admin/settings/header.php' ); ?>

<h2 class="nav-tab-wrapper"><?php echo gr_get_admin_tabs( 'gr-integration-tracking-code' ) ?></h2>

<?php if (false === $is_feature_enabled): ?>

<table class="form-table subscription-settings gr_box">
    <tbody>
    <tr>
        <td class="subscription-settings-cell">
            <h2><?php _e( 'Web Event Tracking', 'Gr_Integration' ); ?></h2>
            <p class="description gr_description">
                <a href="https://secure.getresponse.com/upgrade-pro" target="_blank">Upgrade</a> to a Max or Pro account in GetResponse to be able to use Web Event Tacking. Once you’ve upgraded, return to this tab and check the box to start tracking the URLs of the pages your customers visit.
            </p>
        </td>
    </tr>
    </tbody>
</table>
<?php else: ?>

<?php gr_load_template( 'admin/settings/partials/messages.php' ); ?>

<form method="post" action="<?php echo admin_url( 'admin.php?page=gr-integration-tracking-code' ); ?>">
    <table class="form-table subscription-settings gr_box">
        <tbody>
            <tr>
                <td class="subscription-settings-cell">
                    <h2><?php _e( 'Web Event Tracking', 'Gr_Integration' ); ?></h2>
                    <p class="description gr_description">
                        <?php _e( 'Track customer activity on your website to plan a customer journey based on the visits to your site.', 'Gr_Integration' ); ?>
                    </p>
                    <table class="form-table">
                        <tr>
                            <td>
                                <label for="tracking_code">
                                    <input
		                                <?php if ($status): ?> checked="checked"<?php endif ?>
                                            type="checkbox"
                                            name="tracking_code"
                                            value="1"
                                            id="tracking_code"/>

                                    <?php _e( 'Send web event data to GetResponse', 'Gr_Integration' ); ?>
                                </label>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="save_button">
                    <input
                            name="save_tracking_code"
                            type="submit"
                            value="<?php _e( 'Save Changes', 'Gr_Integration' ); ?>"
                            class="button button-primary"
                    />
                </td>
            </tr>
        </tbody>
    </table>
</form>
<?php endif ?>

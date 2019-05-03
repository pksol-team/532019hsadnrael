<?php

use Getresponse\WordPress\ApiException;
use Getresponse\WordPress\ApiFactory;
use Getresponse\WordPress\AutoresponderService;
use Getresponse\WordPress\CampaignService;
use Getresponse\WordPress\GrCache;
use Getresponse\WordPress\OrdersMap;
use Getresponse\WordPress\ProductsMap;
use Getresponse\WordPress\ShopService;
use Getresponse\WordPress\VariantsMap;
use Getresponse\WordPress\WoocommerceService;

defined( 'ABSPATH' ) || exit;

/**
 * @param $current_store
 * @param $name
 * @param bool $disabled
 */
function gr_return_ecommerce_stores_selector( $current_store, $name, $disabled = false ) {

    try {
	    $ecommerce = new ShopService(
            ApiFactory::create_api(),
            new GrCache(),
            new ProductsMap(),
            new OrdersMap(),
            new VariantsMap(),
            new WoocommerceService(ApiFactory::create_api())
        );
	    $stores = $ecommerce->get_shops();
    } catch (ApiException $e) {
        $stores = array();
    }

	if (empty($stores)) {
		_e( 'To send ecommerce data, first create a GetResponse store in the <a href="#stores">Stores section</a>', 'Gr_Integration' );

		return;
	}

	?>
    <select name="<?php echo $name; ?>" id="<?php echo $name; ?>" <?php if ($disabled) :?>disabled<?php endif ?>>
    	<option value="" disabled selected>Select a store</option>
		<?php foreach ( $stores as $store ) {
			echo '<option value="' . $store['shopId'] . '" id="' . $store['shopId'] . '"', $current_store == $store['shopId'] ? ' selected="selected"' : '', '>', $store['name'], '</option>';
		}
		?>
    </select>
<?php }

/**
 * Return campaign selector.
 *
 * @param string $name
 * @param string $selected_option
 * @param bool $is_autoresponder_status
 * @param string $current_autoresponder_id
 * @param bool $disabled
 */
function gr_return_campaign_selector(
	$name,
	$selected_option,
	$is_autoresponder_status = false,
	$current_autoresponder_id = '',
    $disabled = false
) {
	$api = ApiFactory::create_api();

	try {
		$campaign_service = new CampaignService( $api );
		$autoresponder_service = new AutoresponderService( $api );

		$campaigns  = $campaign_service->get_campaigns();
		$responders = $autoresponder_service->get_autoresponders();

	} catch (ApiException $e) {
		$campaigns  = array();
		$responders = array();
	}

	if ( empty( $campaigns ) ) {
		_e( 'No Campaigns.', 'Gr_Integration' );

		return;
	}
	?>

    <tr>
        <th>
            <label for="<?php echo $name ?>_campaigns">
				<?php _e( 'Contact list', 'Gr_Integration' ); ?>
            </label>
        </th>
        <td>
            <select
	            <?php if ($disabled) :?>disabled<?php endif ?>
                    name="<?php echo $name ?>"
                    id="campaigns_for_<?php echo $name ?>"
                    class="campaign-select"
                    data-selected="<?php echo $selected_option ?>">

                <option disabled selected value="">Select a list</option>
            </select>
        </td>
    </tr>
    <tr>
        <th>
            <label for="<?php echo $name ?>_autoresponder">
				<?php _e( 'Autoresponder day', 'Gr_Integration' ); ?>
            </label>
        </th>
        <td>
            <label for="<?php echo $name ?>_autoresponder_enabled">
                <input
	                <?php if ($disabled) :?>disabled<?php endif ?>
                    type="checkbox"
                    id="<?php echo $name ?>_autoresponder_enabled"
                    name="<?php echo $name ?>_autoresponder_enabled"
					<?php if ( $is_autoresponder_status ): ?>checked="checked"<?php endif ?>
                />
                Add to autoresponder cycle
            </label>
        </td>
    </tr>
    <tr>
    	<th></th>
        <td>
            <select
				<?php if ( $disabled || false === $is_autoresponder_status ): ?>disabled="disabled"<?php endif ?>
                name="<?php echo $name ?>_selected_autoresponder"
                id="responders_for_<?php echo $name ?>"
                data-selected="<?php echo $current_autoresponder_id ?>">
            </select>
        </td>
    </tr>

    <script type="text/javascript">
        var campaigns_json = <?php echo json_encode( $campaigns ) ?>;
        var responders_json = <?php echo json_encode( $responders ) ?>;

        var campaigns = new Campaigns();
        var auto_responders = new AutoResponders();

        campaigns.load_campaigns('campaigns_for_<?php echo $name?>', campaigns_json);
        auto_responders.load_responders('responders_for_<?php echo $name ?>', responders_json);

        document.getElementById('campaigns_for_<?php echo $name?>').addEventListener('change', function () {
            auto_responders.load_responders('responders_for_<?php echo $name ?>', responders_json);
        });

        document.getElementById('<?php echo $name ?>_autoresponder_enabled').addEventListener('click', function () {

            if (this.checked) {
                document.getElementById('responders_for_<?php echo $name?>').removeAttribute('disabled');
            } else {
                document.getElementById('responders_for_<?php echo $name?>').setAttribute('disabled', 'disabled');
            }
        });
    </script>
<?php }

/**
 * Get WP details list.
 */
function gr_get_wp_details_list() {
	echo "Version : " . get_bloginfo( 'version' ) . "\n";
	echo "Charset : " . get_bloginfo( 'charset' ) . "\n";
	echo "Url : " . get_bloginfo( 'url' ) . "\n";
	echo "Language : " . get_bloginfo( 'language' ) . "\n";
	echo "PHP : " . phpversion() . "\n";
}

/**
 * Return list of active plugins
 */
function gr_get_active_plugins_list() {
	echo "\nActive plugins:\n\n";
	foreach ( get_plugins() as $plugin_name => $plugin_details ) {
		if ( is_plugin_active( $plugin_name ) === true ) {
			foreach ( $plugin_details as $details_key => $details_value ) {
				if ( in_array( $details_key, array( 'Name', 'Version', 'PluginURI' ) ) ) {
					echo $details_key . " : " . $details_value . "\n";
				}
			}
			echo "Path : " . $plugin_name . "\n";
            echo "-----\n";
		}
	}
}

/**
 * Return list of active plugins
 */
function gr_get_gr_plugin_details_list() {
	echo "Getresponse-integration details:\n";
	$details = gr()->db->get_gr_plugin_details();
	if ( empty( $details ) ) {
		return;
	}

	foreach ( $details as $detail ) {
		echo str_replace( gr_prefix(), '',
				$detail->option_name ) . " : " . $detail->option_value . "\n";
	}
}

/**
 * Return active widgets
 */
function gr_get_widgets_list() {
	echo "Widgets:\n";
	$widgets = get_option( 'sidebars_widgets' );
	echo serialize( $widgets );
}

/**
 * Display GetResponse blog 10 RSS links
 */
function gr_rss_feeds() {

	$lang     = get_bloginfo( "language" ) == 'pl-PL' ? 'pl' : 'com';
	$feed_url = 'http://blog.getresponse.' . $lang . '/feed';

	$num = 10; // numbers of feeds:
	include_once( ABSPATH . WPINC . '/feed.php' );
	$rss = fetch_feed( $feed_url );

	if ( is_wp_error( $rss ) ) {
		_e( 'No rss items, feed might be broken.', 'Gr_Integration' );
	} else {
		$rss_items = $rss->get_items( 0, $rss->get_item_quantity( $num ) );

		// If the feed was erroneously
		if ( ! $rss_items ) {
			$md5 = md5( $feed_url );
			delete_transient( 'feed_' . $md5 );
			delete_transient( 'feed_mod_' . $md5 );
			$rss       = fetch_feed( $feed_url );
			$rss_items = $rss->get_items( 0, $rss->get_item_quantity( $num ) );
		}

		$content = '';
		if ( ! $rss_items ) {
			$content .= '<p>' . _e( 'No rss items, feed might be broken.',
					'Gr_Integration' ) . '</p>';
		} else {
			foreach ( $rss_items as $item ) {
				$url     = preg_replace( '/#.*/', '',
					esc_url( $item->get_permalink(), $protocolls = null, 'display' ) );
				$content .= '<p>';
				$content .= '<a class="GR_rss_a" href="' . $url . '" target="_blank">' . esc_html( $item->get_title() ) . '</a> ';
				$content .= '</p>';
			}
		}
		$content .= '';
		echo $content;
	}
}

/**
 * Load tabs.
 *
 * * @param string $active
 *
 * @return mixed
 */
function gr_get_admin_tabs( $active = 'gr-integration' ) {
	return gr()->gr_core_admin->get_admin_tabs( $active );
}

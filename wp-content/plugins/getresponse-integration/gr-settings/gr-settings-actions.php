<?php

use Getresponse\WordPress\ApiException;

defined( 'ABSPATH' ) || exit;

add_action( 'gr_settings_run', 'gr_update_api_key' );
add_action( 'gr_settings_run', 'gr_update_common_settings' );
add_action( 'gr_settings_run', 'gr_load_campaigns' );

add_action( 'wp_ajax_gr-traceroute-submit', 'gr_traceroute_ajax_request' );
add_action( 'wp_ajax_gr-variants-submit', 'gr_variants_ajax_request' );
add_action( 'wp_ajax_gr-forms-submit', 'gr_forms_ajax_request' );
add_action( 'wp_ajax_gr-webforms-submit', 'gr_webforms_ajax_request' );

add_action( 'admin_head', 'gr_js_shortcodes' );

add_action( 'init', 'gr_buttons' );

add_filter( 'admin_footer_text', 'gr_admin_footer_text' );

/**
 * Update API Key settings.
 */
function gr_update_api_key() {

	if ( isset( $_POST['api_key'] ) && empty( $_POST['api_key'] ) ) {
		gr()->add_error_message( 'You need to enter the API key. This field can\'t be empty.' );

		return;
	}

	$api_key = gr_post( 'api_key' );

	if ( null === $api_key ) {
		return;
	}

	$is_mx = gr_post( 'getresponse_360_account' );

	if ( null !== $is_mx ) {
		$api_url    = gr_get_value( 'accountType' );
		$api_domain = gr_get_value( 'domain' );

		if ( 0 === strlen( $api_url ) ) {
			gr()->add_error_message( 'API URL cannot be empty.' );

			return;
		}

		if ( 0 === strlen( $api_domain ) ) {
			gr()->add_error_message( 'Domain cannot be empty.' );

			return;
		}

		if ( ! empty( $api_domain ) ) {
			$url_data             = parse_url( $api_url );
			gr()->traceroute_host = $url_data['host'];
		}
	} else {

		$api_url    = null;
		$api_domain = null;

		gr_delete_option( 'api_url' );
		gr_delete_option( 'api_domain' );
	}

	try {
		$api_service = new \Getresponse\WordPress\ApiService();
		$api_service->connect( $api_key, $api_url, $api_domain, $is_mx );

		gr()->add_success_message( __( 'GetResponse account connected', 'Gr_Integration' ) );

	} catch ( ApiException $e ) {
		gr()->add_error_message( __( $e->getMessage(), 'Gr_Integration' ) );
	} catch ( Exception $e ) {
		gr()->add_error_message( __( $e->getMessage(), 'Gr_Integration' ) );
	}
}

/**
 * Comment campaign send through form.
 */
function gr_update_common_settings() {

	if ( 'Save Changes' !== gr_post( 'commentSubmit' ) ) {
		return;
	}

	$comment_checkout_responder_enabled = $comment_selected_responder = null;
	$comment_campaign         = gr_post( 'comment_checkout_campaign' );
	$comment_checkout_enabled = 'enabled' === gr_post( 'comment_status' ) ? 1 : 0;

	if ( $comment_checkout_enabled && empty( $comment_campaign ) ) {
		gr()->add_error_message( 'You need to select a contact list' );

		return;
	}

	gr_update_option( 'comment_checkout_enabled', $comment_checkout_enabled );

	if ( $comment_checkout_enabled ) {
		$comment_label = gr_post( 'comment_checkout_label' );

		if ( !empty( $comment_label ) ) {
			gr_update_option( 'comment_checkout_label', $comment_label );
		}

		$comment_selected_responder         = gr_post( 'comment_checkout_campaign_selected_autoresponder' );
		$comment_checkout_responder_enabled = 'on' === gr_post( 'comment_checkout_campaign_autoresponder_enabled' ) ? 1 : 0;
	}

	gr_update_option( 'comment_checkout_campaign', $comment_campaign );
	gr_update_option( 'comment_checkout_autoresponder_enabled', $comment_checkout_responder_enabled );

	gr_update_option( 'comment_checkout_selected_autoresponder', $comment_selected_responder );

	$comment_checked = null !== gr_post( 'comment_checked' ) ? 1 : 0;

	gr_update_option( 'comment_checked', $comment_checked );

	if ( gr()->buddypress->is_active() ) {

		$registration_enabled = 'enabled' === gr_post( 'buddypress_status' ) ? 1 : 0;
		$campaign = gr_post( 'bp_registration_campaign' );

		if ( $registration_enabled && empty( $campaign ) ) {
			gr()->add_error_message( 'You need to select a contact list' );

			return;
		}

		gr_update_option( 'bp_registration_on', $registration_enabled );

		$autoresponder_status = $autoresponder_id = $is_checked = null;

		if ( 1 === $registration_enabled ) {

			$label    = gr_post( 'bp_registration_label' );

			$autoresponder_status = 'on' === gr_post( 'bp_registration_campaign_autoresponder_enabled' ) ? 1 : 0;
			$autoresponder_id     = gr_post( 'bp_registration_campaign_selected_autoresponder' );
			$is_checked           = gr_post( 'bp_registration_checked' );

			if ( !empty( $label ) ) {
				gr_update_option( 'bp_registration_label', $label );
			}
		}

		gr_update_option( 'bp_registration_campaign', $campaign );
		gr_update_option( 'bp_registration_campaign_autoresponder_status', $autoresponder_status );
		gr_update_option( 'bp_registration_campaign_autoresponder', $autoresponder_id );
		gr_update_option( 'bp_registration_checked', $is_checked );

	} else {

		$registration_checkout_enabled               = 'enabled' === gr_post( 'register_status' ) ? 1 : 0;
		$registration_checkout_autoresponder_enabled = 'on' === gr_post( 'registration_checkout_campaign_autoresponder_enabled' ) ? 1 : 0;

		$registration_checkout_campaign = gr_post( 'registration_checkout_campaign' );
		$registration_checkout_label = gr_post( 'registration_checkout_label' );

		if ( $registration_checkout_enabled && empty( $registration_checkout_campaign ) ) {
			gr()->add_error_message( 'You need to select a contact list' );

			return;
		}

		if ($registration_checkout_enabled) {

		    if (!empty($registration_checkout_label)) {
			    gr_update_option( 'registration_checkout_label', $registration_checkout_label );
            }
        }

		gr_update_option( 'registration_checkout_enabled', $registration_checkout_enabled );
		gr_update_option( 'registration_checkout_autoresponder_enabled', $registration_checkout_autoresponder_enabled );

		$register_checked = null !== gr_post( 'registration_checked' ) ? 1 : 0;

		gr_update_option( 'registration_checkout_campaign', $registration_checkout_campaign );
		gr_update_option( 'registration_campaign_autoresponder',
        gr_post( 'registration_checkout_campaign_selected_autoresponder' ) );
		gr_update_option( 'registration_checked', $register_checked );

	}

	if ( gr()->contactForm7->is_active() ) {

		$cf7_status   = 'enabled' === gr_post( 'contactFormStatus' ) ? 1 : 0;
		$cf7_campaign = gr_post( 'cf7_registration_campaign' );

		if ( $cf7_status && empty( $cf7_campaign ) ) {
			gr()->add_error_message( 'You need to select a contact list' );

			return;
		}

		gr_update_option( 'cf7_registration_on', $cf7_status );

		$cf7_autoresponder_status = $cf7_autoresponder_id = null;

		if ( $cf7_status ) {
			$cf7_autoresponder_id     = gr_post( 'cf7_registration_campaign_selected_autoresponder' );
			$cf7_autoresponder_status = 'on' === gr_post( 'cf7_registration_campaign_autoresponder_enabled' ) ? 1 : 0;
		}

		gr_update_option( 'cf7_registration_campaign', $cf7_campaign );
		gr_update_option( 'cf7_registration_campaign_autoresponder', $cf7_autoresponder_id );
		gr_update_option( 'cf7_registration_campaign_autoresponder_status', $cf7_autoresponder_status );
	}

    if ( gr()->ninjaForms->is_active() ) {

        $ninjaforms_status   = 'enabled' === gr_post( 'contactNinjaFormStatus' ) ? 1 : 0;
        $ninjaforms_campaign = gr_post( 'ninjaforms_registration_campaign' );

        if ( $ninjaforms_status && empty( $ninjaforms_campaign ) ) {
            gr()->add_error_message( 'You need to select a contact list' );

            return;
        }

        gr_update_option( 'ninjaforms_registration_on', $ninjaforms_status );

        $ninjaforms_autoresponder_status = $ninjaforms_autoresponder_id = null;

        if ( $ninjaforms_status ) {
            $ninjaforms_autoresponder_id     = gr_post( 'ninjaforms_registration_campaign_selected_autoresponder' );
            $ninjaforms_autoresponder_status = 'on' === gr_post( 'ninjaforms_registration_campaign_autoresponder_enabled' ) ? 1 : 0;
        }

        gr_update_option( 'ninjaforms_registration_campaign', $ninjaforms_campaign );
        gr_update_option( 'ninjaforms_registration_campaign_autoresponder', $ninjaforms_autoresponder_id );
        gr_update_option( 'ninjaforms_registration_campaign_autoresponder_status', $ninjaforms_autoresponder_status );
    }

	gr()->add_success_message( __( 'Settings saved', 'Gr_Integration' ) );


}

/**
 * GetResponse MCE buttons
 */
function gr_buttons() {
	add_filter( 'mce_buttons', 'gr_register_buttons' );
	add_filter( 'mce_external_plugins', 'gr_add_buttons' );
}

/**
 * Register buttons.
 *
 * @param array $buttons buttons.
 *
 * @return array
 */
function gr_register_buttons( $buttons ) {
	array_push(
		$buttons,
		'separator',
		'GrShortcodes'
	);

	return $buttons;
}

/**
 * Add buttons.
 *
 * @param array $plugin_array plugins.
 *
 * @return array
 */
function gr_add_buttons( $plugin_array ) {
	global $wp_version;

	$url = gr()->asset_path . '/js/gr-plugin_3_8.js?v' . gr()->settings->js_plugin_version;

	if ( $wp_version >= 3.9 ) {
		$url = gr()->asset_path . '/js/gr-plugin.js?v' . gr()->settings->js_plugin_version;
	}

	$plugin_array['GrShortcodes'] = $url;

	return $plugin_array;
}


/**
 * Add js variables.
 */
function gr_js_shortcodes() {

	$allowedPages = array( 'post.php', 'post-new.php' );
	global $pagenow;

	if (false === in_array($pagenow, $allowedPages )) {
		return;
	}

	$api = \Getresponse\WordPress\ApiFactory::create_api();

	if (empty($api)) {
		return;
	}

	$api_key = 'true';
	$service = new \Getresponse\WordPress\WebformService( $api );
	$campaign_service = new \Getresponse\WordPress\CampaignService( $api );

	try {
		$old_forms = $service->get_old_forms();
		$new_forms = $service->get_new_forms();
		$campaigns = $campaign_service->get_campaigns();
	} catch ( ApiException $e ) {
		$old_forms = $new_forms = $campaigns = array();
	}

	$old_forms = json_encode( $old_forms );
	$new_forms = json_encode( $new_forms );
	$campaigns = json_encode( $campaigns ); // for 3.8 version
	?>
    <script type="text/javascript">
        var my_webforms = <?php echo $old_forms; ?>;
        var my_forms = <?php echo $new_forms; ?>;
        var my_campaigns = <?php echo $campaigns;  // for 3.8 version ?>;
        var text_forms = '<?php echo __( 'New Forms', 'Gr_Integration' ); ?>';
        var text_webforms = '<?php echo __( 'Old Web Forms', 'Gr_Integration' ); ?>';
        var text_no_forms = '<?php echo __( 'No Forms', 'Gr_Integration' ); ?>';
        var text_no_webforms = '<?php echo __( 'No Web Forms', 'Gr_Integration' ); ?>';
        var api_key = <?php echo $api_key; ?>;
    </script>
	<?php
}

/**
 * Load client Campaigns.
 */
function gr_load_campaigns() {

	if ( null === gr_get_option( 'api_key' ) ) {
		return;
	}
}


/**
 * GR Traceroute Ajax request.
 */
function gr_traceroute_ajax_request() {
	$response = '';
	if ( preg_match( '/^win.*/i', PHP_OS ) ) {
		exec( 'tracert ' . gr()->traceroute_host, $out, $code );
	} else {
		exec( 'traceroute -m15 ' . gr()->traceroute_host . ' 2>&1', $out, $code );
	}

	if ( $code && is_array( $out ) ) {
		$response = __( 'An error occurred while trying to traceroute',
				'Gr_Integration' ) . ': <br />' . join( "\n", $out );
	}

	if ( ! empty( $out ) ) {
		foreach ( $out as $line ) {
			$response .= $line . "<br/>";
		}
	}

	$response = wp_json_encode( array( 'success' => $response ) );
	header( 'Content-Type: application/json' );
	echo $response;
	exit;
}

/**
 * GR Variants Ajax request.
 */
function gr_variants_ajax_request() {

	$api = \Getresponse\WordPress\ApiFactory::create_api();

	if ( empty( $api ) ) {
		return;
	}

	$service  = new \Getresponse\WordPress\WebformService($api);
	$response = json_encode( array( 'error' => 'No variants' ) );

	if ( null !== gr_get( 'form_id' ) ) {

		try {
			$variants = $service->get_web_form_variants( gr_get( 'form_id' ) );
		} catch (ApiException $e ) {
			$variants = array();
		}
		if ( ! empty( $variants ) ) {
			$response = json_encode( array( 'success' => $variants ) );
		}
	}

	header( 'Content-Type: application/json' );
	echo $response;
	exit;
}

/**
 * GR Forms Ajax request.
 */
function gr_forms_ajax_request() {

	$api = \Getresponse\WordPress\ApiFactory::create_api();

	if ( empty( $api ) ) {
		return;
	}

	try {
		$service = new \Getresponse\WordPress\WebformService( $api );
		$forms   = $service->get_new_forms();
	} catch ( ApiException $e ) {
		$forms = array();
	}
	$response = json_encode( array( 'success' => $forms ) );

	header( 'Content-Type: application/json' );
	echo $response;
	exit;
}

/**
 * GR Webforms Ajax request.
 */
function gr_webforms_ajax_request() {

	$api = \Getresponse\WordPress\ApiFactory::create_api();

	if ( empty( $api ) ) {
		return;
	}

	try {
		$service = new \Getresponse\WordPress\WebformService($api);
		$forms   = $service->get_old_forms();
	} catch ( ApiException $e ) {
		$forms = array();
	}
	$response = json_encode( array( 'success' => $forms ) );

	header( 'Content-Type: application/json' );
	echo $response;
	exit;
}

/**
 * @param string $footer_text
 *
 * @return mixed
 */
function gr_admin_footer_text( $footer_text ) {

	$pages = array(
		'getresponse_page_gr-integration-status',
		'getresponse_page_gr-integration-subscription-settings',
		'getresponse_page_gr-integration-web-form',
		'getresponse_page_gr-integration-buddypress',
		'getresponse_page_gr-integration-woocommerce',
		'getresponse_page_gr-integration-help',
		'getresponse_page_gr-integration-ecommerce',
		'getresponse_page_gr-integration-tracking-code',
		'getresponse_page_gr-integration-error'
	);

	$current_screen = get_current_screen();

	if ( false === in_array( $current_screen->id, $pages ) ) {
		return $footer_text;
	}

	gr_load_template( 'admin/settings/footer.php' );

	return '';
}

<?php

defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', 'gr_admin_settings_page' );
add_action( 'admin_init', 'gr_admin_init' );
/**
 * Register admin init hook.
 */
function gr_admin_init() {
	register_setting( 'gr-integration', 'gr-options-name' );
}

/**
 * Register admin settings page.
 */
function gr_admin_settings_page() {

	$position = null;
	$icon_url = gr()->asset_path . '/img/menu_icon.png';

	add_menu_page(
		__( gr()->plugin_name, 'Gr_Integration' ),
		__( gr()->plugin_name, 'Gr_Integration' ),
		'manage_options',
		'gr-integration',
		'gr_create_status_page',
		$icon_url,
		$position
	);

	add_submenu_page(
		'gr-integration',        // parent slug, same as above menu slug
		'',        // empty page title
		'',        // empty menu title
		'manage_options',        // same capability as above
		'gr-integration',        // same menu slug as parent slug
		'gr_create_status_page'      // same function as above
	);

	remove_submenu_page( 'gr-integration', 'gr-integration' );

	// Status page.
	add_submenu_page(
		'gr-integration',
		__( 'Account', 'Gr_Integration' ),
		__( 'Account', 'Gr_Integration' ),
		'manage_options',
		'gr-integration-status',
		'gr_create_status_page'
	);

	if ( gr()->is_connected_to_getresponse() && gr()->check_requirements() ) {

		// Add Common Settings page.
		add_submenu_page(
			'gr-integration',
			__( 'Adding Contacts', 'Gr_Integration' ),
			__( 'Adding Contacts', 'Gr_Integration' ),
			'manage_options',
			'gr-integration-subscription-settings',
			'gr_create_subscription_settings_page'
		);

		if ( gr()->is_woocommerce_plugin_active() ) {

			// Add WooCommerce page.
			add_submenu_page(
				'gr-integration',
				__( 'WooCommerce', 'Gr_Integration' ),
				__( 'WooCommerce', 'Gr_Integration' ),
				'manage_options',
				'gr-integration-woocommerce',
				'gr_create_woocommerce_page'
			);
		}

		// E-commerce page.
		add_submenu_page(
			'gr-integration',
			__( 'Web Event Tracking', 'Gr_Integration' ),
			__( 'Web Event Tracking', 'Gr_Integration' ),
			'manage_options',
			'gr-integration-tracking-code',
			'gr_create_tracking_code_page'
		);

		// Landing Pages page.
		add_submenu_page(
			'gr-integration',
			__( 'Landing Pages', 'Gr_Integration' ),
			__( 'Landing Pages', 'Gr_Integration' ),
			'manage_options',
			'gr-integration-landing-pages',
			'gr_create_landing_pages_page'
		);
	}

	// Help page.
	add_submenu_page(
		'gr-integration',
		__( 'Help', 'Gr_Integration' ),
		__( 'Help', 'Gr_Integration' ),
		'manage_options',
		'gr-integration-help',
		'gr_create_help_page'
	);

	add_submenu_page(
		'gr-integration',        // parent slug, same as above menu slug
		'',        // empty page title
		'',        // empty menu title
		'manage_options',        // same capability as above
		'gr-integration-error',        // same menu slug as parent slug
		'gr_create_error_page'      // same function as above
	);

// Enqueue CSS.
	wp_enqueue_style( 'GrStyle' );
	wp_enqueue_style( 'GrCustomsStyle' );

// Enqueue JS.
	wp_enqueue_script( 'GrCustomsJs' );
	wp_enqueue_script( 'GrScript', gr()->asset_path . '/js/gr-script.js' );

// Detect adblock.
	wp_register_script( 'GrAdsJs', gr()->asset_path . '/js/ads.js' );
	wp_enqueue_script( 'GrAdsJs' );

// run main settings action.
	do_action( 'gr_settings_run' );
}


/**
 * @param string|null $error
 */
function gr_create_error_page( $error = null ) {
	gr_load_template( 'admin/settings/error-page.php', array( 'error' => $error ) );
}

/**
 * Load WooCommerce page.
 */
function gr_create_woocommerce_page() {
	gr_load_template( 'admin/settings/woocommerce.php' );
}

/**
 * Load BuddyPress page.
 */
function gr_create_buddypress_page() {
	gr_load_template( 'admin/settings/buddypress.php' );
}

/**
 * Load ContactForm7 page.
 */
function gr_create_contact_form_7_page() {
	gr_load_template( 'admin/settings/contact_form_7.php' );
}

/**
 * Load help page.
 */
function gr_create_help_page() {
	gr_load_template( 'admin/settings/help.php' );
}

/**
 * Load help page.
 */
function gr_create_tracking_code_page() {
	gr_load_template( 'admin/settings/tracking_code.php' );
}

/**
 * Load Landing Pages page.
 */
function gr_create_landing_pages_page() {
    gr_load_template( 'admin/settings/landing_pages.php' );
}


/**
 * Load help page.
 */
function gr_create_ecommerce_page() {
	gr_load_template( 'admin/settings/ecommerce.php' );
}

/**
 * Load Common Settings template.
 */
function gr_create_subscription_settings_page() {
	gr_load_template( 'admin/settings/subscription_settings.php' );
}

/**
 * Load Web Form template.
 */
function gr_create_webform_page() {
	gr_load_template( 'admin/settings/webform.php' );
}

/**
 * Create settings page in admin section.
 */
function gr_create_settings_page() {
	gr_load_template( 'admin/settings/common_api_key.php' );
}

/**
 * Load status page.
 */
function gr_create_status_page() {
	if ( 'disconnect' === gr_get_value( 'action' ) ) {;
		gr()->disconnect_integration();
		gr()->add_success_message( __( 'GetResponse account disconnected', 'Gr_Integration' ) );
	}

	gr()->db_validator->validate();

	if ( null !== gr_get_option( 'api_key' ) ) {
		gr_load_template( 'admin/settings/status.php' );
	} else {
        gr_load_template( 'admin/settings/common_no_api_key.php' );
	}
}

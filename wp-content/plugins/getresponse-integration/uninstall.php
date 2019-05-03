<?php
// Plugin uninstall tasks
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	return;
}

wp_clear_scheduled_hook( 'getresponse_jobs' );

require 'gr-loader.php';

global $wpdb;

foreach ( gr()->db->plugin_tables as $table ) {
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . $table );
}

if ( false === function_exists( 'gr_delete_option' ) ) {
    return;
}

gr_delete_option( 'api_key' );
gr_delete_option( 'widget' );
gr_delete_option( 'comment_checkout_campaign' );
gr_delete_option( 'comment_checkout_enabled' );
gr_delete_option( 'comment_label' );
gr_delete_option( 'comment_checked' );
gr_delete_option( 'registration_checkout_campaign' );
gr_delete_option( 'registration_checkout_enabled' );
gr_delete_option( 'registration_label' );
gr_delete_option( 'registration_checked' );
gr_delete_option( 'checkout_campaign' );
gr_delete_option( 'woocommerce_checkout_on' );
gr_delete_option( 'checkout_label' );
gr_delete_option( 'checkout_checked' );
gr_delete_option( 'sync_order_data' );
gr_delete_option( 'bp_registration_campaign' );
gr_delete_option( 'bp_registration_checked' );
gr_delete_option( 'bp_registration_on' );
gr_delete_option( 'bp_registration_label' );
gr_delete_option( 'fields_prefix' );
gr_delete_option( 'getresponse_360_account' );
gr_delete_option( 'api_url' );
gr_delete_option( 'api_domain' );
gr_delete_option( 'web_forms' );

wp_cache_flush();

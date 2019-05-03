<?php

use Getresponse\WordPress\LandingPageService;
use Getresponse\WordPress\ApiFactory;

defined( 'ABSPATH' ) || exit;

add_action( 'gr_settings_run', 'remove_landing_page' );
add_action( 'gr_settings_run', 'update_landing_page' );
add_action( 'gr_settings_run', 'add_landing_page' );

function add_landing_page()
{
    if (
        gr_get( 'page' ) !== 'gr-integration-landing-pages'
        || gr_get( 'action' ) !== 'add_landing_page'
        || null === gr_post( 'id' )
        || null === gr_post( 'status' )
    ) {
        return;
    }

    $page_id = gr_post( 'id' );
    $service = new LandingPageService( ApiFactory::create_api() );
    $pages = $service->get_connected_pages();
    $page = $service->get_page_by_id( $page_id );
    $key = gr_post( 'key' );

    if (!empty($key)) {
        unset($pages[ $key ]);
    }

    $slug = trim( gr_post( 'url' ), '/');
    $pages[$slug] = array(
        'title' => $page['metaTitle'],
        'url' => $page['url'],
        'id' => $page_id,
        'status' => gr_post( 'status' )
    );

    $service->update_connected_pages($pages);
    exit;
}

function remove_landing_page() {
    $page = gr_get( 'page' );
    $action = gr_get( 'action' );
    $id = gr_get( 'id' );

    if ($page !== 'gr-integration-landing-pages' || $action !== 'remove_landing_page') {
        return;
    }

    $service = new LandingPageService( ApiFactory::create_api() );
    $pages = $service->get_connected_pages();

    unset( $pages[$id] );

    $service->update_connected_pages($pages);


    gr()->add_success_message( 'Landing Page removed' );

    wp_redirect( admin_url( 'admin.php?page=gr-integration-landing-pages' ) );
    exit;
}

function update_landing_page() {

	$submit = gr_post( 'save_landing_page' );

	if ( 'Save Changes' !== $submit ) {
		return;
	}

	$status = gr_post( 'lp_status' );
    $service = new LandingPageService( ApiFactory::create_api() );
    $service->update( $status === 'enabled' ? 1 : 0 );

	gr()->add_success_message( $status === 'enabled' ? 'Landing Pages enabled' : 'Landing Pages disabled' );

}

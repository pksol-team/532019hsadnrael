<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class LandingPageService
 * @package Getresponse\WordPress
 */
class LandingPageService {

	/** @var Api */
	private $api;

	/**
	 * @param Api $api
	 */
	public function __construct( $api ) {
		$this->api = $api;
	}

	/**
	 * @param bool $status
	 */
	public function update( $status ) {
		gr_update_option( 'landing_page_status', (int) $status );
	}

    /**
     * @return array
     */
    public function get_pages() {
        $list = array();
        $paginationIndex = 1;

        do {
            try {
                $pages = $this->api->get_landing_pages(['perPage' => 100, 'page' => $paginationIndex, 'query' => ['status' => 'enabled']]);
            } catch (ApiException $e) {
                $pages = [];
            }

            foreach ($pages as $page) {
                $list[$page['landingPageId']] = $page['metaTitle'];
            }

            $paginationIndex++;
        } while (100 === count($pages));

        return $list;
    }

    /**
     * @param string $slug
     * @return array
     */
    public function get_page_by_slug( $slug ) {
        $pages = gr_get_option( 'landing_pages' );

        if ( !empty( $pages ) && isset( $pages[$slug] ) ) {
            return $pages[$slug];
        }

        return [];
    }

    public function get_page_by_id( $page_id ) {
        try {
            $page = $this->api->get_landing_page( $page_id );
        } catch (ApiException $e) {
            $page = [];
        }

        return $page;
    }

    /**
     * @return array
     */
    public function get_connected_pages() {
        return gr_get_option( 'landing_pages' );
    }

    /**
     * @param array $pages
     */
    public function update_connected_pages( $pages ) {
        gr_update_option( 'landing_pages', $pages );
    }
}

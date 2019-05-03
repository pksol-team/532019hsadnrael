<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class CoreAdmin
 * @package Getresponse\WordPress
 */
class CoreAdmin {

	/**
	 * Admin URL.
	 * @var string
	 */
	public $admin_url = '';

	/**
	 * Admin directory path.
	 *
	 * @var string
	 */
	public $admin_dir = '';

	/**
	 * Settings page name.
	 *
	 * @var string
	 */
	public $settings_page = 'options-general.php';

	public function __construct() {
		$this->setup_globals();
		$this->setup_filters();
	}

	/**
	 * Set object variables.
	 */
	private function setup_globals() {

		$this->admin_url = trailingslashit( gr()->plugin_dir . 'gr-core/admin' );
		$this->admin_dir = trailingslashit( gr()->plugin_dir . 'gr-core/admin' );
	}

	/**
	 * Set filters.
	 */
	private function setup_filters() {
		// Add Settings page to links.
		add_filter( 'plugin_action_links', array( $this, 'modify_plugin_action_links' ), 11, 2 );
	}

	/**
	 * Modify links in Admin section.
	 *
	 * @param array $links array of links.
	 *
	 * @return array
	 */
	public function modify_plugin_action_links( $links ) {
		return $links;
	}

	/**
	 * Get Admin tabs.
	 *
	 * * @param $active
	 *
	 * @return string
	 */
	public function get_admin_tabs( $active ) {

		$html_tabs = '';

		$tab_links   = array();
		$tab_links[] = array(
			'url'   => 'gr-integration-status',
			'name'  => __('Account', 'Gr_Integration')
		);

		if ( gr()->is_connected_to_getresponse() ) {

			$tab_links[] = array(
				'url'   => 'gr-integration-subscription-settings',
				'name'  => __('Adding Contacts', 'Gr_Integration')

			);

			if ( gr()->is_woocommerce_plugin_active() ) {
				$tab_links[] = array(
					'url'   => 'gr-integration-woocommerce',
					'name'  => __('WooCommerce', 'Gr_Integration')
				);
			}

			$tab_links[] = array(
				'url'   => 'gr-integration-tracking-code',
				'name'  => __('Web Event Tracking', 'Gr_Integration')
			);

			$tab_links[] = array(
				'url'   => 'gr-integration-landing-pages',
				'name'  => __('Landing Pages', 'Gr_Integration')
			);
		}

		$tab_links[] = array(
			'url'   => 'gr-integration-help',
			'name'  => __('Help', 'Gr_Integration')
		);

		foreach ( $tab_links as $tab ) {

			$url = admin_url( add_query_arg( array( 'page' => $tab['url'] ), 'admin.php' ) );

			$activeTab = null;

			if ( $active === $tab['url'] ) {
				$activeTab = 'nav-tab-active';
			}

			$class = 'nav-tab ' . $activeTab;
            $icon = '';

            if (isset($tab['class'])) {
                $icon = '<span class="dashicons ' . $tab['class'] . '"></span>';
            }

			$html_tabs .= '<a class="' . $class . '" href="' . $url . '">' . $icon . $tab['name'] . '</a>';
		}

		return $html_tabs;
	}
}
<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class GrWidget
 * @package Getresponse\WordPress
 */
class GrWidget extends \WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {

		parent::__construct(
			'getresponse-widget',
			__( 'GetResponse Web Form widget', 'Gr_Integration' ),
			array( 'description' => __( 'Add Contacts via GetResponse Web Form widget', 'Gr_Integration' ) )
		);

		gr()->int_widget = $this;
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {

		$title         = isset( $instance['title'] ) ? $instance['title'] : null;
		$webform_id    = isset( $instance['select'] ) ? $instance['select'] : null;
		$variants_id   = isset( $instance['variants'] ) ? $instance['variants'] : null;
		$style_id      = isset( $instance['style'] ) ? $instance['style'] : null;
		$center        = isset( $instance['center'] ) ? $instance['center'] : null;
		$center_margin = isset( $instance['center_margin'] ) ? $instance['center_margin'] : null;
		$version       = isset( $instance['version'] ) ? $instance['version'] : null;

		$web_forms = json_decode( gr_get_option( 'web_forms' ), true );

		if ( isset( $web_forms[ $webform_id ] ) ) {
			$webform = $web_forms[ $webform_id ];
		} else {

			$api = ApiFactory::create_api();

			if ( false === empty( $api ) ) {

				try {
					$service = new WebformService( $api );

					$webform = 'old' == $version ? $service->get_old_form( $webform_id ) : $service->get_new_form( $webform_id );
				} catch (ApiException $e ) {
					$webform = array();
				}
				$webform['scriptUrl'] = isset( $webform->scriptUrl ) ? $webform->scriptUrl : null;
				$webform['status']    = isset( $webform->status ) ? $webform->status : null;

				$webform_option                        = array();
				$webform_option[ $instance['select'] ] = (array) $webform;

				gr_update_option( 'web_forms', json_encode( $webform_option ) );
			}
		}

		// Css styles Webform/Wordpress.
		$css     = ( 1 === $style_id && 'old' === $version ) ? '&css=1' : null;
		$variant = ( $variants_id >= 0 && 'new' === $version ) ? '&v=' . (int) $variants_id : null;

		if (!empty($webform) && isset($webform['scriptUrl']) && in_array( $webform['status'], array('enabled', 'published'))) {

			$style = ( '1' === $center ) ? 'style="margin-left: auto; margin-right: auto; width: ' . $center_margin . 'px;"' : '';
			$webform['scriptUrl'] = $this->replaceHttpsToHttpIfSslOn( $webform['scriptUrl'] );

			$form = '<div ' . $style . '>';
			$form .= '<script type="text/javascript" src="' . htmlspecialchars( $webform['scriptUrl'] . $css . $variant ) . '"></script>';
			$form .= '</div>';
		}

		if ( ! empty( $form ) ) {
			echo $args['before_widget'];
			echo $args['before_title'];
			echo $title;
			echo $args['after_title'];
			echo __( $form, 'text_domain' );
			echo $args['after_widget'];
		}
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 *
	 * @return string|void
	 */
	public function form( $instance ) {

		global $wp_customize;

		$params['title']         = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$params['select']        = isset( $instance['select'] ) ? esc_attr( $instance['select'] ) : '';
		$params['variants']      = isset( $instance['variants'] ) ? esc_attr( $instance['variants'] ) : '';
		$params['style']         = isset( $instance['style'] ) ? esc_attr( $instance['style'] ) : '';
		$params['center']        = isset( $instance['center'] ) ? esc_attr( $instance['center'] ) : '';
		$params['center_margin'] = isset( $instance['center_margin'] ) ? esc_attr( $instance['center_margin'] ) : '';

		$api_key = gr_get_option( 'api_key' );

		if ( null === $api_key ) {
			gr_load_template( 'admin/widgets/admin/widget_error.php' );
		} else {
			if ( isset( $wp_customize ) ) {

				gr_load_template( 'admin/widgets/customizer/widget.php', $params );
			} else {
				gr_load_template( 'admin/widgets/admin/widget.php', $params );
				gr_load_template( 'admin/widgets/admin/scripts.php', $params );
			}
		}
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {

		$instance                  = array();
		$instance['title']         = ! empty( $new_instance['title'] ) ? strip_tags( $new_instance['title'] ) : null;
		$instance['select']        = ! empty( $new_instance['select'] ) ? strip_tags( $new_instance['select'] ) : null;
		$instance['variants']      = ! empty( $new_instance['variants'] ) && $new_instance['variants'] !== '-' ? strip_tags( $new_instance['variants'] ) : null;
		$instance['style']         = ! empty ( $new_instance['style'] ) ? strip_tags( $new_instance['style'] ) : null;
		$instance['center']        = ! empty( $new_instance['center'] ) ? strip_tags( $new_instance['center'] ) : null;
		$instance['center_margin'] = ! empty( $new_instance['center_margin'] ) ? (int) strip_tags( $new_instance['center_margin'] ) : null;
		$instance['version']       = ( in_array( strip_tags( $new_instance['version'] ),
			array( 'old', 'new' ) ) ) ? strip_tags( $new_instance['version'] ) : 'old';

		$api = ApiFactory::create_api();

		if ( empty( $api ) ) {
			return $instance;
		}

		try {
			$service = new WebformService( $api );

			$webform = ( $instance['version'] == 'old' ) ? $service->get_old_form( $instance['select'] ) : $service->get_new_form( $instance['select'] );

		} catch (ApiException $e) {
			$webform = array();
		}

		$web_forms = json_decode( gr_get_option( 'web_forms' ), true );
		if ( ! empty( $web_foms ) ) {
			$web_forms = array();
		}
		$web_forms[ $instance['select'] ] = $webform;

		if ( function_exists( 'is_customize_preview' ) == false || is_customize_preview() == false ) {
			gr_update_option( 'web_forms', json_encode( $web_forms ) );
		}

		return $instance;
	}

	private function replaceHttpsToHttpIfSslOn( $url ) {
		return ( ! empty( $url ) && ! is_ssl() && strpos( $url, 'https' ) === 0 ) ? str_replace( 'https', 'http',
			$url ) : $url;
	}

}
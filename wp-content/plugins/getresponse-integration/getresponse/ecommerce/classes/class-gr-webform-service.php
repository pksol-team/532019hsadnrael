<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class WebformService
 * @package Getresponse\WordPress
 */
class WebformService {

	const CACHE_WEB_FORM_OLD_KEY = 'old_web_form';
	const CACHE_WEB_FORM_NEW_KEY = 'new_web_form';
	const CACHE_OLD_FORMS_KEY = 'gr_old_forms';
	const CACHE_NEW_FORMS_KEY = 'gr_new_forms';
	const CACHE_FORMS_VARIANTS_KEY = 'gr_new_forms';

	const CACHE_TIME = 300;

	/** @var Api */
	private $api;

	/**
	 * @param Api $api
	 */
	public function __construct( $api ) {
		$this->api = $api;
	}

	/**
	 * @return array
	 * @throws ApiException
	 */
	public function get_old_forms() {

		$old_forms = gr_cache_get( self::CACHE_OLD_FORMS_KEY );

		if ( false === $old_forms ) {
			$old_forms = $this->api->get_web_forms( array( 'sort' => array( 'name' => 'asc' ) ) );

			if (!empty($old_forms)) {
				gr_cache_set( self::CACHE_OLD_FORMS_KEY, $old_forms, self::CACHE_TIME );
			}
		}

		return $old_forms;
	}

	/**
	 * @return array
	 * @throws ApiException
	 */
	public function get_new_forms() {

		$new_forms = gr_cache_get( self::CACHE_NEW_FORMS_KEY );

		if ( false === $new_forms ) {
			$new_forms = $this->api->get_forms( array( 'sort' => array( 'name' => 'asc' ) ) );

			if ( ! empty( $new_forms ) ) {

				gr_cache_set( self::CACHE_NEW_FORMS_KEY, $new_forms, self::CACHE_TIME );
			}
		}

		return $new_forms;
	}

	/**
	 * @param string $form_id
	 *
	 * @return array
	 * @throws ApiException
	 */
	public function get_web_form_variants( $form_id ) {

		if ( empty( $form_id ) ) {
			return array();
		}

		$variants = gr_cache_get( self::CACHE_FORMS_VARIANTS_KEY . $form_id );

		if ( false === $variants ) {
			$variants = $this->api->get_form_variants( $form_id );
			gr_cache_set( self::CACHE_FORMS_VARIANTS_KEY . $form_id, $variants, self::CACHE_TIME );
		}

		return $variants;
	}

	/**
	 * @param string $form_id
	 *
	 * @return array
	 * @throws ApiException
	 */
	public function get_old_form( $form_id ) {

		if ( empty( $form_id ) ) {
			return array();
		}

		$web_form = gr_cache_get( self::CACHE_WEB_FORM_OLD_KEY . $form_id );

		if ( false === $web_form ) {
			$web_form = $this->api->get_web_form( $form_id );

			if ( ! empty( $web_form ) ) {
				gr_cache_set( self::CACHE_WEB_FORM_OLD_KEY . $form_id, $web_form, self::CACHE_TIME );
			}
		}

		return $web_form;
	}

	/**
	 * @param string $form_id
	 *
	 * @return array
	 * @throws ApiException
	 */
	public function get_new_form( $form_id ) {

		if ( empty( $form_id ) ) {
			return array();
		}

		$web_form = gr_cache_get( self::CACHE_WEB_FORM_NEW_KEY . $form_id );

		if ( false === $web_form ) {
			$web_form = $this->api->get_form( $form_id );

			if ( ! empty( $web_form ) ) {

				gr_cache_set( self::CACHE_WEB_FORM_NEW_KEY . $form_id, $web_form, self::CACHE_TIME );
			}
		}

		return $web_form;
	}
}

<?php

/**
 * Class ITSEC_Geolocator_Nekudo
 */
final class ITSEC_Geolocator_Nekudo implements ITSEC_Geolocator {

	const HOST = 'https://geoip.nekudo.com/api/';

	/**
	 * @inheritDoc
	 */
	public function geolocate( $ip ) {

		$response = wp_remote_get( self::HOST . $ip );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );

		if ( ! $body || null === ( $data = json_decode( $body, true ) ) || empty( $data['location']['latitude'] ) || empty( $data['location']['longitude'] ) ) {
			return new WP_Error( 'itsec-geolocate-nekudo-invalid-response', __( 'Invalid Geolocation response from Nekudo', 'it-l10n-ithemes-security-pro' ), compact( 'body', 'ip' ) );
		}

		$label = $data['country']['name'];

		if ( ! empty( $data['city'] ) ) {
			/* translators: 1. City Name, 2. Country Name */
			$label = sprintf( _x( '%1$s, %2$s', 'Location', 'it-l10n-ithemes-security-pro' ), $data['city'], $label );
		}

		return array(
			'lat'    => (float) $data['location']['latitude'],
			'long'   => (float) $data['location']['longitude'],
			'label'  => esc_html( $label ),
			'credit' => esc_html__( 'Location data provided by GeoIP Nekudo', 'it-l10n-ithemes-security-pro' ),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function is_available() {
		return true;
	}
}
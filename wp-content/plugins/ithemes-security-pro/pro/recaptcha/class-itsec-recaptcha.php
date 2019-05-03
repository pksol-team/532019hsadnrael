<?php

class ITSEC_Recaptcha {
	private $settings;
	private $cookie_name;

	// Keep track of the number of recaptcha instances on the page
	private static $captcha_count = 0;


	public function run() {
		$this->cookie_name = 'itsec-recaptcha-opt-in-' . COOKIEHASH;

		// Run on init so that we can use is_user_logged_in()
		// Warning: BuddyPress has issues with using is_user_logged_in() on plugins_loaded
		add_action( 'init', array( $this, 'setup' ) );

		add_filter( 'itsec_lockout_modules', array( $this, 'register_lockout_module' ) );

		// Check for the opt-in and set the cookie.
		if ( isset( $_REQUEST['recaptcha-opt-in'] ) && 'true' === $_REQUEST['recaptcha-opt-in'] ) {
			setcookie( $this->cookie_name, 'true', time() + MONTH_IN_SECONDS, ITSEC_Lib::get_home_root(), COOKIE_DOMAIN, is_ssl(), true );
		}
	}

	public function setup() {
		$this->settings = ITSEC_Modules::get_settings( 'recaptcha' );

		if ( empty( $this->settings['site_key'] ) || empty( $this->settings['secret_key'] ) ) {
			// Only run when the settings are fully filled out.
			return;
		}

		ITSEC_Recaptcha_API::init( $this );

		// Logged in users are people, we don't need to re-verify
		if ( is_user_logged_in() ) {
			if ( is_multisite() ) {
				add_action( 'network_admin_notices', array( $this, 'show_last_error' ) );
			} else {
				add_action( 'admin_notices', array( $this, 'show_last_error' ) );
			}

			return;
		}


		add_action( 'login_enqueue_scripts', array( $this, 'login_enqueue_scripts' ) );

		if ( $this->settings['comments'] ) {

			if ( version_compare( $GLOBALS['wp_version'], '4.2', '>=' ) ) {
				add_filter( 'comment_form_submit_button', array( $this, 'comment_form_submit_button' ) );
			} else {
				add_filter( 'comment_form_field_comment', array( $this, 'comment_form_field_comment' ) );
			}
			add_filter( 'preprocess_comment', array( $this, 'filter_preprocess_comment' ) );

		}

		if ( $this->settings['login'] ) {

			add_action( 'login_form', array( $this, 'login_form' ) );
			add_filter( 'login_form_middle', array( $this, 'wp_login_form' ), 100 );
			add_filter( 'authenticate', array( $this, 'filter_authenticate' ), 30 );

		}

		if ( $this->settings['register'] ) {

			add_action( 'register_form', array( $this, 'register_form' ) );
			add_filter( 'registration_errors', array( $this, 'registration_errors' ) );

		}

	}

	public function show_last_error() {
		if ( ! ITSEC_Core::current_user_can_manage() || $this->settings['validated'] || empty( $this->settings['last_error'] ) ) {
			return;
		}

		echo '<div class="error"><p><strong>';
		printf( wp_kses( __( 'The reCAPTCHA settings for iThemes Security are invalid. %1$s Bots will not be blocked until <a href="%2$s" data-module-link="recaptcha">the reCAPTCHA settings</a> are set properly.', 'it-l10n-ithemes-security-pro' ), array( 'a' => array( 'href' => array(), 'data-module-link' => array() ) ) ), esc_html( $this->settings['last_error'] ), ITSEC_Core::get_settings_module_url( 'recaptcha' ) );
		echo '</strong></p></div>';
	}

	/**
	 * Add recaptcha form to comment form
	 *
	 * @since 1.17
	 *
	 * @param string  $comment_field The comment field in the comment form
	 *
	 * @return string The comment field with our recaptcha field appended
	 */
	public function comment_form_field_comment( $comment_field ) {

		$comment_field .= $this->get_recaptcha();

		return $comment_field;

	}

	/**
	 * Preferred method to add recaptcha form to comment form. Used in WP 4.2+
	 *
	 * @since 1.17
	 *
	 * @param string  $submit_button The submit button in the comment form
	 *
	 * @return string The submit button with our recaptcha field prepended
	 */
	public function comment_form_submit_button( $submit_button ) {

		$submit_button = $this->get_recaptcha() . $submit_button;

		return $submit_button;

	}

	/**
	 * Add appropriate scripts to login page
	 *
	 * @since 1.13
	 *
	 * @return void
	 */
	public function login_enqueue_scripts() {

		wp_enqueue_style( 'itsec-recaptcha', plugin_dir_url( __FILE__ ) . 'css/itsec-recaptcha.css', array(), ITSEC_Core::get_plugin_build() );

	}

	/**
	 * Add the recaptcha field to the login form
	 *
	 * @since 1.13
	 *
	 * @return void
	 */
	public function login_form() {

		$this->show_recaptcha();

	}

	/**
	 * Add the Recaptcha to the `wp_login_form()` template function.
	 *
	 * @param string $html
	 *
	 * @return string
	 */
	public function wp_login_form( $html ) {
		$html .= $this->get_recaptcha( 10, 0, 10, 0 );

		return $html;
	}

	/**
	 * Process recaptcha for comments
	 *
	 * @since 1.13
	 *
	 * @param array $comment_data Comment data.
	 *
	 * @return array Comment data.
	 */
	public function filter_preprocess_comment( $comment_data ) {

		$result = $this->validate_captcha();

		if ( is_wp_error( $result ) ) {
			wp_die( $result->get_error_message() );
		}

		return $comment_data;

	}

	/**
	 * Add the recaptcha field to the registration form
	 *
	 * @since 1.13
	 *
	 * @return void
	 */
	public function register_form() {

		$this->show_recaptcha();

	}

	/**
	 * Set the registration error if captcha wasn't validated
	 *
	 * @since 1.13
	 *
	 * @param WP_Error $errors               A WP_Error object containing any errors encountered
	 *                                       during registration.
	 *
	 * @return WP_Error A WP_Error object containing any errors encountered
	 *                                       during registration.
	 */
	public function registration_errors( $errors ) {

		$result = $this->validate_captcha();

		if ( is_wp_error( $result ) ) {
			$errors->add( $result->get_error_code(), $result->get_error_message() );
		}

		return $errors;

	}

	// Leave this in as iThemes Exchange relies upon it.
	public function show_field( $echo = true, $deprecated1 = true, $margin_top = 0, $margin_right = 0, $margin_bottom = 0, $margin_left = 0, $deprecated2 = null ) {
		if ( $echo ) {
			$this->show_recaptcha( $margin_top, $margin_right, $margin_bottom, $margin_left );
		} else {
			return $this->get_recaptcha( $margin_top, $margin_right, $margin_bottom, $margin_left );
		}
	}

	public function show_recaptcha( $margin_top = 10, $margin_right = 0, $margin_bottom = 10, $margin_left = 0 ) {
		echo $this->get_recaptcha( $margin_top, $margin_right, $margin_bottom, $margin_left );
	}

	private function has_visitor_opted_in() {
		if ( isset( $_REQUEST['recaptcha-opt-in'] ) && 'true' === $_REQUEST['recaptcha-opt-in'] ) {
			return true;
		}

		if ( isset( $_COOKIE[$this->cookie_name] ) && 'true' === $_COOKIE[$this->cookie_name] ) {
			return true;
		}

		return false;
	}

	private function show_opt_in() {
		if ( $this->has_visitor_opted_in() ) {
			return '';
		}

		$url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		if ( false === strpos( $url, '?' ) ) {
			$url .= '?recaptcha-opt-in=true';
		} else {
			$url .= '&recaptcha-opt-in=true';
		}

		/* Translators: 1: Google's privacy policy URL, 2: Google's terms of use URL */
		$p1 = sprintf( wp_kses( __( 'For security, use of Google\'s reCAPTCHA service is required which is subject to the Google <a href="%1$s">Privacy Policy</a> and <a href="%2$s">Terms of Use</a>.', 'it-l10n-ithemes-security-pro' ), array( 'a' => array( 'href' => array() ) ) ), 'https://policies.google.com/privacy', 'https://policies.google.com/terms' );

		/* Translators: 1: URL to agree to terms */
		$p2 = sprintf( wp_kses( __( 'If you agree to these terms, please click <a href="%1$s">here</a>.', 'it-l10n-ithemes-security-pro' ), array( 'a' => array( 'href' => array() ) ) ), $url );

		$html = '<div id="itsec-recaptcha-opt-in">';
		$html .= '<p>' . $p1 . '</p>';
		$html .= '<p>' . $p2 . '</p>';
		$html .= '</div>';

		return $html;
	}

	public function get_recaptcha( $margin_top = 0, $margin_right = 0, $margin_bottom = 0, $margin_left = 0 ) {
		if ( $html = $this->show_opt_in() ) {
			return $html;
		}

		self::$captcha_count++;

		$script = 'https://www.google.com/recaptcha/api.js';

		$query_args = array(
			'render' => 'explicit'
		);

		if ( ! empty( $this->settings['language'] ) ) {
			$query_args['hl'] = $this->settings['language'];
		}

		if ( $this->settings['type'] === 'invisible' ) {
			$query_args['onload'] = 'itsecInvisibleRecaptchaLoad';
		} else {
			$query_args['onload'] = 'itsecRecaptchav2Load';
		}

		if ( ! empty( $query_args ) ) {
			$script .= '?' . http_build_query( $query_args, '', '&' );
		}

		wp_register_script( 'itsec-recaptcha-api', $script );

		if ( 'invisible' === $this->settings['type'] ) {
			wp_enqueue_script( 'itsec-recaptcha-script', plugin_dir_url( __FILE__ ) . 'js/invisible-recaptcha.js', array( 'jquery', 'itsec-recaptcha-api' ) );

			$recaptcha = '<div class="g-recaptcha" id="g-recaptcha-' . esc_attr( self::$captcha_count ) . '" data-sitekey="' . esc_attr( $this->settings['site_key'] ) . '" data-size="invisible" data-badge="' . esc_attr( $this->settings['invis_position'] ) . '"></div>';
		} else {
			wp_enqueue_script( 'itsec-recaptcha-script-v2', plugin_dir_url( __FILE__ ) . 'js/recaptcha-v2.js', array( 'itsec-recaptcha-api' ) );

			$theme = $this->settings['theme'] ? 'dark' : 'light';
			$style_value = sprintf( 'margin:%dpx %dpx %dpx %dpx', $margin_top, $margin_right, $margin_bottom, $margin_left );

			$recaptcha = '<div class="g-recaptcha" id="g-recaptcha-' . esc_attr( self::$captcha_count ) . '" data-sitekey="' . esc_attr( $this->settings['site_key'] ) . '" data-theme="' . esc_attr( $theme ) . '" style="' . esc_attr( $style_value ) . '"></div>';
		}


		$recaptcha .= '<noscript>
			<div>
				<div style="width: 302px; height: 422px; position: relative;">
					<div style="width: 302px; height: 422px; position: absolute;">
						<iframe src="https://www.google.com/recaptcha/api/fallback?k=' . esc_attr( $this->settings['site_key'] ) . '" frameborder="0" scrolling="no" style="width: 302px; height:422px; border-style: none;"></iframe>
					</div>
				</div>
				<div style="width: 300px; height: 60px; border-style: none; bottom: 12px; left: 25px; margin: 0px; padding: 0px; right: 25px; background: #f9f9f9; border: 1px solid #c1c1c1; border-radius: 3px;">
					<textarea id="g-recaptcha-response" name="g-recaptcha-response" class="g-recaptcha-response" style="width: 250px; height: 40px; border: 1px solid #c1c1c1; margin: 10px 25px; padding: 0px; resize: none;"></textarea>
				</div>
			</div>
		</noscript>';


		return $recaptcha;

	}

	/**
	 * Validates the captcha code
	 *
	 * This function is used both internally in iThemes Security and externally in other projects, such as iThemes
	 * Exchange.
	 *
	 * @since 1.13
	 *
	 * @return bool|WP_Error Returns true or a WP_Error object on error.
	 */
	public function validate_captcha() {
		if ( isset( $GLOBALS['__itsec_recaptcha_cached_result'] ) ) {
			return $GLOBALS['__itsec_recaptcha_cached_result'];
		}

		if ( empty( $_POST['g-recaptcha-response'] ) ) {
			if ( ! $this->settings['validated'] ) {
				ITSEC_Modules::set_setting( 'recaptcha', 'last_error', esc_html__( 'The Site Key may be invalid or unrecognized. Verify that you input the Site Key and Private Key correctly.', 'it-l10n-ithemes-security-pro' ) );

				$GLOBALS['__itsec_recaptcha_cached_result'] = true;
				return $GLOBALS['__itsec_recaptcha_cached_result'];
			}

			$GLOBALS['__itsec_recaptcha_cached_result'] = new WP_Error( 'itsec-recaptcha-form-not-submitted', esc_html__( 'You must verify you are a human.', 'it-l10n-ithemes-security-pro' ) );

			$this->log_failed_validation( $GLOBALS['__itsec_recaptcha_cached_result'] );

			return $GLOBALS['__itsec_recaptcha_cached_result'];
		}


		$url = add_query_arg(
			array(
				'secret'   => $this->settings['secret_key'],
				'response' => esc_attr( $_POST['g-recaptcha-response'] ),
			),
			'https://www.google.com/recaptcha/api/siteverify'
		);

		$response = wp_remote_get( $url );

		if ( is_wp_error( $response ) ) {
			// Don't lock people out when reCAPTCHA servers cannot be contacted.
			$GLOBALS['__itsec_recaptcha_cached_result'] = true;
			return $GLOBALS['__itsec_recaptcha_cached_result'];
		}


		$status = json_decode( $response['body'], true );

		if ( ! isset( $status['success'] ) ) {
			// Unrecognized response. Do not prevent access.
			$GLOBALS['__itsec_recaptcha_cached_result'] = true;
			return $GLOBALS['__itsec_recaptcha_cached_result'];
		}

		if ( $status['success'] && $this->validate_host( $status ) ) {
			if ( ! $this->settings['validated'] ) {
				ITSEC_Modules::set_setting( 'recaptcha', 'validated', true );
			}

			if ( ! empty( $this->settings['last_error'] ) ) {
				ITSEC_Modules::set_setting( 'recaptcha', 'last_error', '' );
			}

			$GLOBALS['__itsec_recaptcha_cached_result'] = true;
			return $GLOBALS['__itsec_recaptcha_cached_result'];
		}

		if ( ! $this->settings['validated'] ) {
			if ( ! empty( $status['error-codes'] ) ) {
				if ( array( 'invalid-input-secret' ) === $status['error-codes'] ) {
					ITSEC_Modules::set_setting( 'recaptcha', 'last_error', esc_html__( 'The Secret Key is invalid or unrecognized.', 'it-l10n-ithemes-security-pro' ) );
				} else if ( 1 === count( $status['error-codes'] ) ) {
					$code = current( $status['error-codes'] );

					ITSEC_Modules::set_setting( 'recaptcha', 'last_error', sprintf( esc_html__( 'The reCAPTCHA server reported the following error: <code>%1$s</code>.', 'it-l10n-ithemes-security-pro' ), $code ) );
				} else {
					ITSEC_Modules::set_setting( 'recaptcha', 'last_error', sprintf( esc_html__( 'The reCAPTCHA server reported the following errors: <code>%1$s</code>.', 'it-l10n-ithemes-security-pro' ), implode( ', ', $status['error-codes'] ) ) );
				}
			}

			$GLOBALS['__itsec_recaptcha_cached_result'] = true;
			return $GLOBALS['__itsec_recaptcha_cached_result'];
		}

		$GLOBALS['__itsec_recaptcha_cached_result'] = new WP_Error( 'itsec-recaptcha-incorrect', esc_html__( 'The captcha response you submitted does not appear to be valid. Please try again.', 'it-l10n-ithemes-security-pro' ) );

		$this->log_failed_validation( $GLOBALS['__itsec_recaptcha_cached_result'] );

		return $GLOBALS['__itsec_recaptcha_cached_result'];
	}

	/**
	 * Validate the hostname the Recaptcha was filled on.
	 *
	 * This allows the user to disable "Domain Name Validation" on large multisite installations because Google
	 * limits the number of sites a recaptcha key can be used on.
	 *
	 * @since 4.2.0
	 *
	 * @param array $status
	 *
	 * @return bool
	 */
	private function validate_host( $status ) {

		if ( ! apply_filters( 'itsec_recaptcha_validate_host', false ) ) {
			return true;
		}

		if ( ! isset( $status['hostname'] ) ) {
			return true;
		}

		$site_parsed = parse_url( site_url() );

		if ( ! is_array( $site_parsed ) || ! isset( $site_parsed['host'] ) ) {
			return true;
		}

		return $site_parsed['host'] === $status['hostname'];
	}

	private function log_failed_validation( $data ) {
		/** @var ITSEC_Lockout $itsec_lockout */
		global $itsec_lockout;

		ITSEC_Log::add_notice( 'recaptcha', 'failed-validation', $data );

		$itsec_lockout->do_lockout( 'recaptcha' );
	}

	/**
	 * Set the login error if captcha wasn't validated
	 *
	 * @since 1.13
	 *
	 * @param null|WP_User|WP_Error $user     WP_User if the user is authenticated.
	 *                                        WP_Error or null otherwise.
	 *
	 * @return null|WP_User|WP_Error $user     WP_User if the user is authenticated.
	 *                                         WP_Error or null otherwise.
	 */
	public function filter_authenticate( $user ) {
		if ( empty( $_POST ) || ITSEC_Core::is_api_request() ) {
			return $user;
		}

		$result = $this->validate_captcha();

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $user;

	}

	/**
	 * Register recaptcha for lockout
	 *
	 * @since 1.13
	 *
	 * @param  array $lockout_modules array of lockout modules
	 *
	 * @return array                   array of lockout modules
	 */
	public function register_lockout_module( $lockout_modules ) {

		$lockout_modules['recaptcha'] = array(
			'type'   => 'recaptcha',
			'reason' => __( 'too many failed captcha submissions.', 'it-l10n-ithemes-security-pro' ),
			'host'   => isset( $this->settings['error_threshold'] ) ? absint( $this->settings['error_threshold'] ) : 7,
			'period' => isset( $this->settings['check_period'] ) ? absint( $this->settings['check_period'] ) : 5,
		);

		return $lockout_modules;

	}
}

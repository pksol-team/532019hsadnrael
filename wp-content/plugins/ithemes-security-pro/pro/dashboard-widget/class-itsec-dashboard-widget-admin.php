<?php

class ITSEC_Dashboard_Widget_Admin {

	function run() {

		add_action( 'admin_init', array( $this, 'admin_init' ) );

	}

	/**
	 * Execute all hooks on admin init
	 *
	 * All hooks on admin init to make certain user has the correct permissions
	 *
	 * @since 1.9
	 *
	 * @return void
	 */
	public function admin_init() {

		if ( ITSEC_Core::current_user_can_manage() ) {

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) ); //enqueue scripts for admin page
			add_action( 'wp_dashboard_setup', array( $this, 'wp_dashboard_setup' ) );
			add_action( 'wp_ajax_itsec_release_dashboard_lockout', array( $this, 'itsec_release_dashboard_lockout' ) );
			add_action( 'wp_ajax_itsec_dashboard_summary_postbox_toggle', array( $this, 'itsec_dashboard_summary_postbox_toggle' ) );

		}
	}

	/**
	 * Add malware scheduling admin Javascript
	 *
	 * @since 1.9
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {

		if ( isset( get_current_screen()->id ) && ( strpos( get_current_screen()->id, 'dashboard' ) !== false ) ) {

			$deps = array( 'jquery' );

			if ( ITSEC_Modules::is_active( 'file-change' ) ) {
				if ( ! class_exists( 'ITSEC_File_Change_Admin' ) ) {
					ITSEC_Modules::load_module_file( 'admin.php', 'file-change' );
				}

				ITSEC_File_Change_Admin::enqueue_scanner();
				$deps[] = 'itsec-file-change-scanner';
			}

			wp_enqueue_style( 'itsec_dashboard_widget_css', plugins_url( 'css/admin-dashboard-widget.css', __FILE__ ), array(), ITSEC_Core::get_plugin_build() );
			wp_enqueue_script( 'itsec_dashboard_widget_js', plugins_url( 'js/admin-dashboard-widget.js', __FILE__ ), $deps, ITSEC_Core::get_plugin_build() );

			wp_localize_script( 'itsec_dashboard_widget_js', 'itsec_dashboard_widget_js', array(
				'host'          => '<p>' . __( 'Currently no hosts are locked out of this website.', 'it-l10n-ithemes-security-pro' ) . '</p>',
				'user'          => '<p>' . __( 'Currently no users are locked out of this website.', 'it-l10n-ithemes-security-pro' ) . '</p>',
				'scanning'      => __( 'Scanning files...', 'it-l10n-ithemes-security-pro' ),
				'scan_nonce'    => wp_create_nonce( 'itsec_dashboard_scan_files' ),
				'postbox_nonce' => wp_create_nonce( 'itsec_dashboard_summary_postbox_toggle' ),
			) );

		}

	}

	/**
	 * Echo dashboard widget content
	 *
	 * @since 1.9
	 *
	 * @return void
	 */
	public function dashboard_widget_content() {

		/** @var ITSEC_Lockout $itsec_lockout */
		global $itsec_lockout, $wpdb;

		$white_class = '';

		if ( function_exists( 'wp_get_current_user' ) ) {

			$current_user = wp_get_current_user();

			$meta = get_user_meta( $current_user->ID, 'itsec_dashboard_widget_status', true );

			if ( is_array( $meta ) ) {

				if ( isset( $meta['itsec_lockout_summary_postbox'] ) && $meta['itsec_lockout_summary_postbox'] == 'close' ) {
					$white_class = ' closed';
				}
			}

		}

		//Access Logs
		echo '<div class="itsec_links widget-section clear">';
		echo '<ul>';
		echo '<li><a href="' . esc_url( ITSEC_Core::get_settings_page_url() ) . '">' . __( '> Plugin Settings', 'it-l10n-ithemes-security-pro' ) . '</a></li>';
		echo '<li><a href="' . esc_url( ITSEC_Core::get_logs_page_url() ) . '">' . __( '> View Security Logs', 'it-l10n-ithemes-security-pro' ) . '</a></li>';
		echo '</ul>';
		echo '</div>';

		//Whitelist
		echo '<div class="itsec_summary_widget widget-section clear postbox' . $white_class . '" id="itsec_lockout_summary_postbox">';

		$lockouts = $itsec_lockout->get_lockouts( 'all', array( 'current' => false ) );
		$current  = $itsec_lockout->get_lockouts( 'host', array( 'return' => 'count' ) ) + $itsec_lockout->get_lockouts( 'user', array( 'return' => 'count' ) );

		$total_users = (int) $wpdb->get_var( "SELECT count(`id`) FROM {$wpdb->users}" );

		$users_with_weak_password = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT count(`user_id`) AS count FROM {$wpdb->usermeta} WHERE `meta_key` = %s AND `meta_value` < 3",
			ITSEC_Strong_Passwords::STRENGTH_KEY
		) );

		if ( class_exists( 'ITSEC_Two_Factor' ) ) {
			$users_with_2fa = (int) $wpdb->get_var( $wpdb->prepare(
				"SELECT count(`user_id`) AS count FROM {$wpdb->usermeta} WHERE `meta_key` = %s AND `meta_value` != ''",
				'_two_factor_enabled_providers'
			) );
			$users_without_2fa = $total_users - $users_with_2fa;
		} else {
			$users_without_2fa = $total_users;
		}

		echo '<div class="handlediv" title="Click to toggle"><br /></div>';
		echo '<h4 class="dashicons-before dashicons-shield-alt">' . __( 'Security Summary', 'it-l10n-ithemes-security-pro' ) . '</h4>';
		echo '<div class="inside">';
		echo '<div class="summary-item">';
		echo '<h5>' . __( 'Times protected from attack.', 'it-l10n-ithemes-security-pro' ) . '</h5>';
		echo '<span class="summary-total">' . sizeof( $lockouts ) . '</span>';
		echo '</div>';
		echo '<div class="summary-item">';
		echo '<h5>' . __( 'Current Number of lockouts.', 'it-l10n-ithemes-security-pro' ) . '</h5>';
		echo '<span class="summary-total" id="current-itsec-lockout-summary-total">' . $current . '</span>';
		echo '</div>';

		echo '<div class="summary-item">';
		echo '<h5>' . __( 'Users without Two-Factor Authentication', 'it-l10n-ithemes-security-pro' ) . '</h5>';
		echo '<span class="summary-total">' . absint( $users_without_2fa ) . '</span>';
		echo '</div>';

		echo '<div class="summary-item">';
		echo '<h5>' . __( 'Users without strong password', 'it-l10n-ithemes-security-pro' ) . '</h5>';
		echo '<span class="summary-total">' . absint( $users_with_weak_password ) . '</span>';
		echo '</div>';

		echo '<a href="' . esc_url( admin_url( 'admin.php?page=itsec&module=user-security-check' ) ) . '" class="button-secondary itsec-widget-user-security-check">User Security Check</a>';
		echo '</div>';
		echo '</div>';

		//Run file-change Scan
		echo '<div class="itsec_file-change_widget widget-section">';
		$this->file_scan();
		echo '</div>';

		//Show lockouts table
		echo '<div class="itsec_lockouts_widget widget-section clear">';
		$this->lockout_metabox();
		echo '</div>';

	}

	/**
	 * Show file scan button
	 *
	 * @since 1.9
	 *
	 * @return void
	 */
	private function file_scan() {
		if ( ! ITSEC_Modules::is_active( 'file-change' ) ) {
			return;
		}

		ITSEC_Modules::load_module_file( 'scanner.php', 'file-change' );

		if ( ITSEC_File_Change_Scanner::is_running() ) {
			$text     = esc_attr__( 'Scan in Progress', 'it-l10n-ithemes-security-pro' );
			$disabled = 'disabled';
			$class    = 'button-secondary';
		} else {
			$text     = esc_attr__( 'Scan Files Now', 'it-l10n-ithemes-security-pro' );
			$disabled = '';
			$class    = 'button-primary';
		}

		echo "<p><input type=\"button\" id=\"itsec_dashboard_one_time_file_check\" {$disabled} class=\"{$class}\" value=\"{$text}\" /></p>";
		echo '<div id="itsec_dashboard_one_time_file_check_results"></div>';
	}

	/**
	 * Active lockouts table and form for dashboard.
	 *
	 * @Since 1.9
	 *
	 * @return void
	 */
	private function lockout_metabox() {

		/** @var ITSEC_Lockout $itsec_lockout */
		global $itsec_lockout;

		$host_class = '';
		$user_class = '';

		if ( function_exists( 'wp_get_current_user' ) ) {

			$current_user = wp_get_current_user();

			$meta = get_user_meta( $current_user->ID, 'itsec_dashboard_widget_status', true );

			if ( is_array( $meta ) ) {

				if ( isset( $meta['itsec_lockout_host_postbox'] ) && $meta['itsec_lockout_host_postbox'] == 'close' ) {
					$host_class = ' closed';
				}

				if ( isset( $meta['itsec_lockout_user_postbox'] ) && $meta['itsec_lockout_user_postbox'] == 'close' ) {
					$user_class = ' closed';
				}
			}

		}

		//get locked out hosts and users from database
		$host_locks = $itsec_lockout->get_lockouts( 'host', array( 'limit' => 100 ) );
		$user_locks = $itsec_lockout->get_lockouts( 'user', array( 'limit' => 100 ) );
		?>
		<div class="postbox<?php echo $host_class; ?>" id="itsec_lockout_host_postbox">
			<div class="handlediv" title="Click to toggle"><br/></div>
			<h4 class="dashicons-before dashicons-lock"><?php _e( 'Locked out hosts', 'it-l10n-ithemes-security-pro' ); ?></h4>

			<div class="inside">
				<?php if ( sizeof( $host_locks ) > 0 ) { ?>

					<ul>
						<?php foreach ( $host_locks as $host ) { ?>

							<li>
								<label for="lo_<?php echo $host['lockout_id']; ?>">
									<a target="_blank" rel="noopener noreferrer" href="<?php echo esc_url( ITSEC_Lib::get_trace_ip_link( $host['lockout_host'] ) ); ?>"><?php esc_html_e( $host['lockout_host'] ); ?></a>
									<a href="<?php echo wp_create_nonce( 'itsec_reloease_dashboard_lockout' . $host['lockout_id'] ); ?>" id="<?php echo $host['lockout_id']; ?>" class="itsec_release_lockout locked_host">
										<span class="itsec-locked-out-remove">&mdash;</span>
									</a>
								</label>
							</li>

						<?php } ?>
					</ul>

				<?php } else { //no host is locked out ?>

					<p><?php _e( 'Currently no hosts are locked out of this website.', 'it-l10n-ithemes-security-pro' ); ?></p>

				<?php } ?>
			</div>
		</div>
		<div class="postbox<?php echo $user_class; ?>" id="itsec_lockout_user_postbox">
			<div class="handlediv" title="Click to toggle"><br/></div>
			<h4 class="dashicons-before dashicons-admin-users"><?php _e( 'Locked out users', 'it-l10n-ithemes-security-pro' ); ?></h4>

			<div class="inside">
				<?php if ( sizeof( $user_locks ) > 0 ) { ?>
					<ul>
						<?php foreach ( $user_locks as $user ) { ?>

							<?php $userdata = get_userdata( $user['lockout_user'] ); ?>

							<li>
								<label for="lo_<?php echo $user['lockout_id']; ?>">

									<a href="<?php echo wp_create_nonce( 'itsec_reloease_dashboard_lockout' . $user['lockout_id'] ); ?>"
									   id="<?php echo $user['lockout_id']; ?>"
									   class="itsec_release_lockout locked_user"><span
											class="itsec-locked-out-remove">&mdash;</span><?php echo isset( $userdata->user_login ) ? $userdata->user_login : ''; ?>
									</a>
								</label>
							</li>

						<?php } ?>
					</ul>
				<?php } else { //no user is locked out ?>

					<p><?php _e( 'Currently no users are locked out of this website.', 'it-l10n-ithemes-security-pro' ); ?></p>

				<?php } ?>
			</div>
		</div>
	<?php
	}

	/**
	 * Process the ajax call for opening and closing postboxes
	 *
	 * @since 1.9
	 *
	 * @return string json string for success or failure
	 */
	public function itsec_dashboard_summary_postbox_toggle() {

		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'itsec_dashboard_summary_postbox_toggle' ) ) {
			die ( __( 'Security error', 'it-l10n-ithemes-security-pro' ) );
		}

		$id        = isset( $_POST['id'] ) ? sanitize_text_field( $_POST['id'] ) : false;
		$direction = isset( $_POST['direction'] ) ? sanitize_text_field( $_POST['direction'] ) : false;

		if ( $id === false || $direction === false || ! function_exists( 'wp_get_current_user' ) || ! function_exists( 'get_user_meta' ) ) {
			die( false );
		}

		$current_user = wp_get_current_user();

		$meta = get_user_meta( $current_user->ID, 'itsec_dashboard_widget_status', true );

		if ( ! is_array( $meta ) ) {

			$meta = array(
				$id => $direction,
			);

		} else {

			$meta[$id] = $direction;

		}

		update_user_meta( $current_user->ID, 'itsec_dashboard_widget_status', $meta );

		die( true );

	}

	/**
	 * Process the ajax call for releasing lockouts from the dashboard
	 *
	 * @since 1.9
	 *
	 * @return string json string for success or failure
	 */
	public function itsec_release_dashboard_lockout() {

		/** @var ITSEC_Lockout $itsec_lockout */
		global $itsec_lockout;

		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'itsec_reloease_dashboard_lockout' . sanitize_text_field( $_POST['resource'] ) ) ) {
			die ( __( 'Security error', 'it-l10n-ithemes-security-pro' ) );
		}

		die( $itsec_lockout->release_lockout( absint( $_POST['resource'] ) ) );

	}

	/**
	 * Create dashboard widget
	 *
	 * @since 1.9
	 *
	 * @return void
	 */
	public function wp_dashboard_setup() {

		wp_add_dashboard_widget(
			'itsec-dashboard-widget',
			ITSEC_Core::get_plugin_name(),
			array( $this, 'dashboard_widget_content' )
		);

	}

}

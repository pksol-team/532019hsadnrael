<?php
$can_register = get_option( 'users_can_register' );  ?>

<div class="ld-modal ld-login-modal <?php if( $can_register) echo 'ld-can-register'; ?>">

	<span class="ld-modal-closer ld-icon ld-icon-delete"></span>

	<div class="ld-login-modal-login">
		<div class="ld-login-modal-wrapper">
			<?php
			/**
			 * Action to add custom content before the modal heading
			 *
			 * @since 3.0
			 */
			do_action( 'learndash-login-modal-heading-before' ); ?>
			<div class="ld-modal-heading">
				<?php echo esc_html_e( 'Login', 'learndash' ); ?>
			</div>
			<?php
			/**
			 * Action to add custom content after the modal heading
			 *
			 * @since 3.0
			 */
			do_action( 'learndash-login-modal-heading-after' ); ?>
			<div class="ld-modal-text">
				<?php esc_html_e( 'Accessing this course requires a login, please enter your credentials below!', 'learndash' ); ?>
			</div>
			<?php
            /**
             * Action to add custom content after the modal text
             *
             * @since 3.0
             */
			do_action( 'learndash-login-modal-text-after' );
			if( isset($_GET['login']) && $_GET['login'] == 'failed' ):

				learndash_get_template_part(
					'modules/alert.php',
					array(
						'type'      =>  'warning',
			            'icon'      =>  'alert',
						'message'	=>	__( 'Incorrect username or password. Please try again', 'learndash' )
					), true );

					/**
					 * Action to add custom content after the modal alert
					 *
					 * @since 3.0
					 */
					do_action( 'learndash-login-modal-alert-after' );

			endif; ?>
			<div class="ld-login-modal-form">

                <?php
				/**
				 * Action to add custom content before the modal form
				 *
				 * @since 3.0
				 */
				do_action( 'learndash-login-modal-form-before' );

				// Add a filter for validation returns
				add_filter( 'login_form_middle', 'learndash_add_login_field' );

                // Just so users can supply their own args if desired
                $args = apply_filters( 'learndash-login-form-args', array() );

                wp_login_form($args);

				/**
				 * Action to add custom content after the modal form
				 *
				 * @since 3.0
				 */
				do_action( 'learndash-login-modal-form-after' );

				$logo_id = LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Theme_LD30', 'login_logo' );

				if( $logo_id ): ?>
					<div class="ld-login-modal-branding">
						<img src="<?php echo esc_url(wp_get_attachment_url($logo_id)); ?>" alt="<?php echo esc_attr(get_post_meta($logo_id , '_wp_attachment_image_alt', true)); ?>">
					</div>
				<?php endif;

				/**
				 * Action to add custom content after the modal form
				 *
				 * @since 3.0
				 */
				do_action( 'learndash-login-modal-after' ); ?>

			</div> <!--/.ld-login-modal-form-->
		</div> <!--/.ld-login-modal-wrapper-->
	</div> <!--/.ld-login-modal-login-->

	<?php
	if ( $can_register ) : ?>
		<div class="ld-login-modal-register">
			<div class="ld-login-modal-wrapper">
				<div class="ld-content">
					<?php
					/**
					 * Action to add custom content before the register modal heading
					 *
					 * @since 3.0
					 */
					do_action( 'learndash-register-modal-heading-before' ); ?>
					<div class="ld-modal-heading">
						<?php esc_html_e( 'Register', 'learndash' ); ?>
					</div>
					<?php
					/**
					 * Action to add custom content after the register modal heading
					 *
					 * @since 3.0
					 */
					do_action( 'learndash-register-modal-heading-after' ); ?>
					<div class="ld-modal-text">
						<?php esc_html_e( 'Don\'t have an account? Register one!', 'learndash' ); ?>
					</div>
					<?php
					/**
					 * Action to add custom content before the register modal heading
					 *
					 * @since 3.0
					 */
					do_action( 'learndash-register-modal-text-after' );

					$errors = array(
						'has_errors' => false,
						'message' => ''
					);

					$errors_conditions = apply_filters( 'learndash-registration-errors', array(
						'empty_username' => __( 'Registration requires a username.', 'learndash' ),
						'empty_email'	 => __( 'Registration requires a valid email.', 'learndash' ),
						'invalid_username' => __( 'Invalid username.', 'learndash' ),
						'invalid_email'		=> __( 'Invalid email.', 'learndash' )
					) );

					foreach( $errors_conditions as $param => $message ) {

						if( isset($_GET[$param]) && $_GET[$param] ) {
							$errors['has_errors'] = true;
							$errors['message'] .= $message . '<br>';
						}

					}

					if( $errors['has_errors'] ):

						learndash_get_template_part(
							'modules/alert.php',
							array(
								'type'      =>  'warning',
					            'icon'      =>  'alert',
								'message'	=>	$errors['message']
							), true );

							/**
							 * Action to add custom content after the register modal errors
							 *
							 * @since 3.0
							 */
							do_action( 'learndash-register-modal-errors-after', $errors );

					endif; ?>

					<a href="#ld-user-register" class="ld-button ld-button-reverse ld-js-register-account"><?php echo esc_html_e( 'Register an Account', 'learndash' ); ?></a>

					<?php
					/**
					 * Action to add custom content before the register modal heading
					 *
					 * @since 3.0
					 */
					do_action( 'learndash-register-modal-registration-link-after' ); ?>

				</div> <!--/.ld-content-->
				<div id="ld-user-register" class="ld-hide">
					<?php
					/**
					 * Action to add custom content before the register modal heading
					 *
					 * @since 3.0
					 */
					do_action( 'learndash-register-modal-register-form-before' ); ?>
					<form name="registerform" id="registerform" action="<?php echo esc_url( site_url( 'wp-login.php?action=register', 'login_post' ) ); ?>" method="post" novalidate="novalidate">
						<p>
							<label for="user_login"><?php esc_html_e( 'Username', 'learndash' ); ?><br />
							<input type="text" name="user_login" id="user_login" class="input" value="" size="20" /></label>
						</p>
						<p>
							<label for="user_email"><?php esc_html_e( 'Email', 'learndash' ) ?><br />
							<input type="email" name="user_email" id="user_email" class="input" value="" size="25" /></label>
						</p>
						<?php
						/**
						 * Fires following the 'Email' field in the user registration form.
						 *
						 * @since 3.0
						 */
						do_action( 'register_form' );
						do_action( 'learndash_register_form' ); ?>
						<input name="learndash-registration-form" value="true" type="hidden">
						<input name="learndash-registration-form-redirect" type="hidden" value="<?php echo esc_url( apply_filters( 'learndash-registration-form-redirect', get_permalink() ) ); ?>">
						<p id="reg_passmail"><?php esc_html_e( 'Registration confirmation will be emailed to you.', 'learndash' ); ?></p>
						<br class="clear" />
						<input type="hidden" name="redirect_to" value="<?php echo esc_attr( get_the_permalink() ); ?>" />
						<p class="submit"><input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php esc_attr_e( 'Register', 'learndash' ); ?>" /></p>
					</form>
					<?php
					/**
					 * Action to add custom content before the register modal heading
					 *
					 * @since 3.0
					 */
					do_action( 'learndash-register-modal-register-form-after' ); ?>
				</div> <!--/#ld-user-register-->
				<?php
				/**
				 * Action to add custom content before the register modal heading
				 *
				 * @since 3.0
				 */
				do_action( 'learndash-register-modal-register-wrapper-after' ); ?>
			</div> <!--/.ld-login-modal-wrapper-->
		</div> <!--/.ld-login-modal-register-->
	<?php endif; ?>

</div> <!--/.ld-modal-->

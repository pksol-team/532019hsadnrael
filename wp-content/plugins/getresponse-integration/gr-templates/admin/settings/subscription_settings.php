<?php

defined( 'ABSPATH' ) || exit;

/**
 * Display Settings form.
 */
?>

<?php gr_load_template( 'admin/settings/header.php' ); ?>

<h2 class="nav-tab-wrapper"><?php echo gr_get_admin_tabs( 'gr-integration-subscription-settings' ) ?></h2>

<?php gr_load_template( 'admin/settings/partials/messages.php' ); ?>

<form method="post" action="<?php echo admin_url( 'admin.php?page=gr-integration-subscription-settings' ); ?>">
    <table class="form-table subscription-settings gr_box">
        <tbody>
        <tr>
            <td class="subscription-settings-cell">
                <h2><?php _e( 'Add contacts via GetResponse Form Widget', 'Gr_Integration' ); ?></h2>
                <p class="description gr_description">
					<?php _e( 'With this widget, you can add new contacts to your selected GetResponse list when your visitors fill out a contact form.',
						'Gr_Integration' ); ?>
                </p>
				<?php
				gr_load_template( 'admin/settings/partials/subs_via_webform.php' );
				?>
            </td>
        </tr>

        <tr>
            <td class="subscription-settings-cell">
                <h2><?php _e( 'Add contacts via GetResponse forms in blog post', 'Gr_Integration' ); ?></h2>
                <p>
					<?php _e( 'With the GetResponse for WordPress plugin, you can use shortcodes to add a contact form to your blog posts. To add a contact form to blog posts, you need to place a tag wherever you want the form to appear. The tag might look like this:',
						'Gr_Integration' ); ?>
                </p>
				<?php
					gr_load_template( 'admin/settings/partials/subs_via_shortcode.php' );
				?>
            </td>
        </tr>

        <tr>
            <td class="subscription-settings-cell">
                <h2><?php _e( 'Add Contacts from Comment', 'Gr_Integration' ); ?></h2>
                <p class="description gr_description">
					<?php _e( 'Add WordPress visitors to your contact list in GetResponse when they leave a comment and check the opt-in box.',
						'Gr_Integration' ); ?>
                </p>
				<?php
					gr_load_template( 'admin/settings/partials/subs_via_comment.php' );
				?>
            </td>
        </tr>

        <tr>
            <td class="subscription-settings-cell">
				<?php
				if ( true === gr()->buddypress->is_active() ) {
					gr_load_template( 'admin/settings/partials/subs_via_buddypress.php' );
				} else {
					gr_load_template( 'admin/settings/partials/subs_via_registration_page.php' );
				}
				?>
            </td>
        </tr>

		<?php if ( gr()->contactForm7->is_active() ) { ?>

            <tr>
                <td class="subscription-settings-cell">
                    <h2><?php _e( 'Add contacts via Contact Form 7', 'Gr_Integration' ); ?></h2>
                    <p class="description gr_description">
						<?php _e( 'Add subscribers from Contact Form 7 to a GetResponse list when they opt in to your list.',
							'Gr_Integration' ); ?>
                    </p>
					<?php
					gr_load_template( 'admin/settings/contact_form_7.php' );
					?>
                </td>
            </tr>

		<?php } ?>

		<?php if ( gr()->ninjaForms->is_active() ) { ?>

            <tr>
                <td class="subscription-settings-cell">
                    <h2><?php _e( 'Add contacts via Ninja Forms', 'Gr_Integration' ); ?></h2>
                    <p class="description gr_description">
						<?php _e( 'Add contacts to GetResponse from Ninja Forms form submissions when they opt in to your list.',
							'Gr_Integration' ); ?>
                    </p>
					<?php
					gr_load_template( 'admin/settings/ninja_forms.php' );
					?>
                </td>
            </tr>

		<?php } ?>

        <tr>
            <td class="save_button">
                <input
                        type="submit"
                        name="commentSubmit"
                        id="commentSubmit"
                        class="button button-primary"
                        value="<?php _e( 'Save Changes', 'Gr_Integration' ); ?>"
                >
            </td>
        </tr>

        </tbody>
    </table>
</form>

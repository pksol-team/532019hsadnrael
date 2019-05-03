<?php

defined( 'ABSPATH' ) || exit;

/**
 * Display Settings form.
 */
?>

<h2 class="nav-tab-wrapper"><?php echo gr_get_admin_tabs( 'gr-integration-web-form' ) ?></h2>


<div id="poststuff" class="wrap">

	<div id="post-body" class="metabox-holder columns-2">

		<div id="post-body-content">

			<?php gr_load_template( 'admin/settings/partials/messages.php' ); ?>

			<table class="widefat fixed">
				<thead>
				<tr>
					<th><b><?php _e( 'Web Form Shortcode', 'Gr_Integration' ); ?></b></th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td>

						<div style="margin-top: 20px">
							<?php _e( 'With the GetResponse Wordpress plugin, you can use shortcodes to place web forms in blog posts. Simply place the following tag in your post wherever you want the web form to appear:',
									'Gr_Integration' ); ?>
						</div>
						<div style="margin-top: 20px">
							<code>[grwebform url="PUT_WEBFORM_URL_HERE" css="on/off" center="on/off" center_margin="200"/]</code>
						</div>
						<div style="margin-top: 20px">
							<b><?php _e( 'Allowed attributes', 'Gr_Integration' ); ?>:</b>
							<br/>
							<code>CSS</code>
							- <?php _e( 'Set this parameter to ON, and the form will be displayed in a GetResponse format; set it to OFF, and the form will be displayed in a standard Wordpress format. Allowed only for legacy forms.',
									'Gr_Integration' ); ?>
							<br/>
							<code>CENTER</code>
							- <?php _e( 'Set this parameter to ON, and the form will be centralized; set it to OFF, and the form will be displayed in the standard left side without margin.',
									'Gr_Integration' ); ?>
							<br/>
							<code>CENTER_MARGIN</code>
							- <?php _e( 'Set this parameter to customize margin (element width) [Default is 200px] ',
									'Gr_Integration' ); ?>
							<br/>
							<code>VARIANT</code>
							- <?php _e( 'Set this parameter to customize form variant, allowed values: A-H. Variants can be set in your GetResponse panel. Not allowed for legacy forms.',
									'Gr_Integration' ); ?>
						</div>

						<div style="margin-top: 20px" class="GR_img_webform_shortcode"></div>

					</td>
				</tr>
				</tbody>
			</table>

		</div>

		<div id="postbox-container-1" class="postbox-container">
			<div id="side-sortables" class="meta-box-sortables ui-sortable">

			</div>
		</div>
	</div>
</div>
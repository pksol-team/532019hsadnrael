<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Display Settings form.
 */
?>

<?php gr_load_template( 'admin/settings/header.php' ); ?>

<h2 class="nav-tab-wrapper"><?php echo gr_get_admin_tabs( 'gr-integration-status' ) ?></h2>

<?php gr_load_template( 'admin/settings/partials/messages.php' ); ?>

<div class="wrap">

	<h1><?php _e( 'GetResponse Account', 'Gr_Integration' ); ?></h1>
	<p>Use the GetResponse API key to connect your site and GetResponse</p>

	<form method="post" action="<?php echo admin_url( gr()->settings->page_url ); ?>">
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="api_key">
							<?php _e( 'API Key', 'Gr_Integration' ); ?>
						</label>
					</th>
					<td>
						<input 
							name="api_key"
							type="text"
							id="api_key"
							class="regular-text"
							value="<?php echo gr_get_option( 'api_key' ) ?>"
						>
						<p class="description">
							<?php _e( 'Your API key is part of the settings of your GetResponse account.', 'Gr_Integration' ); ?>
						</p>
						<p class="description">
							<?php _e( 'Log into your GetResponse acocunt and go to', 'Gr_Integration ' ); ?>
							<strong>
								<?php _e( 'Profile > Integration & API > API', 'Gr_Integration ' ); ?>
							</strong>
							<?php _e( 'to find the key.', 'Gr_Integration '); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="enterprise">
							<?php _e( 'Enterprise', 'Gr_Integration' ); ?>
						</label>
					</th>
					<td>
						<label>
							<input
								id="getresponse_360_account"
								type="checkbox"
								name="getresponse_360_account"
								id="getresponse_360_account"
								value="1"
								<?php if ( '1' === gr_get_option( 'getresponse_360_account' ) ) : ?>
									checked="checked"
								<?php endif ?>
							/>
							I have the Enterprise package
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="accountType">
							<?php _e( 'Account type', 'Gr_Integration' ); ?>
						</label>
					</th>
					<td>
						<select name="accountType" id="accountType" 
							<?php if ( '1' !== gr_get_option( 'accountType' ) ) : ?> disabled <?php endif ?>
						>
							<option
	                        	value="<?php echo Api::API_URL_360_PL; ?>"
	                        	<?php selected( gr_get_option( 'accountType' ), Api::API_URL_360_PL ); ?>
	                        >
								<?php _e( 'GetResponse Enterprise PL', 'Gr_Integration' ); ?>
							</option>
							<option
								value="<?php echo Api::API_URL_360_COM; ?>"
								<?php selected( get_option( 'accountType' ), Api::API_URL_360_COM ); ?>
							>
								<?php _e( 'GetResponse Enterprise COM', 'Gr_Integration' ); ?>
							</option>
						</select>						
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="domain">
							<?php _e( 'Your domain', 'Gr_Integration' ); ?>
						</label>
					</th>
					<td>
						<input
							name="domain"
							type="text"
							id="domain"
							class="regular-text"
							<?php if ( '1' !== gr_get_option( 'accountType' ) ) : ?> disabled <?php endif ?>
							value="<?php echo gr_get_option( 'domain' ) ?>"
						>
						<p class="description">
							<?php _e( 'Enter your domain name without the protocol (https://) eg: "example.com"' ); ?>
						</p>		
					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit">
			<input
				type="submit"
				name="Submit"
				id="submit"
				class="button button-primary"
				value="<?php _e( 'Connect', 'Gr_Integration' ); ?>"
			>
		</p>
	</form>
</div>
<script>

	if (window.canRunAds === undefined) {
		jQuery('#GrDetails').append('\nAdBlock : active');
	}

	var enterpriseCheckbox = jQuery('#getresponse_360_account'),
		accountTypeSelect = jQuery('#accountType'),
		domainInput = jQuery('#domain');

	enterpriseCheckbox.on('change', function () {
		var _this = jQuery(this);

		if (_this.attr('checked')) {
			accountTypeSelect.removeAttr('disabled');
			domainInput.removeAttr('disabled');
		} else {
			accountTypeSelect.attr('disabled', 'disabled');
			domainInput.attr('disabled', 'disabled');
		}
	});
</script>

<?php

defined( 'ABSPATH' ) || exit;

$apiKey = gr_get_option( 'api_key' );
$apiKey = strlen($apiKey) > 0 ? str_repeat("*", strlen($apiKey) - 6) . substr($apiKey, -6) : '';

?>

<?php gr_load_template( 'admin/settings/header.php' ); ?>

<h2 class="nav-tab-wrapper"><?php echo gr_get_admin_tabs( 'gr-integration-status' ) ?></h2>

<?php gr_load_template( 'admin/settings/partials/messages.php' ); ?>

<div class="wrap">
	<h1><?php _e( 'GetResponse account data', 'Gr_Integration' ); ?></h1>
	<table class="widefat importers striped gr_box form-table">
		<tbody>
			<tr>
				<td>
					<span><?php _e( 'Status', 'Gr_Integration' ); ?></span>
				</td>
				<td class="desc">
					<span><?php _e( 'CONNECTED', 'Gr_Integration' ); ?></span><br />
					<span>
						<a id="diconnectAccountButton" onclick="return confirm('Please confirm you want to disconnect your account. This will stop sending data to GetResponse')" href="<?php echo admin_url('admin.php?page=gr-integration-status&action=disconnect') ?>">
							<?php _e( 'Disconnect', 'Gr_Integration' ) ?>
						</a>
					</span>
				</td>
			</tr>
			<tr>
				<td>
					<span><?php _e( 'API Key', 'Gr_Integration' ); ?></span>
				</td>
				<td class="desc">
					<span><?php echo $apiKey ?></span>
				</td>
			</tr>

			<?php if (null !== gr_get_option('account_first_name') && null !== gr_get_option('account_last_name')) :?>

				<tr>
					<td>
						<span><?php _e( 'Name', 'Gr_Integration' ); ?></span>
					</td>
					<td class="desc">
						<span>
							<?php echo gr_get_option('account_first_name') . ' ' . gr_get_option('account_last_name') ?>
						</span>
					</td>
				</tr>

			<?php endif ?>

			<?php if (null !== gr_get_option('account_email')) :?>

				<tr>
					<td>
						<span><?php _e( 'Email', 'Gr_Integration' ); ?></span>
					</td>
					<td class="desc">
						<span><?php echo gr_get_option('account_email') ?></span>
					</td>
				</tr>

			<?php endif ?>

			<?php if (null !== gr_get_option('account_company_name')) :?>

				<tr>
					<td>
						<span><?php _e( 'Company', 'Gr_Integration' ); ?></span>
					</td>
					<td class="desc">
						<span><?php echo gr_get_option('account_company_name') ?></span>
					</td>
				</tr>

			<?php endif ?>

			<?php if (null !== gr_get_option('account_phone')) :?>

				<tr>
					<td>
						<span><?php _e( 'Phone', 'Gr_Integration' ); ?></span>
					</td>
					<td class="desc">
						<span><?php echo gr_get_option('account_phone') ?></span>
					</td>
				</tr>

			<?php endif ?>

			<tr>
				<td>
					<span><?php _e( 'Address', 'Gr_Integration' ); ?></span>
				</td>
				<td class="desc">
					<span>
						<?php 
							echo gr_get_option('account_street') . ' ' .
							gr_get_option('account_zip_code') . ' ' .
							gr_get_option('account_city') . ' ' .
							gr_get_option('account_state') . ' ' .
							gr_get_option('account_country_name');
						?>
					</span>
				</td>
			</tr>
		</tbody>
	</table>
</div>

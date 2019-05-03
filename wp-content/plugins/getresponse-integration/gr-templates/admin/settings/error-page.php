<?php

defined( 'ABSPATH' ) || exit;
?>

<h2 class="nav-tab-wrapper"><?php echo gr_get_admin_tabs( 'gr-integration-status' ) ?></h2>


<div id="poststuff" class="wrap">

	<div id="post-body" class="metabox-holder columns-2">

		<div id="post-body-content">

			<?php gr_load_template( 'admin/settings/partials/messages.php' ); ?>

			<table class="widefat fixed">
				<thead>
				<tr>
					<th><b><?php _e( 'GetResponse Plugin - API Error', 'Gr_Integration' ); ?></b></th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td style="width: 50%; vertical-align: top">

						<table>
							<tbody>
								<tr>
									<td>
										<label class="GR_label" for="api_key">
											<?php _e( 'CURL extension:', 'Gr_Integration' ); ?>
										</label>
									</td>
									<td>
										<?php if ( false === gr()->valid_curl_extension() ) : ?>
											<span class="not-connected-status"><?php _e( 'NOT INSTALLED', 'Gr_Integration' ); ?></span>
										<?php else : ?>
											<span class="connected-status"><?php _e( 'INSTALLED', 'Gr_Integration' ); ?></span>
										<?php endif ?>

										<a class="gr-tooltip">
											<span class="gr-tip">
												<span>
													<?php _e( 'GetResponse Integration Plugin requires PHP cURL extension',
														'Gr_Integration' ) ?>
												</span>
											</span>
										</a>
									</td>
								</tr>
								<tr>
									<td>
										<label class="GR_label" for="api_key">
											<?php _e( 'GetResponse API:', 'Gr_Integration' ); ?>
										</label>
									</td>
									<td>
										<?php if ( false === gr()->is_connected_to_getresponse() ) : ?>
											<span class="not-connected-status"><?php _e( 'NOT AVAILABLE', 'Gr_Integration' ); ?></span>
										<?php else : ?>
											<span class="connected-status"><?php _e( 'AVAILABLE', 'Gr_Integration' ); ?></span>
										<?php endif ?>
									</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
				</tbody>
			</table>

			<?php if ( false === gr()->is_connected_to_getresponse() ) : ?>
				<table class="widefat fixed second-table">
					<thead>
					<tr>
						<th><b>API Error details</b></th>
					</tr>
					</thead>
					<tbody>
					<tr>
						<td>

							<table>
								<tbody>
								<tr>
									<td>
										<label class="GR_label">
											<?php _e( 'Traceroute result', 'Gr_Integration' ); ?>:
										</label>
									</td>
									<td>
										<div class="GR_traceroute" id="GrTraceroutResult">
											<img src="images/loading.gif"/>
											<?php _e( 'Receiving data, please be patient', 'Gr_Integration' ); ?>...
										</div>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<?php _e( 'Please', 'Gr_Integration' ); ?>
										<a href="<?= esc_html( gr()->contact_form_url ) ?>" target="_blank">
											<strong><?= __( 'CONTACT US', 'Gr_Integration' ) ?></strong>
										</a>
										<?php _e( 'and send error code/message and traceroute result.',
											'Gr_Integration' ); ?>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<a href="<?php echo admin_url('admin.php?page=gr-integration-status') ?>">
											<?php esc_html_e( 'Back to GetResponse Plugin site', 'Gr_Integration' ) ?>
										</a>
									</td>
								</tr>
								</tbody>
							</table>
						</td>
					</tr>
					</tbody>
				</table>
			<?php endif; ?>

		</div>

		<div id="postbox-container-1" class="postbox-container">

		</div>
	</div>
</div>
<script>
	jQuery(document).ready(function ($) {
		$.ajax({
			url: 'admin-ajax.php',
			data: {
				'action': 'gr-traceroute-submit'
			},
			success: function (response) {
				$('#GrTraceroutResult').html(response.success);
			},
			error: function (errorThrown) {
				$('#GrTraceroutResult').html(errorThrown);
			}
		});
	});
</script>
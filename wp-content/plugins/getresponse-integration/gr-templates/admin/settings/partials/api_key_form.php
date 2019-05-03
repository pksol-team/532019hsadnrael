<?php

defined( 'ABSPATH' ) || exit;

?>

<table>
	<tbody>
	<!-- API Key -->
	<tr>
		<td>
			<label class="GR_label" for="api_key">
				<?php _e( 'Status', 'Gr_Integration' ); ?>:
			</label>
		</td>
		<td>
			<span class="not-connected-status"><?php _e( 'NOT CONNECTED', 'Gr_Integration' ); ?></span>
		</td>
	</tr>
	<tr>
		<td>
			<label class="GR_label" for="api_key">
				<?php _e( 'API Key:', 'Gr_Integration' ); ?>
			</label>
		</td>
		<td>
			<input
                id="api_key"
				class="GR_api"
				type="text"
				name="api_key"
				value="<?php echo gr_get_option( 'api_key' ) ?>"/>

			<a class="gr-tooltip">
				<span class="gr-tip">
					<span>
						<?php _e( 'Enter your API key. You can find it on your GetResponse profile in Account Details -> GetResponse API.',
							'Gr_Integration' ); ?>
					</span>
				</span>
			</a>
		</td>
	</tr>
</table>
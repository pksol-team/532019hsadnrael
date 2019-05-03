<?php

/**
 * RSS template.
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="account-info postbox">
	<h3><?php _e( 'GetResponse RSS', 'Gr_Integration' ) ?></h3>

	<div class="inside">
		<table>
			<tbody id="the-list2">
			<tr class="active" id="">
				<td class="desc">
					<?php
					/**
					 * Load RSS feeds.
					 */
					gr_rss_feeds(); ?>
				</td>
			</tr>
			</tbody>
		</table>
	</div>
</div>
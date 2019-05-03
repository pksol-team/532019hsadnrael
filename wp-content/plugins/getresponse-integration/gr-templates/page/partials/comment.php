<?php

/**
 * Display GetResponse checkbox on comment box.
 */
defined( 'ABSPATH' ) || exit;

$checked = gr_get_option( 'comment_checked' );
?>
<br />
<p>
	<input class="GR_checkbox"
           value="1"
           id="gr_comment_checkbox"
           type="checkbox"
	       name="gr_comment_checkbox"
        <?php if ( $checked ) : ?> checked="checked"<?php endif; ?> />
	<?php echo gr_get_option( 'comment_checkout_label' ); ?>
</p><br/>

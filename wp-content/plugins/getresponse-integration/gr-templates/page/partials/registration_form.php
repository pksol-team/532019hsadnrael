<?php

/**
 * Display GetResponse checkbox on comment box.
 */
defined( 'ABSPATH' ) || exit;

$checked = gr_get_option( 'registration_checked' );
?>
<p class="form-row form-row-wide gr-comment-checkbox">
	<label for="gr_registration_checkbox" class="checkbox">
		<input class="input-checkbox GR_registrationbox" value="1" id="gr_registration_checkbox" type="checkbox"
		       name="gr_registration_checkbox" <?php if ( $checked )
		       { ?>checked="checked"<?php } ?> />
		<?php echo gr_get_option( 'registration_checkout_label' ); ?>
	</label>
</p><br/>

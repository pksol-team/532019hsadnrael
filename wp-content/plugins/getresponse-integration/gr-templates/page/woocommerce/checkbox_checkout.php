<?php

defined( 'ABSPATH' ) || exit;

$checked = gr_get_option( 'checkout_checked' );
?>
	<p class="form-row form-row-wide gr-wc-checkbox">
        <label
                for="gr_checkout_checkbox"
                class="checkbox">
            <input
                    class="input-checkbox GR_checkoutbox"
                    value="1"
                    id="gr_checkout_checkbox"
                    type="checkbox" name="gr_checkout_checkbox"
			        <?php if ( $checked ) : ?>checked="checked"<?php endif ?> />
			<span><?php echo gr_get_option( 'checkout_label' ); ?></span>
        </label>
	</p>
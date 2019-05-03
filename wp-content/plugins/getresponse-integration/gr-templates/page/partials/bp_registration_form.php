<?php

defined( 'ABSPATH' ) || exit;

$bp_checked = gr_get_option( 'bp_registration_checked' );
?>
<div class="gr_bp_register_checkbox" style="clear:both">
	<label>
		<input
			class="input-checkbox GR_bpbox"
			value="1"
			id="gr_bp_checkbox"
			type="checkbox"
			name="gr_bp_checkbox"
			<?php if ( $bp_checked ) : ?> checked="checked" <?php endif; ?>
		/>
		<span class="gr_bp_register_label">
			<?php echo gr_get_option( 'bp_registration_label' ); ?>
		</span>
	</label>
</div>

<?php

defined( 'ABSPATH' ) || exit;

?>

<p>
    <code>[grwebform url="PUT_WEBFORM_URL_HERE" css="on/off" center="on/off"
        center_margin="200"/]</code>
</p>
<br/>
<h4><?php _e( 'To add the tag,', 'Gr_Integration' ); ?></h4>
<ol>
    <li>Find the place within the post where you want to add the form.</li>
    <li>Select a GetResponse form from the dropdown.
		<div class="tooltip">
		  <span class="tooltip-text">
			<img src="<?php echo gr()->asset_path . '/img/select-getresponse-form.png' ?>" alt="Select GetResponse Form" />
		  </span>
		</div>
    </li>
    <li>Click <strong>Update</strong> when you're done.</li>
</ol>
<br/>
<p>Optionally, you can define the format by adding modifications in the form HTML code string. 
	<a id="attributesBtn" class="attributes-btn"><?php _e( 'See allowed attributes', 'Gr_Integration' ) ?></a>
</p>
<table id="allowedAttributes" class="allowed-attributes striped hidden">
	<tr>
		<th>
			<span><?php _e( 'CSS', 'Gr_Integration' ); ?></span>
		</th>
		<td>
			<span>
				<?php _e( 'Set this parameter to ON to display the form in the GetResponse format. Set it to OFF to display the form in the standard Wordpress format. This works only for legacy forms.', 'Gr_Integration' ); ?>
			</span>
		</td>
	</tr>
	<tr>
		<th>
			<span><?php _e( 'CENTER', 'Gr_Integration' ); ?></span>
		</th>
		<td>
			<span>
				<?php _e( 'Set this parameter to ON to center the form. Set it to OFF to align the form to the left.', 'Gr_Integration' ); ?>
			</span>
		</td>
	</tr>
	<tr>
		<th>
			<span><?php _e( 'CENTER_MARGIN', 'Gr_Integration' ); ?></span>
		</th>
		<td>
			<span>
				<?php _e( 'Set this parameter to change margins (element width) [Default size is 200px]', 'Gr_Integration' ); ?>
			</span>
		</td>
	</tr>
	<tr>
		<th>
			<span><?php _e( 'VARIANT', 'Gr_Integration' ); ?></span>
		</th>
		<td>
			<span>
				<?php _e( 'Set this parameter to customize form variant, allowed values: A-H. Variants can be set in your GetResponse panel. Not allowed for legacy forms.', 'Gr_Integration' ); ?>
			</span>
		</td>
	</tr>
</table>

<script>
	var attributesBtn = jQuery('#attributesBtn');
	var allowedAttributesTable = jQuery('#allowedAttributes');

	attributesBtn.on('click', function() {
		var _this = jQuery(this);

		if (allowedAttributesTable.is(":hidden")) {
			_this.before('<h4 class="inline-table"><?php _e( 'Allowed attributes:', 'Gr_Integration' ); ?></h4>');
			_this.text(' (<?php _e( 'hide', 'Gr_Integration' ); ?>)');
		} else {
			_this.prev().remove();
			_this.text('<?php _e( 'See allowed attributes', 'Gr_Integration' ); ?>');
		}
		allowedAttributesTable.toggle();
	});
</script>

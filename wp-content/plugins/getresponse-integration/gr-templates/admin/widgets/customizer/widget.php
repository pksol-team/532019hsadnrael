<?php

/**
 * Widget in Customizer section.
 */
defined( 'ABSPATH' ) || exit;

?>
<div style="margin-top: 10px" id="gr_title">
	<label for="<?php echo gr()->int_widget->get_field_id( 'title' ); ?>">
		<?php _e( 'Title' ); ?>:
	</label>
	<input class="widefat"
       id="<?php echo gr()->int_widget->get_field_id( 'title' ); ?>"
       name="<?php echo gr()->int_widget->get_field_name( 'title' ); ?>"
       type="text" value="<?php echo $title; ?>"/>
</div>

<div style="margin-top: 10px" class="gr_webform_select" style="display: none;">
	<label for="<?php echo gr()->int_widget->get_field_id( 'select' ); ?>">
		<?php _e( 'Web Form' ); ?>:
	</label>
	<select style="width: 98%"
		name="<?php echo gr()->int_widget->get_field_name( 'select' ); ?>"
		id="<?php echo gr()->int_widget->get_field_id( 'select' ); ?>"
		style="max-width: 278px;"
		class="widefa"
		<?php /* onchange="customizerSetVariants(jQuery(this));" */ ?>
		>

		<optgroup
			label="<?php _e( 'New Forms' ); ?>" id="gr-optgroup-new"></optgroup>
		<optgroup
			label="<?php _e( 'Old Web Forms' ); ?>" id="gr-optgroup-old"></optgroup>
	</select>
</div>

<div style="margin-top: 10px" id="gr_css_style">
	<input
		id="<?php echo gr()->int_widget->get_field_id( 'style' ); ?>"
		name="<?php echo gr()->int_widget->get_field_name( 'style' ); ?>"
		type="checkbox"
		value="1" <?php checked( '1', $style ); ?> />

	<label for="<?php echo gr()->int_widget->get_field_id( 'style' ); ?>">
		<?php _e( 'Use Wordpress CSS styles (Old Web Forms)', 'Gr_Integration' ); ?>
	</label>
</div>

<div style="margin-top: 10px" id="gr_center">
	<input
		id="<?php echo gr()->int_widget->get_field_id( 'center' ); ?>"
		name="<?php echo gr()->int_widget->get_field_name( 'center' ); ?>"
		type="checkbox"
		value="1" <?php checked( '1', $center ); ?> />

	<label for="<?php echo gr()->int_widget->get_field_id( 'center' ); ?>">
		<?php _e( 'Center Webform', 'Gr_Integration' ); ?>
	</label>

	<label for="<?php echo gr()->int_widget->get_field_id( 'center_margin' ); ?>">
		(<?php _e( 'Margin', 'Gr_Integration' ); ?>:
		<input
			id="<?php echo gr()->int_widget->get_field_id( 'center_margin' ); ?>"
			name="<?php echo gr()->int_widget->get_field_name( 'center_margin' ); ?>"
			type="text"
			value="<?php echo ! empty( $center_margin ) ? $center_margin : '200'; ?>"
			size="4"/>px)
	</label>
</div>

<div style="margin-top: 10px" id="gr_version">
	<input
		id="<?php echo gr()->int_widget->get_field_id( 'version' ); ?>"
		name="<?php echo gr()->int_widget->get_field_name( 'version' ); ?>"
		type="hidden" value="old" size="4"/>
</div>

<script type="text/javascript">

	jQuery(document).ready(function ($) {
		var select_name = '<?php echo gr()->int_widget->get_field_id( 'select' ); ?>';

		customizerGetOldWebForms($('#' + select_name));

		function customizerGetOldWebForms(selector) {

			var parent = selector.parent().parent();
			var variants_loader = parent.find('.gr-loading-select');
			var variants_options = parent.find('.grvariants');
			var select = '<?php echo $select; ?>';

			$.ajax({
				url: 'admin-ajax.php',
				data: {
					'action': 'gr-webforms-submit'
				},
				beforeSend: function () {
					variants_options.hide();
					variants_loader.show();
				},
				success: function (response) {
					if (response.success && response.success.httpStatus !== 404) {
						var html = '';
						$.each(response.success, function (key, obj) {
							if (obj.status == 'enabled') {
								var selected = (obj.webformId == select) ? 'selected="selected"' : '';
								var campaign_name = (obj.campaign.name != undefined) ? ' (' + obj.campaign.name + ')' : '';
								html += '<option data-version="old" id="' + obj.webformId + '" value="' + obj.webformId + '" ' + selected + '>' + obj.name + campaign_name + '</option>';
							}
						});

						html = (html != '') ? html : '<option value="-" disabled>No webforms</option>';
						selector.find('#gr-optgroup-old').html(html);
					}
					else {
						variants_selector.html('<option value="-">-</option>');
					}
				},
				complete: function () {
					variants_loader.hide();
					customizerGetNewWebforms($('#' + select_name));
				}
			});
		}

		function customizerGetNewWebforms(selector) {

			var parent = selector.parent().parent();
			var variants_loader = parent.find('.gr-loading-select');
			var variants_selector = parent.find('.grvariants_select');
			var gr_webform_select = parent.find('.gr_webform_select');
			var variants_options = parent.find('.grvariants');
			var select = '<?php echo $select; ?>';
			var variant = '<?php echo $variants; ?>';

			$.ajax({
				url: 'admin-ajax.php',
				data: {
					'action': 'gr-forms-submit'
				},
				beforeSend: function () {
					variants_options.hide();
					variants_loader.show();
				},
				success: function (response) {
					if (response.success && response.success.httpStatus !== 404) {
						var html = '';
						$.each(response.success, function (key, obj) {
							if (obj.status == 'published') {
								var selected = (obj.formId == select) ? 'selected="selected"' : '';
								var has_variatns = (obj.hasVariants && obj.hasVariants == 'true') ? 1 : 0;
								var campaign_name = (obj.campaign.name != undefined) ? ' (' + obj.campaign.name + ')' : '';
								html += '<option data-version="new" data-variants="' + has_variatns + '" id="' + obj.formId + '" value="' + obj.formId + '" ' + selected + '>' + obj.name + campaign_name + '</option>';
							}
						});

						html = (html != '') ? html : '<option value="-" disabled>No webforms</option>';
						selector.find('#gr-optgroup-new').html(html);
					}
					else {
						variants_selector.html('<option value="-">-</option>');
					}
				},
				complete: function () {
					gr_webform_select.show();
					variants_loader.hide();

					if (variant >= 0) {
//						customizerSetVariants(selector);
					}
				}
			});
		}
	});

</script>
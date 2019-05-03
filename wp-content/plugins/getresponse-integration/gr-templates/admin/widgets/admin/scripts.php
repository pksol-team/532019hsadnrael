<script type="text/javascript">

	jQuery(document).ready(function ($) {
		var select_name = '<?php echo gr()->int_widget->get_field_id( 'select' ); ?>';

		getOldWebForms($('#' + select_name));

		function getNewWebforms(selector) {

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
						setVariants(selector);
					}
				}
			});
		}

		function getOldWebForms(selector) {

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
					getNewWebforms($('#' + select_name));
				}
			});
		}

		function setVariants(selector) {

			var form_id = selector.find(':selected').attr('id');
			var has_variant = selector.find(':selected').attr('data-variants');
			var version = selector.find(':selected').attr('data-version');
			var parent = selector.parent().parent();
			var variants_selector = parent.find('.grvariants_select');
			var variants_loader = parent.find('.gr-loading');
			var variants_options = parent.find('.grvariants');
			var version_options = parent.find('#gr_version input');
			version_options.val(version);

			if (has_variant == '1') {
				var selected_variant = '<?php echo $variants; ?>';
				var select = '<?php echo $select; ?>';

				$.ajax({
					url: 'admin-ajax.php',
					data: {
						'action': 'gr-variants-submit',
						'form_id': form_id
					},
					beforeSend: function () {
						variants_options.hide();
						variants_loader.show();
					},
					success: function (response) {
						if (response.success && response.success.httpStatus !== 404) {
							var html = '';
							$.each(response.success, function (key, obj) {
								if (obj.status == 'enabled' || obj.status == 'published') {

									var selected = (obj.variant == selected_variant) ? 'selected="selected"' : '';
									html += '<option value="' + obj.variant + '" ' + selected + '>' + obj.variantName + '</option>';
								}
							});

							variants_selector.html(html);
							variants_options.show();
						}
						else {
							variants_selector.html('<option value="-">-</option>');
						}
					},
					complete: function () {
						variants_loader.hide();
					}
				});
			}
			else {
				variants_selector.html('<option value="-">-</option>');
				variants_options.hide();
			}
		}
	});

	function setVariants(selector) {

		var form_id = selector.find(':selected').attr('id');
		var has_variant = selector.find(':selected').attr('data-variants');
		var version = selector.find(':selected').attr('data-version');
		var parent = selector.parent().parent();
		var variants_selector = parent.find('.grvariants_select');
		var variants_loader = parent.find('.gr-loading');
		var variants_options = parent.find('.grvariants');
		var version_options = parent.find('#gr_version input');
		version_options.val(version);

		if (has_variant == '1') {
			var selected_variant = '<?php echo $variants; ?>';
			var select = '<?php echo $select; ?>';

			jQuery.ajax({
				url: 'admin-ajax.php',
				data: {
					'action': 'gr-variants-submit',
					'form_id': form_id
				},
				beforeSend: function () {
					variants_options.hide();
					variants_loader.show();
				},
				success: function (response) {

					if (response.success && response.success.httpStatus !== 404) {
						var html = '';
						jQuery.each(response.success, function (key, obj) {
							if (obj.status == 'enabled' || obj.status == 'published') {
								var selected = (obj.variant == selected_variant && obj.formId == select) ? 'selected="selected"' : '';
								html += '<option value="' + obj.variant + '" ' + selected + '>' + obj.variantName + '</option>';
							}
						});

						variants_selector.html(html);
						variants_options.show();
					}
					else {
						variants_selector.html('<option value="-">-</option>');
						variants_options.hide();
					}
				},
				complete: function () {
					variants_loader.hide();
				}
			});
		}
		else {
			variants_selector.html('<option value="-">-</option>');
			variants_options.hide();
		}
	}

</script>
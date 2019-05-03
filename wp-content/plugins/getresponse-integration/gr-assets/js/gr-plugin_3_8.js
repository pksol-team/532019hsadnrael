(function() {
	tinymce.create('tinymce.plugins.GrShortcodes', {

		init : function(ed, url) {
		},
		createControl : function(n, cm) {
			if (n=='GrShortcodes'){
				var mlb = cm.createListBox('GR Web Form Shortcode', {
					title: 'GR Web Form Shortcode',
					onselect : function(v) {
						if (tinyMCE.activeEditor.selection.getContent() == '' && v && v.length>0 && !v.disabled){
							var shortcode = '[grwebform url="' + v + '" css="on"/]';
							tinyMCE.activeEditor.selection.setContent( shortcode )
						}
					}
				});
				if (my_campaigns != null) {
					if (my_webforms != null && my_forms != null) {
						mlb.add(text_forms, {disabled: true});
						addWebform(mlb, my_campaigns, my_forms, 'published');
						if (mlb.items.length == 1) {
							mlb.add(text_no_forms, {disabled: true});
						}
						mlb.add(text_webforms, {disabled: true});
						var webflorm_length = mlb.items.length;
						addWebform(mlb, my_campaigns, my_webforms, 'enabled');
						if (webflorm_length == mlb.items.length) {
							mlb.add(text_no_webforms, {disabled: true});
						}
					}
					else if (my_forms != null) {
						mlb.add(text_forms, {disabled: true});
						addWebform(mlb, my_campaigns, my_forms, 'published');
					}
					else if (my_webforms != null) {
						mlb.add(text_webforms, {disabled: true});
						addWebform(mlb, my_campaigns, my_webforms, 'enabled');
					}

					if (mlb.items.length == 2) {
						mlb.items = [];
						mlb.add(text_forms, {disabled: true});
						mlb.add(text_no_forms, {disabled: true});
						mlb.add(text_webforms, {disabled: true});
						mlb.add(text_no_webforms, {disabled: true});
					}
					return mlb;
				}
			}
			return null;
		},

	});
	if (api_key) {
		tinymce.PluginManager.add('GrShortcodes', tinymce.plugins.GrShortcodes);
	}

	function addWebform(mlb, my_campaigns, webform, status){
		for (var i in webform) {
			if (my_campaigns[i] != null && webform[i].status == status) {
				mlb.add(webform[i].name + ' (' + my_campaigns[i].name + ')', webform[i].scriptUrl);
			}
		}
		return mlb;
	}
})();
(function() {

	if (api_key) {
		tinymce.PluginManager.add('GrShortcodes', function (editor, url) {

			editor.addButton('GrShortcodes', {
				type: 'listbox',
				title: 'GetResponse Web Form integration',
				text: 'GR Web Form',
				values: getValues(),
				onselect: function (v) {
					if (v.control.settings.url != null) {
						var shortcode = '[grwebform url="' + v.control.settings.url + '" css="on" center="off" center_margin="200"/]';
						editor.insertContent(shortcode);
					}
				}
			});
		});
	}

	function getValues() {
		var wf = [];

		wf.push({text:text_forms, url:null, disabled:true});

		setOption(my_forms, wf, 'new');

		wf.push({text:text_webforms, url:null, disabled:true});

		setOption(my_webforms, wf, 'old');

		return wf;
	}

	function setOption(items, wf, ver)
	{
		var no_webforms = (ver == 'new') ? text_no_forms : text_no_webforms;

		if (typeof items !== 'undefined' && items != null) {
			var total = 0;
			for (var i in items) {

				var webforms = {};
				if (typeof items[i] !== 'undefined' && (items[i].status == 'enabled' || items[i].status == 'published'))
				{
					var campaign_name = (typeof items[i].campaign.name !== 'undefined') ? ' (' + items[i].campaign.name + ')' : '';
					webforms.text = items[i].name + campaign_name;
					webforms.url = items[i].scriptUrl;
					wf.push(webforms);
					total++;
				}
			}
			if (total == 0) {
				wf.push({text: ' '+no_webforms, url:null, disabled:true});
			}
		}else {
			wf.push({text:' '+no_webforms, url:null, disabled:true});
		}
	}

})();
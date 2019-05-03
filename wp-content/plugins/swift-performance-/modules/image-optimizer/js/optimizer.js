(function(){
	var bulk = {};
	var enqueued_image_timer = null;

	jQuery(window).on('blur', function(){
		clearTimeout(enqueued_image_timer);
	});
	jQuery(window).on('focus load', function(){
		if (jQuery('.swift-enqueued-image-count').length > 0){
			clearTimeout(enqueued_image_timer);
			update_stat();
		}
	});

	jQuery(function(){
		// Check already selected checkbox for bulk select
		jQuery('body').on('swift-list-table-paginated', function(){
			for (var i in bulk){
				jQuery('#' + bulk[i]['hash']).prop('checked',true);
			}
		});

		// Bulk select
		jQuery(document).on('click', '.swift-performance-list-table input[type="checkbox"]', function(){
			jQuery('.swift-bulk-select-checkbox').each(function(){
				if (jQuery(this).prop('checked')){
					bulk[jQuery(this).attr('id')] = {
						'hash' : jQuery(this).attr('id'),
						'src' : jQuery(this).attr('data-src'),
						'bgsize' : jQuery(this).attr('data-bgsize')
					}
				}
				else if (typeof bulk[jQuery(this).attr('id')] !== 'undefined'){
					delete(bulk[jQuery(this).attr('id')]);
				}
			});

			coverflow('#swift-selected-images', bulk);
		});

		// Select all filtered result
		jQuery(document).on('click', '#swift-optimizer-select-all', function(e){
			e.preventDefault();
			var filtered = JSON.parse(jQuery('#swift-optimizer-ids').text());
			bulk = jQuery.extend(bulk, filtered);
			jQuery('body').trigger('swift-list-table-paginated');
			coverflow('#swift-selected-images', bulk);
		});

		// Dequeue queued images
		jQuery(document).on('click', '#swift-clear-image-queue', function(e){
			e.preventDefault();
			jQuery.post(ajaxurl, {action: 'swift_performance_image_optimizer', 'nonce' : swift_performance_image_optimizer.nonce, 'swift_performance_action' : 'clear_queue'}, function(response){
				update_stat();
			});
		});

		// Clear all selected
		jQuery(document).on('click', '#clear-all-selected-images', function(e){
			e.preventDefault();
			bulk = {};
			jQuery('.swift-performance-list-table input[type="checkbox"]').removeAttr('checked');
			coverflow('#swift-selected-images', bulk);
		});

		// Remove item from flow
		jQuery(document).on('click', '.swift-coverflow-remove-content', function(e){
			e.preventDefault();
			if (typeof bulk[jQuery(this).attr('data-remove')] !== 'undefined'){
				delete(bulk[jQuery(this).attr('data-remove')]);
				jQuery('#' + jQuery(this).attr('data-remove')).removeAttr('checked');
				jQuery(this).closest('li').remove();
			}
		});

		// Scan images
		jQuery(document).on('click', '#swift-performance-scan-images', function(e){
			jQuery('body').addClass('swift-loading');
			jQuery.post(ajaxurl, {action: 'swift_performance_image_optimizer', 'nonce' : swift_performance_image_optimizer.nonce, 'swift_performance_action' : 'load_images'}, function(response){
				jQuery('body').removeClass('swift-loading');
				jQuery('#swift-performance-refresh-list-table').trigger('click');
			});
			e.preventDefault();
		});

		// Optimize images
		jQuery(document).on('click', '#swift-performance-optimize-images', function(e){
			jQuery('body').addClass('swift-loading');
			var data = {
				action: 'swift_performance_image_optimizer',
				'nonce' : swift_performance_image_optimizer.nonce,
				'swift_performance_action' : 'optimize_bulk',
				'images' : Object.keys(bulk),
				'jpeg-quality' : jQuery('[name="jpeg-quality"]').val(),
				'png-quality' : jQuery('[name="png-quality"]').val(),
				'resize-large-images' : (jQuery('[name="resize-large-images"]').prop('checked') ? 1 : 0),
				'maximum-image-width' : jQuery('[name="maximum-image-width"]').val(),
				'keep-original-images' : (jQuery('[name="keep-original-images"]').prop('checked') ? 1 : 0),
			};
			jQuery.post(ajaxurl, data, function(response){
				jQuery('body').removeClass('swift-loading');
				jQuery('#swift-performance-refresh-list-table').trigger('click');
				jQuery('#clear-all-selected-images').trigger('click');
				update_stat();
			});
			e.preventDefault();
		});

		// Restore original images
		jQuery(document).on('click', '#swift-performance-restore-images', function(e){
			jQuery('body').addClass('swift-loading');
			jQuery.post(ajaxurl, {action: 'swift_performance_image_optimizer', 'nonce' : swift_performance_image_optimizer.nonce, 'swift_performance_action' : 'restore_bulk', 'images' : Object.keys(bulk)}, function(response){
				jQuery('body').removeClass('swift-loading');
				jQuery('#swift-performance-refresh-list-table').trigger('click');
				jQuery('#clear-all-selected-images').trigger('click');
				update_stat();
			});
			e.preventDefault();
		});

		// Remove original images
		jQuery(document).on('click', '#swift-performance-delete-original-images', function(e){
			jQuery('body').addClass('swift-loading');
			jQuery.post(ajaxurl, {action: 'swift_performance_image_optimizer', 'nonce' : swift_performance_image_optimizer.nonce, 'swift_performance_action' : 'remove_bulk', 'images' : Object.keys(bulk)}, function(response){
				jQuery('body').removeClass('swift-loading');
				jQuery('#swift-performance-refresh-list-table').trigger('click');
				jQuery('#clear-all-selected-images').trigger('click');
			});
			e.preventDefault();
		});

		// Single optimize
		jQuery(document).on('click', '.swift-performance-list-table a.single-optimize', function(e){
	            e.preventDefault();
	            var button = jQuery(this);
	            var row = jQuery(button).closest('tr');
	            jQuery(button).closest('td').addClass('swift-loading');

			var data = {
				action: 'swift_performance_image_optimizer',
				'nonce' : swift_performance_image_optimizer.nonce,
				'swift_performance_action' : 'optimize_bulk',
				'images' : jQuery(button).attr('data-hash'),
				'jpeg-quality' : jQuery('[name="jpeg-quality"]').val(),
				'png-quality' : jQuery('[name="png-quality"]').val(),
				'resize-large-images' : (jQuery('[name="resize-large-images"]').prop('checked') ? 1 : 0),
				'maximum-image-width' : jQuery('[name="maximum-image-width"]').val(),
				'keep-original-images' : (jQuery('[name="keep-original-images"]').prop('checked') ? 1 : 0),
			};
			jQuery.post(ajaxurl, data, function(response){
				update_table_row(row, response);
				jQuery(button).closest('td').removeClass('swift-loading');
				update_stat();
			});
	      });

		// Quickview
		jQuery(document).on('click', '.swift-image-optimizer-preview.quickview', function(e){
			e.preventDefault();
			var button = jQuery(this);
			jQuery('body').addClass('swift-loading');
			jQuery.post(ajaxurl, {action: 'swift_performance_image_optimizer', 'nonce' : swift_performance_image_optimizer.nonce, 'swift_performance_action' : 'quickview', 'hash' : jQuery(button).attr('data-hash')}, function(html){
				jQuery('body').removeClass('swift-loading');
				jQuery('body').append(html);
				jQuery('.swift-quickview').removeClass('swift-hidden');
			});
		});

		// Show original/optimized
		jQuery(document).on('click', '.swift-toggle-quickview', function(){
			jQuery(this).closest('.thumbnail').find('.details-image, .swift-toggle-quickview span').toggleClass('swift-hidden');
		});

		// Close quickview modal
	      jQuery(document).on('click', '.swift-modal-close', function(e){
	            e.preventDefault();
	            jQuery(this).closest('.swift-modal').remove();
	      });

		// Single restore
		jQuery(document).on('click', '.swift-performance-list-table a.single-restore', function(e){
			e.preventDefault();
			var button = jQuery(this);
			var row = jQuery(button).closest('tr');
			jQuery(button).closest('td').addClass('swift-loading');
			jQuery.post(ajaxurl, {action: 'swift_performance_image_optimizer', 'nonce' : swift_performance_image_optimizer.nonce, 'swift_performance_action' : 'restore_single', 'hash' : jQuery(button).attr('data-hash')}, function(response){
				update_table_row(row, response);
				jQuery(button).closest('td').removeClass('swift-loading');
			});
		});

		// Single remove original
		jQuery(document).on('click', '.swift-performance-list-table a.remove-original', function(e){
			e.preventDefault();
			var button = jQuery(this);
			var row = jQuery(button).closest('tr');
			jQuery(button).closest('td').addClass('swift-loading');
			jQuery.post(ajaxurl, {action: 'swift_performance_image_optimizer', 'nonce' : swift_performance_image_optimizer.nonce, 'swift_performance_action' : 'remove_original', 'hash' : jQuery(button).attr('data-hash')}, function(response){
				update_table_row(row, response);
				jQuery(button).closest('td').removeClass('swift-loading');
			});
		});

		// Single Exclude
		jQuery(document).on('click', '.swift-performance-list-table a.single-exclude', function(e){
			e.preventDefault();
			var button = jQuery(this);
			var row = jQuery(button).closest('tr');
			jQuery(button).closest('td').addClass('swift-loading');
			jQuery.post(ajaxurl, {action: 'swift_performance_image_optimizer', 'nonce' : swift_performance_image_optimizer.nonce, 'swift_performance_action' : 'restore_single', 'hash' : jQuery(button).attr('data-hash')}, function(response){
				update_table_row(row, response);
				jQuery(button).closest('td').removeClass('swift-loading');
			});
		});

		// Open settings
		jQuery(document).on('click', '.swift-custom-settings-trigger', function(e){
			e.preventDefault();
			jQuery('.swift-custom-settings-container').toggleClass('opened');
			jQuery('body').toggleClass('swift-overlay');
		});

		// Close settings
		jQuery(document).on('click','body.swift-overlay', function(e){
			if (jQuery(e.target).closest('.swift-custom-settings-container').length > 0){
				return;
			}
			jQuery(this).removeClass('swift-overlay');
			jQuery('.swift-custom-settings-container').removeClass('opened');
		});
		jQuery(document).on('click','#swift-close-custom-settings', function(e){
			e.preventDefault();
			jQuery('body').removeClass('swift-overlay');
			jQuery('.swift-custom-settings-container').removeClass('opened');
		});

		// Reset settings to global
		jQuery(document).on('click', '#swift-reset-custom-settings', function(e){
			// Image Quality
			jQuery('[name="jpeg-quality"]').val(jQuery('[name="jpeg-quality"]').attr('data-global'));
			jQuery('[name="png-quality"]').val(jQuery('[name="png-quality"]').attr('data-global'));

			// Resize images
			if (jQuery('[name="resize-large-images"]').attr('data-global') == 1){
				jQuery('[name="resize-large-images"]').prop('checked', true);
				jQuery('[name="maximum-image-width"]').val(jQuery('[name="maximum-image-width"]').attr('data-global'));
			}
			else {
				jQuery('[name="resize-large-images"]').removeAttr('checked');
			}

			// Keep original images
			if (jQuery('[name="keep-original-images"]').attr('data-global') == 1){
				jQuery('[name="keep-original-images"]').prop('checked', true);
			}
			else {
				jQuery('[name="keep-original-images"]').removeAttr('checked');
			}
			jQuery('.swift-custom-settings input[type="range"]').trigger('input');
		});

		// Adjust quality manually
		jQuery(document).on('change', '.swift-range-slider__value', function(){
			var slider	= jQuery(this).prev();
			var value	= jQuery(this).val();
			var min	= jQuery(slider).attr('min');
			var max	= jQuery(slider).attr('max');

			// Min value
			if (min){
				value = Math.max(min, value);
			}

			// Max value
			if (max){
				value = Math.min(max, value);
			}


			jQuery(slider).val(value);
			jQuery(this).val(value);
		});

		// Range slider
		(function(){
		  var slider = jQuery('.swift-range-slider'),
		      range = jQuery('.swift-range-slider__range'),
		      value = jQuery('.swift-range-slider__value');

		  slider.each(function(){

		    value.each(function(){
		      var value = jQuery(this).prev().attr('value');
		      jQuery(this).html(value);
		    });

		    range.on('input', function(){
		      jQuery(this).next(value).val(this.value);
		    });
		  });
	  	})();
	});

	/**
	 * Localization
	 * @param string text
	 * @return string
	 */
	function __(text){
		if (typeof swift_performance_image_optimizer.i18n[text] !== 'undefined'){
			return swift_performance_image_optimizer.i18n[text];
		}
		else {
			return text;
		}
	}

	/**
	 * Show selected images in coverflow
	 */
	function coverflow(container, images){
		var c = 0;
		jQuery('.swift-coverflow-max-number-message').addClass('swift-hidden');
		jQuery('.selected-images-count').empty();
		jQuery(container).empty();

		if (Object.keys(images).length > 0){
			jQuery('.selected-images-count').text(Object.keys(images).length);
		}

		for (var i in images){
			c++;
			if (c > 1000){
				jQuery('.swift-coverflow-max-number-message').removeClass('swift-hidden');
				return;
			}
			jQuery(container).append(
				jQuery('<li>').append(
					jQuery('<span>', {
						'class'	: 'swift-coverflow-content',
						'style'	: 'background-image: url(' + images[i]['src'] + ');background-size:' + images[i]['bgsize'] + ';'
					}),
					jQuery('<a>', {
						'class'		: 'swift-coverflow-remove-content',
						'href'		: '#',
						'html'		: '&times;',
						'data-remove'	: images[i]['hash']
					}),
				)
			);
		}
	}

	/**
	 * Update table row based on server response
	 * @param DOMElement row
	 * @param object response
	 */
	function update_table_row(row, response){
		for (var i in response){
			if (response[i]['action'] == 'hide'){
				jQuery(row).find(response[i]['target']).addClass('swift-hidden');
			}
			if (response[i]['action'] == 'show'){
				jQuery(row).find(response[i]['target']).removeClass('swift-hidden');
			}

		}
	}

	/**
	 * Refresh enqueued image counter
	 */
	function update_stat(){
		jQuery.post(ajaxurl, {action: 'swift_performance_image_optimizer', 'nonce' : swift_performance_image_optimizer.nonce, 'swift_performance_action' : 'image_stat'}, function(response){
			var duration = (Math.abs(response['queued'] - jQuery('.swift-enqueued-image-count').val()) > 10 ? 5000 : 1000);

			// Hide init nag
			if (response['total'] > 0){
				jQuery('#swift-image-init').addClass('swift-hidden');
			}

			// Queue count
			jQuery('#swift-clear-image-queue').addClass('swift-hidden');
			if (response['queued'] > 0){
				jQuery('#swift-clear-image-queue').removeClass('swift-hidden');
			}
			jQuery('.swift-enqueued-image-count').swiftCount(response['queued'], duration, 'linear');
			clearTimeout(enqueued_image_timer);
			enqueued_image_timer = setTimeout(update_stat, 5000);

			// Optimized count
			jQuery('.swift-optimized-image-count').swiftCount(response['optimized'], duration, 'linear');

			// Saved count
			jQuery('.saved-space-count').swiftCount(response['formatted_save'], duration, 'linear');

			// Pie chart
			var percent = Math.round((response['optimized']/Math.max(1,response['total'])*100),0);
			if (!isNaN(percent)){
				jQuery('.swift-pie-chart .swift-pie-fill').css('stroke-dasharray', (percent == 100 ? percent + 1 : percent) + ' 100');
				jQuery('.swift-pie-chart-container .swift-counter').swiftCount(percent, 2000);
			}

			// Bar chart
			jQuery('.swift-bar-chart.original-size label > span').swiftCount(response['formatted_original_size'], duration, 'linear');

			jQuery('.swift-bar-chart.optimized-size .swift-bar-chart-bar-outer').animate({ width: Math.min(100,parseInt(response['current_size']/response['original_size'] * 100)) + "%" }, {duration: 2000, easing:'swing'});
			jQuery('.swift-bar-chart.optimized-size label > span').swiftCount(response['formatted_current_size'], duration, 'linear');
		});
	}

})();

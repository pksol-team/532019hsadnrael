jQuery(function(){
      var _interval, cache_status_interval;

      jQuery(window).on('blur', function(){
		clearTimeout(cache_status_interval);
	});
	jQuery(window).on('focus load', function(){
            // Cache status
            if (jQuery('#swift-cache-status-box').length > 0){
                  clearTimeout(cache_status_interval);
                  cache_status();
            }
	});

      // Show pointers if any
      jQuery(window).on('load', pointers);

      // Fire cron if WP cron is disabled
      if (swift_performance.cron.length > 0){
            jQuery.get(swift_performance.cron);
            setInterval(function(){
                  jQuery.get(swift_performance.cron);
            },60000);
      }

      // Clear messages and container if any buttons was clicked
      jQuery(document).on('click', '.swift-performance-control', function(){
            if (jQuery(this).hasClass('clear-response-container')){
                  jQuery('.swift-preformatted-box .response-container').empty();
            }
            clear_messages();
      });

      jQuery(document).on('click', '.swift-box-close', function(){
            jQuery(this).closest('.swift-box').addClass('swift-hidden');
      });

      // Initialize charts and counters
      jQuery(window).on('load', function(){
            // Pie chart
            if (jQuery('.swift-pie-chart').length > 0){
                  jQuery('.swift-pie-chart').each(function(index, element) {
                        var that = jQuery(this);
                        var num = +(jQuery(that).attr('data-value'));
                        jQuery(that).html('<svg class="swift-pie" viewBox="0 0 32 32"><circle class="swift-pie-fill" r="16" cx="16" cy="16" style="stroke-dasharray: 0 100" /></svg>');
                        setTimeout(function(){
                              jQuery(that).find('.swift-pie-fill').css('stroke-dasharray', num + ' 100');
                        },1);
                  });

            }

            // Bar chart
            jQuery(".swift-bar-chart-bar .swift-bar-chart-bar-outer").each(function() {
                  var that = jQuery(this);
                  if (!jQuery(that).hasClass("swift-animated")) {
                    jQuery(that).addClass("swift-animated");
                    jQuery(that).animate({ width: jQuery(that).attr("data-value") + "%" }, {duration: 2000, easing:'swing'});
                  }
            });

            // Run counters
            jQuery('.swift-counter').each(function() {
                  jQuery(this).swiftCount();
            });
      });

      // Increase/decrease threads
      jQuery(document).on('click', '#swift-cache-status-box .change-thread-limit', function(){
            clearInterval(cache_status_interval);
            jQuery('body').addClass('swift-loading');
            var limit = (jQuery(this).hasClass('thread-plus') ? 1 : -1);
            jQuery.post(ajaxurl, {action: 'swift_performance_change_thread_limit', '_wpnonce' : swift_performance.nonce, 'limit' : limit}, function(){
                  cache_status(function(){
                        jQuery('body').removeClass('swift-loading');
                  });
            });
      });

      // Paginate list table
      jQuery(document).on('click', '#swift-performance-list-table-container .pagination-links a, .swift-performance-list-table thead th a', function(e){
            e.preventDefault();
            jQuery('body').addClass('swift-loading');
            jQuery('#swift-performance-list-table-url').val(jQuery(this).attr('href'));
            history.pushState(null, null, jQuery(this).attr('href'));
            jQuery.get(jQuery(this).attr('href'), function(source){
                  var html = jQuery.parseHTML(source);
                  jQuery('#swift-performance-list-table-container').replaceWith(jQuery(html).find('#swift-performance-list-table-container'));
                  jQuery('body').trigger('swift-list-table-paginated');
                  jQuery('body').removeClass('swift-loading');
            });
      });

      // Filter list table
      jQuery(document).on('submit', '.swift-list-table-filter', function(e){
            e.preventDefault();
            jQuery('body').addClass('swift-loading');
            history.pushState(null, null, jQuery(this).attr('action') + '?' + jQuery(this).serialize());
            jQuery.get(jQuery(this).attr('action'), jQuery(this).serialize(), function(source){
                  var html = jQuery.parseHTML(source);
                  jQuery('#swift-performance-list-table-container').replaceWith(jQuery(html).find('#swift-performance-list-table-container'));
                  jQuery('body').removeClass('swift-loading');
            });
      });

      // Refresh warmup
      jQuery(document).on('click', '#swift-performance-refresh-list-table', function(e){
            e.preventDefault();
            jQuery('body').addClass('swift-loading');
            jQuery.get(document.location.href, function(source){
                  var html = jQuery.parseHTML(source);
                  jQuery('#swift-performance-list-table-container').replaceWith(jQuery(html).find('#swift-performance-list-table-container'));
                  jQuery('body').removeClass('swift-loading');
            });
      });

      // Reset warmup
      jQuery(document).on('click', '#swift-performance-reset-warmup', function(e){
            e.preventDefault();
            if (confirm(__('Do you want to reset prebuild links?'))){
                  jQuery('body').addClass('swift-loading');
                  jQuery.post(ajaxurl, {action: 'swift_performance_reset_warmup', '_wpnonce' : swift_performance.nonce}, function(){
                        jQuery('#swift-performance-refresh-list-table').trigger('click');
                  });
            }
      });

      // Clear cache
      jQuery(document).on('click', '.swift-performance-clear-cache', function(e){
            jQuery('body').addClass('swift-loading');
            var type = jQuery(this).attr('data-type');
            jQuery.post(ajaxurl, {action: 'swift_performance_clear_cache', '_wpnonce' : swift_performance.nonce, 'type': type}, function(response){
                  response = (typeof response === 'string' ? JSON.parse(response) : response);

                  jQuery('body').removeClass('swift-loading');
                  show_message(response);
                  jQuery('#swift-performance-refresh-list-table').trigger('click');
            });
            e.preventDefault();
      });

      // Clear assets cache
      jQuery(document).on('click', '#swift-performance-clear-assets-cache', function(e){
            jQuery('body').addClass('swift-loading');
            jQuery.post(ajaxurl, {action: 'swift_performance_clear_assets_cache', '_wpnonce' : swift_performance.nonce}, function(response){
                  response = (typeof response === 'string' ? JSON.parse(response) : response);

                  jQuery('body').removeClass('swift-loading');
                  show_message(response);
            });
            e.preventDefault();
      });

      // Start prebuild cache
      jQuery(document).on('click', '#swift-performance-prebuild-cache', function(e){
            jQuery('#swift-performance-prebuild-cache').addClass('swift-hidden');
            jQuery('#swift-performance-stop-prebuild-cache').removeClass('swift-hidden');
            jQuery('body').addClass('swift-loading');
            jQuery.post(ajaxurl, {action: 'swift_performance_prebuild_cache', '_wpnonce' : swift_performance.nonce}, function(response){
                  response = (typeof response === 'string' ? JSON.parse(response) : response);

                  jQuery('body').removeClass('swift-loading');
                  show_message(response);
            });
            e.preventDefault();
      });

      // Stop prebuild cache
      jQuery(document).on('click', '#swift-performance-stop-prebuild-cache', function(e){
            jQuery('#swift-performance-stop-prebuild-cache').addClass('swift-hidden');
            jQuery('#swift-performance-prebuild-cache').removeClass('swift-hidden');
            jQuery('body').addClass('swift-loading');
            jQuery.post(ajaxurl, {action: 'swift_performance_stop_prebuild_cache', '_wpnonce' : swift_performance.nonce}, function(response){
                  response = (typeof response === 'string' ? JSON.parse(response) : response);

                  jQuery('body').removeClass('swift-loading');
                  show_message(response);
            });
            e.preventDefault();
      });

      // Change prebuild priority
      jQuery(document).on('submit', '.swift-priority-update', function(e){
            e.preventDefault();
            var form = jQuery(this);
            var data = jQuery(form).serialize();
            jQuery(form).closest('td').addClass('swift-loading');
            jQuery.post(ajaxurl, {action: 'swift_performance_update_prebuild_priority', '_wpnonce' : swift_performance.nonce, 'data' : data}, function(response){
                  response = (typeof response === 'string' ? JSON.parse(response) : response);

                  jQuery(form).closest('td').removeClass('swift-loading');
                  show_message(response);
            });
      });

      // Single prebuild
      jQuery(document).on('click', '.swift-performance-list-table .do-cache', function(e){
            e.preventDefault();
            var button = jQuery(this);
            var row = jQuery(button).closest('tr');
            jQuery(button).closest('td').addClass('swift-loading');
            jQuery.post(ajaxurl, {action: 'swift_performance_single_prebuild', '_wpnonce' : swift_performance.nonce, 'url' : jQuery(button).attr('data-url')}, function(response){
                  response = (typeof response === 'string' ? JSON.parse(response) : response);

                  jQuery(button).closest('td').removeClass('swift-loading');
                  show_message(response);
                  update_warmup_row(row, response);
            });
      });

      // Clear single
      jQuery(document).on('click', '.swift-performance-list-table .clear-cache', function(e){
            e.preventDefault();
            var button = jQuery(this);
            var row = jQuery(button).closest('tr');
            jQuery(button).closest('td').addClass('swift-loading');
            jQuery.post(ajaxurl, {action: 'swift_performance_single_clear_cache', '_wpnonce' : swift_performance.nonce, 'url' : jQuery(button).attr('data-url')}, function(response){
                  response = (typeof response === 'string' ? JSON.parse(response) : response);

                  jQuery(button).closest('td').removeClass('swift-loading');
                  show_message(response);

                  // Remove if 404 cleared
                  if (jQuery(button).attr('data-status') == '404'){
                        jQuery(row).remove();
                  }
                  else {
                        update_warmup_row(row, response);
                  }
            });
      });

      // Remove warmup URL
      jQuery(document).on('click', '.remove-warmup-url', function(e){
            e.preventDefault();
            var button = jQuery(this);
            var row = jQuery(button).closest('tr');
            jQuery(button).closest('td').addClass('swift-loading');
            jQuery.post(ajaxurl, {action: 'swift_performance_remove_warmup_url', '_wpnonce' : swift_performance.nonce, 'url' : jQuery(button).attr('data-url')}, function(response){
                  response = (typeof response === 'string' ? JSON.parse(response) : response);

                  jQuery(button).closest('td').removeClass('swift-loading');
                  show_message(response);

                  // Remove if 404 cleared
                  if (response.type == 'success'){
                        jQuery(row).remove();
                  }
                  else {
                        update_warmup_row(row, response);
                  }
            });
      });

      // Add warmup link
      jQuery(document).on('click', '#swift-performance-add-warmup-link', function(e){
            e.preventDefault();
            jQuery('.swift-add-warmup-link-container').removeClass('swift-hidden');
      });

      jQuery(document).on('click', '#swift-performance-cancel-add-warmup-link', function(e){
            e.preventDefault();
            jQuery('.swift-add-warmup-link-container').addClass('swift-hidden');
      });

      jQuery(document).on('click', '#swift-save-warmup-link', function(e){
            e.preventDefault();
            var form = jQuery(this).closest('.field-container');
            jQuery.post(ajaxurl, {action: 'swift_performance_add_warmup_url', '_wpnonce' : swift_performance.nonce, 'url' : jQuery(form).find('[name="url"]').val(), 'priority' : jQuery(form).find('[name="priority"]').val()}, function(response){
                  response = (typeof response === 'string' ? JSON.parse(response) : response);
                  show_message(response)

                  // Link was successfully added
                  if (response.type == 'success'){
                        jQuery(form).find('input').val('');
                        jQuery('#swift-performance-refresh-list-table').trigger('click');
                  }
            });
      });

      // Show Rewrite Rules
      jQuery(document).on('click', '#swift-performance-show-rewrite', function(e){
            clearInterval(_interval);

            jQuery('body').addClass('swift-loading');
            jQuery.post(ajaxurl, {action: 'swift_performance_show_rewrites', '_wpnonce' : swift_performance.nonce}, function(response){
                  response = (typeof response === 'string' ? JSON.parse(response) : response);
                  show_message(response);

                  jQuery('.swift-preformatted-box').removeClass('swift-hidden');
                  jQuery('.swift-preformatted-box h3 .title').text(response.title);
                  jQuery('.swift-preformatted-box pre.response-container').text(response.rewrites);
                  jQuery('body').removeClass('swift-loading');
            });
            e.preventDefault();
      });

      // Show Log
      jQuery(document).on('click', '#swift-performance-log', function(e){
            clearInterval(_interval);

            jQuery('body').addClass('swift-loading');
            jQuery.post(ajaxurl, {action: 'swift_performance_show_log', '_wpnonce' : swift_performance.nonce}, function(response){
                  response = (typeof response === 'string' ? JSON.parse(response) : response);
                  show_message(response);

                  jQuery('.swift-preformatted-box').removeClass('swift-hidden');
                  jQuery('.swift-preformatted-box h3 .title').text(response.title);
                  jQuery('.swift-preformatted-box pre.response-container').text(response.status);
                  jQuery('body').removeClass('swift-loading');
            });
            _interval = setInterval(function(){
                  var scroll_top = jQuery('.swift-preformatted-box .response-container').scrollTop();
                  jQuery.post(ajaxurl, {action: 'swift_performance_show_log', '_wpnonce' : swift_performance.nonce}, function(response){
                        response = (typeof response === 'string' ? JSON.parse(response) : response);

                        jQuery('.swift-preformatted-box pre.response-container').text(response.status);
                        jQuery('.swift-preformatted-box .response-container').scrollTop(scroll_top);
                  });
            }, 5000);
            e.preventDefault();
      });

      // Clear logs
      jQuery(document).on('click', '#swift-performance-clear-logs', function(e){
            if (confirm(__('Do you want to clear all logs'))){
                  jQuery('body').addClass('swift-loading');
                  jQuery.post(ajaxurl, {action: 'swift_performance_clear_logs', '_wpnonce' : swift_performance.nonce}, function(){
                        jQuery('#swift-performance-log').trigger('click');
                  });
            }
      });

      // Developer Mode
      jQuery(document).on('click', '#swift-performance-toggle-developer-mode', function(e){
            e.preventDefault();
            jQuery('body').addClass('swift-loading');
            jQuery.post(ajaxurl, {action: 'swift_performance_toggle_dev_mode', '_wpnonce' : swift_performance.nonce}, function(response){
                  response = (typeof response === 'string' ? JSON.parse(response) : response);

                  jQuery('body').removeClass('swift-loading');
                  jQuery('#swift-performance-toggle-developer-mode > span').toggleClass('swift-hidden');
                  show_message(response);
            });
      });

      /*
       * DB Optimizer
       */

      // Backup confirmation
      jQuery(document).on('click','.swift-confirm-backup', function(e){
            e.preventDefault();
            jQuery('.swift-dashboard').removeClass('content-blurred');
            jQuery(this).parent().remove();
      });

      // Ajax actions
      jQuery(document).on('click', '.swift-db-optimizer-action', function(e){
            e.preventDefault();
            var action  = jQuery(this).attr('id');
            var count   = jQuery(this).closest('ul').find('.count');
            jQuery(count).html('<span class="dashicons dashicons-update swift-spin"></span>');
            jQuery.post(ajaxurl, {'action': 'swift_performance_db_optimizer', 'swift-action': action, '_wpnonce' : swift_performance.nonce}, function(response){
                  jQuery(count).html(response);
            });
      });

      // Toggle schedule form
      jQuery(document).on('click', '.swift-toggle-scheduled-dbo', function(e){
            e.preventDefault();
            jQuery('#schedule-' + jQuery(this).attr('data-option')).toggleClass('swift-hidden');
      });

      // Change status for scheduled event
      jQuery(document).on('click', '.swift-scheduled-dbo-change', function(e){
            e.preventDefault();
            var option  = jQuery(this).closest('form').find('[name="option"]').val();
            var data    = jQuery(this).closest('form').serialize();
            data        += '&action=swift_performance_db_optimizer&swift-action=' + jQuery(this).attr('data-action') + '&_wpnonce=' + swift_performance.nonce;

            jQuery.post(ajaxurl, data, function(response){
                  jQuery('#trigger-' + option).html(response);
            });

            jQuery(this).closest('form').addClass('swift-hidden');
      });

      /*
       * Critical Font
       */

      // Select icons
      jQuery(document).on('click', '.swift-font-selector li', function(){
            jQuery(this).toggleClass('active');
      });

      // Filter
      jQuery(document).on('keypress focus blur change', '.swift-critical-font-filter', function(){
            var container     = jQuery(this).closest('.swift-critical-font-container');
            var that          = jQuery(this);

            setTimeout(function(){
                  var key = jQuery(that).val();
                  if (key == ''){
                        jQuery(container).find('.swift-font-selector li').removeClass('swift-hidden');
                  }

                  jQuery(container).find('.swift-font-selector li').each(function(){
                        if (jQuery(this).attr('data-selector').match(key)){
                              jQuery(this).removeClass('swift-hidden');
                        }
                        else {
                              jQuery(this).addClass('swift-hidden');
                        }
                  });
            }, 100);
      });

      // Clear font filter
      jQuery(document).on('click', '.swift-clear-critical-font-filter', function(){
            jQuery(this).closest('.swift-critical-font-container').find('.swift-critical-font-filter').val('').trigger('change');
      });

      // Clear selected icons
      jQuery(document).on('click', '.swift-critical-font-clear-all', function(e){
            e.preventDefault();
            jQuery(this).closest('.swift-critical-font-container').find('.swift-font-selector li.active').removeClass('active');
      });

      // Enqueue font
      jQuery(document).on('click', '.swift-enqueue-critical-font', function(e){
            e.preventDefault();
            jQuery('body').addClass('swift-loading');
            var css           = '';
            var content       = [];
            var active        = [];
            var container     = jQuery(this).closest('.swift-critical-font-container');
            var font          = jQuery(container).attr('data-font');
            jQuery(container).find('.swift-font-selector li').each(function(){
                  if (jQuery(this).hasClass('active')){
                        css += jQuery(this).attr('data-selector') + '{content:"' + jQuery(this).attr('data-content') + '"}';
                        content.push(jQuery(this).attr('data-content'));
                        active.push(jQuery(this).attr('data-class'));
                  }
            });
            jQuery.post(ajaxurl, {'action': 'swift_performance_enqueue_critical_font', 'font': font, 'css': css, 'content': content, 'active': active, '_wpnonce' : swift_performance.nonce}, function(response){
                  response = (typeof response === 'string' ? JSON.parse(response) : response);

                  jQuery(container).find('.status').text(response.status_message)
                  jQuery(container).find('.swift-dequeue-critical-font').removeClass('swift-hidden');

                  show_message(response);
                  jQuery('body').removeClass('swift-loading');
            });
      });

      // Dequeue font
      jQuery(document).on('click', '.swift-dequeue-critical-font', function(e){
            e.preventDefault();
            jQuery('body').addClass('swift-loading');
            var container     = jQuery(this).closest('.swift-critical-font-container');
            var font          = jQuery(container).attr('data-font');
            jQuery.post(ajaxurl, {'action': 'swift_performance_dequeue_critical_font', 'font': font, '_wpnonce' : swift_performance.nonce}, function(response){
                  response = (typeof response === 'string' ? JSON.parse(response) : response);

                  jQuery(container).find('.status').text(response.status_message)
                  jQuery(container).find('.swift-dequeue-critical-font').addClass('swift-hidden');
                  jQuery(container).find(response.selector).addClass('active');
                  show_message(response);
                  jQuery('body').removeClass('swift-loading');
            });
      });

      // Scan used icons
      jQuery(document).on('click', '.swift-scan-used-icons', function(e){
            e.preventDefault();
            jQuery('body').addClass('swift-loading');
            var container     = jQuery(this).closest('.swift-critical-font-container');
            var font          = jQuery(container).attr('data-font');
            jQuery.post(ajaxurl, {'action': 'swift_performance_scan_used_icons', 'font': font, '_wpnonce' : swift_performance.nonce}, function(response){
                  response = (typeof response === 'string' ? JSON.parse(response) : response);

                  if (typeof response.selectors !== 'undefined'){
                        for (var i in response.selectors){
                              console.log('[data-selector*="'+response.selectors[i]+'"]');
                              jQuery(container).find('[data-selector*="'+response.selectors[i]+'"]').addClass('active');
                        }
                  }

                  show_message(response);
                  jQuery('body').removeClass('swift-loading');
            });
      });


      // Edit Plugin Rule
      jQuery(document).on('click', '.swift-performance-edit-plugin-rule', function(e){
            e.preventDefault();
            var summary       = '';
            var container     = jQuery(this).closest('.swift-box-inner');
            if (jQuery(this).closest('.plugin-rule').find('select').length > 0){
                  jQuery(this).closest('.plugin-rule').find('select option:selected').each(function(){
                        summary += jQuery(this).text() + ', ';
                  });
                  summary = summary.replace(/,\s$/, '');
            }
            else {
                  summary = jQuery(this).closest('.plugin-rule').find('input').val();
            }

            if (summary == ''){
                summary = __('Not set');
            }

            if (jQuery(this).closest('.plugin-rule').hasClass('is-editing')){
                  save_plugin_organizer_rule();
            }

            jQuery(this).closest('.plugin-rule').toggleClass('is-editing');
            jQuery(container).toggleClass('disabled');
            jQuery(this).closest('.plugin-rule').find('.rule-summary').empty().text(summary);
      });

      // Cancel editing
      jQuery(document).on('click', '.cancel-editing', function(e){
            e.preventDefault();
            jQuery(this).closest('.plugin-rule').removeClass('is-editing');
            jQuery(this).closest('.swift-box-inner').removeClass('disabled');
      });

      // Remove Plugin Rule
      jQuery(document).on('click', '.swift-performance-remove-plugin-rule', function(e){
           e.preventDefault();
           jQuery(this).closest('.swift-box-inner').removeClass('disabled');
           jQuery(this).closest('.plugin-rule').remove();
           save_plugin_organizer_rule();
      });

      // Show rule help
      jQuery(document).on('change', '.rule-mode-selector', function(){
            var mode = jQuery(this).val();
            if (mode !== ''){
                  jQuery(this).closest('.swift-box-inner').find('.swift-plugin-rule-help').addClass('swift-hidden');
                  jQuery(this).closest('.swift-box-inner').find('.swift-plugin-rule-help.swift-help-' + mode).removeClass('swift-hidden');
            }
      });
      jQuery('.rule-mode-selector').trigger('change');

      // Add Disable Plugin Rule
      jQuery(document).on('click', '.swift-add-plugin-rule', function(e){
            e.preventDefault();
            var container     = jQuery(this).closest('.swift-box-inner');
            var type          = jQuery(container).find('.rule-mode-selector option:selected').attr('data-type');
            var mode          = jQuery(container).find('.rule-mode-selector').val();
            var slug          = jQuery(container).attr('data-plugin');
            var clone         = jQuery('#swift-plugin-rule-samples').find('.' + mode + '-sample').clone();

            var randid        = parseInt(Math.random()*1000000000);

            if (mode !== ''){
                  if (jQuery(clone).hasClass('editable')){
                        jQuery(clone).addClass('is-editing');
                        jQuery(container).addClass('disabled');
                  }
                  jQuery(clone).find('input, select').each(function(){
                        jQuery(this).attr('name', jQuery(this).attr('name').replace('%SLUG%', slug));
                        jQuery(this).attr('name', jQuery(this).attr('name').replace('%TYPE%', type));
                        jQuery(this).attr('name', jQuery(this).attr('name').replace('%RANDID%', randid));
                        if (type == 'exception'){
                              jQuery(clone).find('i.fa-ban').attr('class', 'fa fa-check');
                        }
                  });

                  jQuery(clone).appendTo(jQuery(container).find('ul.rule-container'));
                  if (!jQuery(clone).hasClass('is-editing')) {
                        save_plugin_organizer_rule();
                  }
            }
      });

      /**
       * Save plugin organizer rule
       */
       function save_plugin_organizer_rule(){
            jQuery('body').addClass('swift-loading');
            jQuery.post(document.location.href, jQuery('#plugin-organizer').serialize(), function(){
                  jQuery('body').removeClass('swift-loading');
            });
       }

      /**
       * Show Cache Status
       */
      function cache_status(callback){
            jQuery.post(ajaxurl, {action: 'swift_performance_cache_status', '_wpnonce' : swift_performance.nonce}, function(response){
                  response = (typeof response === 'string' ? JSON.parse(response) : response);

                  if (typeof response.text !== 'undefined' && response.text.length > 0){
                        jQuery('.swift-message').removeClass('swift-hidden');
                        jQuery('.swift-message').addClass(response.type).text(response.text);
                  }

                  jQuery('#swift-cache-status-box .prebuild-status').text(response.prebuild);
                  if (response.prebuild == ''){
                        jQuery('#swift-performance-stop-prebuild-cache').addClass('swift-hidden');
                        jQuery('#swift-performance-prebuild-cache').removeClass('swift-hidden');
                  }
                  else {
                        jQuery('#swift-performance-prebuild-cache').addClass('swift-hidden');
                        jQuery('#swift-performance-stop-prebuild-cache').removeClass('swift-hidden');
                  }

                  jQuery('#swift-cache-status-box .warmup-pages-count').text(response.all_pages);
                  jQuery('#swift-cache-status-box .cached-pages-count').text(response.cached_pages);

                  jQuery('#swift-cache-status-box .cache-size-count').text(response.size);
                  jQuery('#swift-cache-status-box .thread-count').html(response.threads);

                  jQuery('#swift-cache-status-box .ajax-object-count').text(response.ajax_objects);
                  jQuery('#swift-cache-status-box .ajax-size-count').text(response.ajax_size);

                  jQuery('#swift-cache-status-box .cached-dynamic-pages-count').html(response.dynamic_pages);
                  jQuery('#swift-cache-status-box .cached-dynamic-size-count').html(response.dynamic_size);
                  if (typeof callback === 'function'){
                        callback();
                  }

                  clearTimeout(cache_status_interval);
                  cache_status_interval = setTimeout(cache_status, 5000);

            });
      }

      /**
       * Update warmup table row
       * @param Object row
       * @param Object response
       */
      function update_warmup_row(row, response){
            // Status
            if (typeof response.status !== 'undefined'){
                  jQuery(row).find('.column-status .dashicons').addClass('swift-hidden');
                  if (response.status == 'html'){
                        jQuery(row).find('.view-cached').removeClass('swift-hidden');
                        jQuery(row).find('.column-status .dashicons-yes').removeClass('swift-hidden');
                        jQuery(row).find('.do-cache').addClass('swift-hidden');
                        jQuery(row).find('.clear-cache').removeClass('swift-hidden');
                  }
                  else {
                        jQuery(row).find('.view-cached').addClass('swift-hidden');
                        jQuery(row).find('.column-status .dashicons-no').removeClass('swift-hidden');
                        jQuery(row).find('.do-cache').removeClass('swift-hidden');
                        jQuery(row).find('.clear-cache').addClass('swift-hidden');
                  }
            }

            // Date
            if (typeof response.date !== 'undefined'){
                  jQuery(row).find('.column-date').empty().text(response.date);
            }
      }

      /**
       * Show message if any
       * @param object response
       */
      function show_message(response){
            if (typeof response.text !== 'undefined' && response.text.length > 0){
                  jQuery('.swift-message').removeClass('success warning critical swift-hidden').addClass(response.type).text(response.text);
                  var message_top = jQuery('.swift-message').offset().top - 42;
                  if (jQuery("html").scrollTop() > message_top || jQuery("body").scrollTop() > message_top){
                        jQuery("html, body").animate({scrollTop: message_top});
                  }
                  setTimeout(clear_messages,3000);
            }
      }

      /**
       * Clear messages
       */
      function clear_messages(){
            jQuery('.swift-message').empty();
            jQuery('.swift-message').attr('class', 'swift-message swift-hidden');
      }

      /**
       * Add new meta box row
       */
      jQuery(document).on('click', '.swift-meta-box-group .add-new-row', function(e){
            e.preventDefault();
            var row = jQuery(this).closest('.swift-meta-box-group').find('.sample').clone().removeClass('swift-hidden sample');
            jQuery(row).insertBefore(this);
      });

       /**
        * Delete meta box row
        */
      jQuery(document).on('click', '.swift-meta-box-group .remove-row', function(e){
            e.preventDefault();
            jQuery(this).closest('.swift-meta-box-row').remove();
      });

      /**
       * Show tooltips
       */
      function pointers(){
            var item = jQuery('[data-swift-pointer]:first');
            if (jQuery(item).length > 0){
                  jQuery(item).pointer({
                        content: jQuery(item).attr('data-swift-pointer-content'),
                        position: jQuery(item).attr('data-swift-pointer-position'),
                        buttons: function( event, t ) {
      				var close  = swift_performance.i18n['Dismiss'],
      					button = jQuery('<a class="close" href="#">' + close + '</a>');

      				return button.bind( 'click.pointer', function(e) {
      					e.preventDefault();
      					t.element.pointer('close');
      				});
      			},
                        hide: function( event, t ) {
      				t.pointer.hide();
      				t.closed();
                              jQuery.post(ajaxurl, {'action': 'swift_performance_dismiss_pointer', 'id': jQuery(item).attr('data-swift-pointer'), '_wpnonce': swift_performance.nonce})
      			},
                  }).pointer('open');
            }
      }

      /* FRAMEWORK CUSTOMIZATIONS */

      // Hide info popups on tab chane
      jQuery('.luv-framework-tab').on('luv-tab-changed', function(){
            jQuery('.wp-pointer').css('display', 'none');
      });

      // Settings mode switch
      jQuery(document).on('change', '.swift-settings-mode input', function(){
            jQuery('[name="_luv_settings-mode"]').val(jQuery('.swift-settings-mode input:checked').val()).trigger('change');
      });

      //Image Optimizer Preset buttons
      jQuery(document).on('change', '.swift-performance-io-preset', function(){
            jQuery('[name="_luv_jpeg-quality"]').val(jQuery(this).attr('data-jpeg')).trigger('change');
            jQuery('[name="_luv_png-quality"]').val(jQuery(this).attr('data-png')).trigger('change');
            if (jQuery(this).attr('data-jpeg') * 1 < 100){
                  jQuery('[name="_luv_resize-large-images"]').attr('checked', true).trigger('change');
                  jQuery('[name="_luv_maximum-image-width"]').val('1920').trigger('change');
            }
            else {
                  jQuery('[name="_luv_resize-large-images"]').removeAttr('checked').trigger('change');
            }
      });

      // Clear cache after change settings
      jQuery(document).on('change', '.should-clear-cache input, .should-clear-cache select, .should-clear-cache textarea', function(){
            jQuery('.luv-framework-container.swift-performance-settings').attr('data-clear-cache', 'true');
            jQuery('.swift-performance-ajax-preview').addClass('swift-visible');
      });

      jQuery(document).on('luv-saved', '.luv-framework-container.swift-performance-settings', function(){
            if (jQuery(this).attr('data-clear-cache')){
                  jQuery('.luv-modal').empty().append(jQuery('.swift-confirm-clear-cache').clone().removeClass('luv-hidden')).removeClass('luv-modal-hide').show();
            }
            jQuery('.swift-performance-ajax-preview').removeClass('swift-visible');
            jQuery(this).removeAttr('data-clear-cache');
      });

      // Reset Image Optimizer Presets
      jQuery(document).on('luv-reset', '.luv-framework-tab', function(){
            if (jQuery(this).find('#io-preset-lossless').length > 0){
                  jQuery(this).find('#io-preset-lossless').trigger('click');
            }
      });

      // Preview
      jQuery(document).on('click', '.swift-performance-ajax-preview', function(e){
            e.preventDefault();
            jQuery('body').addClass('swift-loading');

            jQuery.post(luv_framework_fields.ajax_url, _serialize(jQuery(this).attr('data-fieldset')) + '&action=' + 'swift_performance_preview&_wpnonce=' + swift_performance.nonce, function(response){
                  jQuery('.luv-framework-section-header .has-issues').each(function(){
                        jQuery(this).removeClass('has-issues').removeClass('has-error').removeClass('has-warning');
                  });
                  window.open(response.url);
                  jQuery('body').removeClass('swift-loading');
            });
      });

      // Clear cache
      jQuery(document).on('click', '[data-swift-clear-cache]', function(e){
		e.preventDefault();
            if (jQuery(this).closest('.luv-modal').length > 0){
                  jQuery(this).closest('.luv-modal').addClass('luv-modal-hide');
            }
            else if (jQuery(this).closest('[data-message-id]').length > 0){
                  jQuery.post(ajaxurl, {action: 'swift_performance_dismiss_notice', '_wpnonce' : swift_performance.nonce, 'id': jQuery(this).closest('[data-message-id]').attr('data-message-id')});
                  jQuery(this).closest('[data-message-id]').fadeOut();
            }

            jQuery.post(ajaxurl, {action: 'swift_performance_clear_cache', '_wpnonce' : swift_performance.nonce, 'type': 'all'}, function(response){
                  response = (typeof response === 'string' ? JSON.parse(response) : response);

                  var result = (response.type == 'success' ? 'luv-success' : 'luv-error');
      		var notice = jQuery('<div>', {
                        'class': 'luv-framework-notice ' + result,
                  }).append(jQuery('<span>', {
                        'class': 'luv-framework-notice-inner',
                        'text': response.text
                  }));

                  jQuery('body').append(notice);
                  setTimeout(function(){
                        jQuery(notice).find('.luv-framework-notice-inner').css('max-width', '100%');
                  }, 100);
                  setTimeout(function(){
                        jQuery(notice).remove();
                  }, 5000);
            });
	});

      // Should clear cache after change
      jQuery(document).on('change', '.should-clear-cache input, .should-clear-cache select, .should-clear-cache textarea', function(){
		jQuery('.luv-framework-container.swift-performance-settings').attr('data-clear-cache', 'true');
            jQuery('.swift-performance-ajax-preview').addClass('swift-visible');
	});

	jQuery(document).on('luv-saved', '.luv-framework-container.swift-performance-settings', function(){
		if (jQuery(this).attr('data-clear-cache')){
			jQuery('.luv-modal').empty().append(jQuery('.swift-confirm-clear-cache').clone().removeClass('luv-hidden')).removeClass('luv-modal-hide').show();
		}
            jQuery('.swift-performance-ajax-preview').removeClass('swift-visible');
		jQuery(this).removeAttr('data-clear-cache');
	});

      // Reset Image Optimizer
      jQuery(document).on('luv-reset', '.luv-framework-tab', function(){
            if (jQuery(this).find('#io-preset-lossless').length > 0){
                  jQuery(this).find('#io-preset-lossless').trigger('click');
            }
      });

      // Dismiss notice
      jQuery(document).on('click', '[data-swift-dismiss-notice]', function(){
            jQuery.post(ajaxurl, {action: 'swift_performance_dismiss_notice', '_wpnonce' : swift_performance.nonce, 'id': jQuery(this).closest('[data-message-id]').attr('data-message-id')});
            jQuery(this).closest('[data-message-id]').fadeOut();
      });

      /**
       * Serialize fieldset
       * @param string s
       * @return string
       */
      function _serialize(s){
            var params = '';
            jQuery(s).find('input:not([type="checkbox"]):not([type="radio"]), input[type="checkbox"]:checked, input[type="radio"]:checked , option:selected, textarea').each(function(){
                  var name = (jQuery(this).is('option') ? jQuery(this).closest('select').attr('name') : jQuery(this).attr('name'));
                  params += name + '=' + encodeURIComponent(jQuery(this).val()) + '&';
            });
            return _trim(params, '&');
      }

      /**
       * Trim characters from string
       * @param string s
       * @param string c
       * @return string
       */
      function _trim (s, c) {
       if (c === "]") c = "\\]";
       if (c === "\\") c = "\\\\";
       return s.replace(new RegExp(
          "^[" + c + "]+|[" + c + "]+$", "g"
       ), "");
      }

      /**
       * Localize strings
       * @param string text
       * @return string
       */
      function __(text){
            if (typeof swift_performance.i18n[text] === 'string'){
                  return swift_performance.i18n[text]
            }
            else {
                  return text;
            }
      }
});

/**
 * Counter effect
 * @param int countTo
 */
jQuery.fn.swiftCount = function(countTo, duration, easing){
      var that    = jQuery(this),
      unit        = n(countTo)[1] || n(jQuery(that).attr('data-count'))[1],
      countTo     = n(countTo)[0] || n(jQuery(that).attr('data-count'))[0],
      size        = countTo.split(".")[1] ? countTo.split(".")[1].length : 0,
      duration    = duration || 2000,
      easing      = easing || 'swing';

      jQuery({countNum: n(jQuery(that).text())[0]}).animate({
          countNum: countTo
      },
      {
          duration: duration,
          easing: easing,
          step: function(now) {
            jQuery(that).text(parseFloat(now).toFixed(size) + unit);
          }
      });

      /**
       * Return numeric and unit part of a string
       * @param string text
       * @return array
       */
      function n(text){
            if (typeof text !== 'undefined'){
                  text = text.toString();
                  var v = u = '';
                  for (var i in text){
                        if (u == '' && text[i].match(/[\d\.]/)){
                              v += text[i];
                        }
                        else if (typeof text[i] !== 'undefined'){
                              u += text[i];
                        }
                  }
                  return [v,u];
            }
            return [0,''];
      }
}

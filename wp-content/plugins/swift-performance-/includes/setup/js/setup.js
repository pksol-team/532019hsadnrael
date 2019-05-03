jQuery(function(){

      // Move slide
      jQuery(document).on('click', '.swift-setup-tile a, a[data-swift-setup-slide]', function(e){
            e.preventDefault();
            move_slide(jQuery(this).attr('data-swift-setup-slide'));
      });

      jQuery(document).on('click', 'a.swift-setup-manual-config', function(e){
            e.preventDefault();
            var href    = jQuery(this).attr('href');
            var delay   = play_animations('out');
            setTimeout(function(){
                  document.location.href = href;
            }, delay);
      });

      // Set Purchase Key
      jQuery(document).on('click', '#set-performance-key', function(e){
            e.preventDefault();
            jQuery('body').addClass('swift-loading');
            jQuery.post(swift_performance.ajax_url, {'action': 'swift_performance_setup', 'setup-action': 'set-purchase-key', 'key': jQuery('[name="purchase-key"]').val(), 'nonce': swift_performance.nonce}, function(response){
                  jQuery('body').removeClass('swift-loading');
                  _show_notice(response);
                  if (response.result == 'success'){
                        var delay = play_animations('out');
                        jQuery('.fadeOut').removeClass('fadeOut');
                        jQuery('.swift-image-optimizer').removeClass('swift-hidden');
                        setTimeout(function(){
                              move_slide('dashboard');
                        }, delay);
                  }
            });
      });

      // Set Cloudflare API
      jQuery(document).on('click', '#set-cloudflare-api', function(e){
            e.preventDefault();
            jQuery('body').addClass('swift-loading');
            jQuery.post(swift_performance.ajax_url, {'action': 'swift_performance_setup', 'setup-action': "set-cloudflare-api", 'auto-purge': (jQuery('[name="cloudflare-auto-purge"]:checked').length > 0 ? '1' : '0'), 'cloudflare-email': jQuery('[name="cloudflare-email"]').val(), 'cloudflare-api-key': jQuery('[name="cloudflare-api-key"]').val(), 'nonce': swift_performance.nonce}, function(response){
                  jQuery('body').removeClass('swift-loading');
                  _show_notice(response);
                  if (response.result == 'success'){
                        move_slide('finish');
                  }
            });
      });

      // Import
      jQuery(document).on('change', '.swift-import-file', function(fe) {
            var that = jQuery(this);
            var file = fe.target.files[0];

            if (file) {
                  var reader = new FileReader();
                  reader.onload = function(e) {
            	      jQuery(that).closest('.swift-import-file-container').find('textarea').val(e.target.result);
                        jQuery('.luv-modal').empty().append(jQuery('.luv-framework-confirm-import').clone().removeClass('luv-hidden')).removeClass('luv-modal-hide').show();
                  }
                  reader.readAsText(file);
            } else {
                  jQuery('.luv-modal').empty().append(jQuery('.luv-framework-import-failed').clone().removeClass('luv-hidden')).removeClass('luv-modal-hide').show();
            }
      });

      jQuery(document).on('click', '[data-luv-proceed-import]', function(e){
            e.preventDefault();
            var href = jQuery(this).attr('href');
            jQuery(this).closest('.luv-modal').addClass('luv-modal-hide');
            jQuery('body').addClass('swift-loading');

            jQuery.post(swift_performance.ajax_url, {'action': 'luv_framework_import', 'settings': jQuery('textarea.swift-import').val(), 'nonce': swift_performance.luv_nonce}, function(response){
                  jQuery('body').removeClass('swift-loading');
                  _show_notice(response)
                  if (response.result == 'success'){
                        move_slide('finish');
                  }
            });
      });

      // Preset
      jQuery(document).on('click', '.swift-setup-use-preset', function(e) {
            e.preventDefault();
            var href = jQuery(this).attr('href');
            var preset = jQuery(this).attr('data-preset');
            jQuery('body').addClass('swift-loading');

            jQuery.post(swift_performance.ajax_url, {'action': 'luv_framework_import', 'settings': jQuery('#preset-' + preset).val(), 'nonce': swift_performance.luv_nonce}, function(response){
                  jQuery('body').removeClass('swift-loading');
                  _show_notice(response)
                  if (response.result == 'success'){
                        move_slide('finish');
                  }
            });
      });

      // Autoconfig
      jQuery(document).on('click', '.swift-autoconfig-start', function(e){
            e.preventDefault();

            jQuery('.swift-autoconfig-welcome').addClass('animated').addClass('fadeOut');

            setTimeout(function(){
                  jQuery('.swift-autoconfig-welcome').addClass('swift-hidden');
                  jQuery('.swift-autoconfig').removeClass('swift-hidden').addClass('animated').addClass('fadeIn');
            },800);

            play_animations();
            autoconfig_step();
      });

      function autoconfig_step(){
            var step = jQuery('.swift-autoconfig-list li:not(.done):first');
            jQuery(step).find('i').attr('class', 'fas fa-spinner fa-spin');
            jQuery.post(swift_performance.ajax_url, {'action' : 'swift_performance_setup', 'setup-action': jQuery(step).attr('data-step'), 'nonce': swift_performance.nonce}).always(function(response){
                  jQuery(step).addClass('done');

                  if (response.result == 'success'){
                        jQuery(step).find('i').attr('class', 'fas fa-check');
                  }
                  else if (response.result == 'warning'){
                        jQuery(step).find('i').attr('class', 'fas fa-exclamation-triangle');
                  }
                  else {
                        jQuery(step).find('i').attr('class', 'fas fa-times');
                  }

                  if (typeof response.message !== 'undefined'){
                        jQuery(step).find('.result').html(response.message);
                  }

                  if (typeof response.next_slide !== 'undefined' && response.next_slide !== ''){
                        jQuery('.swift-autoconfig-finish').attr('data-swift-setup-slide', response.next_slide);
                  }

                  if (jQuery('.swift-autoconfig-list li:not(.done)').length > 0){
                        autoconfig_step();
                  }
                  else {
                        jQuery('.swift-autoconfig-finish').addClass('animated').addClass('fadeInUp');
                  }
            });
      }

      jQuery(document).on('click', '#deactivate-plugin', function(e){
            e.preventDefault();
            var href = jQuery(this).attr('href');
            jQuery('body').addClass('swift-loading');
            jQuery.post(swift_performance.ajax_url, {
                  'action': 'swift_performance_setup',
                  'setup-action': 'set-uninstall-options',
                  'keep-settings': (jQuery('#keep-settings:checked').length > 0 ? 1 : 0),
                  'keep-custom-htaccess': (jQuery('#keep-custom-htaccess:checked').length > 0 ? 1 : 0),
                  'keep-warmup-table': (jQuery('#keep-warmup-table:checked').length > 0 ? 1 : 0),
                  'keep-image-optimizer-table': (jQuery('#keep-image-optimizer-table:checked').length > 0 ? 1 : 0),
                  'keep-logs': (jQuery('#keep-logs:checked').length > 0 ? 1 : 0),
                  'nonce': swift_performance.nonce
            }, function(){
                  document.location.href = href;
            });
      });

      /**
       * Helpers
       */

      // Show notice
      function _show_notice(response){
            var result = response.result || '';
            var notice = jQuery('<div>', {
                  'class': 'luv-framework-notice luv-' + result,
            }).append(jQuery('<span>', {
                  'class': 'luv-framework-notice-inner',
                  'text': response.message
            }));

            jQuery('body').append(notice);
            setTimeout(function(){
                  jQuery(notice).find('.luv-framework-notice-inner').css('max-width', '100%');
            }, 100);
            setTimeout(function(){
                  jQuery(notice).remove();
            }, 5000);
      }

      // Move slider
      function move_slide(slide){
            var delay   = play_animations('out');
            if (jQuery(slide)){
                  jQuery('.fadeOut').removeClass('fadeOut');
                  setTimeout(function(){
                        jQuery('.swift-setup-slide').removeClass('active');
                        jQuery('#' + slide).addClass('active');
                        play_animations();
                  },delay);
            }
      }

      // Animations
      function play_animations(direction){
            direction = direction || 'in';
            var delay = 0;
            jQuery('.swift-setup-slide.active [data-animation-' + direction + ']').each(function(){
                  var slide = jQuery(this);
                  setTimeout(function(){
                        jQuery(slide).addClass('animated').addClass(jQuery(slide).attr('data-animation-' + direction));
                  }, delay);
                  delay += 300;
            });
            return jQuery('.swift-setup-slide.active [data-animation-' + direction + ']').length * 300;
      }

      // Initial animations
      play_animations();
});

/* Lettering.JS 0.6.1 by Dave Rupert  - http://daverupert.com */
(function($){function injector(t,splitter,klass,after){var a=t.text().split(splitter),inject='';if(a.length){$(a).each(function(i,item){inject+='<span class="'+klass+(i+1)+'">'+item+'</span>'+after});t.empty().append(inject)}}var methods={init:function(){return this.each(function(){injector($(this),'','char','')})},words:function(){return this.each(function(){injector($(this),' ','word',' ')})},lines:function(){return this.each(function(){var r="eefec303079ad17405c889e092e105b0";injector($(this).children("br").replaceWith(r).end(),r,'line','')})}};$.fn.lettering=function(method){if(method&&methods[method]){return methods[method].apply(this,[].slice.call(arguments,1))}else if(method==='letters'||!method){return methods.init.apply(this,[].slice.call(arguments,0))}$.error('Method '+method+' does not exist on jQuery.lettering');return this}})(jQuery);
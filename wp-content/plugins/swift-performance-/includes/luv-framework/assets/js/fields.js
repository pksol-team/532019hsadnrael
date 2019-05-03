(function(){

      // contain editors on the page
      var editors = [];

      // Depencencies
      var tab_timer, field_timer;

      function check_dependencies(){
            clearTimeout(field_timer);
            field_timer = setTimeout(function(){
                  // Check fields
                  for (var i in luv_framework_fields.dependencies){
                        for (var j in luv_framework_fields.dependencies[i]){
                              var x = luv_framework_fields.dependencies[i][j];
                              if (check_dependency(x.field, x.condition, x.value)){
                                    _enable(i);
                              }
                              else {
                                    _disable(i);
                                    break;
                              }
                        }
                  }
                  // CodeMirror
                  codemirror();
            },1);

            // Hide empty tabs
            clearTimeout(tab_timer);
            tab_timer = setTimeout(function(){
                  jQuery('.luv-framework-section-body').find('.luv-framework-tab').each(function(){
                        if (jQuery(this).find('.luv-framework-field-container:not(.luv-hidden)').length == 0){
                              jQuery('#' + jQuery(this).attr('id') + '-header').addClass('luv-hidden');
                              jQuery(this).addClass('luv-hidden');
                        }
                        else {
                              jQuery('#' + jQuery(this).attr('id') + '-header').removeClass('luv-hidden');
                              jQuery(this).removeClass('luv-hidden');
                        }
                  });
            },1);
      }

      function check_dependency(field, condition, check){
            var value;
            var field = jQuery('[name="' + field + '"]');
            if (typeof field.attr('data-disabled') !== 'undefined'){
                  value = '';
            }
            else if (typeof jQuery(field).attr('type') !== 'undefined' && ['radio', 'checkbox'].indexOf(field.attr('type')) > -1){
                  if (jQuery('[name="' + jQuery(field).attr('name') + '"]:checked').length > 0){
                        value = [];
                        jQuery('[name="' + jQuery(field).attr('name') + '"]:checked').each(function(){
                              value.push(jQuery(this).val());
                        });
                  }
                  else {
                        value = '';
                  }
            }
            else {
                  value = jQuery(field).val();
            }

            switch (condition.toUpperCase()) {
                  case 'NOT_EMPTY':
                        return (value !== '');
                  case 'EQUAL':
                  case 'EQ':
                  case '=':
                  case '==':
                        return (value == check);
                  case '===':
                        return (value === check);
                  case 'NOT_EQUAL':
                  case 'NE':
                  case '!=':
                        return (value != check);
                  case '!==':
                        return (value !== check);
                  case 'CONTAINS':
                        return value.indexOf(check) !== -1;
                  case 'IN':
                        return (value.indexOf(check) !== -1);
                  case 'NOT_IN':
                        return (value.indexOf(check) == -1);
            }
            return false;
      }

      function resetfield(field){
            var type          = jQuery(field).attr('data-type');
            var _default      = (jQuery(field).attr('data-default') ? JSON.parse(window.atob(jQuery(field).attr('data-default'))) : '');

            switch (type) {
                  case 'license':
                        break;
                  case 'checkbox':
                  case 'radio':
                        jQuery(field).find('input[type="checkbox"], input[type="radio"]').each(function(){
                              if (_default.indexOf(jQuery(this).val()) !== -1){
                                    jQuery(this).attr('checked', true);
                              }
                              else {
                                    jQuery(this).attr('checked', false);
                              }
                              jQuery(this).trigger('change');
                        });
                        break;
                  case 'switch':
                        jQuery(field).find('input[type="checkbox"]').each(function(){
                              if (_default == 1){
                                    jQuery(this).attr('checked', true);
                              }
                              else {
                                    jQuery(this).attr('checked', false);
                              }
                              jQuery(this).trigger('change');
                        });
                        break;
                  case 'text':
                  case 'number':
                        jQuery(field).find('input').each(function(){
                              jQuery(this).val(_default);
                              jQuery(this).trigger('change');
                        });
                        break;
                  case 'dropdown':
                        jQuery(field).find('select').each(function(){
                              jQuery(this).find('option').each(function(){
                                    jQuery(this).removeAttr('selected');
                              });
                              if (typeof _default == 'array'){
                                    for (var i in _default){
                                          jQuery(this).find('option[value="' + _default[i] + '"]').prop('selected', true);
                                    }
                              }
                              else {
                                    jQuery(this).find('option[value="' + _default + '"]').prop('selected', true);
                              }


                              jQuery(this).trigger('change');
                        });
                        break;
                  case 'editor':
                        jQuery(field).find('.luv-framework-editor-field').each(function(){
                              var editor = editors[jQuery(this).attr('name')];
                              if (typeof editor !== 'undefined'){
                                    editor.getDoc().setValue(_default);
                                    jQuery(this).trigger('change');
                              }
                        });
                        break;
                  case 'multi-text':
                        _default = (_default == '' ? [] : _default);
                        jQuery(field).find('input').each(function(){
                              jQuery(this).val('');
                              jQuery(this).trigger('change');
                              jQuery(this).closest('.luv-framework-multitext-outer:not(.luv-framework-multitext-sample)').remove();
                        });
                        for (var i in _default){
                              var _x = add_multi_text_field(field);
                              jQuery(_x).find('input').val(_default[i]).trigger('change');
                        }
                        break;
            }
      }

      /**
       * Helpers
       */

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

      function _disable(id, fields){
            var element = document.getElementById(id + '-container');
            document.getElementsByName(id).forEach(function(field){
                  field.setAttribute('data-disabled', 'true');
            });
            if (typeof element !== undefined){
                  element.classList.add('luv-hidden');
            }
      }

      function _enable(id, fields){
            var element = document.getElementById(id + '-container');
            document.getElementsByName(id).forEach(function(field){
                  field.removeAttribute('data-disabled');
            });
            if (typeof element !== undefined){
                  element.classList.remove('luv-hidden');
            }
      }

      function _trigger(element, eventName){
            var event;
            if (document.createEvent) {
              event = document.createEvent("HTMLEvents");
              event.initEvent(eventName, true, true);
            } else {
              event = document.createEventObject();
              event.eventType = eventName;
            }

            event.eventName = eventName;

            if (document.createEvent) {
              element.dispatchEvent(event);
            } else {
              element.fireEvent("on" + event.eventType, event);
            }
      }

      function _closest(el, s) {
            var i;
            var matches = (this.document || this.ownerDocument).querySelectorAll(s);
            do {
                  i = matches.length;
                  while (--i >= 0 && matches.item(i) !== el) {};
            } while ((i < 0) && (el = el.parentElement));
            return el;
      };

      function _serialize(s){
            var params = '';
            jQuery(s).find('input:not([type="checkbox"]):not([type="radio"]), input[type="checkbox"]:checked, input[type="radio"]:checked , option:selected, textarea').each(function(){
                  var name = (jQuery(this).is('option') ? jQuery(this).closest('select').attr('name') : jQuery(this).attr('name'));
                  params += name + '=' + encodeURIComponent(jQuery(this).val()) + '&';
            });
            return _trim(params, '&');
      }

      function _trim (s, c) {
        if (c === "]") c = "\\]";
        if (c === "\\") c = "\\\\";
        return s.replace(new RegExp(
          "^[" + c + "]+|[" + c + "]+$", "g"
        ), "");
      }

      function _ready(callback) {
          document.readyState === "interactive" || document.readyState === "complete" ? callback() : document.addEventListener("DOMContentLoaded", callback);
      }

      function _save(selector, action){
            jQuery(selector).addClass('luv-framework-loading').removeClass('unsaved');
            jQuery(selector).trigger('luv-save');

            jQuery.post(luv_framework_fields.ajax_url, _serialize(selector) + '&action=' + action + '&luv-action=save', function(response){
                  response = (typeof response === 'string' ? JSON.parse(response) : response);

                  jQuery('.luv-field-validation').remove();
                  jQuery('.luv-framework-section-header .has-issues').each(function(){
                        jQuery(this).removeClass('has-issues').removeClass('has-error').removeClass('has-warning');
                  });

                  jQuery(selector).removeClass('luv-framework-loading');
                  _show_notice(response);
                  jQuery(selector).trigger('luv-saved');

                  if (typeof response.issues !== 'undefined'){
                        for (var i in response.issues){
                              var field   = jQuery('#' + luv_framework_fields.prefix + i + '-container');
                              var header  = jQuery('#' + jQuery(field).closest('.luv-framework-tab').attr('id') + '-header');

                              jQuery(header).addClass('has-issues').addClass('has-' + response.issues[i]['type']);;

                              if (jQuery(header).hasClass('subsection')){
                                    jQuery(header).closest('.has-child').addClass('has-issues').addClass('has-' + response.issues[i]['type']);
                              }

                              jQuery(field).before(
                                    jQuery('<div>', {
                                          'class'     : 'luv-field-validation luv-field-validation-' + response.issues[i]['type'],
                                          'text'      : response.issues[i]['value']
                                    })
                              );
                        }
                  }
            });
      }

      function add_multi_text_field(field){
            var clone = jQuery(field).find('.luv-framework-multitext-sample').clone();
            jQuery(clone).removeClass('luv-hidden').removeClass('luv-framework-multitext-sample');
            jQuery(clone).find('input').attr('name', jQuery(clone).find('input').attr('data-name'));
            jQuery(clone).insertBefore(jQuery(field).find('.luv-framework-add-multi-field'));
            return jQuery(clone);
      }

      // Initialize visible codemirror fields
      function codemirror(){

            function mime(key){
                  switch (key){
                        case 'css':
                        return 'text/x-scss';
                  }
                  return 'text/plain';
            }


            if (jQuery('.luv-framework-tab.active > .luv-framework-field-container:not(.luv-hidden) textarea.luv-framework-editor-field:not(.initialized)').length > 0){
                  jQuery('.luv-framework-tab.active > .luv-framework-field-container:not(.luv-hidden) textarea.luv-framework-editor-field:not(.initialized)').each(function(){
                        var editor        = this;
                        var codemirror    = CodeMirror.fromTextArea(editor, {
                            lineNumbers: true,
                            matchBrackets: true,
                            mode: mime(editor.getAttribute('data-mode')),
                            value: editor.value,
                            theme: 'monokai'
                        })
                        codemirror.on('change',function (cm) {
                          jQuery(editor).val(cm.getValue());
                        });

                        editors[jQuery(this).attr('name')] = codemirror;
                        jQuery(editor).addClass('initialized');
                  });
            }
      }

      /**
       * Hooks
       */

      // Show confirm dialog if there are unsaved changes
      window.addEventListener('beforeunload', function (e) {
            if (jQuery('.luv-framework-container.unsaved').length > 0){
                  // Cancel the event as stated by the standard.
                  e.preventDefault();
                  // Chrome requires returnValue to be set.
                  e.returnValue = '';
            }
      });

      jQuery('.luv-framework-container').each(function(){
            var container = jQuery(this);
            jQuery(this).find('input, select, textarea').each(function(){
                  jQuery(this).on('change keyup', function(){
                        jQuery(container).addClass('unsaved');
                        check_dependencies();
                  });
            });
            jQuery(this).on('changed', check_dependencies);
      });

      // Search
      jQuery(document).on('change keyup', '.luv-framework-search', function(e){
            e.preventDefault();
            var keyword = new RegExp(jQuery(this).val(), 'i');
            var fieldset = jQuery(this).attr('data-fieldset');

            jQuery(fieldset).removeClass('luv-search-active');
            jQuery(fieldset).find('.luv-search-match').removeClass('luv-search-match');

            if (jQuery(this).val() != ''){
                  jQuery(fieldset).addClass('luv-search-active');
                  jQuery(fieldset).find('.luv-framework-field-container strong').each(function(){
                        if (jQuery(this).text().match(keyword)){
                              jQuery(this).closest('.luv-framework-field-container').addClass('luv-search-match');
                        }
                  });
            }

      });

      // Save
      jQuery(document).on('click', '.luv-framework-ajax-save', function(e){
            e.preventDefault();
            var selector      = jQuery(this).attr('data-fieldset');
            var action        = jQuery(this).attr('data-action');

            _save(selector, action);
      });

      // Reset section
      jQuery(document).on('click', '.luv-framework-reset-section', function(e){
            e.preventDefault();
            jQuery('.luv-modal').empty().append(jQuery('.luv-confirm-reset-section').clone().removeClass('luv-hidden')).removeClass('luv-modal-hide').show();
      });

      jQuery(document).on('click', '[data-luv-proceed-reset-section]', function(e){
            e.preventDefault();
            jQuery(this).closest('.luv-modal').addClass('luv-modal-hide');

            var selector      = jQuery(this).attr('data-fieldset');
            var action        = jQuery(this).attr('data-action');

            jQuery(selector).addClass('luv-framework-loading');

            jQuery(selector + ' .luv-framework-tab:not(.has-child).active .luv-framework-field-container, ' + selector + ' .luv-framework-tab.subsection.active .luv-framework-field-container').each(function(){
                  resetfield(this);
            });

            jQuery(selector + ' .luv-framework-tab:not(.has-child).active .luv-framework-field-container, ' + selector + ' .luv-framework-tab.subsection.active .luv-framework-field-container').trigger('luv-reset');

            _save(selector, action);
      });

      // Reset all
      jQuery(document).on('click', '.luv-framework-reset-all', function(e){
            e.preventDefault();
            jQuery('.luv-modal').empty().append(jQuery('.luv-confirm-reset-all').clone().removeClass('luv-hidden')).removeClass('luv-modal-hide').show();
      });

      jQuery(document).on('click', '[data-luv-proceed-reset-all]', function(e){
            e.preventDefault();
            var that = jQuery(this);
            jQuery(that).closest('.luv-modal').addClass('luv-modal-hide');

            var selector      = jQuery(that).attr('data-fieldset');
            var action        = jQuery(that).attr('data-action');

            jQuery(selector).addClass('luv-framework-loading');

            setTimeout(function(){
                  jQuery(selector + ' .luv-framework-tab .luv-framework-field-container').each(function(){
                        resetfield(this);
                  });

                  jQuery(selector + ' .luv-framework-tab .luv-framework-field-container').trigger('luv-reset');

                  _save(selector, action);
            }, 1);
      });

      // Import
      jQuery(document).on('change', '.luv-framework-import-file', function(fe) {
            var that = jQuery(this);
            var file = fe.target.files[0];

            if (file) {
                  var reader = new FileReader();
                  reader.onload = function(e) {
            	      jQuery(that).closest('.luv-framework-field-inner').find('textarea').val(e.target.result);
                        jQuery('.luv-modal').empty().append(jQuery('.luv-framework-confirm-import').clone().removeClass('luv-hidden')).removeClass('luv-modal-hide').show();
                  }
                  reader.readAsText(file);
            } else {
                  jQuery('.luv-modal').empty().append(jQuery('.luv-framework-import-failed').clone().removeClass('luv-hidden')).removeClass('luv-modal-hide').show();
            }
      });

      jQuery(document).on('click', '[data-luv-proceed-import]', function(e){
            e.preventDefault();
            jQuery(this).closest('.luv-modal').addClass('luv-modal-hide');
            var field_container     = jQuery(this).attr('data-field-id') + '-container';
            var container           = jQuery('#' + field_container).closest('.luv-framework-container');
            var settings            = jQuery('#' + field_container + ' .luv-framework-import').val();

            jQuery(container).addClass('luv-framework-loading').removeClass('unsaved');

            jQuery.post(luv_framework_fields.ajax_url, {'action': 'luv_framework_import', 'settings': settings, 'nonce': luv_framework_fields.nonce}, function(response){
                  response = (typeof response === 'string' ? JSON.parse(response) : response);

                  jQuery(container).removeClass('luv-framework-loading');
                  _show_notice(response)
                  setTimeout(function(){
                        document.location.hash = '';
                        document.location.reload();
                  }, 500);
            });
      });

      // Switch tabs
      jQuery(document).on('click','.luv-framework-tab-title', function(e){
            e.preventDefault();
            var id      = jQuery(this).attr('href');

            document.location.hash = 'tab-' + id;

            jQuery('.luv-framework-section-header .active, .luv-framework-tab.active').removeClass('active');

            if (jQuery(id).hasClass('has-child')){
                  jQuery(id).addClass('active');
                  jQuery(id + '-header').addClass('active');

                  jQuery(id + '-header').find('subsection:first').addClass('active');
                  jQuery(id).find('subsection:first').addClass('active');
            }
            else {
                  var parent  = jQuery('#' + jQuery(id).attr('data-parent'));
                  var parent_header  = jQuery('#' + jQuery(id).attr('data-parent') + '-header');

                  jQuery(id).addClass('active');
                  jQuery(id + '-header').addClass('active');

                  if (parent.length > 0){
                        jQuery(parent_header).addClass('active');
                        jQuery(parent).addClass('active');
                  }
            }

            codemirror();

            jQuery(id).trigger('luv-tab-changed');
      });

      // Add multiple field
      jQuery(document).on('click', '.luv-framework-add-multi-field', function(e){
            e.preventDefault();
            add_multi_text_field(jQuery(this).closest('.luv-framework-field-container'));
      });

      // Delete multiple field
      jQuery(document).on('click', '.luv-framework-remove-multi-field', function(e){
            e.preventDefault();
            jQuery(this).parent().remove();
      });

      // Clear license field
      jQuery(document).on('click', '.luv-clear-license-field', function(e){
            e.preventDefault();
            jQuery(this).siblings('.luv-framework-text-field').removeClass('luv-hidden').val('');
            jQuery(this).siblings('.luv-framework-text-field').trigger('change');
            jQuery(this).siblings('.luv-license-placeholder').remove();
            jQuery(this).remove();
      });

      jQuery(document).on('click', '.luv-framework-toggle-all', function(e){
            e.preventDefault();
            jQuery(this).parent().find('input[type="checkbox"]').each(function(){
                  if (jQuery(this).prop('checked')){
                        jQuery(this).removeAttr('checked');
                  }
                  else {
                        jQuery(this).prop('checked',true);
                  }
            });
            jQuery(this).closest('.luv-framework-container').trigger('changed');
      });

      // Show info
      jQuery(document).on('click', '.luv-framework-show-info', function(e){
            e.preventDefault();
            var container = jQuery(this).parent();
            var text = jQuery(this).parent().find('.luv-framework-info').html();

            // Close opened pointers
            jQuery('.luv-pointer').hide();

            jQuery(this).pointer({
                  pointerClass: 'wp-pointer luv-pointer',
                  content: text,
                  position: 'bottom',
           }).pointer('open');
      });

      jQuery(function(){
            // Check dependencies on load
            check_dependencies();

            // Select2
            jQuery('.luv-framework-dropdown-field').select2();

            // Range Slider
            jQuery('.luv-framework-range-slider').each(function(){
                  var range = jQuery(this).find('[type="range"]');
                  var number = jQuery(this).find('[type="number"]');
                  jQuery(number).val(jQuery(range).val());
                  jQuery(range).on('change input', function(){
                        jQuery(number).val(jQuery(range).val());
                  });
                  jQuery(number).on('change keyup', function(){
                        jQuery(range).val(jQuery(number).val());
                  });
            });

            // Switch to tab if it is defined in hash
            var tab = document.location.hash.replace(/^#tab-/,'') + '-header a';
            if (document.location.hash != '' && jQuery(tab).length > 0){
                  jQuery(tab).trigger('click');
            }
      });

})();
/**
* Urgency Countdown common admin JS
*/
"use strict";

var ucd_blockTriggerCustom = false;

jQuery(function($) {
    // Datetime picker has a bug with min time limiting
    // to disable it we set minDateTime to midnight today
    var minDate = new Date();
    minDate.setHours(0);
    minDate.setMinutes(0);
    minDate.setSeconds(0);

    // DateTime picker
    $('#ucd-expiration_date').datetimepicker({
        dateFormat: 'yy-mm-dd',
        timeFormat: 'HH:mm',
        showSecond: false,
        separator: ' ',
        controlType: 'select', //'slider'
        //showButtonPanel: false,
        minDateTime: minDate,
        maxDate: 100
    });

    // Timezone widget
    $('#ucd-expiration_date_timezone_offset_hours').change(function() {
        var basic = '00' != $(this).val();

        var elem = $('#ucd-expiration_date_timezone_offset_minutes-full')
            elem.toggleClass('ucd-hidden', basic)
            .prop('disabled', basic)
            .attr('name', !basic? elem.attr('data-name') : null);
        var elem = $('#ucd-expiration_date_timezone_offset_minutes-basic')
            .toggleClass('ucd-hidden', !basic)
            .prop('disabled', !basic)
            .attr('name', basic? elem.attr('data-name') : null);
    });

    // Option details
    $('.ucd-form-wrap .ucd-radio-has-details').click(function() {
        var selectedId = $(this).attr('id');
        $('.ucd-form-wrap input[name="' + $(this).attr('name') + '"]').each(function() {
            var id = $(this).attr('id');
            $('.ucd-form-wrap [data-details-for-option="' + id + '"]').each(function() {
                var visible = selectedId == id;
                if (visible != $(this).is(':visible')) {
                    $(this).slideToggle();
                }
            });
        });
    });

    // Restart details
    $('.ucd-form-wrap #ucd-restart').change(function() {
        var $this = $(this);
        var $details = $('#ucd-restart-details');
        if ($this.attr('checked') != $details.is(':visible')) {
            $details.slideToggle();
        }
    });

    // Options auto-select
    $('.ucd-for-option').click(function() {
        $('#' + $(this).attr('data-for-option')).prop('checked', true);
    });

    // Disable content action if expiration option is "once"
    $('.ucd-form-wrap .ucd-expiration_type').click(function() {
        var expirationOnce = $('#ucd-expiration_type-once').prop('checked');
        var actionContentElem = $('#ucd-action_type-content');
        actionContentElem.prop('disabled', expirationOnce);
        if (expirationOnce && actionContentElem.prop('checked')) {
            $('#ucd-action_type-redirect').click();
        }

        /*
        var block = $('#ucd-appearance-block');
        if (expirationOnce == block.is(':visible')) {
            block.slideToggle();
        }
        */
    });
    /*
    // After preview initalization
    $(window).load(function() {
        if ($('#ucd-expiration_type-once').prop('checked')) {
            $('#ucd-appearance-block')
                .hide()
                .removeClass('ucd-appearance-block-hidden');
        }
    });
    */

    // Popup menu
    $('html').click(function() {
        $('.ucd-popup-menu:visible').each(function() {
            var $this = $(this);

            $this.slideUp();
            var parent = $this.data('parent');
            if (parent) {
                parent.removeClass('ucd-active');
            }
        });
    });

    $('.ucd-popup-menu').click(function(event){
        event.stopPropagation();
    });

    $('.ucd-has-popup-menu').click(function(event) {
        var $this = $(this);

        if ($this.hasClass('ucd-active')) {
            // Already open
            return;
        }

        // Triggers hiding all open menus
        $('html').triggerHandler('click');

        $this.addClass('ucd-active');

        var offset = $this.position();
        offset.top += $this.outerHeight() + 1;
        offset.left += 1;

        var popup = $('#' + $(this).attr('id') + '-popup');
        popup.css({
                left: offset.left + 'px',
                top: offset.top + 'px'
            })
            .data('parent', $this)
            .slideDown();

        if (popup.hasClass('ucd-colorpicker-popup')) {
            var container = $('.ucd-colorpicker-popup-input-picker-container', popup);
            var cover = $('.ucd-colorpicker-popup-input-picker-cover', popup);

            var offset = container.position();

            cover.css({
                left: offset.left + 'px',
                top: offset.top + 'px',
                width: container.width() + 'px',
                height: container.height() + 'px'
            });
        }

        event.stopPropagation();
    });

    // Color picker popup
    $('.ucd-colorpicker-popup-input').each(function() {
        var $this = $(this);
        $('#' + $this.attr('id') + '-colorpicker').farbtasticmod(this);
        $this.on('farbtasticmod', function(event, color) {
            var $this = $(this);
            $('#' + $this.attr('id') + '-preview')
                .removeClass('ucd-color-empty')
                .css('background-color', color);
        });
    });

    $('.ucd-colorpicker-popup-switch').change(function() {
        var $this = $(this);
        var popup = $this.parents('.ucd-colorpicker-popup');

        var input = $('.ucd-colorpicker-popup-input', popup);

        if ($this.prop('checked')) {
            $('.ucd-colorpicker-popup-input-picker-cover', popup).fadeIn();
            input.prop('disabled', true);

            $('#' + input.attr('id') + '-preview')
                .addClass('ucd-color-empty')
                .css('background-color', 'transparent');

        } else {
            $('.ucd-colorpicker-popup-input-picker-cover', popup).fadeOut();
            input.prop('disabled', false);

            $('#' + input.attr('id') + '-preview')
                .removeClass('ucd-color-empty')
                .css('background-color', input.val());
        }
    });

    $('.ucd-colorpicker-popup-input-picker-cover').click(function() {
        var $this = $(this);
        $(this).parents('.ucd-colorpicker-popup')
            .find('.ucd-colorpicker-popup-switch')
            .click();
    });

    // Switch button
    $('.ucd-toolbar-button-switch').click(function() {
        var $this = $(this);
        var input = $('#' + $this.attr('id') + '-input');
        var newVal = '0' == input.val();

        input.val(newVal? '1' : '0').change();
        $this.toggleClass('ucd-active', newVal);
    });

    //
    // WYSIWYG editor
    // (has metabox-specific parts due to common function use)
    //

    $(window).load(function() {
        attachEditorEventHandlers();

        if ('undefined' != typeof(tinyMCE)
            && 'undefined' != typeof(tinyMCE.onAddEditor)
        ) {
            tinyMCE.onAddEditor.add(attachEditorEventHandlers);
        }

        $('.ucd-wysiwyg-field .switch-tmce').attr('onclick', '')
            .click(function() {
                switchEditors.switchto(this);
                attachEditorEventHandlers();
            });
    });

    function attachEditorEventHandlers()
    {
        // iframe events
        $('.ucd-wysiwyg-field').each(function() {
            var id = $(this).attr('data-id');

            if (!$(this).data('attached-iframe-events')) {
                var iframe = $('iframe', this);
                if (0 != iframe.length) {
                    var body = iframe.contents().find('body');
                    body.data('editor-id', id)

                    if ('metabox' == ucd_admin.form) {
                        body.on('focus', triggerCustom);
                    }

                    $(this).data('attached-iframe-events', true);
                }
            }

            if (!$(this).data('attached-tinymce-events')
                && 'undefined' != typeof(tinyMCE)
                && 'undefined' != typeof(tinyMCE.get(id))
                && null !== tinyMCE.get(id)
                && 'undefined' != typeof(tinyMCE.get(id).onExecCommand)
            ) {
                var ed = tinyMCE.get(id);

                if ('metabox' == ucd_admin.form) {
                    ed.onExecCommand.add(triggerCustom);
                    ed.onChange.add(triggerCustom);
                }

                // Workaround for content not saved if wpautop disabled
                ed.onSaveContent.add(function(ed, o) {
                    if (!ed.getParam('wpautop', false)
                        && 'object' == typeof(switchEditors)
                        && ed.isHidden()
                    ) {
                        o.content = o.element.value;
                    }
                });

                $(this).data('attached-tinymce-events', true);
            }

            if (!$(this).data('attached-ckeditor-events')
                && 'undefined' != typeof(CKEDITOR)
                && 'undefined' != typeof(CKEDITOR.instances[id])
            ) {
                var ed = CKEDITOR.instances[id];

                if ('metabox' == ucd_admin.form) {
                    ed.on('key', triggerCustom);
                    ed.on('afterCommandExec', triggerCustom);
                }

                $(this).data('attached-ckeditor-events', true);
            }
        });
    }

    $('#ucd-form-metabox .ucd-custom, #ucd-form-metabox .ucd-trigger-custom').on('focus click', triggerCustom);

    function triggerCustom()
    {
        if (ucd_blockTriggerCustom) {
            return;
        }

        var customField = $('#ucd-custom');
        if (customField.length > 0 && !customField.prop('checked')) {
            customField.prop('checked', true).change();
        }
    }

    $('#post').submit(function() {
        ucd_blockTriggerCustom = true;
    });

    //
    // Live preview
    //

    // Font family
    $('#ucd-appearance_font_family').change(function() {
        var val = $(this).val();
        $('#ucd-countdown-preview').css('font-family', val
            ? ucd_admin.counter.fonts[val].family
            : null
        );
    });
    // Size
    $('#ucd-appearance_size').change(function() {
        var val = $(this).val();
        var preview = $('#ucd-countdown-preview');
        $.each(ucd_admin.counter.sizes, function(key, size) {
            preview.toggleClass('ucd-countdown-size-' + key, val==key);
        });
    });
    // Color
    $('#ucd-appearance_color').on('farbtasticmod', function() {
        $('#ucd-countdown-preview').css('color', $(this).val());
    });
    $('#ucd-appearance_color-switch').change(function() {
        $('#ucd-countdown-preview').css('color',
            $(this).prop('checked')
                ? ''
                : $('#ucd-appearance_color').val()
        );
    });
    // Background Color
    $('#ucd-appearance_background_color').on('farbtasticmod', function() {
        $('#ucd-countdown-preview').css('background-color', $(this).val());
    });
    $('#ucd-appearance_background_color-switch').change(function() {
        $('#ucd-countdown-preview').css('background-color',
            $(this).prop('checked')
                ? ''
                : $('#ucd-appearance_background_color').val()
        );
    });
    // Bold
    $('#ucd-appearance_font_bold-button-input').change(function() {
        $('#ucd-countdown-preview').css('font-weight',
            '1' == $(this).val()? 'bold' : '');
    });
    // Italic
    $('#ucd-appearance_font_italic-button-input').change(function() {
        $('#ucd-countdown-preview').css('font-style',
            '1' == $(this).val()? 'italic' : '');
    });

    // Elements Visibility
    $('.ucd-elements_visible-input').change(function() {
        var $this = $(this);
        var elem = $('#ucd-countdown-preview').find('.ucd-countdown-dash-' + $this.val());

        if ($this.prop('checked')) {
            elem.css('display', 'inline-block');
        } else {
            elem.css('display', 'none');
        }
    });

    $('.ucd-element_label-input').on('keyup change', function() {
        var $this = $(this);
        var elem = $('#ucd-countdown-preview').find('.ucd-countdown-dash-' + $this.attr('data-key') + ' .ucd-countdown-dash_title');

        elem.text($this.val());
    });

    //
    // Clear history
    //
    $('#ucd-button-clear_history').click(function() {
        var $this = $(this);
        $this.prop('disabled', true);
        $('#ucd-ajax-loader-clear_history').show();
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action:            'ucd',
                controller:        'admin-ajax',
                controller_action: 'clear-history'
            },
            dataType: 'json',
            context: $this,
            success: function(response) {
                // Nothing so far
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert('AJAX request failed.')
            },
            complete: function() {
                $this.prop('disabled', false);
                $('#ucd-ajax-loader-clear_history').hide();
            }
        });
    });

    // Embed code
    $('#ucd-button-embed').click(function() {
        function updateEmbedButton() {
            var $elem = $('#ucd-button-embed');

            $elem.html($elem.attr($('#ucd-embed-code-container').is(':visible')
                ? 'data-label-hide'
                : 'data-label-show'));
        }

        var $cont = $('#ucd-embed-code-container');
        if ($cont.is(':visible')) {
            $cont.slideUp(function() {
                updateEmbedButton();
            });
        } else {
            $cont.slideDown(function() {
                updateEmbedButton();
            });
            $('#ucd-embed-code').select();
        }
    });
});
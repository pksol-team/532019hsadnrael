/**
* Countdown Dynamite metabox JS
*/
"use strict";

jQuery(function($) {

    //
    // Custom on/off
    //

    $('#ucd-custom').change(function() {
        if ($(this).prop('checked')) {
            $('#ucd-form-metabox').find('.ucd-custom').removeClass('disabled');
        } else {
            ucd_blockTriggerCustom = true;

            var metabox = $('#ucd-form-metabox');
            metabox.find('.ucd-custom').addClass('disabled');
            for (var field in ucd_admin.defaultValues) {
                var value = ucd_admin.defaultValues[field];
                if ('function' == typeof value) {
                    continue;
                }

                var elem = metabox.find('[name="ucd_metabox[' + field + ']"]');
                if ('checkbox' == elem.attr('type')) {
                    elem.prop('checked', value);
                } else if ('radio' == elem.attr('type')) {
                    elem.each(function() {
                        var $this = $(this);
                        var checked =  value == $this.val();
                        $this.prop('checked', checked);
                        if (checked) {
                            $this.click();
                        }
                    });
                } else if (elem.hasClass('ucd-colorpicker-popup-input')) {
                    var switchElem = $('#' + elem.attr('id') + '-switch');
                    if ('' == value) {
                        value = elem.attr('data-default-value');
                        elem.val(value).keyup();
                        switchElem.prop('checked', true).change();
                    } else {
                        elem.val(value).keyup();
                        switchElem.prop('checked', false).change();
                    }
                } else {
                    elem.val(value);
                }

                elem.change();
            }

            ucd_blockTriggerCustom = false;

            // Prevent script execution
            var oldEval = $.globalEval;
            $.globalEval = function(){};

            $('#ucd-form-metabox').find('.ucd-wysiwyg-field').each(function() {
                var $this = $(this);
                var id = $this.attr('data-id');
                $('iframe', $this).contents()
                    .find('body')
                    .html($('textarea#' + id, $this).val());
            });
            $.globalEval = oldEval;
        }
    });

    $('#ucd-button-update').click(function() {
        $('#ucd-metabox-ajax_loader').show();

        $(this).prop('disabled', true);
        $(this).addClass('button-primary-disabled');
        $('#submitpost').find(':button, :submit, a.submitdelete, #post-preview').addClass('disabled');

        // Stop autosave
        if (wp.autosave) {
            wp.autosave.server.suspend();
        }

        $(window).off('beforeunload.edit-post');

        $('#post').submit();
    });

    //
    // Prevent editor breaking on sorting
    //

    $('#normal-sortables').on('sortstart', function() {
        $('#ucd-form-metabox').find('.ucd-wysiwyg-field').each(function() {
            var $this = $(this);
            var id = $this.attr('data-id');
            if ('undefined' != typeof(tinyMCE) && 'undefined' != typeof(tinyMCE.get(id))) {
                $this.data('has-editor', 'tinymce');
                tinyMCE.execCommand('mceRemoveControl', false, id);
            } else {
                $this.data('has-editor', null);
            }
        });
    });
    $('#normal-sortables').on('sortstop', function() {
        $('#ucd-form-metabox').find('.ucd-wysiwyg-field').each(function() {
            var $this = $(this);
            var id = $this.attr('data-id');
            if ('tinymce' == $this.data('has-editor') && 'undefined' != typeof(tinyMCE)) {
                var options = 'undefined' != typeof(tinyMCEPreInit.mceInit[id])
                    ? tinyMCEPreInit.mceInit[id]
                    : {};
                if ('undefined' != typeof(options.wpautop) && options.wpautop) {
                    $('#' + id).val(switchEditors.wpautop($('#' + id).val()));
                }
                tinyMCE.execCommand('mceAddControl', false, id, options);
            }
        });
    });

    //
    // Insert shortcode
    //

    $('#ucd-insert-shortcode').click(function() {
        var shortcode = '[' + $(this).attr('data-shortcode-name')  + ']';

        if ('undefined' != typeof(tinyMCE)) {
            if (tinyMCE.get('content') && !tinyMCE.get('content').isHidden()) {
                tinyMCE.get('content').execCommand('mceInsertContent', false, shortcode);
            } else {
                insertToTextarea(shortcode);
            }
        }

        if ('undefined' != typeof(CKEDITOR)) {
            if ('undefined' != typeof(CKEDITOR.instances.content)) {
                CKEDITOR.instances.content.insertHtml(shortcode);
            } else {
                insertToTextarea(shortcode);
            }
        }

        if ('undefined' != typeof(FCKeditorAPI)) {
            if (undefined != FCKeditorAPI.GetInstance('content')) {
                FCKeditorAPI.GetInstance('content').InsertHtml(shortcode);
            }
        }

        // Check "Enable" checkbox
        $('#ucd-enabled').prop('checked', true);

        function insertToTextarea(text)
        {
            if ('undefined' != typeof(QTags)) {
                QTags.insertContent(text);
            }
        }
    });
});
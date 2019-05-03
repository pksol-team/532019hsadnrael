;
(function ($, window) {

    $.fn.ptb_wp_editor = function (options) {

        if (!$(this).is('textarea'))
            console.warn('Element must be a textarea');

        if (typeof tinyMCEPreInit == 'undefined' || typeof QTags == 'undefined' || typeof ap_vars == 'undefined')
            console.warn('ptb_wp_editor( $settings ); must be loaded');

        if (!$(this).is('textarea') || typeof tinyMCEPreInit == 'undefined' || typeof QTags == 'undefined' || typeof ap_vars == 'undefined')
            return this;

        var default_options = {
            'mode': 'html',
            'mceInit': {
                "theme": "modern",
                "skin": "lightgray",
                "language": "en",
                "formats": {
                    "alignleft": [
                        {
                            "selector": "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li",
                            "styles": {"textAlign": "left"},
                            "deep": false,
                            "remove": "none"
                        },
                        {
                            "selector": "img,table,dl.wp-caption",
                            "classes": ["alignleft"],
                            "deep": false,
                            "remove": "none"
                        }
                    ],
                    "aligncenter": [
                        {
                            "selector": "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li",
                            "styles": {"textAlign": "center"},
                            "deep": false,
                            "remove": "none"
                        },
                        {
                            "selector": "img,table,dl.wp-caption",
                            "classes": ["aligncenter"],
                            "deep": false,
                            "remove": "none"
                        }
                    ],
                    "alignright": [
                        {
                            "selector": "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li",
                            "styles": {"textAlign": "right"},
                            "deep": false,
                            "remove": "none"
                        },
                        {
                            "selector": "img,table,dl.wp-caption",
                            "classes": ["alignright"],
                            "deep": false,
                            "remove": "none"
                        }
                    ],
                    "strikethrough": {"inline": "del", "deep": true, "split": true}
                },
                "relative_urls": false,
                "remove_script_host": false,
                "convert_urls": false,
                "browser_spellcheck": true,
                "fix_list_elements": true,
                "entities": "38,amp,60,lt,62,gt",
                "entity_encoding": "raw",
                "keep_styles": false,
                "paste_webkit_styles": "font-weight font-style color",
                "preview_styles": "font-family font-size font-weight font-style text-decoration text-transform",
                "wpeditimage_disable_captions": false,
                "wpeditimage_html5_captions": false,
                "width": '100%',
                "height": 400,
                "autoresize_min_height": 400,
                "autoresize_max_height": 800,
                "plugins": "wpautoresize,charmap,hr,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpeditimage,wpgallery,wplink,wpdialogs,wpview,image",
                "content_css": ap_vars.includes_url + "css/dashicons.css?ver=4.5.2," + ap_vars.includes_url + "js/mediaelement/mediaelementplayer.min.css?ver=4.5.2," + ap_vars.includes_url + "js/mediaelement/wp-mediaelement.css?ver=4.5.2," + ap_vars.includes_url + "js/tinymce/skins/lightgray/wp-content.css?ver=4.5.2",
                "selector": "#apid",
                "resize": "vertical",
                "menubar": false,
                "wpautop": true,
                "indent": false,
                "toolbar1": "bold,italic,strikethrough,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,link,unlink,wp_more,spellchecker,wp_adv",
                "toolbar2": "formatselect,underline,alignjustify,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help",
                "toolbar4": "",
                "tabfocus_elements": ":prev,:next",
                "body_class": "ptb_editor"
            }
        }, id_regexp = new RegExp('apid', 'g');

        if (tinyMCEPreInit.mceInit['apid']) {
            default_options.mceInit = tinyMCEPreInit.mceInit['apid'];
        }

        var options = $.extend(true, default_options, options);

        return this.each(function () {

            if (!$(this).is('textarea'))
                console.warn('Element must be a textarea');
            else {
                var current_id = $(this).attr('id');
                $.each(options.mceInit, function (key, value) {
                    if ($.type(value) == 'string')
                        options.mceInit[key] = value.replace(id_regexp, current_id);
                });
                options.mode = options.mode == 'tmce' ? 'tmce' : 'html';

                tinyMCEPreInit.mceInit[current_id] = options.mceInit;

                $(this).addClass('wp-editor-area').show();
                var self = this;
                if ($(this).closest('.wp-editor-wrap').length) {
                    var parent_el = $(this).closest('.wp-editor-wrap').parent();
                    $(this).closest('.wp-editor-wrap').before($(this).clone());
                    $(this).closest('.wp-editor-wrap').remove();
                    self = parent_el.find('textarea[id="' + current_id + '"]');
                }

                var wrap = $('<div id="wp-' + current_id + '-wrap" class="wp-core-ui wp-editor-wrap ' + options.mode + '-active" />'),
                        editor_tools = $('<div id="wp-' + current_id + '-editor-tools" class="wp-editor-tools hide-if-no-js" />'),
                        editor_tabs = $('<div class="wp-editor-tabs" />'),
                        switch_editor_html = $('<a id="' + current_id + '-html" data-wp-editor-id="' + current_id + '" class="wp-switch-editor switch-html" onclick="switchto(this);">Text</a>'),
                        switch_editor_tmce = $('<a id="' + current_id + '-tmce" data-wp-editor-id="' + current_id + '" class="wp-switch-editor switch-tmce" onclick="switchto(this);">Visual</a>'),
                        media_buttons = $('<div id="wp-' + current_id + '-media-buttons" class="wp-media-buttons" />'),
                        insert_media_button = $('<a href="#" id="' + current_id + '-insert-media-button" class="button insert-media add_media" data-editor="' + current_id + '" title="Add Media"><span class="wp-media-buttons-icon"></span> Add Media</a>'),
                        editor_container = $('<div id="wp-' + current_id + '-editor-container" class="wp-editor-container" />');

                insert_media_button.appendTo(media_buttons);
                media_buttons.appendTo(editor_tools);

                switch_editor_html.appendTo(editor_tabs);
                switch_editor_tmce.appendTo(editor_tabs);
                editor_tabs.appendTo(editor_tools);

                editor_tools.appendTo(wrap);
                editor_container.appendTo(wrap);
                editor_container.append($(self).clone().addClass('wp-editor-area'));

                $(self).before('<link rel="stylesheet" id="editor-buttons-css" href="' + ap_vars.includes_url + 'css/editor.css" type="text/css" media="all">');

                $(self).before(wrap);
                $(self).remove();

                new QTags(current_id);
                QTags._buttonsInit();

                $(wrap).on('click', '.insert-media', function (event) {
                    $(wrap).find('.switch-html').trigger('click');//hack tinemce image uploader

                    var elem = $(event.currentTarget),
                            editor = elem.data('editor'),
                            options = {
                                frame: 'post',
                                state: 'insert',
                                title: wp.media.view.l10n.addMedia,
                                multiple: true
                            };

                    event.preventDefault();


                    if (elem.hasClass('gallery')) {
                        options.state = 'gallery';
                        options.title = wp.media.view.l10n.createGalleryTitle;
                    }
                    $(wrap).find('.switch-tmce').trigger('click');//hack tinemce image uploader
                    elem.blur();
                    wp.media.editor.open(editor, options);
                });
            }
        });

    }
})(jQuery, window);

var switchto = function switchto($el) {
    if (typeof tinymce !== 'undefined') {
        var $id = jQuery($el).attr('id').split('-');
        $id = $id[0];


        var $wrap = jQuery('#wp-' + $id + '-wrap');
        $wrap.removeClass('tmce-active html-active');
        var tinemce = $wrap.find('.mce-tinymce');
        var textarea = jQuery('#' + $id);
        var tid = tinyMCE.get($id);
        if (jQuery($el).hasClass('switch-tmce')) {
            $wrap.addClass('tmce-active');
            tinemce.show();
            textarea.hide();
            if (tid) {
                tid.setContent(textarea.val());
            }
        }
        else {
            $wrap.addClass('html-active');
            tinemce.hide();
            textarea.show();
            if (tid) {
                textarea.val(tid.getContent());
            }

        }
    }
    return false;
};
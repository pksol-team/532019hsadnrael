var PTB;
(function ($) {
    'use strict';
    function triggerEvent(a, b) {
        var c;
        document.createEvent ? (c = document.createEvent("HTMLEvents"), c.initEvent(b, !0, !0)) : document.createEventObject && (c = document.createEventObject(), c.eventType = b), c.eventName = b, a.dispatchEvent ? a.dispatchEvent(c) : a.fireEvent && htmlEvents["on" + b] ? a.fireEvent("on" + c.eventType, c) : a[b] ? a[b]() : a["on" + b] && a["on" + b]()
    }

    PTB = {
        LoadAsync: function (src, callback, version, defer, test) {
            var id = src.split("/").pop().replace(/\./g, '_'), // Make script filename as ID
                    existElemens = document.getElementById(id);

            if (existElemens) {
                if (callback) {
                    if (test) {
                        var callbackTimer = setInterval(function () {
                            var call = false;
                            try {
                                call = test.call();
                            } catch (e) {
                            }

                            if (call) {
                                clearInterval(callbackTimer);
                                callback.call();
                            }
                        }, 100);
                    } else {
                        setTimeout(callback, 110);
                    }
                }
                return;
            }
            var s, r, t;
            r = false;
            s = document.createElement('script');
            s.type = 'text/javascript';
            s.id = id;
            s.src = !version && 'undefined' !== typeof tbLocalScript ? src + '?version=' + tbLocalScript.version : src;
            if (!defer) {
                s.async = true;
            }
            else {
                s.defer = true;
            }
            s.onload = s.onreadystatechange = function () {
                if (!r && (!this.readyState || this.readyState === 'complete'))
                {
                    r = true;
                    if (callback) {
                        callback();
                    }
                }
            };
            t = document.getElementsByTagName('script')[0];
            t.parentNode.insertBefore(s, t);
        },
        LoadCss: function (href, version, before, media) {

            if ($("link[href='" + href + "']").length > 0) {
                return;
            }
            var doc = window.document;
            var ss = doc.createElement("link");
            var ref;
            if (before) {
                ref = before;
            }
            else {
                var refs = (doc.body || doc.getElementsByTagName("head")[ 0 ]).childNodes;
                ref = refs[ refs.length - 1];
            }

            var sheets = doc.styleSheets;
            ss.rel = "stylesheet";
            ss.href = version ? href + '?version=' + version : href;
            // temporarily set media to something inapplicable to ensure it'll fetch without blocking render
            ss.media = "only x";
            ss.async = 'async';

            // Inject link
            // Note: `insertBefore` is used instead of `appendChild`, for safety re: http://www.paulirish.com/2011/surefire-dom-element-insertion/
            ref.parentNode.insertBefore(ss, (before ? ref : ref.nextSibling));
            // A method (exposed on return object for external use) that mimics onload by polling until document.styleSheets until it includes the new sheet.
            var onloadcssdefined = function (cb) {
                var resolvedHref = ss.href;
                var i = sheets.length;
                while (i--) {
                    if (sheets[ i ].href === resolvedHref) {
                        return cb();
                    }
                }
                setTimeout(function () {
                    onloadcssdefined(cb);
                });
            };

            // once loaded, set link's media back to `all` so that the stylesheet applies once it loads
            ss.onloadcssdefined = onloadcssdefined;
            onloadcssdefined(function () {
                ss.media = media || "all";
            });
            return ss;
        }
    };

    $(document).ready(function () {
        var $container = $('.ptb_post.ptb_is_excerpt .ptb_items_wrapper'),
            $is_excerpt = $container.length !== 0;

        if (!$is_excerpt) {
            $container = $('.ptb_post .ptb_items_wrapper');
        }
        else{
            $('.ptb_post.type-post').addClass('ptb_is_excerpt');
        }
        if ($container.length > 0) {
            var $loop = $('.ptb_loops_wrapper').length > 0;
            $container.each(function () {
                if ($(this).closest('.ptb_loops_shortcode').length === 0) {
                    var $post = $(this).closest('.ptb_post');
                    if (!$is_excerpt) {
                        $post.html($(this));
                    }
                    if ($loop) {
                        $post.closest('.ptb_loops_wrapper').append($post);
                    }
                }
            });
            if ($loop) {
                if (!$is_excerpt) {
                    $('.ptb_loops_wrapper:not(.ptb_loops_shortcode)>*').not('.ptb_post').remove();
                }
                else {
                    $('.ptb_loops_wrapper:not(.ptb_loops_shortcode)>*').not('.ptb_post.ptb_is_excerpt').remove();
                }
                $( document ).ajaxComplete(function() {
                    var $ptb_posts = $('.ptb_loops_wrapper').nextAll('.ptb_post');
                    if($ptb_posts.length>0){
                        $ptb_posts.each(function(){
                            var $post = $(this).find('.ptb_post').first();
                            if($post.length===0){
                                $post = $(this);
                                $post.html($post.find('.ptb_items_wrapper').first());
                            }
                            $('.ptb_loops_wrapper').append($post);
                        });
                        if (!$is_excerpt) {
                            $('.ptb_loops_wrapper:not(.ptb_loops_shortcode)>*').not('.ptb_post').remove();
                        }
                        else {
                            $('.ptb_loops_wrapper:not(.ptb_loops_shortcode)>*').not('.ptb_post.ptb_is_excerpt').remove();
                        }
                        $.event.trigger({type: "ptb_loaded"});
                    }
                });
            }
        }
        $('.ptb_loops_wrapper,.ptb_pagenav,.ptb_single .ptb_post,.ptb_category_wrapper').css('display', 'block');
        $(document).trigger('ptb_loaded',false);
        triggerEvent(window, 'resize');

        //Single Page Lightbox
        $(document).on('ptb_ligthbox_close', function () {
            $('#lightcase-case').removeClass('ptb_is_single_lightbox');
            $('body').removeClass('ptb_hide_scroll');
            $(window).unbind('resize', ptb_lightbox_position);
        });

        $('a.ptb_open_lightbox').lightcase({
            type: 'ajax',
            maxWidth: $(window).width() * 0.8,
            onFinish: {
                bar: function () {
                    $('body').addClass('ptb_hide_scroll');
                },
                baz: function () {
                    var $container = $('#lightcase-case');
                    $container.addClass('ptb_is_single_lightbox');
                    $container.find('.ptb_post img').css('display', 'block');
                    $(document).trigger('ptb_loaded',true);
                    triggerEvent(window, 'resize');
                    ptb_lightbox_position();
                    $(window).resize(ptb_lightbox_position);
                }
            },
            onClose: {
                qux: function () {
                    $.event.trigger({type: "ptb_ligthbox_close"});
                }
            }
        });

        function ptb_lightbox_position() {
            $('#lightcase-case').find('.ptb_single_lightbox').css('max-height', $(window).height() - 100);
        }
        //Page Lightbox
        $('a.ptb_lightbox').lightcase({
            type: 'iframe',
            onFinish: {
                bar: function () {
                    $.event.trigger({type: "ptb_ligthbox_close"});
                    $('body').addClass('ptb_hide_scroll');
                }
            },
            onClose: {
                qux: function () {
                    $.event.trigger({type: "ptb_ligthbox_close"});
                }
            }
        });

        //Isotop Filter

        var $filter = $('.ptb-post-filter');
        $filter.each(function () {
            var $entity = $filter.next('.ptb_loops_wrapper');
            $(this).on('click', 'li', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var $posts = $entity.find('.ptb_post');
                $posts.removeClass('ptb-isotop-filter-clear');

                if ($(this).hasClass('ptb_filter_active')) {
                    $filter.find('li.ptb_filter_active').removeClass('ptb_filter_active');
                    $entity.removeClass('ptb-isotop-filter');
                    $posts.stop().fadeIn('normal');
                }
                else {
                    $filter.find('li.ptb_filter_active').removeClass('ptb_filter_active');
                    $(this).addClass('ptb_filter_active');
                    $entity.addClass('ptb-isotop-filter');
                    var $tax = '.ptb-tax-' + $(this).data('tax'),
                            $child = $(this).find('li');
                    if ($child.length > 0) {
                        $child.each(function () {
                            $tax += ' ,.ptb-tax-' + $(this).data('tax');
                        });
                    }
                    var $items = $posts.filter($tax),
                        $grid = $entity.hasClass('ptb_grid4') ? 4 : ($entity.hasClass('ptb_grid3') ? 3 : ($entity.hasClass('ptb_grid2') ? 2 : 1));
                    if ($grid > 1) {
                        $items.each(function ($i) {
                            if ($i % $grid === 0) {
                                $(this).addClass('ptb-isotop-filter-clear');
                            }
                        });
                    }
                    $posts.hide();
                    $items.not('visible').stop().fadeIn('normal');
                }
            });
        });

    });
}(jQuery));
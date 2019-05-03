/**
* Countdown Dynamite front-end JS
*/
"use strict";

jQuery(function($) {
    var counterMaxWidths = {
        xl: 540,
        large: 353,
        medium: 262,
        small: 0,
    };

    $(window).on('load resize', function() {
        $('.ucd-countdown-container').each(function() {
            var $this = $(this);
            var countdown = $this.find('.ucd-countdown');
            var width = $this.width();
            var origSize = countdown.attr('data-orig-size');

            var allowChangeSize = false;
            var sizeChanged = false;
            $.each(counterMaxWidths, function(size, maxWidth) {
                if (size == origSize) {
                    allowChangeSize = true;
                } else if (!allowChangeSize) {
                    return true;
                }

                if (!sizeChanged && width >= maxWidth) {
                    countdown.addClass('ucd-countdown-size-' + size);
                    sizeChanged = true;
                } else {
                    countdown.removeClass('ucd-countdown-size-' + size);
                }
            });
        });
    });
});
/*!
 * jQuery Countdown plugin
 * based on project by Vassilis Dourdounis
 * http://www.littlewebthings.com/projects/countdown/
 * Partly refactored by Anton Oliinyk (Pumka.net)
 *
 * Copyright 2010, Vassilis Dourdounis
 * Copyright 2013, Anton Oliinyk (Pumka.net)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
(function($){
    $.fn.ucd_countDown = function (options) {
        if (!this.length) {
            return;
        }

        var config = {
            omitWeeks: false,
            callback: null,
            duration: 500,
        };

        $.extend(config, options);

        $.data(this[0], config);

        $(this).find('.ucd-countdown-digit').each(function() {
            var digit = $(this);
            digit.html('<div class="ucd-figure ucd-countdown-digit-top"></div><div class="ucd-figure ucd-countdown-digit-bottom"></div>');
        });

        this.ucd_startCountDown();

        return this;
    };

    $.fn.ucd_startCountDown = function() {
        $.data(this[0], 'timer',
            setInterval("jQuery('#" + this.attr('id') + "')._ucd_doCountDown()", 1000));
        this._ucd_doCountDown();

        return this;
    };

    $.fn.ucd_stopCountDown = function () {
        var t = $.data(this[0], 'timer');
        if (t) {
            clearInterval(t);
        }
        $.removeData(this[0], 'timer');

        return this;
    };

    $.fn._ucd_doCountDown = function() {
        var targetDate = $.data(this[0], 'targetDate');
        var now = new Date();
        var diffSecs = Math.round((targetDate.getTime() - now.getTime())/1000);

        var duration = $.data(this[0], 'duration');

        if (diffSecs <= 0) {
            diffSecs = 0;
        }

        secs = diffSecs % 60;
        mins = Math.floor(diffSecs/60)%60;
        hours = Math.floor(diffSecs/60/60)%24;
        if ($.data(this[0], 'omitWeeks')) {
            days = Math.floor(diffSecs/60/60/24);
            weeks = 0;
        } else {
            days = Math.floor(diffSecs/60/60/24)%7;
            weeks = Math.floor(diffSecs/60/60/24/7);
        }

        this.find('.ucd-countdown-dash-seconds').ucd_dashChangeTo(secs, duration);
        this.find('.ucd-countdown-dash-minutes').ucd_dashChangeTo(mins, duration);
        this.find('.ucd-countdown-dash-hours').ucd_dashChangeTo(hours, duration);
        this.find('.ucd-countdown-dash-days').ucd_dashChangeTo(days, duration);
        this.find('.ucd-countdown-dash-weeks').ucd_dashChangeTo(weeks, duration);

        $.data(this[0], 'diffSecs', diffSecs);
        if (diffSecs > 0) {
            $.data(this[0], 'prevDiffSecs', diffSecs);
            diffSecs--;
        } else {
            var prevDiffSecs = $.data(this[0], 'prevDiffSecs');
            if (!prevDiffSecs || prevDiffSecs <= 0) {
                var cb = $.data(this[0], 'callback');
                if (cb) {
                    $.data(this[0], 'callback')(this[0]);
                }
                this.ucd_stopCountDown();
            }
            $.data(this[0], 'prevDiffSecs', 0);
        }

        $.data(this[0], 'diffSecs', diffSecs);

        return this;
    };

    $.fn.ucd_dashChangeTo = function(n, duration) {
        var digits = this.find('.ucd-countdown-digit');
        var i = digits.length-1;
        $(digits.get().reverse()).each(function() {
            var d = n%10;
            n = (n - d) / 10;
            $(this).ucd_digitChangeTo(d, duration);
            i--;
        });

        return this;
    };

    $.fn.ucd_digitChangeTo = function(n, duration) {
        var top = this.find('.ucd-countdown-digit-top');
        var bottom = this.find('.ucd-countdown-digit-bottom');

        if (top.html() != n + '') {
            if (top.data('locked')) {
                top.html((n ? n : '0'));
            } else {
                if (!duration) {
                    duration = 800;
                }

                top.data('locked', true)
                    .hide()
                    .height('auto')
                    .html((n ? n : '0'))
                    .data('bottom-element', bottom)
                    .slideDown(duration, function() {
                        var top = $(this);
                        var bottom = top.data('bottom-element');

                        bottom.html(top.html())
                            .show();
                        top.hide()
                            .height(0);

                        top.removeData('locked');
                });
            }
        }

        return this;
    };

    $(window).trigger('ucd_countDown-loaded');
})(jQuery);
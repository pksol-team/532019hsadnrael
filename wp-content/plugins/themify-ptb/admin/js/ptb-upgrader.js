;
(function ($, window, document, undefined) {

    'use strict';

    var _updater_el;

    function showLogin(status) {
        $('.ptb-promt-box .show-login').show();
        $('.ptb-promt-box .show-error').hide();
        if (status == 'error') {
            if ($('.ptb-promt-box .prompt-error').length == 0) {
                $('.ptb-promt-box .prompt-msg').after('<p class="prompt-error">' + ptb_upgrader.invalid_login + '</p>');
            }
        } else {
            $('.ptb-promt-box .prompt-error').remove();
        }
        $('.ptb-promt-box').addClass('update-plugin');
        $(".ptb_promt_overlay,.ptb-promt-box").fadeIn(500);
    }
    function hideLogin() {
        $('.ptb_promt_overlay,.ptb-promt-box').fadeOut(500, function () {
            var $prompt = $('.ptb-promt-box'), $iframe = $prompt.find('iframe');
            if ($iframe.length > 0) {
                $iframe.remove();
            }
            $prompt.removeClass('show-changelog');
        });
    }
    function showAlert() {
        $(".ptb_alert").addClass("busy").fadeIn(800);
    }
    function hideAlert(status) {
        if (status == 'error') {
            status = 'error';
            showErrors();
        } else {
            status = 'done';
        }
        $(".ptb_alert").removeClass("busy").addClass(status).delay(800).fadeOut(800, function () {
            $(this).removeClass(status);
        });
    }
    function showErrors(verbose) {
        $(".ptb_promt_overlay, .ptb-promt-box").delay(900).fadeIn(500);
        $('.ptb-promt-box .show-error').show();
        $('.ptb-promt-box .show-error p').remove();
        $('.ptb-promt-box .error-msg').after('<p class="prompt-error">' + verbose + '</p>');
        $('.ptb-promt-box .show-login').hide();
    }

    $(function () {
        //
        // Upgrade Theme / Framework
        //
        $(".themify-ptb-upgrade-plugin").on('click', function (e) {
            e.preventDefault();
            _updater_el = $(this);
            showLogin();
        });

        //
        // Login Validation
        //
        $(".themify-ptb-upgrade-login").on('click', function (e) {
            e.preventDefault();
            if ($('.ptb-promt-box').hasClass('update-plugin')) {
                var el = $(this),
                        username = el.parent().parent().find('.username').val(),
                        password = el.parent().parent().find('.password').val(),
                        login = el.closest('.notifications').find('.update').hasClass('login');
                if (username != "" && password != "") {
                    hideLogin();
                    showAlert();
                    $.post(
                            ajaxurl,
                            {
                                'action': 'themify_ptb_validate_login',
                                'type': 'plugin',
                                'login': login,
                                'username': username,
                                'password': password,
                                'nicename_short': _updater_el.data('nicename_short'),
                                'update_type': _updater_el.data('update_type')
                            },
                    function (data) {
                        data = $.trim(data);
                        if (data == 'true') {
                            hideAlert();
                            $('#ptb_update_form').append('<input type="hidden" name="plugin" value="' + _updater_el.data('plugin') + '" /><input type="hidden" name="package_url" value="' + _updater_el.data('package_url') + '" /><input type="hidden" name="nicename_short" value="' + _updater_el.data('nicename_short') + '" />').submit();
                        } else {
                            hideAlert('error');
                            showLogin('error');
                        }
                    }
                    );
                } else {
                    hideAlert('error');
                    showLogin('error');
                }
            }
        });
        //
        // Hide Overlay
        //
        $(".ptb_promt_overlay").on('click', function () {
            hideLogin();
        });

        $('.ptb_changelogs').on('click', function (e) {
            e.preventDefault();
            var $self = $(this),
                    url = $self.data('changelog');
            $('.ptb-promt-box .show-login,.ptb-promt-box .show-error').hide();
            $('.ptb_alert').addClass('busy').fadeIn(300);
            $('.ptb_promt_overlay,.ptb-promt-box').fadeIn(300);
            var $iframe = $('<iframe src="' + url + '" />');
            $iframe.on('load', function () {
                $('.ptb_alert').removeClass('busy').fadeOut(300);
            }).prependTo('.ptb-promt-box');
            $('.ptb-promt-box').addClass('show-changelog');

        });
    });
}(jQuery, window, document));
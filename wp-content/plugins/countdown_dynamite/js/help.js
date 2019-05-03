/**
* Countdown Dynamite help JS
*/
"use strict";

jQuery(function($) {
    $('.ucd-help-link').click(function() {
        ucd_showHelpTooltip($(this).attr('data-identifier'));
        return false;
    });

    $('#ucd-help-bkg, .ucd-help-close').click(function() {
        ucd_hideHelpTooltip();
        return false;
    });

    function ucd_hideHelpTooltip()
    {
        $('.ucd-help-block').hide();
        $('#ucd-help-bkg').hide();
    }

    function ucd_showHelpTooltip(identifier)
    {
        ucd_hideHelpTooltip();
        $('#ucd-help-bkg').show();
        $('#ucd-help-block-' + identifier).slideDown('fast');
    }
});
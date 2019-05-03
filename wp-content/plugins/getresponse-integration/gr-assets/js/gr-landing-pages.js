function grLpGenerateSlug(string)
{
    return string
        .toString()
        .trim()
        .toLowerCase()
        .replace(/\s+/g, "-")
        .replace(/[^\w\-]+/g, "")
        .replace(/\-\-+/g, "-")
        .replace(/^-+/, "")
        .replace(/-+$/, "");
}

function grLpValidation(tr)
{
    var id = tr.find('.pageId').val(),
        url = tr.find('.pageUrl').val().replace(/^\/|\/$/g, ''),
        editUrlList = jQuery('.edit-url'),
        validatorHandler = jQuery('.gr-url-validator');

    if (editUrlList.length > 0) {
        var usedUrls = editUrlList.map(function(){return jQuery.trim(jQuery(this).text());}).get();

        if (jQuery.inArray(url, usedUrls) >= 0) {
            validatorHandler.addClass('gr-lp-url-error');
            return false;
        }

        validatorHandler.removeClass('gr-lp-url-error');
    }

    if (id === null || id.length === 0) {
        return false;
    }

    return true;
}

function grLpClickActions()
{
    jQuery('.wp-list-table .trash').click(function (e) {
        e.preventDefault();
        if (confirm(deleteConfirmationMsg)) {
            window.location.href = controllerUrl + '&action=remove_landing_page&id=' + jQuery(this).closest('tr').attr('data-id');
        }
    });

    jQuery('#gr_lp_add').unbind('click').bind('click', function(e) {
        e.preventDefault();
        var tr = jQuery('<tr></tr>');
        tr.insertBefore('#gr_edit_lp_box');
        grLpQuickEdit(tr, '', '', '', 0);
    });

    jQuery('.row-actions .edit').unbind('click').bind('click', function(e) {
        e.preventDefault();
        var tr = jQuery(this).closest('tr'),
            originalId = tr.find('.edit-id').text(),
            originalTitle = tr.find('td').eq(1).find('span').eq(0).text(),
            originalUrl = tr.find('.edit-url').text(),
            originalStatus = tr.find('.edit-status').text();

        grLpQuickEdit(tr, originalId, originalTitle, originalUrl, originalStatus);
    });
}

function grLpQuickEdit(tr, originalId, originalTitle, originalUrl, originalStatus)
{
    var activeEditBox = jQuery('#gr_edit_active_box');
    if (activeEditBox.length > 0) {
        activeEditBox.find('button.cancel').trigger('click');
    }

    var handler = jQuery('#gr_edit_lp_box').clone();

    tr.attr('id', 'gr_edit_active_box');
    handler.removeClass('hidden');
    if (originalId.length > 0) {
        handler.find('.pageId').val(originalId);
    }
    handler.find('.pageUrl').val(originalUrl);
    handler.find('input:radio[name=pageStatus][value=' + originalStatus +']').attr('checked', true)

    tr.attr('class', handler.attr('class'));
    tr.html(handler.find('td'));

    jQuery('#gr_edit_active_box button.cancel').unbind('click').bind('click', function (e) {
        e.preventDefault();

        if (originalId.length === 0) {
            tr.remove();
        } else {
            grReplaceRow(tr, originalTitle, originalId, originalUrl, originalStatus);
        }
    });

    jQuery('#gr_edit_active_box .pageId').unbind('change').bind('change', function() {
        if (jQuery('#gr_edit_active_box .pageUrl').val().length === 0) {
            jQuery('#gr_edit_active_box .pageUrl').val(
                grLpGenerateSlug(jQuery('#gr_edit_active_box .pageId option:selected').text())
            );
        }
    });

    jQuery('#gr_edit_active_box button.save').unbind('click').bind('click', function (e) {
        e.preventDefault();
        var box = jQuery('#gr_edit_active_box'),
            title = box.find('.pageId option:selected').text(),
            id = box.find('.pageId').val(),
            url = box.find('.pageUrl').val().replace(/^\/|\/$/g, ''),
            status = box.find('input:radio[name=pageStatus]').eq(1).is(':checked') ? 1 : 0;

        if (grLpValidation(tr)) {
            grReplaceRow(tr, title, id, url, status);

            jQuery.post(
                controllerUrl + '&action=add_landing_page',
                {id: id, url: url, status: status, key: originalUrl}
            );

            if (jQuery('#gr_lp_nodata').length > 0) {
                jQuery('#gr_lp_nodata').remove();
            }
        }
    });
}

function grReplaceRow(tr, title, id, url, status)
{
    var html = '<tr><td class="manage-column column-cb check-column">' +
        '<span class="hidden">'+
        '<span class="edit-id">' + id + '</span>'+
        '<span class="edit-url">' + url + '</span>'+
        '<span class="edit-status">' + status + '</span>'+
        '</span>'+
        '</td>'+
        '<td class="title column-title has-row-actions column-primary page-title"><span class="gr-cp-title">'+
        title +
        '</span>' +
        '<span> — </span>' +
        '<span>' + (status == 1 ? publishedText : unpublishedText) + '</span><div class="row-actions">'+
        '<span class="edit"><a href="#">'+ editText + '</a> | </span>'+
        '<span class="trash"><a class="submitdelete">' + trashText + '</a></span>'+
        '</div>'+
        '</td>'+
        '<td><a target="_blank" href="' + homeUrl + '/' + url + '">' + homeUrl + '/' + url + '</a></td></tr>';

    tr.replaceWith(html);
    grLpClickActions();
}

jQuery(document).ready(function () {
    grLpClickActions();
});
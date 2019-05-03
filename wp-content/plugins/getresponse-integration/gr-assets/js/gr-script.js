function Campaigns() {
}

function AutoResponders() {
}

Campaigns.prototype.load_campaigns = function (selector_id, campaigns) {

    var select = document.getElementById(selector_id);
    var selected = select.getAttribute('data-selected');

    campaigns.forEach(function (campaign) {
        var option = document.createElement('option');

        option.value = campaign.campaignId;
        option.text = campaign.name;

        if (campaign.campaignId === selected) {
            option.setAttribute('selected', 'selected');
        }

        select.appendChild(option);
    });
};

AutoResponders.prototype.load_responders = function (selector_id, responders) {

    var option;
    var select = document.getElementById(selector_id);
    var selected = select.getAttribute('data-selected');

    select.innerHTML = '';

    var selected_campaign = jQuery('#' + selector_id).parent().parent().parent().find('.campaign-select').val();

    if (responders[selected_campaign] !== undefined) {

        for (var id in responders[selected_campaign]) {

            var responder = responders[selected_campaign][id];

            option = document.createElement('option');

            option.value = responder.id;
            option.text = 'Day ' + responder.day + ': ' + responder.name;

            if (responder.id === selected) {
                option.setAttribute('selected', 'selected');
            }

            select.appendChild(option);
        }
    } else {
        option = document.createElement('option');
        option.value = null;
        option.text = 'no autoresponders';
        select.appendChild(option);
    }
};

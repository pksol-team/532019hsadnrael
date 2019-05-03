(function ($, window, document, undefined) {
    'use strict';
    $(document).ready(function () {
        $('body').on('change', '.ptb_widget_resent_posts', function () {
            var self = $(this),
                wrapper = self.closest('.ptb_recent_widget_wrapper'),
                type = self.data('type'),
                $val = self.val(),
                $data = type===$val?self.data('data'):[],
                $name = self.data('name'),
                $id = self.data('id');
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {'action': 'ptb_ajax_get_post_type', 'post_type': $val, 'type': 'ptb_widget'},
                dataType: 'json',
                global:false,
                beforeSend: function () {
                    wrapper.addClass('ptb_widget_wait');
                },
                complete: function () {
                    wrapper.removeClass('ptb_widget_wait');
                },
                success: function (resp) {
                    var html = '';
                    if (resp) {
                        //  resp.data = resp.data.reverse();
                        var value=null;
                        for (var i in resp.data) {
                            var id = $id.replace('#name#', i),
                                name = $name.replace('#name#', i);
                                value = (typeof $data[i]!=='undefined' && $data[i])?$data[i]:(typeof resp.data[i].value!=='undefined' && resp.data[i].value?resp.data[i].value:'');
                            html += '<div class="ptb_cmb_input_row">';
                            html += '<label class="ptb_cmb_input_label" for="' + id + '">' + resp.data[i].label + ':</label>';
                            html += '<div class="ptb_cmb_input">';
                            if (resp.data[i].type === 'listbox') {
                                html += '<select id="' + id + '" name="' + name + '">';
                                for (var j in resp.data[i].values) {
                                    var selected = value===resp.data[i].values[j].value?'selected="selected"':'';
                                    html += '<option '+selected+' value="' + resp.data[i].values[j].value + '">' + resp.data[i].values[j].text + '</option>';
                                }
                                html += '</select>';
                            }
                            else if (resp.data[i].type === 'textbox') {
                                html += '<input value="'+value+'" id="' + id + '" name="' + name + '" type="text"/>';
                            }
                            else if (resp.data[i].type === 'radio') {
                                var checked = typeof $data[i]!=='undefined' || (value && Object.keys($data).length===0)?'checked="checked"':'';
                                html += '<input '+checked+' id="' + id + '" name="' + name + '" type="checkbox" value="1"/>';
                            }
                            html += '</div></div>';
                        }
                        if (resp.tax && resp.tax.data) {
                            var tax_data = resp.tax.data;
                            for (var i in tax_data) {
                                if(tax_data[i].type==='multiselect'){
                                    var ptb_name = tax_data[i].name;
                                    id = $id.replace('#name#', ptb_name),
                                    name = $name.replace('#name#', ptb_name);
                                    value = (typeof $data[ptb_name]!=='undefined' && $data[ptb_name])?$data[ptb_name]:[];

                                    html += '<div class="ptb_cmb_input_row">';
                                    html += '<label class="ptb_cmb_input_label" for="' + id + '">' + tax_data[i].label + ':</label>';
                                    html += '<div class="ptb_cmb_input">';
                                    html += '<select multiple="multiple" id="' + id + '" name="' + name + '[]">';
                                    for (var j in tax_data[i].values) {
                                        var selected = value.length>0 && $.inArray(tax_data[i].values[j].value,value)!==-1?'selected="selected"':'';
                                        html += '<option '+selected+' value="' + tax_data[i].values[j].value + '">' + tax_data[i].values[j].text + '</option>';
                                    }
                                    html += '</select>';
                                    html += '</div></div>';
                                }
                            }
                        }
                    }
                    wrapper.find('.ptb_recent_widget_items').html(html);
                }
            });
        });
    });
}(jQuery, window, document));

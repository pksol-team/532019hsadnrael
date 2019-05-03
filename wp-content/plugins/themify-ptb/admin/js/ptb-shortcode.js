(function ($, window, document, undefined) {
    'use strict';
     function ptb_show_number_range(item,val){
        var  nextall = item.nextAll(),
                el = nextall.first(),
                origw = '',
                v = val,
                last = nextall.last();
            if(v==='='){
                if(!last.is(':visible')){
                    return false;
                }
                origw = last.offset().left + last.width()-el.offset().left-parseFloat(el.css('padding-right'))-parseFloat(el.css('padding-left'));
                nextall.not(':first').hide();
                el.data('placeholder',el.prop('placeholder')).prop('placeholder','');
            }
            else{
                origw = last.prev().width();
                setTimeout(function(){
                    if(v!=='='){
                        nextall.show();
                        el.prop('placeholder',el.data('placeholder'));
                    }
                },800);

            }
            el.width(origw);
    }
    
    function ptb_parse_query($k,meta,type,resp){
            var container = {
                type: 'container',
                layout: 'flex',
                direction: 'row',
                classes:'ptb_group_meta_items',
                items:[
                        {
                            type:'label',
                            text:meta.label,
                            classes:'ptb_group_meta_label'
                        }
                    ]
            };
            if(!meta.hide_exist){
                container.items.push( {
                            classes:'ptb_meta_exist',
                            name: 'ptb_'+type+'_'+$k+'_exist',
                            type:'radio',
                            tooltip:resp.meta_exists_tooltip.replace('%s',meta.label),
                            values:1,
                            onchange:function(e){
                               var next = $($(this)[0].$el).nextAll();
                               if(this.value()){
                                   next.hide();
                               }
                               else{
                                   next.show();
                               }
                            }
                        });
            }
            else{
                container.items[0].classes+=' ptb_meta_exist_empty';
            }
            if(meta.hide){
               return container;
            }
            var $form_items = {
                classes:'ptb_meta_items',
                name: meta.name,
                hide:false,
                values: meta.values ? meta.values : ''
            };
            $form_items = $.extend(true, $form_items, meta);
            if(meta.type==='number'){
                $form_items.type = 'textbox';
            }
            if(meta.type==='multiselect'){
                $form_items.type = 'container';
                container.classes+= ' ptb_group_meta_multiselect';
                $form_items.html = '<select id="'+meta.name + '_select" style="width:100%;" multiple="multiple">';
                for (var $i in meta.values) {
                    $form_items.html += '<option value="' + meta.values[$i].value + '">' + meta.values[$i].text + '</option>';
                }
                $form_items.html += '</select>';
            }
            else if(meta.type==='textbox'){
                container.items.push({
                    classes:'ptb_meta_small_select',
                    name: $k+'_slike',
                    type:'listbox',
                    tooltip:resp.meta_like_start_tooltip.replace('%s',meta.label),
                    values:[
                        {text:'','value':''},
                        {text:'%','value':'%'}
                    ]
                });
            }
            else if(meta.type==='number'){
                container.items.push(
                    {
                        classes:'ptb_meta_small_select ptb_meta_number_sign',
                        name: $k+'_from_sign',
                        type:'listbox',
                        values:[
                            {text:'=','value':'='},
                            {text:'>=','value':'>='},
                            {text:'>','value':'>'}
                        ],
                        onselect:function(e){
                           ptb_show_number_range($($(this)[0].$el),this.value());
                        }
                    },
                    {
                        classes:'ptb_meta_small_input ptb_meta_placeholder ptb_meta_number_from',
                        name: meta.name+'_from',
                        type:'textbox',
                        value:'From'
                    },
                     {
                        classes:'ptb_meta_space',
                        type:'textbox'
                    }
                );
            }
            if(meta.type!=='number'){
                container.items.push($form_items);
            }
            else if(meta.type==='number'){
                container.items.push(

                    {
                        classes:'ptb_meta_small_input ptb_meta_placeholder ptb_meta_number_to',
                        name: meta.name+'_to',
                        type:'textbox',
                        value:'To'
                    },
                    {
                        classes:'ptb_meta_small_select',
                        name: $k+'_to_sign',
                        type:'listbox',
                        values:[
                            {text:'<=','value':'<='},
                            {text:'<','value':'<'}
                        ]
                    }
                );
            }
            if(meta.type==='textbox'){
                container.items.push({
                    classes:'ptb_meta_small_select',
                    name: $k+'_elike',
                    type:'listbox',
                    tooltip:resp.meta_like_end_tooltip.replace('%s',meta.label),
                    values:[
                        {text:'','value':''},
                        {text:'%','value':'%'}
                    ]
                });
            }
            return container;
    }
    if (typeof ptb_shortcodes_button !== 'undefined' && ptb_shortcodes_button && ptb_shortcodes_button.length > 0) {
        
        tinymce.PluginManager.add('ptb', function (editor, url) {
            var $items = [],
                _keys=[];
            for (var k in ptb_shortcodes_button) {
                if(typeof ptb_shortcodes_button[k]!=='object'){
                    continue ;
                }
                var $item = {
                    'text': ptb_shortcodes_button[k].name,
                    'body': {
                        'type': ptb_shortcodes_button[k].type
                    },
                    onclick: function (e) {
                        var $settings = this.settings.body;
                        $.ajax({
                            url: $ptb_url,
                            type: 'POST',
                            dataType: 'json',
                            data: {'post_type': $settings.type},
                            success: function (resp) {
                                if (resp) {
                                   
                                    var post_data = [],
                                        $data = resp.data;
                                    for (var $key in $data) {

                                        var $form_items = {
                                            'classes':'ptb_group_data_item',
                                            'name': $key,
                                            'values': $data[$key].values ? $data[$key].values : '',
                                        };

                                        $form_items = $.extend(true, $form_items, $data[$key]);
                                        post_data.push($form_items);
                                    }
                                    if (typeof resp.tax!=='undefined'  && resp.tax.data) {
                                        if(typeof resp.tax.title!=='undefined'){
                                            post_data.push({
                                                fixedWidt: true,
                                                html: '<div data-group="taxes" class="ptb_group_container">'+resp.tax.title+'</div>',
                                                height:100,
                                                type: 'container'
                                            });
                                        }
                                        var taxes = resp.tax.data;
                                        for (var i in taxes) {
                                            var $list = {
                                                classes:'ptb_group_taxes_item',
                                                label: taxes[i].label,
                                                name:taxes[i].name,
                                                fixedWidth: true,
                                                tooltip:taxes[i].tooltip,
                                                html: '<select id="' + taxes[i].name + '_select"  style="width:100%;" multiple="multiple">',
                                                type: taxes[i].type==='multiselect'?'container':taxes[i].type
                                            };
                                            if(taxes[i].type==='multiselect'){
                                                for (var $i in taxes[i].values) {
                                                    $list.html += '<option value="' + taxes[i].values[$i].value + '">' + taxes[i].values[$i].text + '</option>';
                                                }
                                                $list.html += '</select>';
                                            }
                                            else{
                                                $list = $.extend(true, $list, taxes[i]);
                                            }
                                            post_data.push($list);
                                        }
                                    }
                                    
                                    if (typeof resp.field!=='undefined' && resp.field.data) {
                                       
                                        var field_items = {
                                                type: 'container',
                                                layout: 'flex',
                                                direction: 'column',
                                                align: 'left',
                                                spacing: 15,
                                                items:[]
                                        };
                                        if( typeof resp.field.title!=='undefined'){
                                            post_data.push({
                                                fixedWidth: true,
                                                html: '<div data-group="meta" class="ptb_group_container">'+resp.field.title+'</div>',
                                                height:100,
                                                type: 'container'
                                            });
                                        }
                                        var fields = resp.field.data;
                                        for (var $key in fields) {
                                            _keys[fields[$key].name+(fields[$key].type==='number'?'_from':'')] = {type:fields[$key].type,key:$key,field:true};
                                            field_items.items.push(ptb_parse_query($key,fields[$key],'field',resp));
                                        }
                                        post_data.push(field_items);
                                    }
                                    if (typeof resp.meta!=='undefined' && resp.meta.data) {
                                       
                                        var meta_items = {
                                                type: 'container',
                                                layout: 'flex',
                                                direction: 'column',
                                                align: 'left',
                                                spacing: 15,
                                                items:[]
                                        };
                                        if( typeof resp.meta.title!=='undefined'){
                                            post_data.push({
                                                fixedWidth: true,
                                                html: '<div data-group="meta" class="ptb_group_container">'+resp.meta.title+'</div>',
                                                height:100,
                                                type: 'container'
                                            });
                                        }
                                        var meta = resp.meta.data;
                                        for (var $key in meta) {
                                            _keys[meta[$key].name+(meta[$key].type==='number'?'_from':'')] = {type:meta[$key].type,key:$key,field:false};
                                            meta_items.items.push(ptb_parse_query($key,meta[$key],'meta',resp));
                                        }
                                        post_data.push(meta_items);
                                    }
                                   editor.windowManager.open({
                                        id: 'ptb_shortcode_dialog',
                                        body: post_data,
                                        title: resp.title,
                                        onOpen:function(){
                                            var placeholder = $('input.mce-ptb_meta_placeholder'),
                                                number_range = $('.mce-ptb_meta_number_sign');
                                            if(placeholder.length>0){
                                                placeholder.each(function(){
                                                   $(this).prop('placeholder',$(this).val()).val(''); 
                                                });
                                            }
                                            if(number_range.length>0){
                                                number_range.each(function(){
                                                    ptb_show_number_range($(this),'=');
                                                });
                                            }
                                        },
                                        onsubmit: function (e) {
                                            var $short = '',
                                                $trigger_short = $.event.trigger("PTB.insert_shortcode", {'shortcode': $short, 'setting': $settings, 'data': e.data});
                                            if ($trigger_short) {
                                                $short = $trigger_short;
                                            }
                                            else {
                                                $short = '[ptb type="' + $settings.type + '"';
                                                var items = [];
                                                for (var $k in e.data) {
                                                    var is_field = _keys[$k] && _keys[$k].field,
                                                        orig_key = _keys[$k]?_keys[$k].key:'',
                                                        field_key = is_field?'ptb_field_':'ptb_meta_',
                                                        exist = !_keys[$k] || (_keys[$k] && !e.data[field_key+orig_key+'_exist']);
                                                    
                                                        if ($('#' + $k + '_select').length > 0 && exist) {
                                                            var $vals = $('#' + $k + '_select').val();
                                                            items[$k] = $vals ? $vals.join(', ') : false;
                                                        }
                                                        else {
                                                            var val = e.data[$k];
                                                            if(typeof val==='string'){
                                                                val = $.trim(val);
                                                            }
                                                            else if(val===true){
                                                                val = 1;
                                                            }
                                                            if(_keys[$k]){
                                                                var include = true;
                                                                if(_keys[$k].type==='textbox' && (!val || !exist)){
                                                                    include = e.data[orig_key+'_slike'] = e.data[orig_key+'_elike'] = items[orig_key+'_slike'] = items[orig_key+'_elike'] = false;
                                                                }
                                                                else if(_keys[$k].type==='number'){
                                                                 
                                                                     if(!$.isNumeric(val) || !exist){
                                                                         include = e.data[orig_key+'_from_sign']  = items[orig_key+'_from_sign'] = false;
                                                                     }
                                                                     else{
                                                                        val = parseFloat(val);
                                                                     }
                                                                     if(!exist || !$.isNumeric(e.data[field_key+orig_key+'_to']) || e.data[orig_key+'_from_sign']==='='){
                                                                         e.data[field_key+orig_key+'_to'] = e.data[orig_key+'_to_sign'] = items[field_key+orig_key+'_to'] = items[orig_key+'_to_sign'] = false;
                                                                         if(e.data[orig_key+'_from_sign']==='='){
                                                                            e.data[orig_key+'_from_sign'] = items[orig_key+'_from_sign'] = false;
                                                                         }
                                                                     }
                                                                     else{
                                                                         e.data[field_key+orig_key+'_to'] = parseFloat(e.data[field_key+orig_key+'_to']);
                                                                     }
                                                                }
                                                                if(include){
                                                                    items[$k] = val;
                                                                }
                                                            }
                                                            else if (val && exist){
                                                                var tax_key = $k.indexOf('_children')!==-1?'_children':($k.indexOf('_operator')!==-1?'_operator':false);
                                                                if(tax_key){
                                                                    tax_key = $k.replace(tax_key,'');
                                                                    if($('#ptb_tax_'+tax_key+'_select').length>0 && (!$('#ptb_tax_'+tax_key+'_select').val() || val==='in')){
                                                                        continue;
                                                                    }
                                                                }
                                                                items[$k] = val;
                                                            }
                                                        }
                                                    
                                                }
                                                for (var $k in items) {
                                                    if(items[$k]){
                                                        $short+= ' ' + $k + '="' + items[$k] + '"';
                                                    }
                                                }
                                                $short += ']';
                                            }
                                            editor.insertContent($short);
                                        }
                                    });
                                }
                            }
                        });
                    },
                    classes: ptb_shortcodes_button[k].classes
                };
                $items.push($item);
            }
            editor.addButton('ptb', {
                icon: 'ptb-favicon',
                type: 'menubutton',
                title: 'PTB Shortcodes',
                menu: $items
            });
            $(document).ready(function() {
                
                $(document)
                .off('change','.mce-ptb_meta_number_from,.mce-ptb_meta_number_to')
                .on('change','.mce-ptb_meta_number_from,.mce-ptb_meta_number_to',function(e){
                    if(!$.isNumeric($(this).val())){
                        var v = parseFloat($(this).val());
                        if(isNaN(v)){
                            v = '';
                        }
                        $(this).val(v);
                    }
                });
            });
        });
    }
}(jQuery, window, document));

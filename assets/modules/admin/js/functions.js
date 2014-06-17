var active_dialog;

// dialog-extend options
var dialogExtendOptions = {
    "close" : true,
	"maximize" : false,
	"minimize" : true,
	"dblclick" : 'collapse',
	"titlebar" : false
};

function save_filter(href) {
    $.ajax({
        url: main_url + "admin/ajax/save_filter",
        type: "POST",
        data: filter,
        dataType: "json",
        success: function(data) {
            if (data.status == 'ok') {
                window.location.href = href;
                //window.location.href = window.location.href;
            }
        }
    });
}

function post_ajax(url, data, form, parent) { 
    if (typeof parent == 'undefined') {
        parent = form.closest('div.portlet');
    }
    
    $.ajax({
        url: url,
        type: "POST",
        dataType: "json",
        data: data,
        beforeSend: function() {
            $('.list_content', parent).addClass('small_ajax_loading');
        },
        success: function(data) {
            if (data.status == 'ok') {
                if (typeof data.callback != 'undefined') {
                    if ($.isFunction(window[data.callback.name])) {
                        window[data.callback.name](parent, data.callback);
                    }
                }
            }
            if (typeof data.messages != 'undefined') {
                $('#info').html(data.messages);
            }
        },
        complete: function() {
            $('.list_content', parent).removeClass('small_ajax_loading');
        },
    });
}

function adapt_links() {
    $('#content').on('click', '.pagination-container a', function() {
        if ($(this).parent().parent().hasClass('no-ajax')) {
            return true;
        }
    
        filter["page_offset"] = $(this).attr('rel');

        var parent = $(this).closest('div.portlet');
        $.ajax({
            url: $(this).attr('href'),
            type: 'POST',
            data: filter,
            dataType: 'json',
            beforeSend: function() {
                $('.list_content', parent).addClass('small_ajax_loading');
            },
            success: function(data) {
                if (typeof data.callback != 'undefined') {
                    if ($.isFunction(window[data.callback.name])) {
                        window[data.callback.name](parent, data);
                    }
                }
            },
            complete: function() {
                $('.list_content', parent).removeClass('small_ajax_loading');
            },
        });
        return false;
    });

    $('#wrapper').on('click', 'a.save-filter', function() {
        var href = $(this).attr('href');
        save_filter(href);
        return false;
    });
    

    
    $('body').on('click', 'a.confirm-delete', function() {
        var that = $(this);
        var type = that.attr('rel');
        if (that.hasAttr('href')) {
            var url = that.attr('href');
        }
        var message = confirm_delete_message(type);
        
        var dialog = $('<div></div>')
            .attr('title', __('Confirm your choice'))
            .html(message)
            .dialog({
                position: { my: "center bottom", at: "center top", of: that, collision: "flip"}, 
                autoOpen: true,
                bgiframe: true,
                resizable: false,
                buttons: {
                    'No': function() { $(this).dialog('close');},
                    'Yes': function() {
                        $(this).dialog('close');
                        if (that.hasClass('no-ajax')) {
                            window.location.href = that.attr('href');
                            return true;
                        }
                        
                        if (that.hasClass('run-function')) {
                            if ( ! that.hasAttr('rel')) return false;
                            var rel = that.attr('rel');
                            if ($.isFunction(window[rel])) {
                                window[rel](that);
                            }
                            return true;
                        }
                        post_ajax(url, null, that);
                    }
                },
                modal: true
            });
        return false;
  });
  
    var cache = {};
    var ac_callback;
    monkeyPatchAutocomplete();
    
    $('.ac_search').each(function(index) {
        var that = $(this);
        var field = that.attr('name');
        that.autocomplete({
            minLength: 2,
            delay: 500,
            source: function( request, response ) {
                var term = request.term;
                if (term in cache) {
                    response(cache[term]);
                    return;
                }
                
                $.ajax({
                    url: main_url + that.closest('form').attr('action') + '/' + field,
                    type: 'POST',
                    dataType: 'json',
                    data: { 'filter' : filter, 'term' : term},
                    success: function(data) {
                        cache[term] = data.ac;
                        response(data.ac);
                        if (typeof data.callback != 'undefined') {
                            ac_callback = data.callback;
                        } else {
                            ac_callback = '';
                        }
                    }
                });
            },
            select: function (a, b) {
                clear_ac_search();
                $(this).val(b.item.value);
                if (typeof filter['search'] == 'undefined') {
                    filter['search'] = {};
                }

                filter['search'][field] = { 'value' : b.item.value, 'id' : b.item.id};
                if ($.isFunction(window[ac_callback.name])) {
                    var parent = $('#' + that.closest('form').attr('rel')).closest('div.portlet');
                    window[ac_callback.name](parent, ac_callback);
                }
            }
        });
    });
  
  
}

function clear_active_list_header() {
    $('th.sort.desc').removeClass('desc').addClass('inactive');
    $('th.sort.asc').removeClass('asc').addClass('inactive');
}

function change_sorting_order(field, order) {
    filter["sorting"] = [field, order];
    $('#'+field).removeClass('inactive').addClass(order);
    check_portlet_for_callback($('#'+field).closest('div.portlet'));
}

function clear_static_filter() {
    $('.static-filter').val('');
    $('.dd-select').each(function() {
        $(this).selectedIndex = 0;
        $(this).msDropDown().data("dd").set('selectedIndex', 0);
    });
    filter['static'] = {};
}

function apply_static_filter_on_load() {
    if (typeof filter == 'undefined') return false;
    if (typeof filter['static'] == 'undefined') return true;
    if (filter['static'].length == 0) return true;
    var cur_obj;
    for (key in filter['static']) {
        cur_obj = $('.static-filter[name="'+key+'"]');
        if (cur_obj.length == 0) continue;
        cur_obj.val(filter['static'][key]);
        if (cur_obj.hasClass('dd-select')) {
            //cur_obj.msDropDown().data("dd").refresh();
            cur_obj.msDropDown().data("dd").set("selectedIndex", filter['static'][key]);
        }
    }
}

function clear_filter(obj) {
    
    clear_ac_search();
    clear_static_filter();
    
    check_filter_for_callback(obj);
    $('.letter_filter a', obj.closest('div.portlet')).removeClass('current');
    return false;
}

function check_filter_for_callback(obj) {
    var parent_form = obj.closest('form');
    var parent = '';
    if (parent_form.hasAttr('rel')) {
        parent = $('#'+parent_form.attr('rel'));
        if (parent.length == 1) {
            parent = parent.closest('div.portlet');
        }
    }
    
    if (parent == '') {
        parent = obj.closest('div.portlet');
    }
    check_portlet_for_callback(parent);
}

function check_portlet_for_callback(portlet_obj) {
    var callback = {
        'name': portlet_obj.data('loadcallback') || '',
        'url': portlet_obj.data('callbackurl') || ''
    }
    
    if (callback.name != '' && callback.url != '') {
        if ($.isFunction(window[callback.name])) {
            window[callback.name](portlet_obj, callback);
        }
    }
}



$(function() {
    apply_static_filter_on_load();
    $('body').on('submit', 'form.ajax', function(e) {
        post_ajax($(this).attr('action'), $(this).serializeArray(), $(this));
        return false;
    });
    
    $('body').on('click', '.calendar_icon', function() {
        $('#'+$(this).attr('rel')).datepicker('show');
    });
    
    $('body').on('change', '.static-filter', function() {
        if (typeof filter['static'] == 'undefined') {
            filter['static'] = {};
        }
        var val = $(this).val();
        if (val == '') {
            if (typeof filter['static'][$(this).attr('name')] != 'undefined') {
                delete filter['static'][$(this).attr('name')];
            }
        } else {
            filter['static'][$(this).attr('name')] = val;
        }
        check_filter_for_callback($(this));
    });
    
    $(".datepicker").each(function() {
        var that = $(this);
        that.datepicker({
            buttonImageOnly: true,
            showOn: 'button',
            buttonText: ''
        });
        if (that.hasAttr('rel')) {
            var rel_obj = $('#' + that.attr('rel'));
            if (rel_obj.length == 0) return false;
            if (strpos(that.attr('name'), '_from') != false) {
                that.datepicker('option', 'onClose',  function( selectedDate ) {
                    rel_obj.datepicker( "option", "minDate", selectedDate );
                });
            } else if (strpos(that.attr('name'), '_to') != false) {
                that.datepicker('option', 'onClose',  function( selectedDate ) {
                    rel_obj.datepicker( "option", "maxDate", selectedDate );
                });
            }
        }
    });
    
    $('ul.messages li').livequery(function() {$(this).AutoHideMessage({'autoHide': true, 'time':20000})});
    
    $('body').on('click', 'th.sort span', function() {
        var parent = $(this).parent();
        var class_list = parent.attr('class').split(/\s+/);
        var id = parent.attr('id');
        $.each(class_list, function(index, item) {
            if (index == 1) {
                switch(item) {
                    case 'inactive':
                        clear_active_list_header();
                        change_sorting_order(id, 'asc');
                    break;
                    case 'desc':
                        clear_active_list_header();
                        change_sorting_order(id, 'asc');
                    break;
                    case 'asc':
                        clear_active_list_header();
                        change_sorting_order(id, 'desc');
                    break;
                }
            }
        });
    });

    adapt_links();
    
    $('body').on('mouseenter', 'li.action-list-menu-releaser', function() {
        $('.action-list-menu', $(this)).removeClass('hidden');
    }).on('mouseleave', 'li.action-list-menu-releaser', function() {
        $('.action-list-menu', $(this)).addClass('hidden');
    });
    
    $('#portlets').on('click', '.run-on-click', function() {
    
        var obj = $(this);
        if ( ! obj.hasAttr('rel')) return false;
        var rel = obj.attr('rel');
        
        if ($.isFunction(window[rel])) {
            return window[rel](obj);
        }
        return false;
    });

    $('#portlets').on('change', '.toogle-all-checkboxes', function() {
        var current_state = ! $(this).prop('checked');

        if (current_state == false) {
            var checkboxes = $("input.toogle-checkbox:checkbox:not(:checked)");
            var new_state = 1;
        } else {
            var checkboxes = $("input.toogle-checkbox:checkbox:checked");
            var new_state = 0;
        }
        if (checkboxes.length == 0) return;
        var checkboxes_arr = {};
        checkboxes.each(function( index ) {
            var data = {'name' : $(this).attr('name'), 'value' : new_state, 'rel' : $(this).attr('rel')};
            checkboxes_arr[index] = data;
        });
        var parent = $(this).closest('div.portlet');
        
        $.ajax({
            url: main_url + "admin/ajax/toogle_checkboxes",
            type: 'POST',
            data: {checkboxes: checkboxes_arr},
            dataType: 'json',
            beforeSend: function() {
                $('.list_content', parent).addClass('small_ajax_loading');
            },
            success: function(data) {
                if (data.status == 'ok') {
                    if (data.chk_selected_total == 0) {
                        $('span.chk_selected_total', parent).html('');
                    } else {
                        $('span.chk_selected_total', parent).html('(' + data.chk_selected_total + ')');
                    }
                    checkboxes.prop('checked', ! current_state);
                } else if (data.status == 'error') {
                    $('#info').html(data.messages);
                }
            },
            complete: function() {
                $('.list_content', parent).removeClass('small_ajax_loading');
            },
        });
        
    });
    
    $('#portlets').on('change', '.toogle-checkbox', function() {
        var input = $(this);
        if (input.is(':checked')) {
            val = 1;
        } else {
            val = 0;
        }
        var parent = input.closest('div.portlet');
        var data = {'name' : input.attr('name'), 'value' : val, 'rel' : input.attr('rel')};
        
        $.ajax({
            url: main_url + "admin/ajax/toogle_checkbox",
            type: 'POST',
            data: data,
            dataType: 'json',
            beforeSend: function() {
                $('.list_content', parent).addClass('small_ajax_loading');
            },
            success: function(data) {
                if (data.status == 'ok') {
                    if (data.chk_selected_total == 0) {
                        $('span.chk_selected_total', parent).html('');
                    } else {
                        $('span.chk_selected_total', parent).html('(' + data.chk_selected_total + ')');
                    }
                } else if (data.status == 'error') {
                    $('#info').html(data.messages);
                }
            },
            complete: function() {
                $('.list_content', parent).removeClass('small_ajax_loading');
            },
        });
    });
    
    
    
    $('body').on('click', '.portlet-header .minmax', function () {
        $(this).toggleClass("ui-icon-triangle-1-n").toggleClass("ui-icon-triangle-1-s");
        $(this).parents(".portlet:first").find(".portlet-content").toggle();
    });
    
    //Popout button click
    $("body").on("click", ".portlet-header .popout", function () {
        var portletId = $(this).closest(".portlet");

        if (portletId.hasClass('popup')) {
            $(this).attr("title", "Pop-out").removeClass("ui-icon").removeClass("ui-icon-arrowthick-1-se").parents(".portlet");
            $(".popout").addClass("ui-icon").addClass("ui-icon-extlink");
            popin(portletId);
        } else {
            //only one pop out at a time, remove popout link to all
            $(".popout").removeClass("ui-icon").removeClass("ui-icon-extlink");
            $(this).attr("title", "Pop-in").addClass("ui-icon").addClass("ui-icon-arrowthick-1-se").parents(".portlet");
            popout(portletId);
        }
    });
    
    $(document).tooltip({
        items: '.tooltip',
        content: function(){
            var content = $(this).data("tooltip") || $(this).attr('title');
            return content;
        }
    });
    
    $('.letter_filter a').click(function() {
        if (typeof filter['search']['user_id'] != 'undefined') {
            //delete filter['search']['user_id'];
            clear_ac_search();
        }
        filter["letter_filter"] = $(this).attr('rel');
        filter["page_offset"] = 1;
        
        
        var parent_form = $('form', $(this).closest('div.portlet'));

        var parent = '';
        if (parent_form.hasAttr('rel')) {

            parent = $('#'+parent_form.attr('rel'));
            if (parent.length == 1) {
                parent = parent.closest('div.portlet');
            }
        }
    
        if (parent == '') {
            parent = $(this).closest('div.portlet');
        }
    
        var callback = {
            'name': parent.data('loadcallback') || '',
            'url': parent.data('callbackurl') || ''
        }
    
        if (callback.name != '' && callback.url != '') {
            if ($.isFunction(window[callback.name])) {
                window[callback.name](parent, callback);
            }
        }
        
        $('.letter_filter a').removeClass('current');
        $(this).addClass('current');
        return false;
    });
});

function popout(portletId) {
    prtlt = portletId;
    var width = prtlt.width();
    $('<div id="popout-base-container"></div>').insertBefore(prtlt);
    prtlt.appendTo(document.body).removeClass("sortable").addClass("popup").draggable({
        handle: prtlt.find(".portlet-header")
    }).resizable({
        minWidth: 150,
        minHeight: 70,
        alsoResize: prtlt.find(".content")
    }).css({
        "width": width,
        "position": "fixed",
        "z-index": "101",
        "top": 0
    });
    calc_popup_position();
    
    
}

function popin(portletId) {
    var popout_container = $("#popout-base-container");
    prtlt = portletId;
    prtlt.insertAfter(popout_container);
    popout_container.remove();
    $(prtlt).addClass("sortable").removeClass("popup").draggable("destroy").resizable("destroy").css({
        "width": "",
        "position": "",
        "z-index": "5",
        "height": ""
    });
    
    $(prtlt).find(".content").css({
        "width": "",
        "height": ""
    });
}

$(window).resize(function() {
    if ($('#popout-base-container').length == 0) return false;
    calc_popup_position();
});
function calc_popup_position() {
    var popup_obj = $('.popup');
    if (popup_obj.length == 0) return false;
    
    
    if (popup_obj.outerWidth() > $( window ).width()) {
        popup_obj.css({
            "left": 0,
            "width": $( window ).width()
        });
    } else {
        lft = $( window ).scrollLeft() + ($( window ).width() / 2 - popup_obj.outerWidth() / 2);
        popup_obj.css({
            "left": lft
        });
    }
}


function clear_ac_search(parent) {
    if (typeof parent == 'undefined') { // global values
        $('.ac_search').val('');
        if (typeof filter['sorting'] != 'undefined') {
            var sorting = filter['sorting'];
        }
        if (typeof filter['ac_search'] != 'undefined') {
            var ac_search = filter['ac_search'];
        } 
        if (typeof filter['static'] != 'undefined') {
            var static_filter = filter['static'];
        }
        if (typeof filter['clipboard'] != 'undefined') {
            var clipboard = filter['clipboard'];
        }
    
        filter = {};

        if (typeof sorting != 'undefined') {
            filter['sorting'] = sorting;
        }
        if (typeof ac_search != 'undefined') {
            filter['ac_search'] = ac_search;
        }
        if (typeof static_filter != 'undefined') {
            filter['static'] = static_filter;
        }
        if (typeof clipboard != 'undefined') {
            filter['clipboard'] = clipboard;
        }
    } else { // local dialog-search - right now there is no module for that
        $('.ac_search', parent).val('');
        var local_filter = parent.data('filter');
        if (typeof local_filter['ac_search'] != 'undefined') {
            var ac_search = local_filter['ac_search'];
        } 
        local_filter = {};
        if (typeof ac_search != 'undefined') {
            local_filter['ac_search'] = ac_search;
        }
        parent.data('filter', local_filter);
    }
}


    // [name] is the name of the event "click", "mouseover", .. 
    // same as you'd pass it to bind()
    // [fn] is the handler function
    $.fn.bindFirst = function(name, fn) {
        // bind as you normally would
        // don't want to miss out on any jQuery magic
        this.on(name, fn);

        // Thanks to a comment by @Martin, adding support for
        // namespaced events too.
        this.each(function() {
            var handlers = $._data(this, 'events')[name.split('.')[0]];
            //console.log(handlers);
            // take out the handler we just inserted from the end
            var handler = handlers.pop();
            // move it at the beginning
            handlers.splice(0, 0, handler);
        });
    };
    
    // usage:
    // $("abbr.timeago").changeTimeago("2004-07-17T09:24:17Z");
    $.fn.changeTimeago = function(isotime) {
        $(this).fadeOut(function() {
            $(this).attr("title", isotime).data("timeago", null).timeago().fadeIn();
        });
        return true;
    }
    

    $.fn.AutoHideMessage = function(options) {

    

    var settings = $.extend({
        'close' : true,
        'autoHide' : false,
        'time' : 5000
    }, options);

    var methods = {
        show : function(ele) {
            var ele = $(ele);
            var content = '<div class="message-box"><div>'+ele.html()+'</div>';

            if(settings.close == true && settings.autoHide == true) {
                content += '<div class="close"><div class="time">' + (settings.time)/1000 + ' s.</div></div>';
            } else if (settings.close == true) {
                content += '<div class="close"></div>';
            } else if (settings.autoHide == true) {
                content += '<div class="time">' + (settings.time)/1000 + ' s.</div>';
            }

            content += '</div>';

            ele.html(content);

            ele.find('.close').click(function() {
                methods.hide(ele);
            });

            if (settings.autoHide == true) {
                var mytime = settings.time;
                var timer = setInterval(function() {
                    mytime -= 1000;
                    ele.find('.time').text((mytime)/1000+' s.');
                    if (mytime < 1000) {
                        methods.hide(ele);
                        clearInterval(timer);
                    }
                },1000);
            }
        },

        hide : function(ele) {
            ele.fadeOut('normal', function() {
                var parent = ele.parent();
                ele.remove();
                if (parent.children().length == 0) {
                    parent.parent().parent().parent().remove();
                }
            });
        }
    }

    this.each(function(index, ele){
        methods.show(ele);
    });

    }

$.fn.hasAttr = function(name, val) {
    var attr = $(this).attr(name);
    if (typeof val !== 'undefined') {
        return attr === val;
    }
    
    if (typeof attr !== 'undefined' && attr !== false) {
        return true;
    } else {
        return false;
    }
};

jQuery.fn.reset = function () {
  $(this).each (function() { this.reset(); });
}

jQuery.fn.setMaxHeight = function() {
    var heights = [];
    $('> li', $(this)).each(function() {
        heights.push($(this).height());
    });
    var max_height = Math.max.apply(null, heights);
    $(this).height(max_height);
}

$.ui.dialog.prototype._oldinit = $.ui.dialog.prototype._init;
$.ui.dialog.prototype._init = function() {
    $(this.element).parent().css('position', 'fixed');
    $(this.element).dialog("option",{
        resizeStop: function(event,ui) {
            var position = [(Math.floor(ui.position.left) - $(window).scrollLeft()),
                            (Math.floor(ui.position.top) - $(window).scrollTop())];
            $(event.target).parent().css('position', 'fixed');
            // $(event.target).parent().dialog('option','position',position);
            // removed parent() according to hai's comment (I didn't test it)
            $(event.target).dialog('option','position',position);
            return true;
        }
    });
    this._oldinit();
};

function strpos (haystack, needle, offset) {
  // From: http://phpjs.org/functions
  // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +   improved by: Onno Marsman
  // +   bugfixed by: Daniel Esteban
  // +   improved by: Brett Zamir (http://brett-zamir.me)
  // *     example 1: strpos('Kevin van Zonneveld', 'e', 5);
  // *     returns 1: 14
  var i = (haystack + '').indexOf(needle, (offset || 0));
  return i === -1 ? false : i;
}

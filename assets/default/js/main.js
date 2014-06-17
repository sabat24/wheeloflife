$(function () {
    $('#chart_panel a').click(function() {
        var href = $(this).attr('href') || '';
        switch (href) {
            case '#clear':
                clear_chart($(this).attr('rel'));
            break;
            case '#submit':
                submit_chart($(this).attr('rel'), $(this));
            break;
            case '#public':
            case '#private':
                change_chart_visibility($(this));
            break;
            default:
                return true;
        }
        return false;
    });
    
    $('div.container').on('click', 'a.submit', function() {
        
        var form = $(this).closest('form');
        var form_disabled = form.data('disabled') || 0; 
        if (form_disabled == 0) {
            $(this).addClass('small_ajax_loading');
            form.data('disabled', 1);
            form.submit();
        }
        return false;
    });
    
    $('body').on('submit', 'form.ajax', function(e) {
        post_ajax($(this).attr('action'), $(this).serializeArray(), $('a.submit', $(this)));
        return false;
    });
    
    $('div.container').on('click', '.pagination-container a', function() {
        var post = {};
        if ($(this).parent().parent().hasClass('no-ajax')) {
            return true;
        }
        post['chart_id'] = $(this).attr('rel');
        post_ajax($(this).attr('href'), post, $(this));
        return false;
    });
    
});



function chart_click(chart, e) {
    // find the clicked values and the series
    var x = e.xAxis[0].value,
    y = e.yAxis[0].value,
    series = chart.series[0];
    
    // Add it
    x = Math.round(x);
    y = Math.round(y);
    if (x >= series.data.length) x = 0;
    if (y > chart.yAxis[0].max) y = chart.yAxis[0].max;
    
    
    if ( (series.data.length - series.data.filter(function(val) { return val.y !== null; }).length) == 1) {
        series.data[x].update(y, false);
        series.update({
            type : 'area'
        });
        $('#chart_panel a.chart-submit').slideDown(400, function() {$(this).css({'display' : 'inline-block'})});
    } else {
        series.data[x].update(y);
    }
}

function clear_chart(chart_name) {
    if (typeof window[chart_name] == 'undefined') return false;
    try {
        var chart = window[chart_name];
        for (var i = 0; i < chart.series[0].data.length; i++) {
            chart.series[0].data[i].update(null, false);
        }
        chart.series[0].update({
            type : 'line'
        });
        chart.redraw();
        $('#chart_panel a.chart-submit').slideUp(400);
    } catch(error) {
        alert(error.message);
    }
}

function submit_chart(chart_name, that) {
    if (typeof window[chart_name] == 'undefined') return false;
    try {
        var chart = window[chart_name];
        var disabled = that.data('disabled') || 0;
        if (disabled == 1) return false;
        that.data('disabled', 1);
        
        var data = {};
        for (var i = 0; i < chart.series[0].data.length; i++) {
            data[i] = [chart.series[0].data[i].x, chart.series[0].data[i].y];
        }
        post_ajax(main_url + 'submit_chart', data, that); 
    
    } catch(error) {
        alert(error.message);
    }
}

function change_chart_visibility(that) {
    var disabled = that.data('disabled') || 0;
    if (disabled == 1) return false;
    that.data('disabled', 1);
    var data = {'token' : chart_token};
    post_ajax(main_url + 'change_chart_visibility', data, that);
}



function submit_chart_response(data, that) {
    if (data.status == 'ok') {
        History.pushState(null, document.title, main_url + data.callback.params.url);
        chart_token = data.callback.params.token;
        $('#chart_panel').slideUp(400, function() {
            $('.chart-submit, .clear-chart', $(this)).addClass('hidden');
            toogle_visibility_buttons(0);
            $(this).slideDown(400);
        });
        $('#right-side div.index').slideUp(400, function() {
            $('#right-side div.form').slideDown(400);
        });
        if (typeof data.callback.params.pagination != 'undefined') {
            $('#pagination').empty().html(data.callback.params.pagination);
        }
        $('#right-side input[name="chart_url"]').val(main_url + data.callback.params.url);
    } else {
        that.data('disabled', 0);
    }
}

function create_account_response(data, that) {
    if (data.status == 'ok') {
        $('#chart_form').slideUp(400);
        setTimeout(function(){location.reload(true);}, 5000);
        
    } else {
        that.closest('form').data('disabled', 0);
    }
}

function change_chart_visibility_response(data, that) {
    if (data.status == 'ok') {
        toogle_visibility_buttons(data.callback.params.public);
    }
    that.data('disabled', 0);
}

function toogle_visibility_buttons(chart_public) {
    if (chart_public == 1) {
        if ( ! $('#chart_panel .make-public').hasClass('hidden')) {
            $('#chart_panel .make-public').slideUp(400, function() {
                $('#chart_panel .make-private').removeClass('hidden').slideDown(400).css({'display' : 'inline-block'});
                $(this).addClass('hidden');
            });
        } else if ($('#chart_panel .make-private').hasClass('hidden') && $('#chart_panel .make-public').hasClass('hidden')) {
            $('#chart_panel .make-private').removeClass('hidden');
        }
        $('#right-side .share_chart').removeClass('hidden').slideDown(400);
    } else {
        if ( ! $('#chart_panel .make-private').hasClass('hidden')) {
            $('#chart_panel .make-private').slideUp(400, function() {
                $('#chart_panel .make-public').removeClass('hidden').slideDown(400).css({'display' : 'inline-block'});
                $(this).addClass('hidden');
            });
        } else if ($('#chart_panel .make-private').hasClass('hidden') && $('#chart_panel .make-public').hasClass('hidden')) {
            $('#chart_panel .make-public').removeClass('hidden');
        }
        $('#right-side .share_chart').slideUp(400, function() {
            $(this).addClass('hidden');
        });
    }
}

function login_response(data, that) {
    if (data.status == 'ok') {
        $('#login').slideUp(400, function() {
            $('#loggedin').removeClass('hidden').slideDown(400);
            location.reload();
        });
    }
    that.closest('form').data('disabled', 0);
}

function logout_response(data, that) {
    if (data.status == 'ok') {
        $('#loggedin').slideUp(400, function() {
            $('#login').removeClass('hidden').slideDown(400);
            location.reload();
        });
    }
    that.closest('form').data('disabled', 0);
}

function get_chart_response(data, that) {
    if (data.status == 'ok') {
        var chart_name = data.callback.params.chart_id || '';
        if (typeof window[chart_name] == 'undefined') return false;
        try {
            var chart = window[chart_name];
            chart.series[0].setData(data.callback.params.chart.data, false);
            chart.setTitle(null, data.callback.params.chart.subtitle);

            series = chart.series[0];
            series.update({
                type : 'area'
            }, false);
                
            chart.redraw();
            $('#pagination').empty().html(data.callback.params.pagination);
            History.pushState(null, document.title, main_url + data.callback.params.url);
            chart_token = data.callback.params.token;
            toogle_visibility_buttons(data.callback.params.chart_public);
            $('#right-side input[name="chart_url"]').val(main_url + data.callback.params.url);
            
            $('#clear').addClass('hidden');
            $('#create-new').removeClass('hidden');
        } catch(error) {
            alert(error.message);
        }
    }
}


function post_ajax(url, data, that) { 
    try {
        $.ajax({
            url: url,
            type: "POST",
            dataType: "json",
            data: data,
            beforeSend: function() {
                that.addClass('small_ajax_loading');
            },
            success: function(data) {
                
                    if (typeof data.callback != 'undefined') {
                        if ($.isFunction(window[data.callback.name])) {
                            window[data.callback.name](data, that);
                        }
                    }
                
                if (typeof data.messages != 'undefined') {
                    $('#info').html(data.messages);
                    if (data.status == 'error') {
                        $('html, body').animate({scrollTop: $('#info').offset().top - 55}, 700);
                    }
                }
            },
            complete: function() {
                that.removeClass('small_ajax_loading');
            },
        });
    } catch(error) {
        alert(error.message);
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
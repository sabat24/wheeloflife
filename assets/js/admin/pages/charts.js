function confirm_delete_message(type) {
    switch(type) {
        case 'chart':
            return __('Are you sure that you want to remove chart?');
        break;
        case 'chart_category':
            return __('Are you sure that you want to remove chart category?');
        break;
     }
}

$(function() {
    $( "#sortable" ).sortable({
        placeholder: "ui-state-highlight",
    });
    $( "#sortable" ).disableSelection();
    
    
    $('form#save-order').bindFirst('submit', function(event) {
        var result = $('#sortable').sortable('toArray');
        $('input#new-order').val(result.join(','));
        return true;
    });
});

function chart_load_data(parent, params) {
    var current_url = main_url + params.url

    $.ajax({
        url: current_url,
        type: 'POST',
        data: filter,
        dataType: 'json',
        beforeSend: function() {
            $('.list_content', parent).addClass('small_ajax_loading');
        },
        success: function(data) {
            set_chart_list(parent, data);
        },
        complete: function() {
            $('.list_content', parent).removeClass('small_ajax_loading');
        },
    });
}

function set_chart_list(parent, data) {
    $('tbody.list-container', parent).html(data.html);
    $('.pagination-container', parent).html(data.pagination);
    
    if ($('.toogle-all-checkboxes', parent).length > 0) {
        $('.toogle-all-checkboxes', parent).prop('checked', false);
    } 
}

// portlet callback
function load_charts_list(parent, callback) {
    chart_load_data(parent, callback);
}
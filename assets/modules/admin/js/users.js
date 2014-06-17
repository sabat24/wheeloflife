function confirm_delete_message(type) {
    switch(type) {
        case 'user':
            return __('Are you sure that you want to remove user?');
        break;
     }
}

function user_load_data(parent, params) {
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
            set_user_list(parent, data);
        },
        complete: function() {
            $('.list_content', parent).removeClass('small_ajax_loading');
        },
    });
}

function set_user_list(parent, data) {
    $('tbody.list-container', parent).html(data.html);
    $('.pagination-container', parent).html(data.pagination);
    
    if ($('.toogle-all-checkboxes', parent).length > 0) {
        $('.toogle-all-checkboxes', parent).prop('checked', false);
    } 
}

// portlet callback
function load_users_list(parent, callback) {
    user_load_data(parent, callback);
}
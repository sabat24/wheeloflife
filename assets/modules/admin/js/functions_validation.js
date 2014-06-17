$(function() {
    $('#content').on('keyup', ':input.submit-on-shift-enter', function(event) {
        if (event.keyCode == 13 && event.shiftKey) {
            $(this).closest('form').find('div.submit').trigger('click');
        }
    });
    
    form_validation_initialize();
});

var form_checked = {};
var soft_errors = [];






function form_validation_initialize() {
    soft_errors = [];
    var form = $('form.validate');
    if (form.length == 0) return false;
    var model = form.attr('rel');
    $('body').on('change', 'form.validate :input.validate, form.validate textarea.validate', function() {
    
    //$("form.validate :input.validate, form.validate textarea.validate").each(function() {
        //var input = $(this);
        //input.change(function(){
            validate_field($(this), model);
        //});
    });
    
    $('form.validate').bindFirst('submit', function(event) {
        $(this).data('disabled', 1);
        if (typeof form_checked[$(this).attr('name')] == 'undefined') {
            form_checked[$(this).attr('name')] = 0;
        }
    //$('form.validate').submit(function() {
        if (form_checked[$(this).attr('name')] == 0) {
            event.stopPropagation();
            validate_form($(this));
            return false;
        } else {
            return true;
        }
    });
}

function validate_form(form) {
    var fields = new Array();
    var model = form.attr('rel');

    $(":input.validate, textarea.validate", form).each(function() {
        
        var input = $(this);
        var val;
        
        if (input.hasAttr('disabled')) return;
        
        if (input.is(':checkbox')) {
            if (input.is(':checked')) {
                val = 1;
            } else {
                val = 0;
            }
        } else {
            val = input.val();
        }
        fields.push(new Array(input.attr('name'), val));
    });
    //console.log(fields);
    validate_fields(fields, true, model, form);
}

function validate_form_init(model) {
    var fields = new Array();

    $("form.validate :input.validate, form.validate textarea.validate").each(function() {
        var input = $(this);
        var val;
        if (input.is(':checkbox')) {
            if (input.is(':checked')) {
                val = 1;
            } else {
                val = 0;
            }
        } else {
            val = input.val();
        }
        fields.push(new Array(input.attr('name'), val));

    });
    //console.log(fields);
    validate_fields(fields, false, model, form);
}

function validate_fields(fields, is_form, model, form) {
    $.ajax({
        url: main_url + "admin/ajax/validate_fields/" + model,
        type: "POST",
        data: {"options" : { "fields" : fields} },
        dataType: "json",
        beforeSend: function() {
            if (is_form == true) {
                $('.list_content', form.closest('div.portlet')).addClass('small_ajax_loading');
            }
        },
        complete: function() {
            if (is_form == true) {
                $('.list_content', form.closest('div.portlet')).removeClass('small_ajax_loading');
            }
        },
        success: function(data) {
            $('#info').html(data.messages);
            if (data.status == 'ok') {
                if (is_form == true) {
                    form_checked[form.attr('name')] = 1;
                    form.submit();
                } else {
                    fields_length = fields.length;
                    for (var i = 0; i < fields_length; i++) {
                        var parent = $("tr."+fields[i][0], form);
                        parent.removeClass('error').removeClass('soft_error');
                        $(".information div.inner", parent).empty();
                    }
                }
            } else if (data.status == 'error') {
                fields_length = fields.length;
                var scroll_to = scroll_to_soft = '';
                var actual_soft_errors = [];
                for(var i = 0; i < fields_length; i++) {
                    var parent = $("tr."+fields[i][0], form);
                    if (typeof data.errors[fields[i][0]] == 'undefined') {
                        parent.removeClass('error').removeClass('soft_error');
                        $(".information div.inner", parent).empty();
                    } else {
                        parent.addClass('error').removeClass('soft_error');
                        $(".information div.inner", parent).html(data.errors[fields[i][0]]);
                        if (scroll_to == '') {
                            scroll_to = parent;
                        }
                    }
                    
                    if (typeof data.soft_errors[fields[i][0]] == 'undefined') {
                        parent.removeClass('soft_error');
                        if (typeof data.errors[fields[i][0]] == 'undefined') {
                            $(".information div.inner", parent).empty();
                        }
                    } else {
                        parent.addClass('soft_error');
                        $(".information div.inner", parent).html(data.soft_errors[fields[i][0]]);
                        if ( ! form.hasClass('pass-soft-errors')) {
                            actual_soft_errors.push(fields[i][0]);
                            if (scroll_to_soft == '') {
                                scroll_to_soft = parent;
                            }
                        }
                    }
                }
                
                if (is_form == true) {
                    form.data('disabled', 0);
                    $('.submit', form).removeClass('small_ajax_loading');
                    //console.log(actual_soft_errors);
                    //console.log(soft_errors);
                    soft_errors = diff_array(actual_soft_errors, soft_errors);
                    //console.log(soft_errors);
                    // scroll_to - oznacza nazwe pola z bledem, jesli jest puste - to oznacza, ze blad nie wystapil
                    if (soft_errors.length == 0 && scroll_to == '') {
                        form_checked[form.attr('name')] = 1;
                        form.submit();
                        return;
                    } else {
                        scroll_to_soft = $('[name='+soft_errors[0]+'].validate', form).closest('tr');
                        soft_errors = actual_soft_errors;
                    }
                }
                if (scroll_to == '') {
                    scroll_to = scroll_to_soft;
                }
                if (typeof $(scroll_to).offset() == 'undefined') {
                    scroll_to = '#info';
                }
                
                $('html, body').animate({scrollTop: $(scroll_to).offset().top - 5}, 1500);
            }
        },
    });

}

function validate_field(field, model) {
    var fields = new Array();
    var val;
    if (field.is(':checkbox')) {
        if (field.is(':checked')) {
            val = 1;
        } else {
            val = 0;
        }
    } else {
        val = field.val();
    }
        
    if (field.is("[rel]")) {
        var rels = field.attr('rel').split(' ');
        for (key in rels) {
            $('form.validate [name^="'+rels[key]+'"]').each(function() {
                if (field.attr('name') != $(this).attr('name')) {
                    fields.push(new Array($(this).attr('name'), $(this).val()));
                }
            
            });
        }
    }
    
    switch(name) {
        case 'password_retype':
            fields.push(new Array('password_retype', $field.val()));
            validate_fields(fields, false, model);
            return;
        break;
    }    
    
    
    fields.push(new Array(field.attr('name'), val))
    
    $.ajax({
        url: main_url + "admin/ajax/validate_fields/" + model,
        type: "POST",
        data: {"options" : { "fields" : fields, "current_field" : field.attr('name')} },
        dataType: "json",
        beforeSend: function() {
            field.addClass('ui-autocomplete-loading');
        },
        success: function(data) {
            var form = field.closest('form.validate');
            if (data.status == 'ok') {
                var parent = $("tr."+field.attr('name'), form);
                parent.removeClass('error').removeClass('soft_error');
                $(".information div.inner", parent).empty();
            } else if (data.status == 'error') {
                var parent = $("tr."+field.attr('name'), form);
                if (typeof data.errors[field.attr('name')] == 'undefined') {
                    parent.removeClass('error').addClass('soft_error');
                    $(".information div.inner", parent).html(data.soft_errors[field.attr('name')]);
                } else {
                    parent.addClass('error').removeClass('soft_error');
                    $(".information div.inner", parent).html(data.errors[field.attr('name')]);
                }
            }
        },
        complete: function() {
            field.removeClass('ui-autocomplete-loading');
        },
    });
}

function diff_array(a, b) {
    var seen = [], diff = [];
    for ( var i = 0; i < b.length; i++)
        seen[b[i]] = true;
    for ( var i = 0; i < a.length; i++)
        if (!seen[a[i]])
            diff.push(a[i]);
    return diff;
}
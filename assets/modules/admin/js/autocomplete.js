function monkeyPatchAutocomplete() {
    // Don't really need to save the old fn, 
    // but I could chain if I wanted to
    var oldFn = $.ui.autocomplete.prototype._renderItem;

    $.ui.autocomplete.prototype._renderItem = function( ul, item) {
    var re = new RegExp("" + this.term, "i") ;
    var t = item.label.replace(re,"<span style='font-weight:bold;color:Blue;'>" + "$&" + "</span>");
    return $( "<li></li>" )
        .data( "item.autocomplete", item )
        .append( "<a>" + t + "</a>" )
        .appendTo( ul );
    };
}
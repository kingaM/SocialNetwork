// Usage:
// Include this line: 
// <div id="<ID>" class="container col-md-4"></div><script src="/helpers/autocompleter.js"></script>
// <script>easyIncludeAutoComplete("<ID>");</script>

function easyIncludeAutoComplete(id) {
    var searchHTML = '<script src="/libs/bootstrap3-typeahead.js"></script>' + 
    '<div class="input-group">' + 
        '<input type="text" class="form-control" data-provide="typeahead" id="searchUsers_' + id + 
            '" placeholder="Username" autocomplete="off">' + 
        '<span class="input-group-btn">' + 
            '<button class="btn btn-default" type="button">' + 
                '<span class="glyphicon glyphicon-search"></span>' + 
            '</button>' + 
        '</span>' + 
    '</div>';

    $('#' + id).append(searchHTML);

    $('#searchUsers_' + id).typeahead({source: autocomplete});
}


function autocomplete(query, process) {
    $.getJSON( "/api/users/autocomplete/" + query, function(data) {
        var suggestions = [];
        $.each(data, function(key, val) {
            if(key == "suggestions") {
                for (var i = 0; i < val.length; i++) {
                    suggestions.push(val[i]);
                };
            }
        });
        process(suggestions);
    });
}

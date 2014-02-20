// Usage:
// Include <div id="searchBar"></div> where you want the search to appear
// Include this file after that (<script src="/helpers/autocompleter.js"></script>)

var searchHTML = '<script src="/libs/bootstrap3-typeahead.js"></script>' + 
'<div class="input-group">' + 
    '<input type="text" class="form-control" data-provide="typeahead" id="searchUsers">' + 
    '<span class="input-group-btn">' + 
        '<button class="btn btn-default" type="button">' + 
            '<span class="glyphicon glyphicon-search"></span>' + 
        '</button>' + 
    '</span>' + 
'</div>';

$("#searchBar").append(searchHTML);

$('#searchUsers').typeahead({source: autocomplete});

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

// Usage:
// Include this line: 
// <div id="<ID>" class="container col-md-4"></div><script src="/helpers/autocompleter.js"></script>
// <script>easyIncludeAutoComplete("<ID>");</script>


/**
 * Includes an autocomplete search box, which autocompletes with the usernames of the registered
 * users. To get the input in javascript use searchUsers_{id} tag.
 * @param  {string} id The id of the div to place the search box in. 
 */
function easyIncludeAutoComplete(id) {
    var searchHTML = '<script src="/libs/bootstrap3-typeahead.js"></script>' + 
    '<div class="input-group" id="searchUsersGroup_' + id + '">' + 
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

/**
 * Helper function for the autocomplete search box. It sends a GET request to get the users that
 * fit the letters already entered in the input field from the database.
 */
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

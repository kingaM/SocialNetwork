var sessionUser;

$(document).ready(function() {
    // Main page JS here
    
    
	easyIncludeAutoComplete("top-search");
	$("#top-center #top-search .input-group").addClass( "input-group-sm" );
	
	
	$.ajaxSetup({async:false});

	$.getJSON( "/api/currentUser", function(data) {
	    sessionUser = data["username"];
	    $( "#profile-link" ).attr( "href", "/user/" + sessionUser );
	    
	    $.getJSON( "/api/user/" + sessionUser + "/profile/image", function(data) {

	    
	    var imgUrl = data["image"];
	
		if(imgUrl != null) {
		
		    $( "#photo-top" ).attr( "src", imgUrl );
	    }
	    });
	});
    
    $.ajaxSetup({async:true});
    
    $('#top-search .btn').click(function(){
		var goToUser = $("#searchUsers_top-search").val();
		window.location = "/user/" + goToUser;
	});
	
	$("#searchUsers_top-search").bind("keypress", function(event) {
	    if(event.which == 13) {
		    event.preventDefault();
	        var goToUser = $("#searchUsers_top-search").val();
			window.location = "/user/" + goToUser;
	    }
	});
    
    
    content();
    

    
});

/**
 * Shows an error message, as an alert box, in a specified div.
 * Note: It overrides whatever was in that div before.
 * 
 * @param  {string} id   The id of the div to put the function to. (without #)
 * @param  {string} text The text of the error message.
 */
function showError(id, text) {
    $("#" + id).html("<div class=\"alert alert-danger alert-dismissable\">" +
        "<button type=\"button\" class=\"close\" data-dismiss=\"alert\"" +
        "aria-hidden=\"true\">&times;</button>" +
        "<strong>Error:</strong> " + text + 
        "</div>");
}

/**
 * Displays a modal (popup) box over all the text.
 * @param  {string} text The text to display.
 */
function displayModal(text) {
    var html = '<div class="modal fade" tabindex="-1" role="dialog" id="modal">' + 
                    '<div class="modal-dialog">' + 
                        '<div class="modal-content">' + 
                            '<div class="modal-body text-center">' + text + '</div>' + 
                        '</div>' + 
                    '</div>' + 
                '</div>';

    $("#modal").remove();
    $("#content").append(html);
    $(".modal").modal();
}




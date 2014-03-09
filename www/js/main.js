$(document).ready(function() {
    // Main page JS here
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

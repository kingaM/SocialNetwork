$(document).ready(function() {
    // Main page JS here
    content();
});

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
    $("#modal").modal();
}

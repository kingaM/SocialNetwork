
/**
 * Adds a selector dropdown with the privacy options selected.
 * @param {string}  id             The id of the div this selector should be placed in. The contents
 *                                 of the div are overriden. 
 * @param {integer} selectedOption The option that currently should be selected. The value should be
 *                                 equal to the id in the database schema. 
 * TODO: Make it dynamically generated from the database.
 */
function addPrivacySelector(id, selectedOption) {
    var selector ='<select class="form-control" id="privacy-options-' + id + '">' +
            '<option value="1">Only Me</option>' +
            '<option value="2">Circles</option>' +
            '<option value="3">Friends</option>' +
            '<option value="4">Friends of Friends</option>' +
            '<option value="5">Public</option>' +
        '</select>';
    $("#" + id).html(selector);
    $("#privacy-options-" + id + " [value=\"" + selectedOption +"\"]")
        .prop("selected", true);
}
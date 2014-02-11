var currentReciepient = null;
var prevConversations = null;
var prevMessages = null;

function content() {
    sendMessage();
    getReciepients();
    setupDropdown();
    window.setInterval(function(){
        getReciepients();
        showMessages(currentReciepient);
    }, 5000);
}

function setupDropdown() {
    $('.dropdown-menu').find('form').click(function (e) {
        e.stopPropagation();
    });
    $('#new-message-form').submit(function(e) {
        e.preventDefault();
        var messageText = $("#new-message").val();
        var to = $("#to").val();
        postMessage(to, messageText);
        $("#new-message").val("");
        $("#to").val("");
        $('[data-toggle="dropdown"]').parent().removeClass('open');
    });
}

function sendMessage() {
    $("#send-message").click(function(e) {
        e.preventDefault();
        var messageText = $("#message-text").val();
        $("#message-text").val("");
        postMessage(currentReciepient, messageText);        
    });
}

function postMessage(to, message) {
    var values = {};
    values["messageText"] = message;
    $.ajax({
            type: "post",
            url: "/api/messages/" + to,
            data: values,
            success: function() {
                        console.log("success");       
                        showMessages(currentReciepient);           
                    },
            });
}

function getReciepients() {
    $.getJSON( "/api/messages/reciepients", function(data) {
        var data_length = data["reciepients"].length;
        var reciepients = data["reciepients"];
        for (var i = 0; i < data_length; i++) {
            if(prevConversations != null && i < prevConversations.length) {
                if(prevConversations[i]["username"] != reciepients[i]["username"] ) {
                    addConversation(reciepients[i]["username"], reciepients[i]["firstName"],
                        reciepients[i]["middleName"], reciepients[i]["lastName"], 
                        reciepients[i]["message"]);
                } else if(prevConversations[i]["message"] != reciepients[i]["message"]) {
                    $("#"+reciepients[i]["username"] +"-message").html(reciepients[i]["message"]);
                }
            } else {
                addConversation(reciepients[i]["username"], reciepients[i]["firstName"],
                        reciepients[i]["middleName"], reciepients[i]["lastName"], 
                        reciepients[i]["message"]);
            }          
        }
        if(data_length > 0 && prevConversations == null) {
            $("#" + data["reciepients"][data_length - 1]["username"]).trigger('click');
        }
        prevConversations = reciepients;
        
    });
}

function showMessages(username) {
    currentReciepient = username;
    $.getJSON( "/api/messages/" + username, function(data) {
        var data_length = data["messages"].length;
        var messages = data["messages"];
        for (var i = 0; i < data_length; i++) {
            if(prevMessages != null && i < prevMessages.length) {
                if(prevMessages[i]["timestamp"] != messages[i]["timestamp"]) {
                  addMessages(messages[i]["firstName"], messages[i]["middleName"], 
                    messages[i]["lastName"], messages[i]["message"], 
                    messages[i]["timestamp"]);
                }
            } else {
                addMessages(messages[i]["firstName"], messages[i]["middleName"], 
                    messages[i]["lastName"], messages[i]["message"], 
                    messages[i]["timestamp"]);
            }
        }
        prevMessages = messages;
    });
}

function addMessages(firstName, middleName, lastName, message, timestamp) {
    var content = "<div class=\"msg-wrap\">" +
                "<div class=\"media msg\">" +
                    "<div class=\"media-body\">" +
                        "<small class=\"pull-right time\"><i class=\"fa fa-clock-o\"></i>" +
                            new Date(timestamp*1000).toLocaleString() + "</small>" +

                        "<h5 class=\"media-heading\">" + firstName + " " + middleName + " " + 
                            lastName +  "</h5>" +
                        "<small class=\"col-lg-10\">" + message + "</small>" +
                    "</div>" +
                "</div>" +
            "</div>";
    $("#messages").append(content);
}

function addConversation(username, firstName, middleName, lastName, message) {
    var content = "<div class=\"media conversation\">" +
                "<div class=\"media-body\">" + 
                    "<h5 class=\"media-heading\">" + 
                        "<a href=\"#\" id=\"" + username + "\">" 
                            + firstName + " " + middleName + " " + lastName + 
                        "</a>" +
                    "</h5>" + 
                    "<small id=\"" + username + "-message" + "\">" + message + "</small>" + 
                "</div>" + 
            "</div>";
    // Assumes that each new item is newer than the last one. Should work for most cases. 
    $("#conversations").prepend(content);
    var id = "#" + username;
            $(id).click(function(e) {
                e.preventDefault();
                $("#messages").empty();
                prevMessages = null;
                showMessages(username);
                // Should scroll down to the most recent message, but it doesn't work, and I 
                // cannot figure out why.
                $("#messages").scrollTop($("#messages").prop("scrollHeight"));
            });
}
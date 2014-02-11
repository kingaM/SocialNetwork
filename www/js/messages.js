var currentReciepient = null;
var prevConversations = null;
var prevMessages = null;

function content() {
    sendMessage();
    getReciepients();
    // Handles menu drop down
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
    window.setInterval(function(){
        getReciepients();
        showMessages(currentReciepient);
    }, 5000);
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
                    },
            });
}

function getReciepients() {
  //  $("#conversations").empty();
    console.log("In get reciepients");
    $.getJSON( "/api/messages/reciepients", function(data) {
        var data_length = data["reciepients"].length;
        for (var i = 0; i < data_length; i++) {
            if(prevConversations != null && i < prevConversations["reciepients"].length) {
                if(prevConversations["reciepients"][i]["username"] != data["reciepients"][i]["username"] ) {
                    addConversation(data["reciepients"][i]["username"], data["reciepients"][i]["firstName"],
                        data["reciepients"][i]["middleName"], data["reciepients"][i]["lastName"], 
                        data["reciepients"][i]["message"]);
                } else if(prevConversations["reciepients"][i]["message"] != data["reciepients"][i]["message"]) {
                    $("#"+data["reciepients"][i]["username"] +"-message").html(data["reciepients"][i]["message"]);
                }
            } else {
                addConversation(data["reciepients"][i]["username"], data["reciepients"][i]["firstName"],
                        data["reciepients"][i]["middleName"], data["reciepients"][i]["lastName"], 
                        data["reciepients"][i]["message"]);
            }          
        }
        if(data_length > 0 && prevConversations == null) {
            $("#" + data["reciepients"][0]["username"]).trigger('click');
        }
        prevConversations = data;
        
    });
}

function showMessages(username) {
    console.log("showMessages " + username);
    currentReciepient = username;
    console.log("showMessages " + currentReciepient);
    $.getJSON( "/api/messages/" + username, function(data) {
        console.log(data);
        var data_length = data["messages"].length;
        for (var i = 0; i < data_length; i++) {
            if(prevMessages != null && i < prevMessages["messages"].length) {
                if(prevMessages["messages"][i]["timestamp"] != data["messages"][i]["timestamp"]) {
                  addMessages(data["messages"][i]["firstName"], data["messages"][i]["middleName"], 
                    data["messages"][i]["lastName"], data["messages"][i]["message"], 
                    data["messages"][i]["timestamp"]);
                }
            } else {
                addMessages(data["messages"][i]["firstName"], data["messages"][i]["middleName"], 
                    data["messages"][i]["lastName"], data["messages"][i]["message"], 
                    data["messages"][i]["timestamp"]);
            }
        }
        prevMessages = data;
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
    $("#conversations").append(content);
    var id = "#" + username;
            $(id).click(function(e) {
                e.preventDefault();
                $("#messages").empty();
                prevMessages = null;
                showMessages(username);
            });
}
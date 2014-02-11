function content() {
    getReciepients();
}

function getReciepients() {
    $("#conversations").empty();
    console.log("In get reciepients");
    $.getJSON( "/api/messages/reciepients", function(data) {
        var data_length = data["reciepients"].length;
        for (var i = 0; i < data_length; i++) {
          addConversation(data["reciepients"][i]["username"], data["reciepients"][i]["firstName"],
            data["reciepients"][i]["middleName"], data["reciepients"][i]["lastName"], 
            data["reciepients"][i]["message"]);
        }
    });
}

function showMessages(username) {
    $("#messages").empty();
    console.log("showMessages " + username);
    $.getJSON( "/api/messages/" + username, function(data) {
        console.log(data);
        var data_length = data["messages"].length;
        for (var i = 0; i < data_length; i++) {
          addMessages(data["messages"][i]["from"], data["messages"][i]["to"], 
            data["messages"][i]["message"], data["messages"][i]["timestamp"]);
        }
    });
}

function addMessages(from, to, message, timestamp) {
    var content = "<div class=\"msg-wrap\">" +
                "<div class=\"media msg\">" +
                    "<div class=\"media-body\">" +
                        "<small class=\"pull-right time\"><i class=\"fa fa-clock-o\"></i>" +
                            new Date(timestamp*1000).toString() + "</small>" +

                        "<h5 class=\"media-heading\">" + from + "</h5>" +
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
                    "<small>" + message + "</small>" + 
                "</div>" + 
            "</div>";
    $("#conversations").append(content);
    var id = "#" + username;
            $(id).click(function(e) {
                e.preventDefault();
                showMessages(username);
            });
}
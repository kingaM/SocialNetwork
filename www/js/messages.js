var currentReciepient = null;
var prevConversations = null;
var prevMessages = null;
var inSearch = false;
var first = true;

function content() {
    $('#message-text').keydown(function (event) {
        var keypressed = event.keyCode || event.which;
        if (keypressed == 13 && $('#send-on-enter').prop('checked')) {
                event.preventDefault();
                $("#send-message").click();         
        }
    });
    sendMessage();
    getReciepients();
    setupDropdown();
    setupSearch();
    window.setInterval(function(){
        getReciepients();
        showMessages(currentReciepient);
    }, 5000);
}

function setupSearch() {
    $('#search-reciepients-text').keydown(function (event) {
        var keypressed = event.keyCode || event.which;
        if (keypressed == 13) {
                event.preventDefault();
                $("#search-reciepients").click();         
        }
    });
    $("#search-reciepients").click(function (e) {
        e.preventDefault();
        inSearch = true;
        var values = {};
        values["searchText"] = $("#search-reciepients-text").val();
        $.ajax({
            type: "post",
            url: "/api/messages/reciepients",
            data: values,
            success: function(data) {
                console.log(data);
                $("#conversations").empty();
                prevConversations = null;
                showReciepients($.parseJSON(data));
            }
        });
    });
    $("#cancel-reciepients").click(function (e) {
        e.preventDefault();
        inSearch = false;
        getReciepients();
    })
}

function setupDropdown() {
     $('.dropdown-menu').find('form').click(function (e) {
        e.stopPropagation();
    });
    $('#new-message-form').submit(function(e) {
        e.preventDefault();
        var messageText = $("#new-message").val();
        var to = $("#searchUsers_search-username").val();
        postMessage(to, messageText, true);
    });
    $('#new-message-circles-form').submit(function(e) {
        e.preventDefault();
        var messageText = $("#new-message-circles").val();
        var to = $("#to-circles").val();
        postMessageCircle(to, messageText, true);
    });
    $('body').click(function(e) {
        $("#new-message").val("");
        $("#to").val("");
        $('[data-toggle="dropdown"]').parent().removeClass('open');
        $("#form-group-to").removeClass("has-error");
        $("#control-label-to").hide();
    });
}

function hideDropdown(valid, friend) {
    if(valid && friend) {
        $("#new-message").val("");
        $("#searchUsers_search-username").val("");
        $('[data-toggle="dropdown"]').parent().removeClass('open');
        $("#form-group-to").removeClass("has-error");
        $("#control-label-to").hide();
    } else {
        $("#form-group-to").addClass("has-error");
        $("#control-label-to").show();
        if(!valid) {
            $("#control-label-to").text("The username is invalid");
        } else if (!friend) {
            $("#control-label-to").text("This user is not your friend, so you cannot send him/her" +
                " a message");
        }
    }
}

function hideDropdownCircles(valid) {
    if(valid) {
        $("#new-message-circles").val("");
        $("#to-circles").val("");
        $('[data-toggle="dropdown"]').parent().removeClass('open');
        $("#form-group-to-circles").removeClass("has-error");
        $("#control-label-to-circles").hide();
    } else {
        $("#form-group-to-circles").addClass("has-error");
        $("#control-label-to-circles").show();
        if(!valid) {
            $("#control-label-to-circles").text("The name of the circle is invalid");
        }         
    }
}

function sendMessage() {
    $("#send-message").click(function(e) {
        e.preventDefault();
        var messageText = $("#message-text").val();
        $("#message-text").val("");
        if(messageText) {
            postMessage(currentReciepient, messageText, false);
            showMessages(currentReciepient);
            window.setTimeout(scrollBottom, 300);  
        }
    });
}

function postMessage(to, message, newM) {
    var values = {};
    values["messageText"] = message;
    $.ajax({
        type: "post",
        url: "/api/messages/user/" + to,
        data: values,
        success: function(data) {
            var json = $.parseJSON(data);
            var valid = json["valid"];
            var friend = json["friend"];   
            if(valid && friend) {
                showMessages(currentReciepient);
            }
            if(newM) {
                hideDropdown(valid, friend);
            }
        }
    });
}

function postMessageCircle(to, message) {
    var values = {};
    values["messageText"] = message;
    $.ajax({
        type: "post",
        url: "/api/messages/circle/" + to,
        data: values,
        success: function(data) {
            var json = $.parseJSON(data);
            var valid = json['valid'];    
            if(valid) {
                showMessages(currentReciepient);
            }
            hideDropdownCircles(valid);
        }
    });
}

function getReciepients() {
    if(!inSearch) {
        $.getJSON( "/api/messages/reciepients", function(data) {
            showReciepients(data);
            prevConversations = data["reciepients"];
        });
    }
}

function showReciepients(data) {
    var data_length = data["reciepients"].length;
    var reciepients = data["reciepients"];
    for (var i = 0; i < data_length; i++) {
        if(prevConversations != null && i < prevConversations.length) {
            if(prevConversations[i]["username"] != reciepients[i]["username"] ) {
                addConversation(reciepients[i]["username"], reciepients[i]["name"], 
                    reciepients[i]["message"]);
            } else if(prevConversations[i]["message"] != reciepients[i]["message"]) {
                $("#"+reciepients[i]["username"] +"-message").html(reciepients[i]["message"]);
            }
        } else {
            addConversation(reciepients[i]["username"], reciepients[i]["name"], 
                    reciepients[i]["message"]);
        }          
    }
    if(data_length > 0 && first) {
        $("#" + data["reciepients"][data_length - 1]["username"]).trigger('click');
        first = false;
    }
}

function showMessages(username) {
    currentReciepient = username;
    $.getJSON( "/api/messages/user/" + username, function(data) {
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
                        "<small class=\"pull-right time\"><i class=\"fa fa-clock-o\"></i> " +
                            new Date(timestamp*1000).toLocaleString() + "</small>" +

                        "<h5 class=\"media-heading\">" + firstName + " " + middleName + " " + 
                            lastName +  "</h5>" +
                        "<small class=\"col-lg-10\">" + message.replace(/\n/g, "<br/>") + "</small>" +
                    "</div>" +
                "</div>" +
            "</div>";
    $("#messages").append(content);
}

function addConversation(username, name, message) {
    var content = "<div class=\"media conversation\" id=\"" + username  + "-wrapper\">" +
                "<div class=\"media-body\">" + 
                    "<h5 class=\"media-heading\">" + 
                        "<a href=\"#\" id=\"" + username + "\">" 
                            + name + 
                        "</a>" +
                    "</h5>" + 
                    "<small id=\"" + username + "-message" + "\">" + message + "</small>" + 
                "</div>" + 
            "</div>";
    // Assumes that each new item is newer than the last one. Should work for most cases. 
    $("#" + username + "-wrapper").remove()
    $("#conversations").prepend(content);
    var id = "#" + username;
    $(id).click(function(e) {
        e.preventDefault();
        $("#messages").empty();
        prevMessages = null;
        showMessages(username);
        // A hack to make it scroll to the bottom. 
        window.setTimeout(scrollBottom, 100);
        
    });
}

function scrollBottom() {
    $("#messages").scrollTop($("#messages").prop("scrollHeight"));
}
function content() {
    prepare();
    getFriends();
}

function getFriends() {
    $.getJSON( "/api/friends", function(data) {
        var friends;
        var requests;
        var circles;
        $.each( data, function(key, val) {
            if(key == "friends")
                friends = val;
            if(key == "friendRequests")
                requests = val;
            if(key == "circles")
                circles = val;
        });
        showFriends(friends);
        showRequests(requests);
        showCircles(circles);
    });
}

function showFriends (friends, requests) {
    $("#friends_list").empty();
    for (var i = 0; i < friends.length; i++) {
        var listItem = "<li>" + friends[i] + 
        " <button type='button' class='btn btn-danger btn-xs' " + 
        "id='" + friends[i] + "_del' username='" + friends[i] + "'>" + 
        "<span class='glyphicon glyphicon-remove'></span></button></li>";
        $("#friends_list").append(listItem);
        $("#" + friends[i] + "_del").click(deleteFriend);
    };
}

function showRequests(requests) {
    $("#requests_list").empty();
    $("#numOfFriendReqs").text(requests.length);
    for (var i = 0; i < requests.length; i++) {
        var listItem = "<li>" + requests[i] + 
        " <button type='button' class='btn btn-success btn-xs' " + 
        "id='" + requests[i] + "_add' username='" + requests[i] + "'>" + 
        "<span class='glyphicon glyphicon-ok'></span></button> " +
        " <button type='button' class='btn btn-danger btn-xs' " + 
        "id='" + requests[i] + "_del' username='" + requests[i] + "'>" + 
        "<span class='glyphicon glyphicon-remove'></span></button></li>";
        $("#requests_list").append(listItem);
        $("#" + requests[i] + "_add").click(acceptFriend);
        $("#" + requests[i] + "_del").click(deleteFriend);
    };
}

function showCircles(circles) {
    $("#circles_list").empty();
    for (var i = 0; i < circles.length; i++) {
        var listItem = "<li>" + circles[i]['name'] + "<ul id='circle_" + i + "'></ul></li>";
        $("#selectCircles").append("<option>" + circles[i]['name'] + "</option>");
        $("#circles_list").append(listItem);
        for (var j = 0; j < circles[i]['users'].length; j++) {
            var user = circles[i]['users'][j];
            var circleListItem = "<li>" + user + "</li>";
            $("#circle_" + i).append(circleListItem);
        };
    };
}

function deleteFriend(event) {
    var username = $(event.target).attr("username");
    $.ajax({
        url: "/api/friends/" + username,
        type: "DELETE",
        success: function(result) {
            getFriends();
        }
    });
}

function acceptFriend(event) {
    addFriend($(event.target).attr("username"));
}

function addFriend(username) {
    $.ajax({
        url: "/api/friends",
        type: "POST",
        data: {username: username},

        success: function(response) {
            var data = $.parseJSON(response);
            $.each( data, function(key, val) {
                if(key == "error")
                    addFriendAlert(val, false);
                else if(key == "result" && val == "requested") {
                    addFriendAlert("", true);
                    $("#addForm")[0].reset();
                }
            });
            getFriends();
        }
    });
}

function addFriendAlert(val, success) {
    if(success) {
        $("#friendAlert").remove();
        $("#add_friends").append('<div class="alert alert-success fade in" id="friendAlert">' + 
            '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' + 
            'Friend request sent</div>');
    } else {
        $("#friendAlert").remove();
        $("#add_friends").append('<div class="alert alert-danger fade in" id="friendAlert">' + 
            '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' + 
            val + '</div>');
    }
}

function prepare() {
    $("#addForm").submit(function(e){
        e.preventDefault();
        addFriend($("#addForm :input").val());
    });

    $("#pending_friends").hide();
    $("#circles").hide();

    $("#friendsTab").click(function() {
        $("#friendsTab").addClass("active");
        $("#requestsTab").removeClass("active");
        $("#circlesTab").removeClass("active");
        $("#friends").show();
        $("#pending_friends").hide();
        $("#circles").hide();
    });

    $("#requestsTab").click(function() {
        $("#friendsTab").removeClass("active");
        $("#requestsTab").addClass("active");
        $("#circlesTab").removeClass("active");
        $("#friends").hide();
        $("#pending_friends").show();
        $("#circles").hide();
    });

    $("#circlesTab").click(function() {
        $("#friendsTab").removeClass("active");
        $("#requestsTab").removeClass("active");
        $("#circlesTab").addClass("active");
        $("#friends").hide();
        $("#pending_friends").hide();
        $("#circles").show();
    });
}
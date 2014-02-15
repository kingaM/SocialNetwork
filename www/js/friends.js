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
        var listItem = "<li>" + friends[i] + " <button type='button' id='" + friends[i] + "_del'" +
        " username='" + friends[i] + "''>Delete</button> " + "</li>";
        $("#friends_list").append(listItem);
        $("#" + friends[i] + "_del").click(deleteFriend);
    };
}

function showRequests(requests) {
    $("#requests_list").empty();
    for (var i = 0; i < requests.length; i++) {
        var listItem = "<li>" + requests[i] + 
        " <button type='button' id='" + requests[i] + "_add' username='" + requests[i] + "''>Accept</button> " +
        " <button type='button' id='" + requests[i] + "_del' username='" + requests[i] + "''>Decline</button> " +
        "</li>";
        $("#requests_list").append(listItem);
        $("#" + requests[i] + "_add").click(acceptFriend);
        $("#" + requests[i] + "_del").click(deleteFriend);
    };
}

function showCircles(circles) {
    
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
                    alert(val);
                else if(key == "result")
                    if(val == "requested") {
                        alert("Friend request sent");
                        $("#addForm")[0].reset();
                    }
                    else if(val == "accepted")
                        alert("Friend accepted");
            });
            getFriends();
        }
    });
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
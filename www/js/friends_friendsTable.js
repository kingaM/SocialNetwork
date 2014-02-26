var sessionUser;

function getFriends() {

    $.ajaxSetup({async:false});
    $.getJSON( "/api/currentUser", function(data) {
        sessionUser = data["username"];
    });
    $.ajaxSetup({async:true});

    var currUsername = window.location.pathname.split( '/' )[2];
    $.getJSON( "/api/user/" + currUsername + "/friends", function(data) {
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
        if(requests)
            showRequests(requests);
        if(circles)
            showCircles(circles);
    });
}

function createFriendsTable() {

    var tableHTML;
    $.ajaxSetup({async:false});
    $.get('/views/friends_friendsTable.mustache', function(template) {
        tableHTML = template;
    });
    $.ajaxSetup({async:true});

    $("#current_friends").append(tableHTML);

    $('#friendsTable').dataTable( {
        "sDom": "<'row'<'span6'l><'span6'f>r>t<'row'<'span6'i><'span6'p>>",
        "sPaginationType": "bootstrap",
        "aLengthMenu": [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],
        "iDisplayLength": 5,
        "oLanguage": {
            "sLengthMenu": "_MENU_ records per page"
        },
    });
}

function showFriends (friends, requests) {

    var friendsList = new Array();

    var monthNames = ["January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"];

    var sessionUserFriends = new Array();

    $.ajaxSetup({async:false});
    $.getJSON( "/api/user/" + sessionUser + "/friends", function(data) {
        $.each(data, function(key, val) {
            if(key == "friends") {
                for (var i = 0; i < val.length; i++) {
                    sessionUserFriends.push(val[i]["login"]);
                };
            };
        });
    });
    $.ajaxSetup({async:true});

    for (var i = 0; i < friends.length; i++) {
        var login = friends[i]['login'];
        var date = new Date(friends[i]['startTimestamp']*1000);
        date = date.getDate() + " " + monthNames[date.getMonth()] + " " + date.getFullYear();
        var image = "<img src='" + "http://i.imgur.com/r8R1C6B.png" + "' style='max-height:100px;'></img>";
        var name = "<a href='/user/" + login + "/profile'>" + friends[i]['name'] + "</a>";
        var action;
        if ($.inArray(login, sessionUserFriends) >= 0) {
            action = "<button type='button' class='btn btn-danger btn-sm' id='" + 
                login + "_del'><span class='glyphicon glyphicon-remove'></span></button>";
        } else {
            action = "<button type='button' class='btn btn-success btn-sm' id='" + 
                login + "_add'><span class='glyphicon glyphicon-plus'></span></button>";
        };
        var friend = [image, name, date, action];
        friendsList.push(friend);
    }

    $('#friendsTable').dataTable().fnClearTable();
    $('#friendsTable').dataTable().fnAddData(friendsList);

    for (var i = 0; i < friends.length; i++) {
        $("#" + friends[i]['login'] + "_del").click(friends[i]['login'], deleteFriend);
        $("#" + friends[i]['login'] + "_add").click(friends[i]['login'], function(name){
            addFriend(name.data);
        });
    }

}

function deleteFriend(name) {
    var username = name.data;
    $.ajax({
        url: "/api/user/" + sessionUser + "/friends/" + username,
        type: "DELETE",
        success: function(result) {
            getFriends();
        }
    });
}

function addFriend(username) {
    $.ajax({
        url: "/api/user/" + sessionUser + "/friends",
        type: "POST",
        data: {username: username},

        success: function(response) {
            var data = $.parseJSON(response);
            $.each( data, function(key, val) {
                if(key == "error")
                    displayModal(val);
                else if(key == "result" && val == "requested") {
                    displayModal("Friend request sent")
                    $("#addForm")[0].reset();
                }
            });
            getFriends();
        }
    });
}

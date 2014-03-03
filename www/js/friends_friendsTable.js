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
        "aaSorting": [[1, "asc"]],
        "oLanguage": {
            "sLengthMenu": "_MENU_ records per page"
        },
        "aoColumnDefs": [
            {"bSortable": false, "aTargets": [0, 3]},
        ],
    });
}

var friendsList; // Used in a few methods
function showFriends (friends, requests) {

    friendsList = new Array();

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
        var loginString = '"' + login + '"';
        var date = new Date(friends[i]['startTimestamp']*1000);
        date = date.getDate() + " " + monthNames[date.getMonth()] + " " + date.getFullYear();
        var image = "<img src='" + "http://i.imgur.com/r8R1C6B.png" + "' style='max-height:100px;'></img>";
        var name = "<a href='/user/" + login + "/profile'>" + friends[i]['name'] + "</a>";
        var action;
        if ($.inArray(login, sessionUserFriends) >= 0) {
            action = "<button type='button' class='btn btn-danger btn-sm'" + 
                "onclick='deleteFriend(" + loginString + ");'>" + 
                "<span class='glyphicon glyphicon-remove'></span></button>";
        } else {
            action = "<button type='button' class='btn btn-success btn-sm'" + 
               "onclick='addFriend(" + loginString + ");'>" + 
               "<span class='glyphicon glyphicon-plus'></span></button>";
        };
        var friend = [image, name, date, action];
        friendsList.push({"login": login, "info": friend});
    }

    var showFriendsInfo = new Array();
    for (var i = 0; i < friendsList.length; i++) {
        showFriendsInfo.push(friendsList[i]["info"]);
    };

    $('#friendsTable').dataTable().fnClearTable();
    $('#friendsTable').dataTable().fnAddData(showFriendsInfo);

}

function deleteFriend(username) {
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
                if(key == "error") {
                    $("#searchUsersGroup_search1").addClass("has-error");
                    var label = '<label id="addError" class="control-label" ' + 
                        'for="searchUsersGroup_search1">' + val + '</label>';
                    $("#addError").remove();
                    $(label).insertAfter("#searchUsersGroup_search1");
                } else if(key == "result" && val == "requested") {
                    $("#searchUsersGroup_search1").removeClass("has-error");
                    $("#addError").remove();
                    $("#addForm")[0].reset();
                }
            });
            getFriends();
        }
    });
}

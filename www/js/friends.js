document.getElementById("content").style.visibility="hidden";

function content() {
    prepare();
    document.getElementById("content").style.visibility="visible";
    getFriends();
}

function showRequests(requests) {
    $("#requests_list").empty();
    $("#numOfFriendReqs").text(requests.length);
    for (var i = 0; i < requests.length; i++) {
        var listItem = "<li>" + requests[i] + 
        " <button type='button' class='btn btn-success btn-xs' " + 
        "id='" + requests[i] + "_add'><span class='glyphicon glyphicon-ok'></span></button> " +
        " <button type='button' class='btn btn-danger btn-xs' " + 
        "id='" + requests[i] + "_del'><span class='glyphicon glyphicon-remove'></span></button></li>";
        $("#requests_list").append(listItem);
        $("#" + requests[i] + "_add").click(requests[i], acceptFriend);
        $("#" + requests[i] + "_del").click(requests[i], deleteFriend);
    };
}

function showCircles(circles) {
    $("#circles_list").empty();
    $("#selectCircles").empty();
    for (var i = 0; i < circles.length; i++) {
        var cName = circles[i]['name'];
        var listItem = "<li>" + cName + " <button type='button' class='btn btn-danger btn-xs' " + 
        "id='" + cName + "_del'><span class='glyphicon glyphicon-remove'></span></button>" + 
        "<ul id='circle_" + i + "'></ul></li>";
        $("#selectCircles").append("<option>" + circles[i]['name'] + "</option>");
        $("#circles_list").append(listItem);
        for (var j = 0; j < circles[i]['users'].length; j++) {
            var user = circles[i]['users'][j];
            var circleListItem = "<li>" + user + "</li>";
            $("#circle_" + i).append(circleListItem);
        };
        $("#" + cName + "_del").click(cName, deleteCircle);
    };
}

function acceptFriend(name) {
    addFriend(name.data);
}

function addCircle(circleName) {
    $.ajax({
        url: "/api/circles",
        type: "POST",
        data: {circleName: circleName},

        success: function(response) {
            var data = $.parseJSON(response);
            $.each( data, function(key, val) {
                if(key == "error")
                    displayModal(val);
                else if(key == "result") {
                    displayModal("Circle added");
                    $("#newCircle")[0].reset();
                }
            });
            getFriends();
        }
    });
}

function addToCircle(circleName, username) {
    $.ajax({
        url: "/api/circles/" + circleName,
        type: "POST",
        data: {username: username},

        success: function(response) {
            var data = $.parseJSON(response);
            $.each( data, function(key, val) {
                if(key == "error")
                    displayModal(val);
                else if(key == "result") {
                    displayModal("Added");
                    $("#addToCircle")[0].reset();
                }
            });
            getFriends();
        }
    });
}

function deleteCircle(name) {
    var circleName = name.data;
    $.ajax({
        url: "/api/circles/" + circleName,
        type: "DELETE",
        success: function(result) {
            getFriends();
        }
    });
}

function prepare() {

    createFriendsTable();

    $("#addForm").submit(function(e){
        e.preventDefault();
        addFriend($("#addForm :input").val());
    });

    $("#newCircle").submit(function(e){
        e.preventDefault();
        addCircle($("#newCircle :input").val());
    });

    $("#addToCircle").submit(function(e){
        e.preventDefault();
        var temp = new Array();
        $("#addToCircle :input").each(function() {
            temp.push($(this).val());
        });
        addToCircle(temp[0], temp[1]);
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

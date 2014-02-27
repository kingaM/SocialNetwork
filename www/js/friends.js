document.getElementById("content").style.visibility="hidden";

function content() {
    prepare();
    document.getElementById("content").style.visibility="visible";
    getFriends();
}

function showRequests(requests) {

    var requestsList = new Array();

    var monthNames = ["January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"];

    for (var i = 0; i < requests.length; i++) {
        var login = requests[i]['login'];
        var date = new Date(requests[i]['startTimestamp']*1000);
        date = date.getDate() + " " + monthNames[date.getMonth()] + " " + date.getFullYear();
        var image = "<img src='" + "http://i.imgur.com/r8R1C6B.png" + "' style='max-height:100px;'></img>";
        var name = "<a href='/user/" + login + "/profile'>" + requests[i]['name'] + "</a>";
        var action =    "<button type='button' class='btn btn-success btn' " + 
                        "id='" + requests[i]['login'] + "_add'>" + 
                            "<span class='glyphicon glyphicon-ok'></span>" + 
                        "</button> <br><br>" +
                        "<button type='button' class='btn btn-danger btn' " + 
                        "id='" + requests[i]['login'] + "_del'>" + 
                            "<span class='glyphicon glyphicon-remove'></span>" + 
                        "</button>";
        var friend = [image, name, date, action];
        requestsList.push(friend);
    }

    $('#requestsTable').dataTable().fnClearTable();
    $('#requestsTable').dataTable().fnAddData(requestsList);

    for (var i = 0; i < requests.length; i++) {
        $("#" + requests[i]['login'] + "_del").click(requests[i]['login'], deleteFriend);
        $("#" + requests[i]['login'] + "_add").click(requests[i]['login'], function(name){
            addFriend(name.data);
        });
    }
}

function showCircles(circles) {

    $("#circlesList").empty();
    $("#selectCircles").empty();

    for (var i = 0; i < circles.length; i++) {
        var cName = circles[i]['name'];

        var tableHTML = '<div class="table-responsive container-fluid">' + 
                            '<table cellpadding="0" cellspacing="0" border="0" class="table ' + 
                            'table-striped table-bordered datatable text-center" ' + 
                            'id="circleTable_' + cName + '">' + 
                                '<thead>' + 
                                    '<tr>' + 
                                        '<th class="text-center">Photo</th>' + 
                                        '<th class="text-center">Name</th>' + 
                                        '<th class="text-center">Friends since</th>' + 
                                        '<th></th>' + 
                                    '</tr>' + 
                                '</thead>' + 
                                '<tbody></tbody>' + 
                            '</table>' + 
                        '</div>';

        var buttonHTML = "<button type='button' class='btn btn-danger btn-xs' " + 
            "id='" + cName + "_del'>Delete Circle</button>"
 
        var listItem =  '<div class="panel panel-default">' + 
                            '<div class="panel-heading">' + 
                                '<h4 class="panel-title">' + 
                                    '<a data-toggle="collapse" data-parent="#circlesList"' + 
                                    ' href="#collapse_' + cName + '">' + cName + '</a>' + 
                                '</h4>' + 
                            '</div>' + 
                            '<div id="collapse_' + cName + '" class="panel-collapse collapse">' + 
                                '<div class="panel-body">' + buttonHTML + "<br><br>" + tableHTML + 
                                '</div>' + 
                            '</div>' + 
                        '</div>';

        $("#circlesList").append(listItem);

        $('#circleTable_' + cName).dataTable( {
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

        // for (var j = 0; j < circles[i]['users'].length; j++) {
        //     var user = circles[i]['users'][j];
        //     var circleListItem = "<li>" + user + "</li>";
        //     $("#circle_" + i).append(circleListItem);
        // };
        $("#" + cName + "_del").click(cName, deleteCircle);
        $("#selectCircles").append("<option>" + circles[i]['name'] + "</option>");
    };

    fixTables();
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

function createRequestsTable() {
    $('#requestsTable').dataTable( {
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

function prepare() {

    createFriendsTable();
    createRequestsTable();

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

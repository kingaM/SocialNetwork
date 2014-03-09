function content() {
    prepare();
}

function getUser(username) {
    $.getJSON( "/api/user/" + username + "/profile", function(data) {
        if(!data['valid']) {
            console.log("error");
            return;
            // TODO: react here
        }

        var user = data['user'];

        var image;
        if(user['profilePicture'] != null)
            image = user['profilePicture'];
        else
            image = "http://i.imgur.com/r8R1C6B.png";

        var name;
        if(user['middleName'] == "")
            name = user['firstName'] + " " + user['lastName'];
        else
            name = name = user['firstName'] + " " + user['middleName'] + " " + user['lastName'];

        var monthNames = ["January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"];
        var date = new Date(user['dob']*1000);
        date = date.getDate() + " " + monthNames[date.getMonth()] + " " + date.getFullYear();

        showUser(name, image, user['username'], date, user['email']);
    });
}

function deleteUser(username) {
    $.ajax({
        url: "/api/user/" + username,
        type: "DELETE",
        success: function(result) {
            $('#userInfo').remove();
        }
    });
}

function banUser(username) {
    $.ajax({
        url: "/api/user/" + username + "/ban",
        type: "DELETE",
        success: function(result) {
            $('#userInfo').remove();
        }
    });
}

function banUserFromComment(username, commentID) {
    banUser(username);
    deleteComment(commentID);
}

function resetPassword(username) {

}

function deleteComment(id) {
    $.ajax({
        url: "/api/comments/" + id,
        type: "DELETE",
        success: function(result) {
            $('#report_' + id).remove();
        }
    });
}

function ignoreReport(id) {
    $.ajax({
        url: "/api/comments/" + id + "/report",
        type: "DELETE",
        success: function(result) {
            $('#report_' + id).remove();
        }
    });
}

function showUser(name, image, username, dob, email) {

    $('#userInfo').remove();

    var html =  '<div id="userInfo" class="col-md-5">' + 
                    '<div class="row-fluid">' + 
                        '<div>' + 
                            '<div class="panel panel-primary">' + 
                                '<div class="panel-heading">' + 
                                    '<h3 class="panel-title">' + name + '</h3>' + 
                                '</div>' + 
                                '<div class="panel-body">' + 
                                    '<div class="row-fluid">' + 
                                        '<div class="col-md-4">' + 
                                            '<img src="' + image + '"' + 
                                                'style="max-height:100px;"></img>' + 
                                        '</div>' + 
                                        '<div class="col-md-8">' + 
                                            '<table class="table table-condensed table-responsive">' + 
                                                '<tbody>' + 
                                                    '<tr>' + 
                                                        '<td>Username:</td>' + 
                                                        '<td>' + username + '</td>' + 
                                                    '</tr>' + 
                                                    '<tr>' + 
                                                        '<td>DOB:</td>' + 
                                                        '<td>' + dob + '</td>' + 
                                                    '</tr>' + 
                                                    '<tr>' + 
                                                        '<td>Email:</td>' + 
                                                        '<td>' + email + '</td>' + 
                                                    '</tr>' + 
                                                    '<tr><td></td><td></td></tr>' + 
                                                '</tbody>' + 
                                            '</table>' + 
                                        '</div>' + 
                                    '</div>' + 
                                '</div>' + 
                                '<div class="panel-footer">' + 
                                    '<button class="btn btn-danger pull-right" onclick="deleteUser(\'' + 
                                                username + '\');">Delete</button> ' + 
                                    '<button class="btn btn-danger pull-right" onclick="banUser(\'' + 
                                                username + '\');">Ban</button>' + 
                                    '<div>' + 
                                        '<div class="input-group">' + 
                                            '<input type="text" class="form-control" id="newPass">' + 
                                              '<span class="input-group-btn">' + 
                                                     '<button class="btn btn-warning">' + 
                                                     'New Password</button>' + 
                                              '</span>' + 
                                        '</div>' + 
                                    '</div>' + 
                                '</div>' + 
                            '</div>' + 
                        '</div>' + 
                    '</div>' + 
                '</div>';

    $('#userControls').append(html);
}

function showReportedComments(comments) {

    var monthNames = ["January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"];

    for (var i = 0; i < comments.length; i++) {
        var comment = comments[i];
        var id = comment['id'];
        var content = comment['content'];
        var date = new Date(comment['timestamp']*1000);
        var time = date.getHours() + ":" + date.getMinutes();
        date = date.getDate() + " " + monthNames[date.getMonth()] + " " + date.getFullYear();
        var titleLink = "/user/" + comment['login'];
        var name = comment['fromName'];
        var username = comment['login'];

        var html = '<article class="search-result row container-fluid" id="report_' + id + '">' + 
            '<div class="panel panel-default">' + 
                '<div class="container-fluid">' + 
                    '<a href="' + titleLink + '" title="">' + name + '</a>' + 
                    '<div class="pull-right">' + 
                        '<i class="glyphicon glyphicon-calendar"></i><span> ' + date + ' </span>' + 
                        '<i class="glyphicon glyphicon-time"></i><span> ' + time + '</span>' + 
                    '</div>' + 
                '</div>' + 
                '<div class="col-xs-12 col-sm-12 col-md-7 excerpet">' + 
                    '<p>' + content + '</p>' + 
                        '<div class="container-fluid">' + 
                    '</div>' + 
                '</div>' + 
                '<span class="clearfix borda"></span>' + 
                '<div class="container-fluid" style="padding-bottom:10px;">' + 
                    '<div class="pull-right">' + 
                        '<button class="btn btn-danger" onclick="banUserFromComment(\'' + 
                            username + '\', ' + id + ');">Ban User</button>' + 
                        ' <button class="btn btn-danger" onclick="deleteComment(' + 
                            id + ');">Delete Comment</button>' + 
                        ' <button class="btn btn-warning" onclick="ignoreReport(' + 
                            id + ');">Ignore</button>' + 
                    '</div>' + 
                '</div>'
            '</div>>' + 
        '</article>';

        $('#reportedPosts').append(html);

    };
}

function prepare() {
   $("#searchForm").submit(function(e){
        e.preventDefault();
        getUser($("#searchForm :input").val());
    });

   $.getJSON( "/api/comments", function(data) {
        showReportedComments(data);
    });
}

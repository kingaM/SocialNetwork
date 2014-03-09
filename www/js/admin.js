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

function resetPassword(username) {

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

function prepare() {
   $("#searchForm").submit(function(e){
        e.preventDefault();
        getUser($("#searchForm :input").val());
    });
}

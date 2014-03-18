function setupNewsItems() {

}

function addComment(id, content) {
    var currUsername = window.location.pathname.split( '/' )[2];
    $.ajax({
        url: "/api/user/" + currUsername + "/posts/" + id,
        type: "POST",
        data: {content: content},

        success: function(response) {
            var data = $.parseJSON(response);
            $.each( data, function(key, val) {
                if(key == "error")
                    displayModal(val);
                else if(key == "result" && val == "added") {
                    $("#replyForm_" + id)[0].reset();
                }
            });
            getPosts();
        }
    });
}

function reportComment(id) {
    $.ajax({
        url: "/api/comments/" + id + "/report",
        type: "POST",
        data: {},

        success: function(response) {
            $("#report_" + id).text("Reported");
            $("#report_" + id).fadeTo("fast", .5).removeAttr("href");
        }
    });
}

function renderReply(titleLink, title, date, time, text, commentID) {
    var reply = '<article class="search-result row container-fluid">' + 
        '<div class="panel panel-default">' + 
            '<div class="container-fluid">' + 
                '<a href="' + titleLink + '" title="">' + title + '</a>' + 
                '<div class="pull-right">' + 
                    '<i class="glyphicon glyphicon-calendar"></i><span> ' + date + ' </span>' + 
                    '<i class="glyphicon glyphicon-time"></i><span> ' + time + '</span>' + 
                '</div>' + 
            '</div>' + 
            '<div class="col-xs-12 col-sm-12 col-md-7 excerpet">' + 
                '<p>' + text + '</p>' + 
                    '<div class="container-fluid">' + 
                '</div>' + 
            '</div>' + 
            '<span class="clearfix borda"></span>' + 
            '<div class="container-fluid">' + 
                '<div class="pull-right">' + 
                    '<i class="glyphicon glyphicon-flag"></i>' + 
                    '<span> <a href="javascript:reportComment(' + commentID + 
                        ')" id="report_' + commentID + '">Report</a></span>' + 
                '</div>' + 
            '</div>'
        '</div>' + 
    '</article>';
    return reply;
}

function showPosts(data) {
    $("#newsItems").empty();
    var posts = data['posts'];
    var currUsername = window.location.pathname.split( '/' )[2];
    var monthNames = ["January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"];

    for (var i = 0; i < posts.length; i++) {
        var post = posts[i];
        var date = new Date(post['timestamp']*1000);
        var time = date.getHours() + ":" + date.getMinutes();
        date = date.getDate() + " " + monthNames[date.getMonth()] + " " + date.getFullYear();
        var id = post['id'];
        var title;
        var content = post['content'];
        if(post['type'] == "image") {
            title = "<a href='/user/" + post['to'] + "'>" + post['toName'] + "</a>" + 
                " <small>added a new photo:</small>";
            var url = null;
            $.ajaxSetup({async:false});
            $.getJSON(post['content'], 
                function(data) {
                    if(data['photo'] != null) {
                        url = data['photo']['url'];
                    }
            });
            $.ajaxSetup({async:true});
            if(url == null) {
                continue;
            }
            content = '<img src="' + url + ' " class="img-responsive">';
        } else if(post['type'] != "friend") {
            title = "<a href='/user/" + post['from'] + "'>" + post['fromName'] + "</a>";
            if(post['from'] != post['to'])
                title += " <span class='glyphicon glyphicon-chevron-right'></span> " + 
                            "<a href='/user/" + post['to'] + "'>" + post['toName'] + "</a>";
        } else {
            if(currUsername == post['to'])
                title = "<a href='/user/" + post['from'] + "'>" + post['fromName'] + "</a>";
            else
                title = "<a href='/user/" + post['to'] + "'>" + post['toName'] + "</a>";
        };

        var wallPostID =  id;
        var numOfReplies =  post['comments'].length;
        var toUser = post['to'];
        var imgURL =  "http://i.imgur.com/r8R1C6B.png";

        $.ajaxSetup({async:false});
        $.getJSON('/api/user/' + toUser + '/profile/image', function(data) {
            if(!data['valid'])
                return;
            if(data['image'])
                imgURL = data['image'];
        });
        $.ajaxSetup({async:true});

        var output = renderNewsItem(imgURL, date, time, title, content, wallPostID, numOfReplies, 
            toUser);
        $("#newsItems").append(output);

        if(sessionUser == toUser) {
            var privacy;
            switch (post['privacy']) {
            case "1":
                privacy = "Me Only";
                break;
            case "2":
                privacy = "Circles";
                break;
            case "3":
                privacy = "Friends";
                break;
            case "4":
                privacy = "Friends of Friends";
                break;
            default:
                privacy = "Everyone";
                break;
            } 
            $('#select_' + wallPostID).val(privacy);

            $('#select_' + wallPostID).change(wallPostID, function(e){

                var privacy;
                switch ($('#select_' + e.data).val()) {
                case "Me Only":
                    privacy = 1;
                    break;
                case "Circles":
                    privacy = 2;
                    break;
                case "Friends":
                    privacy = 3;
                    break;
                case "Friends of Friends":
                    privacy = 4;
                    break;
                default:
                    privacy = 5;
                    break;
                }

                $.ajax({
                    url: "/api/posts/" + e.data + "/privacy",
                    type: "POST",
                    data: {privacyLevel: privacy},
                    success: function(response) {
                    }
                });
            });

        };

        $("#replyForm_" + id).submit(id, function(e){
            e.preventDefault();
            var id = e.data;
            addComment(id, $("#replyForm_" + id + " :input").val());
        });

        for (var j = 0; j < post['comments'].length; j++) {
            var comment = post['comments'][j];
            var commentID = comment['id'];
            var content = comment['content'];
            var date = new Date(comment['timestamp']*1000);
            var time = date.getHours() + ":" + date.getMinutes();
            date = date.getDate() + " " + monthNames[date.getMonth()] + " " + date.getFullYear();
            var titleLink = "/user/" + comment['login'];
            var name = comment['fromName'];
            var commentID = comment['id'];
            var commentHTML = renderReply(titleLink, name, date, time, content, commentID);
            $("#replies_" + id).append(commentHTML);

            if(comment['reported'] == 1) {
                $("#report_" + commentID).text("Reported");
                $("#report_" + commentID).fadeTo("fast", .5).removeAttr("href");
            };
        };
    };
}

function addPost(content, username) {
    $.ajax({
        url: "/api/user/" + username,
        type: "POST",
        data: {content: content},

        success: function(response) {
            var data = $.parseJSON(response);
            $.each( data, function(key, val) {
                if(key == "error")
                    displayModal(val);
                else if(key == "result" && val == "added") {
                    $("#newPostForm")[0].reset();
                }
            });
            getPosts();
        }
    });
}

function renderNewsItem(imgURL, date, time, title, text, wallPostID, numOfReplies, toUser) {

    var privacyOptions = "";

    if(sessionUser == toUser) {
        privacyOptions = 'Privacy' + 
                                '<select id="select_' + wallPostID + '"class="form-control">' + 
                                    '<option>Me Only</option>' + 
                                    '<option>Circles</option>' + 
                                    '<option>Friends</option>' + 
                                    '<option>Friends of Friends</option>' + 
                                    '<option>Everyone</option>' + 
                                '</select>';
    };

    var html = '<section class="container-fluid">' + 
        '<article class="search-result row">' + 
            '<div class="col-xs-12 col-sm-12 col-md-3">' + 
                '<a href="#" class="thumbnail"><img src="'+imgURL+'" style="max-height:120px;"/></a>' + 
            '</div>' + 
            '<div class="col-xs-12 col-sm-12 col-md-2">' + 
                '<ul class="meta-search">' + 
                    '<li><i class="glyphicon glyphicon-calendar"></i><span>' + date + '</span></li>' + 
                    '<li><i class="glyphicon glyphicon-time"></i><span>' + time + '</span></li>' + 
                '</ul>' + privacyOptions + 
            '</div>' + 
            '<div class="col-xs-12 col-sm-12 col-md-7 excerpet">' + 
                '<h3>' + title + '</h3>' + 
                '<p>' + text + '</p>                        ' + 
                    '<div class="container-fluid">' + 
                    '<div class="row">' + 
                        '<div id="replies">' + 
                            '<div class="panel-group">' + 
                                '<h4 class="panel-title">' + 
                                    '<a data-toggle="collapse" data-parent="#accordion" ' + 
                                        'href="#collapseReplies_' + wallPostID + '">' + 
                                      'Replies' + 
                                    '</a>' + 
                                    ' <span class="badge">' + numOfReplies + '</span>' + 
                                '</h4>' + 
                                '<div id="collapseReplies_' + wallPostID + 
                                    '" class="panel-collapse collapse">' + 
                                  '<div class="panel-body" id="replies_' + wallPostID + '">' + 
                                  '</div>' + 
                                '</div>' + 
                            '</div>' + 
                        '</div>' + 
                        '<div class="container-fluid">' + 
                            '<div class="panel-body">' + 
                                '<button class="btn btn-primary pull-right" type="submit" ' + 
                                    'id="replyToggle_' + wallPostID + '" ' + 
                                    'onclick="$(\'#replyToggle_' + wallPostID + 
                                        '\').hide();$(\'#replyForm_' + wallPostID + '\').show();">' + 
                                    'Reply' + 
                                '</button>' + 
                                '<form accept-charset="UTF-8" action="" method="POST" id="replyForm_' + 
                                    wallPostID + '" style="display:none;">' + 
                                    '<textarea class="form-control" name="message" ' + 
                                        'placeholder="Type in your message" rows="2" ' + 
                                        'style="margin-bottom:10px;">' + 
                                    '</textarea>' + 
                                    '<button class="btn btn-primary pull-right" type="submit">' + 
                                        'Reply' + 
                                    '</button>' + 
                                    '<button type="button" class="btn btn-danger pull-left" ' + 
                                        'onclick="$(\'#replyToggle_' + wallPostID + '\').show();' + 
                                            '$(\'#replyForm_' + wallPostID + '\')[0].reset();' + 
                                            '$(\'#replyForm_' + wallPostID + '\').hide();">' + 
                                        '<span class="glyphicon glyphicon-remove"></span>' + 
                                    '</button>' + 
                                '</form>' + 
                            '</div>' + 
                        '</div>' + 
                    '</div>' + 
                '</div>' + 
            '</div>' + 
            '<span class="clearfix borda"></span>' + 
        '</article>' + 
    '</section>';
    return html;
}

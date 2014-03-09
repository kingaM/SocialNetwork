var newsItemTemplate;
function setupNewsItems() {
    $.ajaxSetup({async:false});
    $.get('/views/home_newsItem.mustache', function(template) {
        newsItemTemplate = template;
    });
    $.ajaxSetup({async:true});
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

function renderReply(titleLink, title, date, time, text) {
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
            content = '<img src="' + post['content'] + ' " class="img-responsive">';
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

        var view = {
            wallPostID: id,
            imgURL: "http://i.imgur.com/r8R1C6B.png",
            date: date,
            time: time,
            title: title,
            text: content,
            numOfReplies: post['comments'].length
        };
        var output = Mustache.render(newsItemTemplate, view);
        $("#newsItems").append(output);

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
            var commentHTML = renderReply(titleLink, name, date, time, content);
            $("#replies_" + id).append(commentHTML);
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

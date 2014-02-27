function content() {
    prepare();
    getPosts();
}

function getPosts() {
    $("#newsItems").empty();
    var currUsername = window.location.pathname.split( '/' )[2];
    $.getJSON( "/api/user/" + currUsername, function(data) {
        var posts = data['posts'];
        var monthNames = ["January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"];

        for (var i = 0; i < posts.length; i++) {
            var post = posts[i];
            var date = new Date(post['timestamp']*1000);
            var time = date.getHours() + ":" + date.getMinutes();
            date = date.getDate() + " " + monthNames[date.getMonth()] + " " + date.getFullYear();

            var view = {
                imgURL: "http://i.imgur.com/r8R1C6B.png",
                date: date,
                time: time,
                titleLink: "/user/" + post['from'],
                title: post['fromName'],
                text: post['content']
            };
            var output = Mustache.render(newsItemTemplate, view);
            $("#newsItems").append(output);
        };
    });
}

function addPost(content) {
    var currUsername = window.location.pathname.split( '/' )[2];
    $.ajax({
        url: "/api/user/" + currUsername,
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

var newsItemTemplate;
function prepare() {

    $("#newPostForm").submit(function(e){
        e.preventDefault();
        addPost($("#newPostForm :input").val());
    });

    $.ajaxSetup({async:false});
    $.get('/views/home_newsItem.mustache', function(template) {
        newsItemTemplate = template;
    });
    $.ajaxSetup({async:true});
}

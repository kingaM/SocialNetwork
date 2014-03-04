var username = 'user';
var blog = 'blog';

function content() {
    username = window.location.pathname.split( '/' )[2];
    blog = window.location.pathname.split( '/' )[4];
    getPostNumber();
    getBlogInfo();
    getUserBlogs();
    setupSearch();
    $("#new-post-btn").click(function(e) {
        e.preventDefault();
        window.location.replace("/user/" + username + "/blogs/" + 
            blog + "/newPost");               
    });
}

function getUserBlogs() {
    $.getJSON( "/api/user/" + username + "/blogs/" + 
        blog + "/pages/" + window.location.pathname.split( '/' )[6], 
        function(data) {
            if(!data['valid']) {
                showError("error-unknown", "Something went wrong, but we don't know what." +
                    "Please try again later.");;
            } else {
                showPosts(data['posts']);
            }
    });
}

function showPosts(posts) {
    $("#blog-body").empty();
    var i;
    for (i = 0; i < posts.length; ++i) {
        var post = posts[i];
        showPost(post, true);
    }
}

function showPost(post, addLink) {
    if(addLink) {
        var title = '<h2 class="blog-post-title">' +
        '<a href="/user/' + username + "/blogs/" + blog + "/posts/" + post['id'] + '">' 
        + post['title'] + '</a></h2>';
    } else {
        var title = '<h2 class="blog-post-title">' +
        + post['title'] + '</h2>';
    }
   var html =  '<div class="blog-post">' + title + 
          '<p class="blog-post-meta">' + showDate(post['timestamp']) + '</p>' +
          post['content'] +
        '</div>';
    $('#blog-body').append(html);
}


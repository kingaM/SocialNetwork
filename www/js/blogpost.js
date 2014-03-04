var username = 'user';
var blog = 'blog';

function content() {
    username = window.location.pathname.split( '/' )[2];
    blog = window.location.pathname.split( '/' )[4];
    getBlogInfo();
    getUserBlogs();
}


function getUserBlogs() {
    $.getJSON( "/api/user/" + username + "/blogs/" + 
        blog + "/posts/" + window.location.pathname.split( '/' )[6], 
        function(data) {
            if(!data['valid']) {
                showError("error-unknown", "Something went wrong, but we don't know what." +
                    "Please try again later.");
            } else {
                showPosts(data['posts']);
            }
    });
}

function showPosts(post) {
    $("#blog-body").empty();
    showPost(post)
}

function showPost(post) {
   var html =  '<div class="blog-post">' +
          '<h2 class="blog-post-title">' + post['title'] + '</h2>' +
          '<p class="blog-post-meta">' + showDate(post['timestamp']) + '</p>' +
          post['content'] +
        '</div>';
    $('#blog-body').append(html);
}

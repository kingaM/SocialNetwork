var username = 'user';
var blog = 'blog';
var searchword = '';

function content() {
    username = window.location.pathname.split( '/' )[2];
    blog = window.location.pathname.split( '/' )[4];
    searchword = window.location.pathname.split( '/' )[6];
    getBlogInfo();
    getUserBlogs();
    setupSearch();
    $('#search-txt').val(decodeURIComponent(searchword));
}

function getUserBlogs() {
    $.getJSON( "/api/user/" + username + "/blogs/" + 
        blog + "/search/" + searchword, 
        function(data) {
            if(!data['valid']) {
                showError();
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
        showPost(post);
    }
}

function showPost(post) {
    var content = '';
    var i;
    for (i = 0; i < post['content'][0].length; ++i) {
        content = content + " ... " + boldWords(post['content'][0][i],
            decodeURIComponent(searchword));
    }
    var html =  '<div class="blog-post">' +
          '<h2 class="blog-post-title">' +
        '<a href="/user/' + username + "/blogs/" + blog + "/posts/" + post['id'] + '">' 
        + post['title'] + '</a></h2>' +
          '<p class="blog-post-meta">' + showDate(post['timestamp']) + '</p>' +
          content +
        '</div>';

    $('#blog-body').append(html);
}

function boldWords(input, keyword) {
    return input.replace(new RegExp('(^|\\s)(' + keyword + ')(\\s|$)','ig'), '$1<b>$2</b>$3');
}
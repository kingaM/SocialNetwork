function content() {
    prepare();
    setupNewsItems();
    getPosts();
}

function getPosts() {
    var currUsername = window.location.pathname.split( '/' )[2];
    $.getJSON( "/api/user/" + currUsername, function(data) {
        showPosts(data);
    });
}

function prepare() {
    $("#newPostForm").submit(function(e){
        e.preventDefault();
        var currUsername = window.location.pathname.split( '/' )[2];
        addPost($("#newPostForm :input").val(), currUsername);
    });
}

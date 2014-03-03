function content() {
    prepare();
    setupNewsItems();
    getPosts();
}

function getPosts() {
    $.getJSON( "/api/newsfeed", function(data) {
        showPosts(data);
    });
}

function prepare() {
    var sessionUser;
    $.ajaxSetup({async:false});
    $.getJSON( "/api/currentUser", function(data) {
        sessionUser = data["username"];
    });
    $.ajaxSetup({async:true});
    $("#newPostForm").submit(function(e){
        e.preventDefault();
        addPost($("#newPostForm :input").val(), sessionUser);
    });
}

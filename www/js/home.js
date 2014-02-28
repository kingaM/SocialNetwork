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

}

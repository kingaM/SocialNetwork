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

function prepare() {
    $("#newPostForm").submit(function(e){
        e.preventDefault();
        addPost($("#newPostForm :input").val());
    });
}

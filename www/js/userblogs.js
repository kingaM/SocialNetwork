function content() {
    var pathArray = window.location.pathname.split( '/' );
    getUserBlogs();
}

function getUserBlogs() {
    $.getJSON( "/api/user/" + window.location.pathname.split( '/' )[2] + "/blogs", 
        function(data) {
            if(!data['valid']) {
                showError();
            } else {
                if(data['currentUser']) {
                    g_data = data;
                    $("#edit-btn-group").show();
                } 
                showBlogs(data['blogs']);
            }
    });
}

function showBlogs(blogs) {
    $("#blog-list").empty();
    var i;
    for (i = 0; i < blogs.length; ++i) {
        var blog = blogs[i];
        showBlog(blog);
    }
}

function showBlog(blog) {
    console.log(blog['name']);
    var html = "<div class=\"col-md-6\">" +
            "<div class=\"well well-sm\">" +
                "<div class=\"row\">" +
                    "<div class=\"col-xs-9 col-md-9 section-box\">" +
                        "<h2>"
                             + blog['name']  + 
                            "<a href=\"./blogs/"+ blog['url'] + "\" target=\"_blank\">" +
                            "<span class=\"glyphicon glyphicon-new-window\">" +
                            "</span></a>" +
                        "</h2>" +
                        "<p>"
                             + blog['about'] + 
                        "</p>" +
                    "</div>" +
                "</div>" +
            "</div>" +
        "</div>";

    console.log(html);

    $("#blog-list").append(html);

}

function showError() {
    $("#error-unknown").html("<div class=\"alert alert-danger alert-dismissable\">" +
        "<button type=\"button\" class=\"close\" data-dismiss=\"alert\"" +
        "aria-hidden=\"true\">&times;</button>" +
        "<strong>Error:</strong> Something went wrong, but we don't know what." +
        "Please try again later." + 
        "</div>");
}
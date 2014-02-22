var username = 'username';

function content() {
    username = window.location.pathname.split( '/' )[2];
    getUserBlogs();
    setupDropdown();
}

function setupDropdown() {
     $('.dropdown-menu').find('form').click(function (e) {
        e.stopPropagation();
    });
    $('#new-post-form').submit(function(e) {
        e.preventDefault();
        var postText = $("#new-post").val();
        var name = $("#name").val();
        var url = $("#url").val();
        clearLabels();
        if(!validateAlphanumeric(url)) {
            showErrorDropdown('url', 'The identifier is not alphanumeric.');
        }
        postBlog(name, url, postText);
    });
    $('body').click(function(e) {
        clearDropdown();
    });
}

function postBlog(name, url, text) {
    var values = {};
    values["text"] = text;
    values["name"] = name;
    values["url"] = url;
    $.ajax({
        type: "post",
        url: "/api/user/" + username + "/blogs",
        data: values,
        success: function(data) {
            console.log(data);
            var json = $.parseJSON(data);
            var valid = json['valid'];
            if (!valid) {
                showError();
                return;
            }
            var unique = json['unique'];
            var alphanumeric = json['alphanumeric'];       
            if(valid && unique && alphanumeric) {
                clearDropdown();
                getUserBlogs();
            } 
            if (!unique) {
                showErrorDropdown("url", "The identifier you have chosen is not unique");
            }
        }
    });
}

function clearDropdown() {
    $("#new-post").val("");
    $("#name").val("");
    $("#url").val("");
    clearLabels();
    $('[data-toggle="dropdown"]').parent().removeClass('open');
}

function clearLabels() {
    $("#form-group-url").removeClass("has-error");
    $("#form-group-name").removeClass("has-error");
    $("#control-label-url").hide();
    $("#control-label-name").hide();
}

function showErrorDropdown(id, msg) {
    $("#form-group-" + id).addClass("has-error");
    $("#control-label-" + id).show();
    $("#control-label-" + id).text(msg);
}

function getUserBlogs() {
    $.getJSON( "/api/user/" + username + "/blogs", 
        function(data) {
            if(!data['valid']) {
                showError();
            } else {
                if(data['currentUser']) {
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
                            "<a href=\"./"+ blog['url'] + "/1\" target=\"_blank\">" +
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

function validateAlphanumeric(string){
    if(/[^a-zA-Z0-9]/.test(string)) {
       return false;
    }
    return true;     
 }
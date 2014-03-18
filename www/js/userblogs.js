var username = 'username';

function content() {
    username = window.location.pathname.split( '/' )[2];
    addPrivacySelector("privacy-selector", "3");
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
        var privacy = $("#privacy-options-privacy-selector").val();
        clearLabels();
        if(!validateAlphanumeric(url)) {
            showErrorDropdown('url', 'The identifier is not alphanumeric.');
        } else {
            postBlog(name, url, postText, privacy);
        } 
    });
    $('body').click(function(e) {
        clearDropdown();
    });
}

function postBlog(name, url, text, privacy) {
    var values = {};
    values["text"] = text;
    values["name"] = name;
    values["url"] = url;
    values["privacy"] = privacy;
    $.ajax({
        type: "post",
        url: "/api/user/" + username + "/blogs",
        data: values,
        success: function(data) {
            var json = $.parseJSON(data);
            var valid = json['valid'];
            if (!valid) {
                showError("error-unknown", "Something went wrong, but we don't know what." +
                    "Please try again later.");
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
                showError("error-unknown", "Something went wrong, but we don't know what." +
                    "Please try again later.");
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
    var html = "<div class=\"col-md-6\">" +
            "<div class=\"well well-sm\">" +
                "<div class=\"row\">" +
                    "<div class=\"col-md-12 section-box\">" +
                        "<h2>"
                             + blog['name']  + 
                            "<a href=\"./blogs/"+ blog['url'] + "/pages/1\" target=\"_blank\">" +
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

    $("#blog-list").append(html);

}

function validateAlphanumeric(string){
    if(/[^a-zA-Z0-9]/.test(string)) {
       return false;
    }
    return true;     
 }
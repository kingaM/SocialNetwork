var username = 'user';

function content() {
    username = window.location.pathname.split( '/' )[2];
    getPostNumber();
    getBlogInfo();
    getUserBlogs();
}

function setButtons(postNumber) {
    var page = parseInt(window.location.pathname.split( '/' )[5]);
    if(page == 1) {
        $("#previous").hide();
    } else {
        $('#previous-page').attr('href', "/user/" + username + "/blogs/" + 
            window.location.pathname.split( '/' )[4] + "/" + (page - 1));
    }
    if (page < postNumber/2) {
        $("#next-page").prop("href", "/user/" + username + "/blogs/" + 
            window.location.pathname.split( '/' )[4] + "/" +
            (page + 1));
    } else {
        $("#next").hide();
    }

}

function getPostNumber() {
    $.getJSON( "/api/user/" + username + "/blogs/" + 
        window.location.pathname.split( '/' )[4] + 
        "/postsNum", 
        function(data) {
            if(!data['valid']) {
                showError();
            } else {
                setButtons(data['posts']);
            }
    });
}

function getUserBlogs() {
    $.getJSON( "/api/user/" + username + "/blogs/" + 
        window.location.pathname.split( '/' )[4] + "/" + window.location.pathname.split( '/' )[5], 
        function(data) {
            if(!data['valid']) {
                showError();
            } else {
                showPosts(data['posts']);
            }
    });
}

function getBlogInfo() {
    $.getJSON( "/api/user/" + username + "/blogs/" + 
        window.location.pathname.split( '/' )[4] + "/info", 
        function(data) {
            if(!data['valid']) {
                showError();
            } else {
                $("#blog-title").html(data['name']);
                $("#blog-description").html(data['about']);
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
   var html =  '<div class="blog-post">' +
          '<h2 class="blog-post-title">' + post['title'] + '</h2>' +
          '<p class="blog-post-meta">' + showDate(post['timestamp']) + '</p>' +
          post['content'] +
        '</div>';
    $('#blog-body').append(html);
}

function showDate(timestamp) {
    var m_names = new Array("January", "February", "March", 
    "April", "May", "June", "July", "August", "September", 
    "October", "November", "December");
    var date = new Date(timestamp * 1000);
    var curr_date = date.getDate();
    var sup = "";
    if (curr_date == 1 || curr_date == 21 || curr_date ==31) {
       sup = "st";
    } else if (curr_date == 2 || curr_date == 22) {
       sup = "nd";
    } else if (curr_date == 3 || curr_date == 23) {
       sup = "rd";
    } else {
       sup = "th";
    }

    var curr_month = date.getMonth();
    var curr_year = date.getFullYear();
    return curr_date + "<SUP>" + sup + "</SUP> " 
        + m_names[curr_month] + " " + curr_year;
}

function showError() {
    $("#error-unknown").html("<div class=\"alert alert-danger alert-dismissable\">" +
        "<button type=\"button\" class=\"close\" data-dismiss=\"alert\"" +
        "aria-hidden=\"true\">&times;</button>" +
        "<strong>Error:</strong> Something went wrong, but we don't know what." +
        "Please try again later." + 
        "</div>");
}
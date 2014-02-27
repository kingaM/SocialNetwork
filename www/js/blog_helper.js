function setupSearch() {
    $("#search").click(function(e) {
        e.preventDefault();
        postSearch($("#search-txt").val());
    });
    $('#search-txt').keydown(function(e) {
        var keypressed = e.keyCode || e.which;
        if (keypressed == 13) {
                e.preventDefault();
                postSearch($("#search-txt").val());         
        }
    });
}

function postSearch(text) {
    window.location.replace("/user/" + username + "/blogs/" + blog + "/search/" + 
        encodeURIComponent(text));
}

function setButtons(postNumber) {
    $("#pages").empty();
    var page = parseInt(window.location.pathname.split( '/' )[6]);
    var url = "/user/" + username + "/blogs/" + 
            blog + "/pages/";
    if(page == 1) {
        $("#pages").append('<li class="disabled"><a href="#">&laquo;</a></li>')
    } else {
        $("#pages").append('<li><a href="'+ url + (page - 1) + '">&laquo;</a></li>')
    }
    for(var i = 1; i <= postNumber/2; i++) {
        if (page == i) {
            $('#pages').append('<li class="active"><a href="' + url + i + '">' + i +
                '<span class="sr-only">(current)</span></a></li>');
        } else {
            $('#pages').append('<li><a href="' + url + i  + '">' + i +
                '<span class="sr-only">(current)</span></a></li>');
        }
    }
    if (page == postNumber/2) {
        $("#pages").append('<li class="disabled"><a href="#">&raquo;</a></li>');
    } else {
        $("#pages").append('<li><a href="'+ url + (page + 1) + '">&raquo;</a></li>');
    }
}

function getPostNumber() {
    $.getJSON( "/api/user/" + username + "/blogs/" + 
        blog + 
        "/postsNum", 
        function(data) {
            if(!data['valid']) {
                showError();
            } else {
                setButtons(data['posts']);
            }
    });
}

function getBlogInfo() {
    $.getJSON( "/api/user/" + username + "/blogs/" + 
        blog + "/info", 
        function(data) {
            if(!data['valid']) {
                showError();
            } else {
                $("#blog-title-link").html(data['name']);
                $("#blog-description").html(data['about']);
                $("#blog-title-link").attr("href", "/user/" + username + "/blogs/" + 
                    blog + "/pages/" + 1);
            }
            if(data['currentUser']) {
                $("#new-post-btn").show();
            }
    });
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
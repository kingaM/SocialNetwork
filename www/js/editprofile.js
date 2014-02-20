function content() {
    var pathArray = window.location.pathname.split( '/' );
    console.log(pathArray[2]);
    getUserInfo();
}

function getUserInfo() {
    $.getJSON( "/api/user/" + window.location.pathname.split( '/' )[2] + "/profile", 
        function(data) {
            if(!data['valid']) {
                // Error
            } else {
                if(data['currentUser']) {
                    $("#edit-btn-group").show();
                    $("#edit-btn-group").click(function(e) {
                        e.preventDefault();
                        
                    })
                } 
                showInfo(data['user']);
            }
    });
}

function showInfo(user) {
    $("#name").html("<h4>" + user["firstName"] + " " + user["middleName"] + " " + 
                            user["lastName"] +  "</h4>");
    $("#places-lived").html(user["locations"]);
    $("#dob").html(new Date(user["dob"] * 1000).toLocaleDateString());
    $("#languages").html(user["languages"]);
    $("#gender").html(user["gender"]);
    $("#email").html(user["email"]);
    $("#about").html(user["about"].replace(/\n/g, "<br/>"))
}
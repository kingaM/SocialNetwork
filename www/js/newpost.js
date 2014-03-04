var username = 'user';
var blog = 'blog';

function content() {
    username = window.location.pathname.split( '/' )[2];
    blog = window.location.pathname.split( '/' )[4];
    $('#summernote').summernote({ 
        height: 300,   //set editable area's height
        focus: true,    //set focus editable area after Initialize summernote
    });

    $("#submit-btn").click(function(e) {
        e.preventDefault();
        console.log($("#summernote").code());
        var values = {};
        values["content"] = $("#summernote").code();
        values["title"] = $("#post-title").val();
        if(!values["title"]) {
            $("#form-group-title").addClass("has-error");
            $("#control-label-title").show();
            $("#control-label-title").text("The title cannot be blank");
        } else {
            $.ajax({
                type: "post",
                url: "/api/user/" + username + "/blogs/" + 
                    blog + "/newPost",
                data: values,
                success: function(data) {
                    console.log(data);
                    var json = $.parseJSON(data);
                    var valid = json['valid'];    
                    if(!valid) {
                        showError("error-unknown", "Something went wrong, but we don't know what." +
                            "Please try again later.");
                    } else {
                        window.location.replace("/user/" + username + "/blogs/" + 
                            blog + "/pages/1"); 
                    }
                }
            });
        }
    });

    $("#cancel-btn").click(function(e) {
        e.preventDefault();
        window.location.replace("/user/" + username + "/blogs/" + 
            blog + "/pages/1");
    });
}
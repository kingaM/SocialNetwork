function content() {
    setSubmitBtn();
}

function setSubmitBtn() {
    $("#submit-btn").click(function(e) {
        e.preventDefault();
        clearErrors();
        var password = $("#password").val();
        var passwordRetype = $("#password-retype").val();
        var email = $("#email").val();
        var emailRetype = $("#email-retype").val();
        var same = true;

        if(password !== passwordRetype) {
            $("#form-group-password-retype").addClass("has-error");
            $("#password-retype-error").show();
            $("#password-retype-error").html("The passwords do not match.");
        }

        if(email !== emailRetype) {
            $("#form-group-email-retype").addClass("has-error");
            $("#email-retype-error").show();
            $("#email-retype-error").html("The emails do not match.");
        }

        if (password !== passwordRetype || email !== emailRetype) {
            return;
        }

        values = {};
        values["firstname"] = $("#firstname").val();
        values["middlename"] = $("#middlename").val();
        values["lastname"] = $("#lastname").val();
        values["username"] = $("#username").val();
        values["password"] = $("#password").val();
        values["email"] = $("#email").val();

        $.ajax({
            type: "post",
            url: "/api/register",
            data: values,
            success: function(data) {
                console.log(data);
                var json = $.parseJSON(data);
                if(!json['valid']) {
                    for (var key in json['errors']) {
                        if (json['errors'].hasOwnProperty(key)) {
                            if(key == "email-valid") {
                                $("#form-group-email").addClass("has-error");
                                $("#email-error").show();
                                $("#email-error").html("The e-mail address is not valid.");
                            } 
                            if (key == "email-unique") {
                                $("#form-group-email").addClass("has-error");
                                $("#email-error").show();
                                $("#email-error").html("The e-mail address is not unique.");
                            }
                            if (key == "username-valid") {
                                $("#form-group-username").addClass("has-error");
                                $("#username-error").show();
                                $("#username-error").html("The username address is not valid.");
                            }
                            if (key == "username-unique") {
                                $("#form-group-username").addClass("has-error");
                                $("#username-error").show();
                                $("#username-error").html("The username address is not unique.");
                            }
                        }
                    }
                } else if(!json['suceeded']) {
                    showError();
                } else {
                    showSuccess();
                }
            }
        });
    });
}

function clearErrors() {
    $("#form-group-username").removeClass("has-error");
    $("#form-group-password").removeClass("has-error");
    $("#form-group-password-retype").removeClass("has-error");
    $("#form-group-email").removeClass("has-error");
    $("#form-group-email-retype").removeClass("has-error");
    $("#username-error").hide();
    $("#password-error").hide();
    $("#password-retype-error").hide();
    $("#email-error").hide();
    $("#email-retype-error").hide();

}

function showError() {
    $("#error-unknown").html("<div class=\"alert alert-danger alert-dismissable\">" +
        "<button type=\"button\" class=\"close\" data-dismiss=\"alert\"" +
        "aria-hidden=\"true\">&times;</button>" +
        "<strong>Error:</strong> Something went wrong, but we don't know what." +
        "Please try again later." + 
        "</div>");
}

function showSuccess() {
    $("#error-unknown").html("<div class=\"alert alert-success alert-dismissable\">" +
        "<button type=\"button\" class=\"close\" data-dismiss=\"alert\"" +
        "aria-hidden=\"true\">&times;</button>" +
        "<strong>Success: </strong>" + 
        "We have sent you a confirmation e-mail. In the e-mail there is a link to validate the " +
        "e-mail address. To finish registration please go to the link provided." +
        "</div>");
}
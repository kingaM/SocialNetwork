var username = '';
var email = '';

function content() {
    getUserInfo();
    setSubmitBtns();
    setEditBtns();
    setCancelBtns();
}

function setEditBtns() {
    $("#username-edit-btn").click(function(e) {
        e.preventDefault();
        hideAndClearAll();
        $("#username-edit").show();
        $("#username").hide();
        $("#username-submit-btn").show();
        $("#username-edit-btn").hide();
        $("#new-username").val(username);
    });
    $("#password-edit-btn").click(function(e) {
        e.preventDefault();
        hideAndClearAll();
        $("#password-edit").show();
        $("#password").hide();
        $("#password-submit-btn").show();
        $("#password-edit-btn").hide();
    });
    $("#email-edit-btn").click(function(e) {
        e.preventDefault();
        hideAndClearAll();
        $("#new-email").val(email);
        $("#email-edit").show();
        $("#email").hide();
        $("#email-submit-btn").show();
        $("#email-edit-btn").hide();
    });
}

function setSubmitBtns() {
    $("#username-submit-btn .custom-submit-btn").click(function(e) {
        hideAllErrors();
        e.preventDefault();
        var values = {};
        values['username'] = $("#new-username").val();
        values['password'] = $("#password-username").val();
        $.ajax({
            type: "post",
            url: "/api/settings/username",
            data: values,
            success: function(data) {
                console.log(data);
                hideAllErrors();
                var json = $.parseJSON(data);
                var valid = json['valid'];
                if(valid && json['succeded']) {
                    hideAndClearAll();
                    getUserInfo();
                } else if (valid && !json['succeded']) {
                    showError();
                } else {
                    if (!json['password']) {
                        $("#password-username-error").show();
                        $("#password-username-error").html("The password you have entered is " +
                            "inccorect.");
                        $("#form-group-username-password").addClass("has-error");
                    }
                    // Should not happen
                    if (!json['unique'] && !json['alphaNum']) {
                        showError();
                    }
                    if (!json['unique']) {
                        $("#new-username-error").show();
                        $("#new-username-error").html("The username you have entered is " +
                            "already taken.");
                        $("#form-group-username-username").addClass("has-error");
                    }
                    if (!json['alphaNum']) {
                        $("#new-username-error").show();
                        $("#new-username-error").html("The username you have entered is " +
                            "not alphanumeric.");
                        $("#form-group-username-username").addClass("has-error");
                    }
                }
            }
        }); 
    });
    $("#password-submit-btn .custom-submit-btn").click(function(e) {
        hideAllErrors();
        e.preventDefault();
        if ($("#new-password").val() !== $("#new-password-retype").val()) {
            $("#new-password-retype-error").show();
            $("#new-password-retype-error").html("The passwords you have entered " +
                "do not match.");
            $("#form-group-password-retype").addClass("has-error");
            return;
        }
        var values = {};
        values['newPassword'] = $("#new-password").val();
        values['password'] = $("#password-password").val();
        $.ajax({
            type: "post",
            url: "/api/settings/password",
            data: values,
            success: function(data) {
                console.log(data);
                hideAllErrors();
                var json = $.parseJSON(data);
                var valid = json['valid'];
                if(valid) {
                    hideAndClearAll();
                    getUserInfo();
                } else if (!valid && !json['password']) {
                    $("#password-password-error").show();
                    $("#password-password-error").html("The password is incorrect.");
                    $("#form-group-password-current").addClass("has-error");
                } else {
                    showError();
                }
            }
        }); 
    });
    $("#email-submit-btn .custom-submit-btn").click(function(e) {
        hideAllErrors();
        e.preventDefault();
        if ($("#new-email").val() !== $("#new-email-retype").val()) {
            $("#new-email-retype-error").show();
            $("#new-email-retype-error").html("The e-mails you have entered " +
                "do not match.");
            $("#form-group-email-retype").addClass("has-error");
            return;
        }
        var values = {};
        values['email'] = $("#new-email").val();
        values['password'] = $("#password-email").val();
        $.ajax({
            type: "post",
            url: "/api/settings/email",
            data: values,
            success: function(data) {
                console.log(data);
                var json = $.parseJSON(data);
                var valid = json['valid'];
                var validPassword = json['password'];
                hideAllErrors();
                if(valid && json['succeded']) {
                    hideAndClearAll();
                    getUserInfo();
                } else if (valid && !json['succeded']) {
                    showError();
                } else {
                    if (!json['password']) {
                        $("#password-email-error").show();
                        $("#password-email-error").html("The password you have entered is " +
                            "incorrect.");
                        $("#form-group-email-password").addClass("has-error");
                    }
                    // Should not happen
                    if (!json['unique'] && !json['validEmail']) {
                        showError();
                    }
                    if (!json['unique']) {
                        $("#new-email-error").show();
                        $("#new-email-error").html("The e-mail you have entered is " +
                            "already taken.");
                        $("#form-group-new-email").addClass("has-error");
                    }
                    if (!json['validEmail']) {
                        $("#new-email-error").show();
                        $("#new-email-error").html("The e-mail you have entered is " +
                            "not valid.");
                        $("#form-group-new-email").addClass("has-error");
                    }
                }
            }
        }); 
    });
}

function setCancelBtns() {
    $(".custom-cancel-btn").click(function(e) {
        e.preventDefault();
        hideAndClearAll();
    });
}

function getUserInfo() {
    $.getJSON( "/api/user/" + "-1" + "/profile", 
        function(data) {
            if(!data['valid']) {
                showError();
            } else {
                showInfo(data['user']);
            }
        }
    );
}

function showInfo(user) {
    username = user['username'];
    email = user['email'];
    $("#username").html("<h4>" + user['username'] + "</h4>");
    $("#email").html("<h4>" + user['email'] + "</h4>");
}

function hideAndClearAll() {
    // Hide all "edit" columns
    $("#username-edit").hide();
    $("#password-edit").hide();
    $("#email-edit").hide();
    // Show all "info" columns
    $("#username").show();
    $("#password").show();
    $("#email").show();
    // Clear all the input values
    $("#new-username").val("");
    $("#password-username").val("");
    $("#password-password").val("");
    $("#new-password").val("");
    $("#new-password-retype").val("");
    $("#new-email").val("");
    $("#new-email-retype").val("");
    $("#password-email").val("");
    // Change all buttons to "edit"
    $("#username-submit-btn").hide();
    $("#username-edit-btn").show();
    $("#email-submit-btn").hide();
    $("#email-edit-btn").show();
    $("#password-submit-btn").hide();
    $("#password-edit-btn").show();
    hideAllErrors();
}

function hideAllErrors() {
    // Clear all the error messages
    $("#new-username-error").hide();
    $("#password-username-error").hide();
    $("#password-password-error").hide();
    $("#new-password-retype-error").hide();
    $("#new-email-error").hide();
    $("#new-email-retype-error").hide();
    $("#password-email-error").hide();
    // Remove all error classes
    $("#form-group-username-username").removeClass("has-error");
    $("#form-group-username-password").removeClass("has-error");
    $("#form-group-password-current").removeClass("has-error");
    $("#form-group-password-retype").removeClass("has-error");
    $("#form-group-new-email").removeClass("has-error");
    $("#form-group-email-retype").removeClass("has-error");
    $("#form-group-email-password").removeClass("has-error");
}

function showError() {
    $("#error-unknown").html("<div class=\"alert alert-danger alert-dismissable\">" +
        "<button type=\"button\" class=\"close\" data-dismiss=\"alert\"" +
        "aria-hidden=\"true\">&times;</button>" +
        "<strong>Error:</strong> Something went wrong, but we don't know what." +
        "Please try again later." + 
        "</div>");
}
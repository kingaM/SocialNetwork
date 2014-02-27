var username = '';
var email = '';

function content() {
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
    getUserInfo();
    setSubmitBtns();
}

function setSubmitBtns() {
    $("#username-submit-btn").click(function(e) {
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
                var json = $.parseJSON(data);
                var valid = json['valid'];
                var validPassword = json['password'];
                if(valid && validPassword) {
                    hideAndClearAll();
                    getUserInfo();
                } else {
                    showError();
                }
            }
        }); 
    });
    $("#password-submit-btn").click(function(e) {
        e.preventDefault();
        if ($("#new-password").val() !== $("#new-password-retype").val()) {
            showError();
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
                var json = $.parseJSON(data);
                var valid = json['valid'];
                if(valid) {
                    hideAndClearAll();
                    getUserInfo();
                } else {
                    showError();
                }
            }
        }); 
    });
    $("#email-submit-btn").click(function(e) {
        e.preventDefault();
        if ($("#new-email").val() !== $("#new-email-retype").val()) {
            showError();
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
                if(valid) {
                    hideAndClearAll();
                    getUserInfo();
                } else {
                    showError();
                }
            }
        }); 
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
}

function showError() {
    $("#error-unknown").html("<div class=\"alert alert-danger alert-dismissable\">" +
        "<button type=\"button\" class=\"close\" data-dismiss=\"alert\"" +
        "aria-hidden=\"true\">&times;</button>" +
        "<strong>Error:</strong> Something went wrong, but we don't know what." +
        "Please try again later." + 
        "</div>");
}
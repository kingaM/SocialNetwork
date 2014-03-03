var g_data = null;
var files = null;

function content() {
    var pathArray = window.location.pathname.split( '/' );
    $("#edit-btn-group").click(function(e) {
        e.preventDefault();
        if(g_data) {
            editInfo(g_data['user']);
        }    
    });
    $("#submit-btn").click(function(e) {
        e.preventDefault();
        submitInfo();                
    });

    $("#cancel-btn").click(function(e) {
        e.preventDefault();
        $("#submit-btn-group").hide();
        getUserInfo();              
    });
     
    // Add events
    $('input[type=file]').on('change', prepareUpload);
     
    // Grab the files and set them to our variable
    function prepareUpload(event)
    {
      files = event.target.files;
    }

    $('#image-form').on('submit', uploadFiles);

    getUserInfo();
}

function uploadFiles(event)
{
    event.stopPropagation(); 
    event.preventDefault(); 

    var data = new FormData();
    $.each(files, function(key, value)
    {
        console.log(key + " " + value);
        data.append(key, value);
    });
    
    $.ajax({
        url: '/api/user/profile/image',
        type: 'POST',
        data: data,
        cache: false,
        dataType: 'json',
        processData: false, 
        contentType: false, 
        success: function(data, textStatus, jqXHR)
        {
            console.log(data);
        },
        error: function(jqXHR, textStatus, errorThrown)
        {
            console.log('ERRORS: ' + textStatus);
        }
    });
}

function showError() {
    $("#error-unknown").html("<div class=\"alert alert-danger alert-dismissable\">" +
        "<button type=\"button\" class=\"close\" data-dismiss=\"alert\"" +
        "aria-hidden=\"true\">&times;</button>" +
        "<strong>Error:</strong> Something went wrong, but we don't know what." +
        "Please try again later." + 
        "</div>");
}

function getUserInfo() {
    $.getJSON( "/api/user/" + window.location.pathname.split( '/' )[2] + "/profile", 
        function(data) {
            if(!data['valid']) {
                showError();
            } else {
                if(data['currentUser']) {
                    g_data = data;
                    $("#edit-btn-group").show();
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
    $("#about").html(user["about"].replace(/\n/g, "<br/>"));
}

function editInfo(user) {
    $("#name").html("<div class=\"col-md-4 nopadding\"><input type=\"text\" class=\"form-control\""
        + "value=\"" + user["firstName"] +  "\" id=\"first-name-txt\" ></div>" +
        "<div class=\"col-md-4 nopadding\"><input type=\"text\" class=\"form-control\"" 
        + "placeholder=\"Middle Name\" value=\"" + user["middleName"] +  
        "\" id=\"middle-name-txt\"></div>" + 
        "<div class=\"col-md-4 nopadding\"><input type=\"text\" class=\"form-control\" value=\"" 
        + user["lastName"] +  "\" id=\"last-name-txt\" ></div>");
    $("#places-lived").html("<input type=\"text\" class=\"form-control\" value=\"" + 
        user["locations"] +  "\" id=\"locations-txt\" >");
    $("#dob").html("<input type=\"text\" class=\"span2 form-control\" id=\"dob-txt\"" + 
        "format=\"dd-mm-yyyy\" data-date=\"\">");
    $('#dob-txt').datepicker();
    $('#dob-txt').datepicker('setValue', new Date(user["dob"] * 1000));
    $("#languages").html("<input type=\"text\" class=\"form-control\" value=\"" + 
        user["locations"] +  "\" id=\"languages-txt\" >");
    $("#gender").html("<select class=\"form-control\" value=\"" + 
        user["gender"] +  "\" id=\"gender-txt\" >" +
        "<option value=\"\">N/A</option>" +
        "<option value=\"Female\">Female</option>" +
        "<option value=\"Male\">Male</option>");
    $("#gender-txt option:contains(\"" + user["gender"] +"\")").prop("selected", true);
    $("#email").html(user["email"]);
    $("#about").html("<textarea rows=\"3\" class=\"form-control\" id=\"about-txt\" >" + 
        user["about"] +  "</textarea >");

    $("#edit-btn-group").hide();
    $("#submit-btn-group").show();
}

function submitInfo() {
    var firstName = $("#first-name-txt").val();
    var middleName = $("#middle-name-txt").val();
    var lastName =  $("#last-name-txt").val();
    var locations = $("#locations-txt").val();
    var dob = $("#dob-txt").val();
    var languages = $("#languages-txt").val();
    var gender = $("#gender-txt").val();
    var about = $("#about-txt").val();
    $("#submit-btn-group").hide();

    var values = {};
    values["firstName"] = firstName;
    values["middleName"] = middleName;
    values["lastName"] = lastName;
    values["locations"] = locations;
    values["dob"] = new Date(dob).getTime() / 1000;
    values["languages"] = languages;
    values["gender"] = gender;
    values["about"] = about;

    $.ajax({
        type: "post",
        url: "/api/user/" + window.location.pathname.split( '/' )[2] + "/profile",
        data: values,
        success: function(data) {
            var json = $.parseJSON(data);
            var valid = json['valid'];
            if(valid) {
                getUserInfo();
            } else {
                showError();
            }
        }
    }); 
}
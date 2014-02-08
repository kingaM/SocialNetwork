function content() {

}

$('#login_form').submit(function(e) {
    e.preventDefault();
    var $inputs = $('#login_form :input');
    var values = {};
    $inputs.each(function() {
        values[this.name] = $(this).val();
    });
    delete values[""];

    $.ajax({
    type: "post",
    url: "/api/login",
    data: values,
    success: function(response) {
               var valid = $.parseJSON(response)['valid'];
               if(valid) {
                    window.location.href = "./";
               } else {
                    $("#error").append("The username and/or password are not valid." +
                        " Please try again.");
                    $("#password").val("");
               }
               
            },
    });
});
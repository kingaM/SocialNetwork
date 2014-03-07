var username = 'username';
var files;
var currentAlbumId;

function content() {
    username = window.location.pathname.split( '/' )[2];
    getPhotoAlbums();
    setupDropdown();

    $('input[type=file]').on('change', function (event) {
        files = event.target.files;
    });

    $('#upload-btn').click(uploadFiles);

    $('body').on('hidden.bs.modal', '.modal', function () {
        console.log("Hidden function executed");
        $("#image-description").val("");
        $('.fileinput').fileinput('clear');
    });
}

function uploadFiles(event) {
    event.stopPropagation(); 
    event.preventDefault(); 

    var data = new FormData();
    $.each(files, function(key, value) {
        data.append(key, value);
    });

    data.append("description", $("#image-description").val());
    
    $.ajax({
        url: '/api/user/' + window.location.pathname.split( '/' )[2] + '/photos/' + 
            currentAlbumId,
        type: 'POST',
        data: data,
        cache: false,
        processData: false, 
        contentType: false, 
        success: function(data) {
            console.log(data);
            var json = $.parseJSON(data);
            if(!json['valid']) {
                showError("error-unknown", "Something went wrong, but we don't know what." +
                    "Please try again later.");
            } else if(json['image_error']) {
                showError("error-unknown", "Something went wrong, but we don't know what." +
                    "Please try again later.");
               // $("#control-label-image").show();
            } else {
                $('#myModal').modal('hide');
                loadPhotos(currentAlbumId);
            }
            
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("ERROR: " + textStatus);
            showError("error-unknown", "Something went wrong, but we don't know what." +
                    "Please try again later.");
        }
    });
}

function setupDropdown() {
     $('.dropdown-menu').find('form').click(function (e) {
        e.stopPropagation();
    });
    $('#new-album-form').submit(function(e) {
        e.preventDefault();
        e.stopPropagation();
        var albumAbout = $("#new-album").val();
        var name = $("#name").val();
        clearLabels();
        addAlbum(name, albumAbout);
    });
    $('body').click(function(e) {
        clearDropdown();
    });
}

function addAlbum(name, text) {
    var values = {};
    values["text"] = text;
    values["name"] = name;
    console.log("Adding Album");
    $.ajax({
        type: "post",
        url: "/api/user/" + username + "/photos",
        data: values,
        success: function(data) {
            console.log(data);
            var json = $.parseJSON(data);
            var valid = json['valid'];
            if (!valid) {
                showError("error-unknown", "Something went wrong, but we don't know what." +
                    "Please try again later.");
                return;
            }  
            if(valid) {
                clearDropdown();
                getPhotoAlbums();
            }
        }
    });
}

function clearDropdown() {
    $("#new-album").val("");
    $("#name").val("");
    clearLabels();
    $('[data-toggle="dropdown"]').parent().removeClass('open');
}

function clearLabels() {
    $("#form-group-name").removeClass("has-error");
    $("#control-label-name").hide();
}

function showErrorDropdown(id, msg) {
    $("#form-group-" + id).addClass("has-error");
    $("#control-label-" + id).show();
    $("#control-label-" + id).text(msg);
}

function getPhotoAlbums() {
    $.getJSON( "/api/user/" + username + "/photos", 
        function(data) {
            if(!data['valid']) {
                showError("error-unknown", "Something went wrong, but we don't know what." +
                    "Please try again later.");
            } else {
                if(data['currentUser']) {
                    $("#edit-btn-group").show();
                } 
                console.log(data);
                showPhotoAlbums(data['albums']);
            }
    });
}

function showPhotoAlbums(albums) {
    $("#photo-collections-list").empty();
    var i;
    for (i = 0; i < albums.length; ++i) {
        var album = albums[i];
        showPhotoAlbum(album, i);
    }
    $('.collapse').on('show.bs.collapse', function() {
        var id = $(this).attr('id');
        console.log(id);
        loadPhotos(id);
        $('a[href="#' + id + '"]').closest('.panel-heading').addClass('active-faq');
        $('a[href="#' + id + '"] .panel-title span').html('<i class="glyphicon glyphicon-minus"></i>');
    });
    $('.collapse').on('hide.bs.collapse', function() {
        var id = $(this).attr('id');
        console.log(id);
        $('a[href="#' + id + '"]').closest('.panel-heading').removeClass('active-faq');
        $('a[href="#' + id + '"] .panel-title span').html('<i class="glyphicon glyphicon-plus"></i>');
    });
}

function loadPhotos(id) {
    $.getJSON( "/api/user/" + username + "/photos/" + id, 
        function(data) {
            if(!data['valid']) {
                showError("error-unknown", "Something went wrong, but we don't know what." +
                    "Please try again later.");
            } else {
                console.log(data);
                showPhotos(id, data['photos']);
            }
    });
}

function showPhotos(id, photos) {
    var html = '<div id="links-' + id + '">' + '</div>';
    $("#" + id).html(html);
    for (i = 0; i < photos.length; ++i) {
        var photo = photos[i];
        showPhoto(photo, "links-" + id);
    }
}

function showPhoto(photo, id) {
    var html = '<a href="' + photo["url"] + '" title="' + 
        (photo["description"] ? photo["description"] : "") + '" data-gallery>' +
    '<img src="' + photo["thumbnailUrl"] + '" height="200" width="200">' +
        '</a>';
    $("#" + id).append(html);
}

function showPhotoAlbum(album) {
    var html = 
            '<div class="panel panel-default panel-faq">' +
              '<div class="panel-heading">' +
                '<a class="nohover" data-toggle="collapse" data-parent="#accordion-cat-' + 
                    album['id'] + '" href="#' + album['id'] + '">' +
                '<h4 class="panel-title">' + album['name'] +
                 '<br>' +
                '<small>' + album['about'] + ' </small>' +
                '<span class="pull-right">' +'<i class="glyphicon glyphicon-plus">' +'</i>' 
                    +'</span>' +
                    '<button class="btn btn-primary" id="upload-' + album['id'] + '">' + 
                    'Upload' + '</button>' +
                '</h4>' + 
                '</a>' +
              '</div>' +
              '<div id="' + album['id'] + 
                    '" class="panel-collapse collapse">' +
                '<div class="panel-body">' +

                '</div>' +
              '</div>' +
        '</div>';

    $("#photo-collections-list").append(html);
    $("#upload-" + album['id']).click(function (e) {
        e.preventDefault();
        $('#myModal').modal('show');
        currentAlbumId = album['id'];
    });
}
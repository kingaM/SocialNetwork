var username = 'username';
var files;
var currentAlbumId;
var gPhotos = [];
var photosIndexes = {};
var currentUser = false;

function content() {
    username = window.location.pathname.split( '/' )[2];
    addPrivacySelector("privacy-selector", "3");
    getPhotoAlbums();
    setupDropdown();

    $('input[type=file]').on('change', function (event) {
        files = event.target.files;
    });

    $('#upload-btn').click(uploadFiles);

    $('body').on('hidden.bs.modal', '.modal', function () {
        $("#image-description").val("");
        $('.fileinput').fileinput('clear');
    });
}

/** The functions below are grouped into several sub-groups for ease of readability. Those subgroups
 *  are:
 * 1. Show Albums - shows an accordion with all the photo albums of the user, whose profile it is. 
 *    It also shows the relevant actions a user can do on the album (i.e. if this is the profile of
 *    the user currently logged in, he/she whould be able to delete and add pictures to the profile
 *    unlike other users, who can just view)
 * 2. New Album - shows a modal (popup) with a form needed to add an album and sends the 
 *    approperiate requests to the servers and handles errors and responses relevant to adding new 
 *    album.
 * 3. Delete Album - Deletes an album. Sends a 'delete' request to the server and handles the 
 *    response.
 * 4. Show photo - once a user clicks on a particular photo album, the url of photos for that photo 
 *    album are loaded from the server and a thumbnail is shown to the user. 
 * 5. Delete Photos - deletes a photo from a particular album. 
 * 6. New Photo - a modal (popup) is shown, with which the user can upload a photo from their 
 *    computer. This photo is then sent to the server, and as the response comes through, either an 
 *    error message appears, or the photo is added to the list, and the photos are re-loaded, to 
 *    match the new item.
 * 7. Enlarged Photo - a user can click on a photo thumbnail to enlarge it. This shows a modal with 
 *    a bigger version of the photo as well as comments. In this view the user can add comments to 
 *    the photo.
 * 8. Comments - functions that are relevant for making the comments visible and editable. Those 
 *    are shown in the enlarged photo modal.
 **/


// Show Albums - shows an accordion with all the photo albums of the user, whose profile it is

function getPhotoAlbums() {
    $.getJSON( "/api/user/" + username + "/photos", 
        function(data) {
            if(!data['valid']) {
                showError("error-unknown", "Something went wrong, but we don't know what." +
                    "Please try again later.");
            } else {
                if(data['currentUser']) {
                    $("#edit-btn-group").show();
                    currentUser = data['currentUser'];
                } 
                showPhotoAlbums(data['albums']);
            }
    });
}

function showPhotoAlbums(albums) {
    $("#accordion").empty();
    var i;
    for (i = 0; i < albums.length; ++i) {
        var album = albums[i];
        showPhotoAlbum(album, i);
    }
    $('.collapse').on('show.bs.collapse', function() {
        var id = $(this).attr('id');
        currentAlbumId = id;
        loadPhotos(id);
        $('#icon-' + id).html('<i class="glyphicon glyphicon-minus"></i>');
    });
    $('.collapse').on('hide.bs.collapse', function() {
        var id = $(this).attr('id');
        $('#icon-' + id).html('<i class="glyphicon glyphicon-plus"></i>');
        $("#" + id).empty();
    });
}

function showPhotoAlbum(album) {
    var buttons = '<div class="col-lg-6 col-md-8">' +
                    '<form class="form-inline pull-right" role="form">' +
                        '<div class="form-group">' +
                            '<button class="btn btn-primary" id="upload-' + album['id'] + '"' + 
                                'style="margin-right: 5px;">' + 
                                'Add Photo to Album' + '</button>' +
                        '</div>' +
                        '<div class="form-group">' +
                            '<button class="btn btn-danger" id="delete-' + album['id'] + '">' + 
                                'Delete Album' + '</button>' +
                        '</div>' +
                    '</form>' +
                  '</div>';
    if(!currentUser) {
        buttons = '';
    }
    var html = '<div class="panel panel-default">' +
              '<div class="panel-heading">' +
                '<a class="nohover" data-toggle="collapse" data-parent="#accordion" href="#' + 
                    album['id'] + '">' +
                    '<div class="flex">' +
                    '<div class="col-lg-' + (currentUser ? '6' : '11') + 
                        ' col-md-' + (currentUser ? '4' : '11') +'">' +
                '<h4 class="panel-title">' + album['name'] +
                 '<br>' + 
                '<small>' + album['about'] + ' </small></h4></div>' +
                  buttons +
                '<div class="col-lg-1 col-md-1" style="text-align:left;">' +
                    '<span class="pull-right" id="icon-' + album['id'] + '">' +
                    '<i class="glyphicon glyphicon-plus">' +'</i>' 
                    +'</span>' +
                    '</div>' +
                '</div>' +
                '</div>' +
                '</a>' +
              '<div id="' + album['id'] + 
                    '" class="panel-collapse collapse">' +
                '<div class="panel-body">' +

                '</div>' +
              '</div>' +
        '</div>';

    $("#accordion").append(html);
    $("#upload-" + album['id']).click(function (e) {
        e.preventDefault();
        e.stopPropagation();
        $('#myModal').modal('show');
        currentAlbumId = album['id'];
    });
    $("#delete-" + album['id']).click(function (e) {
        e.preventDefault();
        e.stopPropagation();
        deleteAlbum(album['id']);
    });
}

// New Album - shows a modal (popup) with a form needed to add an album and sends the approperiate
// requests to the servers and handles errors and responses relevant to adding new album.

function setupDropdown() {
     $('.dropdown-menu').find('form').click(function (e) {
        e.stopPropagation();
    });
    $('#new-album-form').submit(function(e) {
        e.preventDefault();
        e.stopPropagation();
        var albumAbout = $("#new-album").val();
        var name = $("#name").val();
        var privacy = $("#privacy-options-privacy-selector").val();
        clearLabels();
        addAlbum(name, albumAbout, privacy);
    });
    $('body').click(function(e) {
        clearDropdown();
    });
}

function addAlbum(name, text, privacy) {
    var values = {};
    values["text"] = text;
    values["name"] = name;
    values["privacy"] = privacy;
    $.ajax({
        type: "post",
        url: "/api/user/" + username + "/photos",
        data: values,
        success: function(data) {
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

// Delete Album - Deletes an album. Sends a 'delete' request to the server and handles the response.

function deleteAlbum(id) {
    $.ajax({
        type: "delete",
        url: "/api/user/" + username + "/photos/" + id,
        success: function(data) {
            var json = $.parseJSON(data);
            var valid = json['valid'];
            if (!valid) {
                showError("error-unknown", "Something went wrong, but we don't know what." +
                    "Please try again later.");
                return;
            }  
            if(valid) {
                getPhotoAlbums();
            }
        }
    });
}

// Show photo -- once a user clicks on a particular photo album, the url of photos for that photo 
// album are loaded from the server and a thumbnail is shown to the user. 

function loadPhotos(id) {
    $.getJSON( "/api/user/" + username + "/photos/" + id, 
        function(data) {
            if(!data['valid']) {
                showError("error-unknown", "Something went wrong, but we don't know what." +
                    "Please try again later.");
            } else {
                showPhotos(id, data['photos']);
            }
    });
}

function showPhotos(id, photos) {
    gPhotos = photos;
    var html = '<div id="links-' + id + '">' + '</div>';
    $("#" + id).html(html);
    for (i = 0; i < photos.length; ++i) {
        var photo = photos[i];
        photosIndexes[photo['id']] = i;
        showPhoto(photo, id);
    }
}

function showPhoto(photo, id) {
    var img = '<a href="' + photo["url"] + "?" + photo['id'] + '" title="' + 
        (photo["description"] ? photo["description"] : "") + '" id="p-' + photo['id'] +'">' +
    '<img src="' + photo["thumbnailUrl"] + '" class="img-responsive">' +
        '</a>';
    var caption = '<button class="btn btn-danger btn-sm" id="photo-btn-' + photo['id'] + '" >' + 
            '<i class="glyphicon glyphicon-remove"></i>' + 
        '</button>';
    var html = '<div class="col-lg-3 col-md-6"><div class="thumbnail" id="photo-' + photo['id'] + 
            '">' + 
      '<div class="caption">' + (currentUser ? caption : '') +
      '</div>' + img + '</div></div>';
    $("#links-" + id).append(html);
    $("#photo-btn-" + photo['id']).hide();
    $("#photo-btn-" + photo['id']).click(function (e) {
        e.preventDefault();
        deletePhoto(id, photo['id']);
    });
    $("#photo-" + photo['id']).hover(
        function() {
            $("#photo-btn-" + photo['id']).show();
        }, function() {
            $("#photo-btn-" + photo['id']).hide();
        }
    );
    $("#p-" + photo['id']).click(
        function(e) {
            e.preventDefault();
            showModal(photo['description'], photo['url'], photo['id'], 0);
        }
    );
}

// Delete Photos - deletes a photo from a particular album. 

function deletePhoto(albumId, photoId) {
    $.ajax({
        type: "delete",
        url: "/api/user/" + username + "/photos/" + albumId + "/" + photoId,
        success: function(data) {
            var json = $.parseJSON(data);
            var valid = json['valid'];
            if (!valid) {
                showError("error-unknown", "Something went wrong, but we don't know what." +
                    "Please try again later.");
                return;
            }  
            if(valid) {
                loadPhotos(albumId);
            }
        }
    });
}

// New Photo - a modal (popup) is shown, with which the user can upload a photo from their computer
// This photo is then sent to the server, and as the response comes through, either an error message
// appears, or the photo is added to the list, and the photos are re-loaded, to match the new item.

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
            var json = $.parseJSON(data);
            if(!json['valid']) {
                showError("error-unknown", "Something went wrong, but we don't know what." +
                    "Please try again later.");
            } else if(json['image_error']) {
                showError("error-unknown", "Something went wrong, but we don't know what." +
                    "Please try again later.");
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

// Enalarged Photo - a user can click on a photo thumbnail to enlarge it. This shows a modal with a
// bigger version of the photo as well as comments. In this view the user can add comments to the
// photo.

function showModal(title, pictureUrl, pictureId, index) {
  var html = '<div id="photo">' + 
      '<div class="modal fade" id="modal-pic">' + 
        '<div class="modal-dialog modal-lg">' + 
          '<div class="modal-content">' + 
            '<div class="modal-header" id="modal-header">' +
            '</div>' + 
            '<div class="modal-body next" id="modal-body">' +
                
            '</div>' + 
            '<div class="modal-footer">' + 
              '<button type="button" class="btn btn-primary pull-left prev" id="prev-pic">' + 
              '<i class="glyphicon glyphicon-chevron-left">' + '</i>' + 
              'Previous' +
              '</button>' + 
              '<button type="button" class="btn btn-primary next" id="next-pic">' + 
              'Next' +
              '<i class="glyphicon glyphicon-chevron-right">' + '</i>' + 
              '</button>' + 
            '</div>' +
          '</div>' + 
        '</div>' + 
      '</div>' + 
    '</div>';

    $("#modal-pic").remove();
    $("body").append(html);
    $("#modal-pic").modal();
    fillModal(title, pictureUrl, pictureId, photosIndexes[pictureId]);
}

function fillModal(title, pictureUrl, pictureId, index) {
    if(title == null) {
        title = '';
    }
    if(pictureUrl == null) {
        pictureUrl = '/uploads/profile_pics/default.png';
    }
    var header = '<button type="button" class="close" aria-hidden="true" data-dismiss="modal">' + 
            '×' + 
          '</button>' + 
          '<h4 class="modal-title">' + title + '</h4>';
    var body = '<img src="' + pictureUrl + '" class="img img-responsive center-block">' +
            '<div id="slides">' +
          '<div class="panel panel-default widget">' + 
            '<div class="panel-heading">' + 
              '<span class="glyphicon glyphicon-comment">' + '</span>' + 
              '<h3 class="panel-title">' + 
              ' Comments ' + '</h3>' + 
              '<span class="label label-info" id="num-comments">' + 
              '</span>' + 
              
              '<span style="float: right;">' + 
              '<button class="btn btn-success btn-xs" id="add-comment">' + 
              '<span class="glyphicon glyphicon-plus">' + '</span>' + 
              '</button>' + 
              '</span>' + 
            '</div>' + 
            '<div id="new-comment" hidden>' + 
                '<div id="error-comment"></div>' +
              '<textarea class="form-control send-post" rows="3" placeholder="Comment"' + 
                ' id="new-comment-txt" required>' + '</textarea>' + 
              '<button type="button" class="btn btn-danger btn-sm" title="Cancel"' +
                'id="comment-cancel">' + 
              '<span class="glyphicon glyphicon-remove">' + '</span>' +  'Cancel' +
              '</button>' + 
              '<button type="button" class="btn btn-success btn-sm" title="Submit"' +
                ' id="comment-submit">' + 
              '<span class="glyphicon glyphicon-ok">' + '</span>' +  'Submit' +
              '</button>' + 
            '</div>' + 
            '<div class="panel-body">' + 
              '<ul class="list-group" id="comments-list">' + 

              '</ul>' + 
            '</div>' + 
          '</div>' + 
        '</div>';

    $("#modal-header").html(header);
    $("#modal-body").html(body);
    loadComments(pictureId);
    $("#add-comment").click( function (e) {
        $("#new-comment").show();
        $("#new-comment-txt").focus();
    });
    $("#comment-cancel").click( function (e) {
        $("#new-comment").hide();
        $("#new-comment-txt").val("");
    });
    $("#next-pic").click( function (e) {
        nextPicture(index);
    });

    $("#prev-pic").click( function (e) {
        previousPicture(index);
    });
    $(document).keydown(function(e){
    if (e.keyCode == 37) { 
        previousPicture(index);
    } else if (e.keyCode == 39) {
        nextPicture(index);
    }
});
    $("#comment-submit").click(function (e) {
        e.preventDefault();
        addComment(currentAlbumId, pictureId);
    });
}

function previousPicture(index) {
    if(index - 1 < 0) {
        var nextIndex = gPhotos.length - 1;
    } else {
        var nextIndex = index - 1;
    }
    fillModal(gPhotos[nextIndex]['description'], gPhotos[nextIndex]['url'], 
        gPhotos[nextIndex]['id'], nextIndex);
}

function nextPicture(index) {
    if(index + 1 >= gPhotos.length) {
        var nextIndex = 0;
    } else {
        var nextIndex = index + 1;
    }
    fillModal(gPhotos[nextIndex]['description'], gPhotos[nextIndex]['url'], 
        gPhotos[nextIndex]['id'], nextIndex);
}

// Comments -- functions that are relevant for making the comments visible and editable. Those are
// shown in the enlarged photo modal.

function loadComments(id) {
    $.ajax({
        type: "get",
        url: "/api/user/" + username + "/photos/" + currentAlbumId + "/" + id,
        success: function(data) {
            var json = $.parseJSON(data);
            var valid = json['valid'];
            if (!valid) {
                showError("error-comment", "Something went wrong, but we don't know what." +
                    "Please try again later.");
                return;
            }
            if(valid) {
                $("#num-comments").html(json['comments'].length);
                showComments(json['comments']);
            }
        }
    });
}

function showComments(comments) {
    $("#comments-list").empty();
    for (i = 0; i < comments.length; ++i) {
        var comment = comments[i];
        showComment(comment);
    }
}

function showComment(comment) {
    if(comment['profilePicture'] == null) {
        var profilePicture = '/uploads/profile_pics/default.png';
    } else {
        var profilePicture = comment['profilePicture'];
    }
    var reported = comment['reported'];
    var id = comment['id'];
    var comment = '<li class="list-group-item">' + 
          '<div class="row">' + 
            '<div class="col-xs-3 col-md-1">' + 
                '<img src="' + profilePicture + '" class="img-responsive" alt="" />' + 
            '</div>' + 
            '<div class="col-xs-7 col-md-10">' + 
              '<div class="comment-text">' + 
                comment['content'] +
              '</div>' + 
              '<div class="mic-info">' + 
                'By:' + '<a href="/user/' + comment['username'] + '">' + comment['firstName'] + 
                    ' ' + comment['lastName'] + 
                    '</a> ' +  
                    new Date(comment['timestamp'] * 1000).toLocaleString() + 
              '</div>' + 
            '</div>' + 
            '<div class="col-xs-2 col-md-1">' + 
              '<button type="button" class="btn btn-primary btn-xs" title="Flag as inappropriate"' + 
                ' style = "float: right;" id="report-' + id + '">' + 
              '<span class="fa fa-flag">' + '</span>' + 
              '</button>' + 
            '</div>' + 
          '</div>' + 
        '</li>';

    $("#comments-list").append(comment);

    if(reported == 1) {
        $("#report-" + id).fadeTo("fast", .5);
    } else {
        $("#report-" + id).click(function (e) {
            e.preventDefault();
            reportComment(id);
        })
    }
}

function addComment(albumId, pictureId) {
    var values = {};
    values['comment'] =  $("#new-comment-txt").val();
    $.ajax({
        type: "post",
        url: "/api/user/" + username + "/photos/" + albumId + "/" + pictureId,
        data: values,
        success: function(data) {
            var json = $.parseJSON(data);
            var valid = json['valid'];
            if (!valid) {
                if(json['emptyComment']) {
                    showError("error-comment", "You cannot submit an empty comment.");
                } else {
                    showError("error-comment", "Something went wrong, but we don't know what." +
                        "Please try again later.");
                }
                return;
            }  
            if(valid) {
                loadComments(pictureId);
                $("#new-comment").hide();
                $("#new-comment-txt").val("");
            }
        }
    });
}

function reportComment(id) {
    $.ajax({
        url: "/api/comments/" + id + "/report",
        type: "POST",
        data: {},

        success: function(response) {
            $("#report-" + id).fadeTo("fast", .5);
            $("#report-" + id).unbind('click');
        }
    });
}

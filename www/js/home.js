function content() {
    prepare();
}

var newsItemTemplate;

function prepare() {
    $.ajaxSetup({async:false});
    $.get('/views/home_newsItem.mustache', function(template) {
        newsItemTemplate = template;
    });
    $.ajaxSetup({async:true});
}

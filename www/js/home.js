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
    
    var view = {
        imgURL: "http://i.imgur.com/r8R1C6B.png",
        date: "Test",
        time: "Test",
        tag: "Test",
        titleLink: "Test",
        title: "Test",
        text: "Test"
    };

    var output = Mustache.render(newsItemTemplate, view);
    for (var i = 0; i < 10; i++) {
         $("#newsItems").append(output);
    };
}

<?php
	error_reporting(E_ALL);
    ob_start();
	ini_set('display_errors', 1);
	
    session_start();

    $public_pages = '#^(/api)?(/login|/register)$|^/activate/#';
    $only_loggedout = '#^(/api)?(/login|/register)$|^/activate/#';

    // Don't let unlogged users enter anything but the register and login pages. (And api methods)
    if((!isset($_SESSION['id']) && !preg_match($public_pages, $_SERVER['REQUEST_URI'])) ||
        (isset($_SESSION['id']) && $_SESSION['id'] == -1)) {
        header('Location: /login');
        die();
    }

    // Don't let logged in users register or login again.
    if((isset($_SESSION['id']) && preg_match($only_loggedout, $_SERVER['REQUEST_URI'])) ||
        (isset($_SESSION['id']) && $_SESSION['id'] == -1)) {
        header('Location: /');
        die();
    }

    require_once(__DIR__ . '/libs/zaphpa.lib.php');

    $router = new Zaphpa_Router();

    // Home

    $router->addRoute(array(
        'path' => '/',
        'get' => array('Index', 'getPage'),
        'file' => 'controllers/index.php',
    ));

    // Helper methods

    $router->addRoute(array(
        'path' => '/api/currentUser',
        'get' => array('Users', 'getCurrentUser'),
        'file' => 'controllers/users.php',
    ));

    $router->addRoute(array(
        'path' => '/api/users/autocomplete/{name}',
        'get' => array('Users', 'autoComplete'),
        'file' => 'controllers/users.php',
    ));

    // Login, logout and register

    $router->addRoute(array(
        'path' => '/login',
        'get' => array('Register', 'getPage'),
        'file' => 'controllers/register.php',
    ));

    $router->addRoute(array(
        'path' => '/api/login',
        'post' => array('Login', 'verifyUser'),
        'file' => 'controllers/login.php',
    ));

    $router->addRoute(array(
        'path' => '/logout',
        'get' => array('Login', 'logout'),
        'file' => 'controllers/login.php',
    ));

    $router->addRoute(array(
        'path' => '/api/register',
        'post' => array('Register', 'addUser'),
        'file' => 'controllers/register.php',
    ));

    $router->addRoute(array(
        'path' => '/activate/{hash}',
        'get' => array('Register', 'activate'),
        'file' => 'controllers/register.php',
    ));

    // Profile

    $router->addRoute(array(
        'path' => '/user/{username}/profile',
        'handlers' => array(
            'username' => Zaphpa_Constants::PATTERN_ALPHA
        ),
        'get' => array('Profile', 'getProfile'),
        'file' => 'controllers/profile.php',
    ));

    $router->addRoute(array(
        'path' => 'api/user/{username}/profile',
        'handlers' => array(
            'username' => Zaphpa_Constants::PATTERN_ALPHA
        ),
        'get' => array('Profile', 'getProfileInfo'),
        'post' => array('Profile', 'editProfileInfo'),
        'file' => 'controllers/profile.php',
    ));

    $router->addRoute(array(
        'path' => 'api/user/{username}/profile/image',
        'handlers' => array(
            'username' => Zaphpa_Constants::PATTERN_ALPHA
        ),
        'get' => array('Profile', 'getPhoto'),
        'post' => array('Profile', 'savePhoto'),
        'file' => 'controllers/profile.php',
    ));

    // Settings
    
    $router->addRoute(array(
        'path' => '/settings',
        'get' => array('Settings', 'getSettings'),
        'file' => 'controllers/settings.php',
    ));

    $router->addRoute(array(
        'path' => 'api/settings/username',
        'post' => array('Settings', 'updateUsername'),
        'file' => 'controllers/settings.php',
    ));

    $router->addRoute(array(
        'path' => 'api/settings/password',
        'post' => array('Settings', 'updatePassword'),
        'file' => 'controllers/settings.php',
    ));

    $router->addRoute(array(
        'path' => 'api/settings/email',
        'post' => array('Settings', 'updateEmail'),
        'file' => 'controllers/settings.php',
    ));

     $router->addRoute(array(
        'path' => 'api/settings/profilePrivacy',
        'post' => array('Settings', 'updateProfilePrivacy'),
        'file' => 'controllers/settings.php',
    ));

    // Friends

    $router->addRoute(array(
        'path' => '/user/{username}/friends',
        'handlers' => array(
            'username' => Zaphpa_Constants::PATTERN_ALPHA
        ),
        'get' => array('Friends', 'getPage'),
        'file' => 'controllers/friends.php',
    ));

    $router->addRoute(array(
        'path' => '/api/user/{username}/friends',
        'handlers' => array(
            'username' => Zaphpa_Constants::PATTERN_ALPHA
        ),
        'get' => array('Friends', 'getFriends'),
        'post' => array('Friends', 'addFriend'),
        'file' => 'controllers/friends.php',
    ));

    $router->addRoute(array(
        'path' => '/api/user/{username}/friends',
        'handlers' => array(
            'username' => Zaphpa_Constants::PATTERN_ALPHA
        ),
        'get' => array('Friends', 'getFriends'),
        'post' => array('Friends', 'addFriend'),
        'file' => 'controllers/friends.php',
    ));

    $router->addRoute(array(
        'path' => '/api/user/{username}/friends/{login}',
        'handlers' => array(
            'username' => Zaphpa_Constants::PATTERN_ALPHA,
            'login' => Zaphpa_Constants::PATTERN_ALPHA
        ),
        'delete' => array('Friends', 'removeFriend'),
        'file' => 'controllers/friends.php',
    ));

    $router->addRoute(array(
        'path' => '/api/circles',
        'post' => array('Friends', 'addCircle'),
        'file' => 'controllers/friends.php',
    ));

    $router->addRoute(array(
        'path' => '/api/circles/{circleName}',
        'post' => array('Friends', 'addToCircle'),
        'delete' => array('Friends', 'deleteCircle'),
        'file' => 'controllers/friends.php',
    ));

    $router->addRoute(array(
        'path' => '/api/circles/{circleName}/{friendName}',
        'handlers' => array(
            'friendName' => Zaphpa_Constants::PATTERN_ALPHA
        ),
        'delete' => array('Friends', 'deleteFromCircle'),
        'file' => 'controllers/friends.php',
    ));

    // Messages

    $router->addRoute(array(
        'path' => '/messages',
        'get' => array('Messages', 'getPage'),
        'file' => 'controllers/messages.php',
    ));

    $router->addRoute(array(
        'path' => '/api/messages/reciepients',
        'get' => array('Messages', 'getReciepients'),
        'post' => array('Messages', 'searchReciepients'),
        'file' => 'controllers/messages.php',
    ));

    $router->addRoute(array(
        'path' => '/api/messages/user/{username}',
        'handlers' => array(
            'username' => Zaphpa_Constants::PATTERN_ALPHA
        ),
        'get' => array('Messages', 'getMessages'),
        'post' => array('Messages', 'addMessage'),
        'file' => 'controllers/messages.php',
    ));

    $router->addRoute(array(
        'path' => '/api/messages/circle/{circleName}',
        'handlers' => array(
            'circleName' => Zaphpa_Constants::PATTERN_ALPHA
        ),
        'post' => array('Messages', 'addCircleMessage'),
        'file' => 'controllers/messages.php',
    ));

    // Timeline

    $router->addRoute(array(
        'path' => '/api/newsfeed/',
        'get' => array('Timeline', 'getNewsFeed'),
        'file' => 'controllers/timeline.php',
    ));

    $router->addRoute(array(
        'path' => '/user/{username}/',
        'handlers' => array(
            'username' => Zaphpa_Constants::PATTERN_ALPHA
        ),
        'get' => array('Timeline', 'getPage'),
        'file' => 'controllers/timeline.php',
    ));

    $router->addRoute(array(
        'path' => '/api/user/{username}/',
        'handlers' => array(
            'username' => Zaphpa_Constants::PATTERN_ALPHA
        ),
        'get' => array('Timeline', 'getPosts'),
        'post' => array('Timeline', 'addPost'),
        'file' => 'controllers/timeline.php',
    ));

    $router->addRoute(array(
        'path' => '/api/user/{username}/posts/{postID}',
        'handlers' => array(
            'username' => Zaphpa_Constants::PATTERN_ALPHA,
            'postID' => Zaphpa_Constants::PATTERN_DIGIT
        ),
        'post' => array('Timeline', 'addComment'),
        'file' => 'controllers/timeline.php',
    ));

    // Blog
    
    $router->addRoute(array(
        'path' => '/user/{username}/blogs',
        'handlers' => array(
            'username' => Zaphpa_Constants::PATTERN_ALPHA
        ),
        'get' => array('Blog', 'getBlogs'),
        'file' => 'controllers/blog.php',
    ));

    $router->addRoute(array(
        'path' => '/user/{username}/blogs/{blogName}/pages/{page}',
        'handlers' => array(
            'page' => Zaphpa_Constants::PATTERN_DIGIT,
            'username' => Zaphpa_Constants::PATTERN_ALPHA,
            'blogName' =>Zaphpa_Constants::PATTERN_ALPHA 
        ),
        'get' => array('Blog', 'getBlogPosts'),
        'file' => 'controllers/blog.php',
    ));

    $router->addRoute(array(
        'path' => '/user/{username}/blogs/{blogName}/newPost',
        'handlers' => array(
            'username' => Zaphpa_Constants::PATTERN_ALPHA,
            'blogName' =>Zaphpa_Constants::PATTERN_ALPHA 
        ),
        'get' => array('Blog', 'getNewPostPage'),
        'file' => 'controllers/blog.php',
    ));

    $router->addRoute(array(
        'path' => '/api/user/{username}/blogs/{blogName}/info',
        'handlers' => array(
            'username' => Zaphpa_Constants::PATTERN_ALPHA,
            'blogName' =>Zaphpa_Constants::PATTERN_ALPHA 
        ),
        'get' => array('Blog', 'apiBlogInfo'),
        'file' => 'controllers/blog.php',
    ));

    $router->addRoute(array(
        'path' => '/api/user/{username}/blogs/{blogName}/postsNum',
        'handlers' => array(
            'username' => Zaphpa_Constants::PATTERN_ALPHA,
            'blogName' =>Zaphpa_Constants::PATTERN_ALPHA 
        ),
        'get' => array('Blog', 'apiPostsNumber'),
        'file' => 'controllers/blog.php',
    ));

    $router->addRoute(array(
        'path' => '/api/user/{username}/blogs',
        'handlers' => array(
            'username' => Zaphpa_Constants::PATTERN_ALPHA
        ),
        'get' => array('Blog', 'apiUserBlogs'),
        'post' => array('Blog', 'addNewBlog'),
        'file' => 'controllers/blog.php',
    ));

    $router->addRoute(array(
        'path' => '/api/user/{username}/blogs/{blogName}/pages/{page}',
        'handlers' => array(
            'page' => Zaphpa_Constants::PATTERN_DIGIT,
            'username' => Zaphpa_Constants::PATTERN_ALPHA,
            'blogName' =>Zaphpa_Constants::PATTERN_ALPHA  
        ),
        'get' => array('Blog', 'apiBlogPosts'),
        'file' => 'controllers/blog.php',
    ));

    $router->addRoute(array(
        'path' => '/api/user/{username}/blogs/{blogName}/newPost',
        'handlers' => array(
            'username' => Zaphpa_Constants::PATTERN_ALPHA,
            'blogName' =>Zaphpa_Constants::PATTERN_ALPHA 
        ),
        'post' => array('Blog', 'addNewPost'),
        'file' => 'controllers/blog.php',
    ));

    $router->addRoute(array(
        'path' => '/user/{username}/blogs/{blogName}/search/{searchText}',
        'handlers' => array(
            'searchText' => Zaphpa_Constants::PATTERN_ANY,
            'username' => Zaphpa_Constants::PATTERN_ALPHA,
            'blogName' =>Zaphpa_Constants::PATTERN_ALPHA 
        ),
        'get' => array('Blog', 'getSearchPosts'),
        'file' => 'controllers/blog.php',
    ));

    $router->addRoute(array(
        'path' => '/api/user/{username}/blogs/{blogName}/search/{searchText}',
        'handlers' => array(
            'searchText' => Zaphpa_Constants::PATTERN_ANY,
            'username' => Zaphpa_Constants::PATTERN_ALPHA,
            'blogName' =>Zaphpa_Constants::PATTERN_ALPHA 
        ),
        'get' => array('Blog', 'searchPosts'),
        'file' => 'controllers/blog.php',
    ));

    $router->addRoute(array(
        'path' => '/user/{username}/blogs/{blogName}/posts/{post}',
        'handlers' => array(
            'post' => Zaphpa_Constants::PATTERN_DIGIT,
            'username' => Zaphpa_Constants::PATTERN_ALPHA,
            'blogName' =>Zaphpa_Constants::PATTERN_ALPHA 
        ),
        'get' => array('Blog', 'getBlogPost'),
        'file' => 'controllers/blog.php',
    ));

    $router->addRoute(array(
        'path' => '/api/user/{username}/blogs/{blogName}/posts/{post}',
        'handlers' => array(
            'post' => Zaphpa_Constants::PATTERN_DIGIT,
            'username' => Zaphpa_Constants::PATTERN_ALPHA,
            'blogName' =>Zaphpa_Constants::PATTERN_ALPHA 
        ),
        'get' => array('Blog', 'apiBlogPost'),
        'file' => 'controllers/blog.php',
    ));

    // Admin

    $router->addRoute(array(
        'path' => '/admin',
        'get' => array('Admin', 'getPage'),
        'file' => 'controllers/admin.php',
    ));

    $router->addRoute(array(
        'path' => '/api/user/{username}',
        'delete' => array('Admin', 'deleteUser'),
        'file' => 'controllers/admin.php',
    ));

    $router->addRoute(array(
        'path' => '/api/user/{username}/ban',
        'post' => array('Admin', 'banUser'),
        'delete' => array('Admin', 'unbanUser'),
        'file' => 'controllers/admin.php',
    ));

    $router->addRoute(array(
        'path' => '/api/user/{username}/changePassword',
        'post' => array('Admin', 'changePassword'),
        'file' => 'controllers/admin.php',
    ));

    $router->addRoute(array(
        'path' => '/api/comments',
        'get' => array('Admin', 'getReportedComments'),
        'file' => 'controllers/admin.php',
    ));

    // Comments

    $router->addRoute(array(
        'path' => '/api/comments/{id}/report',
        'post' => array('Report', 'reportComment'),
        'delete' => array('Admin', 'ignoreReport'),
        'file' => 'controllers/admin.php',
    ));

    $router->addRoute(array(
        'path' => '/api/comments/{id}',
        'delete' => array('Admin', 'deleteComment'),
        'file' => 'controllers/admin.php',
    ));

    // Photos
    
    $router->addRoute(array(
        'path' => '/user/{username}/photos',
        'handlers' => array(
            'username' => Zaphpa_Constants::PATTERN_ALPHA
        ),
        'get' => array('Photos', 'getAlbums'),
        'file' => 'controllers/photos.php',
    ));

    $router->addRoute(array(
        'path' => '/api/user/{username}/photos',
        'handlers' => array(
            'username' => Zaphpa_Constants::PATTERN_ALPHA
        ),
        'get' => array('Photos', 'getPhotoAlbums'),
        'post' => array('Photos', 'addNewAlbum'),
        'file' => 'controllers/photos.php',
    ));

    $router->addRoute(array(
        'path' => '/api/user/{username}/photos/{id}',
        'handlers' => array(
            'id' => Zaphpa_Constants::PATTERN_DIGIT,
            'username' => Zaphpa_Constants::PATTERN_ALPHA
        ),
        'get' => array('Photos', 'getPhotos'),
        'post' => array('Photos', 'addPhoto'),
        'delete' => array('Photos', 'deleteAlbum'),
        'file' => 'controllers/photos.php',
    ));

    $router->addRoute(array(
        'path' => '/api/user/{username}/photos/{albumId}/{photoId}',
        'handlers' => array(
            'albumId' => Zaphpa_Constants::PATTERN_DIGIT,
            'photoId' => Zaphpa_Constants::PATTERN_DIGIT,
            'username' => Zaphpa_Constants::PATTERN_ALPHA
        ),
        'get' => array('Photos', 'getComments'),
        'delete' => array('Photos', 'deletePhoto'),
        'post' => array('Photos', 'addComment'),
        'file' => 'controllers/photos.php',
    ));

    $router->addRoute(array(
        'path' => '/api/user/{username}/photos/{albumId}/photo/{photoId}',
        'handlers' => array(
            'albumId' => Zaphpa_Constants::PATTERN_DIGIT,
            'photoId' => Zaphpa_Constants::PATTERN_DIGIT,
            'username' => Zaphpa_Constants::PATTERN_ALPHA
        ),
        'get' => array('Photos', 'getPhoto'),
        'file' => 'controllers/photos.php',
    ));

    try {
        $router->route();
    } catch (Zaphpa_InvalidPathException $ex) {
        header("Content-Type: text/html;", TRUE, 404);
        $uri = $_SERVER['REQUEST_URI'];
        require_once('mustache_conf.php');
        $content = $m->render('404', array('page' => $uri));
        $out = $m->render('main', array('title' => '404', 'content' => $content));
        die($out);
    }

?>

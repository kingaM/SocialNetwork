<?php
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	
    session_start();

    $public_pages = '#(^/login|/register$)|^/activate/#';

    if((!isset($_SESSION['id']) && !preg_match($public_pages, $_SERVER['REQUEST_URI'])) ||
        (isset($_SESSION['id']) && $_SESSION['id'] == -1)) {
        header('Location: /login');
        die();
    }

    require_once(__DIR__ . '/libs/zaphpa.lib.php');

    $router = new Zaphpa_Router();

    $router->addRoute(array(
        'path' => '/',
        'get' => array('Index', 'getPage'),
        'file' => 'controllers/index.php',
    ));

    $router->addRoute(array(
        'path' => '/friends',
        'get' => array('Friends', 'getPage'),
        'post' => array('Friends', 'addFriend'),
        'file' => 'controllers/friends.php',
    ));

    $router->addRoute(array(
        'path' => '/friends/{login}',
        // Until we add JS properly, POST will have to do instead of DELETE
        'post' => array('Friends', 'removeFriend'),
        'file' => 'controllers/friends.php',
    ));

    $router->addRoute(array(
        'path' => '/login',
        'get' => array('Login', 'getPage'),
        'post' => array('Login', 'verifyUser'),
        'file' => 'controllers/login.php',
    ));

    $router->addRoute(array(
        'path' => '/logout',
        'get' => array('Login', 'logout'),
        'file' => 'controllers/login.php',
    ));

    $router->addRoute(array(
        'path' => '/register',
        'get' => array('Register', 'getPage'),
        'post' => array('Register', 'addUser'),
        'file' => 'controllers/register.php',
    ));

    $router->addRoute(array(
        'path' => '/activate/{hash}',
        'get' => array('Register', 'activate'),
        'file' => 'controllers/register.php',
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

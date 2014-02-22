<?php

    include_once('helpers/database/UsersHelper.php');
    include_once('helpers/database/BlogsHelper.php');

    // TODO: Tidy up repeated code into functions
    class Blog {

        public function getBlogs($req, $res) {
            $usersDB = new UsersHelper();
            $username = $req->params['username'];
            if(!$usersDB->checkUsernameExists($username)) {
                header("Content-Type: text/html;", TRUE, 404);
                $uri = $_SERVER['REQUEST_URI'];
                require_once('mustache_conf.php');
                $content = $m->render('404', array('page' => $uri));
                $res->add($m->render('main', array('title' => '404', 'content' => $content)));
                $res->send();
            }
            require_once('mustache_conf.php');
            $content = $m->render('userblogs', NULL);
            $content = $m->render('user', array('content' => $content, 'username' => $username,
                'blog' => 'active'));
            $res->add($m->render('main', array('title' => 'Profile', 'content' => $content)));
            $res->send();
        }

        public function apiUserBlogs($req, $res) {
            $username = $req->params['username'];
            $blogsDB = new BlogsHelper();
            $usersDB = new UsersHelper();
            if(!$usersDB->checkUsernameExists($username)) {
                $res->add(json_encode(array('valid' => false, 'currentUser' => false, 
                    'user' => NULL)));
                $res->send();
            }
            $userId = $usersDB->getIdFromUsername($username);
            if($userId === $_SESSION['id']) {
                $currentUser = true;
            } else {
                $currentUser = false;
            }
            $blogs = $blogsDB->getBlogs($userId);
            $jsonBlogs = array();
            foreach ($blogs as $blog) {
                $jsonBlogs[] = array(
                    'name' => $blog['name'],
                    'about' => $blog['about'],
                    'url' => $blog['url']);
            }
            $res->add(json_encode(array('valid' => true, 'currentUser' => $currentUser,
                'blogs' => $jsonBlogs)));
            $res->send();
        }


        public function getBlogPosts($req, $res) {
            $usersDB = new UsersHelper();
            $username = $req->params['username'];
            if(!$usersDB->checkUsernameExists($username)) {
                header("Content-Type: text/html;", TRUE, 404);
                $uri = $_SERVER['REQUEST_URI'];
                require_once('mustache_conf.php');
                $content = $m->render('404', array('page' => $uri));
                $res->add($m->render('main', array('title' => '404', 'content' => $content)));
                $res->send();
            }
            require_once('mustache_conf.php');
            $content = $m->render('blog', NULL);
            $res->add($m->render('main', array('title' => 'Blog', 'content' => $content)));
            $res->send();
        } 

        public function getNewPostPage($req, $res) {
            $usersDB = new UsersHelper();
            $username = $req->params['username'];
            $blogName = $req->params['blogName'];
            // TODO: Check if blog name is correct
            if(!$usersDB->checkUsernameExists($username)) {
                header("Content-Type: text/html;", TRUE, 404);
                $uri = $_SERVER['REQUEST_URI'];
                require_once('mustache_conf.php');
                $content = $m->render('404', array('page' => $uri));
                $res->add($m->render('main', array('title' => '404', 'content' => $content)));
                $res->send();
            }
            require_once('mustache_conf.php');
            $content = $m->render('newblogpost', NULL);
            $res->add($m->render('main', array('title' => 'Blog', 'content' => $content)));
            $res->send();
        } 

        public function addNewPost($req, $res) {
            $username = $req->params['username'];
            $blogName = $req->params['blogName'];
            $blogsDB = new BlogsHelper();
            $usersDB = new UsersHelper();
            $userId = $usersDB->getIdFromUsername($username);
            if($userId !== $_SESSION['id']) {
                $res->add(json_encode(array('valid' => false)));
                $res->send();
            }
            $title = $req->data['title'];
            $content = $req->data['content'];
            if($blogsDB->addBlogPost($userId, $blogName, $title, $content, time()) < 0) {
                $res->add(json_encode(array('valid' => false)));
                $res->send();
            } else {
                $res->add(json_encode(array('valid' => true)));
                $res->send();
            }
        }

        public function addNewBlog($req, $res) {
            $username = $req->params['username'];
            $blogsDB = new BlogsHelper();
            $usersDB = new UsersHelper();
            $userId = $usersDB->getIdFromUsername($username);
            if($userId !== $_SESSION['id']) {
                $res->add(json_encode(array('valid' => false)));
                $res->send();
            }
            $text = $req->data['text'];
            $name = $req->data['name'];
            $url = $req->data['url'];
            if(!ctype_alnum($url)) {
                $res->add(json_encode(array('valid' => true, 'alphanumeric' => false, 
                    'unique' => false)));
                $res->send();
            }
            if(!$blogsDB->checkBlogUrlUnique($userId, $url)) {
                $res->add(json_encode(array('valid' => true, 'alphanumeric' => true, 
                    'unique' => false)));
                $res->send();
            } else {
                if($blogsDB->addBlog($userId, $name, $url, $text)) {
                    $res->add(json_encode(array('valid' => true, 'alphanumeric' => true, 
                        'unique' => true)));
                    $res->send();
                } else {
                    $res->add(json_encode(array('valid' => false, 'alphanumeric' => true, 
                        'unique' => true)));
                    $res->send();
                }
                
            }
        }

        public function apiBlogInfo($req, $res) {
            $username = $req->params['username'];
            $blogName = $req->params['blogName'];
            $blogsDB = new BlogsHelper();
            $usersDB = new UsersHelper();
            if(!$usersDB->checkUsernameExists($username)) {
                $res->add(json_encode(array('valid' => false, 'currentUser' => false, 
                    'posts' => NULL)));
                $res->send();
            }
            $userId = $usersDB->getIdFromUsername($username);
            if($userId === $_SESSION['id']) {
                $currentUser = true;
            } else {
                $currentUser = false;
            }
            $blog = $blogsDB->getBlogInfo($userId, $blogName);
            if(sizeof($blog) != 1) {
                $res->add(json_encode(array('valid' => false, 'currentUser' => $currentUser, 
                    'blog' => NULL)));
                $res->send();
            }
            $res->add(json_encode(array('valid' => true, 'currentUser' => $currentUser,
                'name' => $blog[0]['name'],'about' => $blog[0]['about'], 
                'url' => $blog[0]['url'])));
            $res->send();
        }

        public function apiBlogPosts($req, $res) {
            $username = $req->params['username'];
            $blogName = $req->params['blogName'];
            $page = $req->params['page'];
            $blogsDB = new BlogsHelper();
            $usersDB = new UsersHelper();
            if(!$usersDB->checkUsernameExists($username)) {
                $res->add(json_encode(array('valid' => false, 'currentUser' => false, 
                    'posts' => NULL)));
                $res->send();
            }
            $userId = $usersDB->getIdFromUsername($username);
            if($userId === $_SESSION['id']) {
                $currentUser = true;
            } else {
                $currentUser = false;
            }
            $posts = $blogsDB->getBlogPosts($userId, $blogName, $page);
            if(sizeof($posts) <= 0) {
                $res->add(json_encode(array('valid' => false, 'currentUser' => $currentUser, 
                    'posts' => NULL)));
                $res->send();
            }
            $jsonPosts = array();
            foreach ($posts as $post) {
                $jsonPosts[] = array(
                    'title' => $post['title'],
                    'timestamp' => $post['timestamp'],
                    'content' => $post['content']);
            }
            $res->add(json_encode(array('valid' => true, 'currentUser' => $currentUser,
                'posts' => $jsonPosts)));
            $res->send();
        }

        public function apiPostsNumber($req, $res) {
            $username = $req->params['username'];
            $blogName = $req->params['blogName'];
            $blogsDB = new BlogsHelper();
            $usersDB = new UsersHelper();
            if(!$usersDB->checkUsernameExists($username)) {
                $res->add(json_encode(array('valid' => false, 'posts' => NULL)));
                $res->send();
            }
            $userId = $usersDB->getIdFromUsername($username);
            $posts = $blogsDB->getBlogPostsNumber($userId, $blogName);
            $res->add(json_encode(array('valid' => true, 'posts' => $posts)));
            $res->send();
        }
    }
?>
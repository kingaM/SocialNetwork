<?php

    include_once('helpers/database/UsersHelper.php');
    include_once('helpers/database/BlogsHelper.php');

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

        public function getUserBlogs($req, $res) {
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
    }
?>
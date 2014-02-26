<?php

    include_once('helpers/database/UsersHelper.php');
    include_once('helpers/database/BlogsHelper.php');

    class Blog {

        private function show404() {
            header("Content-Type: text/html;", TRUE, 404);
            $uri = $_SERVER['REQUEST_URI'];
            require_once('mustache_conf.php');
            $content = $m->render('404', array('page' => $uri));
            return $m->render('main', array('title' => '404', 'content' => $content));
        }

        private function checkUsernameAndBlogname($username, $blogName, $usersDB, $blogsDB) {
            $id = $usersDB->getIdFromUsername($username);
            if($id == -1 || $blogsDB->getBlogId($id, $blogName) == -1) {
                return -1;
            }
            return $id;
        }

        public function getBlogs($req, $res) {
            $usersDB = new UsersHelper();
            $username = $req->params['username'];
            if(!$usersDB->checkUsernameExists($username)) {
                $res->add($this->show404());
                $res->send();
            }
            require_once('mustache_conf.php');
            $content = $m->render('userblogs', NULL);
            $content = $m->render('user', array('content' => $content, 'username' => $username,
                'blog' => 'active'));
            $res->add($m->render('main', array('title' => 'Profile', 'content' => $content)));
            $res->send();
        }

        public function getNewPostPage($req, $res) {
            $usersDB = new UsersHelper();
            $blogsDB = new BlogsHelper();
            $username = $req->params['username'];
            $blogName = $req->params['blogName'];
            $userId = $this->checkUsernameAndBlogname($username, $blogName, $usersDB, $blogsDB);
            if($userId == -1) {
                $res->add($this->show404());
                $res->send();
            }
            require_once('mustache_conf.php');
            $content = $m->render('newblogpost', NULL);
            $res->add($m->render('main', array('title' => 'Blog', 'content' => $content)));
            $res->send();
        } 

        public function apiUserBlogs($req, $res) {
            $username = $req->params['username'];
            $blogsDB = new BlogsHelper();
            $usersDB = new UsersHelper();
            $userId = $usersDB->getIdFromUsername($username);
            if($userId == -1) {
                $res->add(json_encode(array('valid' => false, 'currentUser' => false, 
                    'user' => NULL)));
                $res->send();
            }
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
                $res->add($this->show404());
                $res->send();
            }
            require_once('mustache_conf.php');
            $content = $m->render('blog', NULL);
            $res->add($m->render('main', array('title' => 'Blog', 'content' => $content)));
            $res->send();
        } 

        public function getBlogPost($req, $res) {
            $username = $req->params['username'];
            $blogName = $req->params['blogName'];
            $blogsDB = new BlogsHelper();
            $usersDB = new UsersHelper();
            $userId = $this->checkUsernameAndBlogname($username, $blogName, $usersDB, $blogsDB);
            if($userId == -1) {
                $res->add($this->show404());
                $res->send();
            }
            require_once('mustache_conf.php');
            $content = $m->render('post', NULL);
            $res->add($m->render('main', array('title' => 'Blog', 'content' => $content)));
            $res->send();
        }

        public function addNewPost($req, $res) {
            $username = $req->params['username'];
            $blogName = $req->params['blogName'];
            $blogsDB = new BlogsHelper();
            $usersDB = new UsersHelper();
            $userId = $this->checkUsernameAndBlogname($username, $blogName, $usersDB, $blogsDB);
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
            $json = array('valid' => false, 'alphanumeric' => false, 
                    'unique' => false);
            if($userId !== $_SESSION['id']) {
                $res->add(json_encode($json));
                $res->send();
            }
            $text = $req->data['text'];
            $name = $req->data['name'];
            $url = $req->data['url'];
            
            if(!ctype_alnum($url)) {
                $json['valid'] = true;
            } else if(!$blogsDB->checkBlogUrlUnique($userId, $url)) {
                $json['valid'] = true;
                $json['alphanumeric'] = true;
            } else {
                if($blogsDB->addBlog($userId, $name, $url, $text)) {
                    $json['valid'] = true;
                    $json['alphanumeric'] = true;
                    $json['unique'] = true;
                } else {
                    $json['valid'] = false;
                    $json['alphanumeric'] = true;
                    $json['unique'] = true;
                }
            }
            $res->add(json_encode($json));
            $res->send();
        }

        public function apiBlogInfo($req, $res) {
            $username = $req->params['username'];
            $blogName = $req->params['blogName'];
            $blogsDB = new BlogsHelper();
            $usersDB = new UsersHelper();
            $userId = $this->checkUsernameAndBlogname($username, $blogName, $usersDB, $blogsDB);
            if($userId == -1) {
                $res->add(json_encode(array('valid' => false, 'currentUser' => false, 
                    'posts' => NULL)));
                $res->send();
            }
            if($userId === $_SESSION['id']) {
                $currentUser = true;
            } else {
                $currentUser = false;
            }
            $blog = $blogsDB->getBlogInfo($userId, $blogName);
            if($blog == -1) {
                $res->add(json_encode(array('valid' => false, 'currentUser' => $currentUser, 
                    'blog' => NULL)));
                $res->send();
            }
            $res->add(json_encode(array('valid' => true, 'currentUser' => $currentUser,
                'name' => $blog['name'],'about' => $blog['about'], 
                'url' => $blog['url'])));
            $res->send();
        }

        public function apiBlogPosts($req, $res) {
            $username = $req->params['username'];
            $blogName = $req->params['blogName'];
            $page = $req->params['page'];
            $blogsDB = new BlogsHelper();
            $usersDB = new UsersHelper();
            $userId = $this->checkUsernameAndBlogname($username, $blogName, $usersDB, $blogsDB);
            if($userId == -1) {
                $res->add(json_encode(array('valid' => false, 'currentUser' => false, 
                    'posts' => NULL)));
                $res->send();
            }
            if($userId === $_SESSION['id']) {
                $currentUser = true;
            } else {
                $currentUser = false;
            }
            $posts = $blogsDB->getBlogPosts($userId, $blogName, $page);
            if(sizeof($posts) < 0) {
                $res->add(json_encode(array('valid' => false, 'currentUser' => $currentUser, 
                    'posts' => NULL)));
                $res->send();
            }
            $jsonPosts = array();
            foreach ($posts as $post) {
                $jsonPosts[] = array(
                    'id' => $post['postId'],
                    'title' => $post['title'],
                    'timestamp' => $post['timestamp'],
                    'content' => $post['content']);
            }
            $res->add(json_encode(array('valid' => true, 'currentUser' => $currentUser,
                'posts' => $jsonPosts)));
            $res->send();
        }

        public function apiBlogPost($req, $res) {
            $username = $req->params['username'];
            $blogName = $req->params['blogName'];
            $post = $req->params['post'];
            $blogsDB = new BlogsHelper();
            $usersDB = new UsersHelper();
            $userId = $this->checkUsernameAndBlogname($username, $blogName, $usersDB, $blogsDB);
            if($userId == -1) {
                $res->add(json_encode(array('valid' => false, 'currentUser' => false, 
                    'posts' => NULL)));
                $res->send();
            }
            if($userId === $_SESSION['id']) {
                $currentUser = true;
            } else {
                $currentUser = false;
            }
            $post = $blogsDB->getBlogPost($userId, $blogName, $post);
            if($post == -1) {
                $res->add(json_encode(array('valid' => false, 'currentUser' => $currentUser, 
                    'posts' => NULL)));
                $res->send();
            }
            $res->add(json_encode(array('valid' => true, 'currentUser' => $currentUser,
                'posts' => array(
                    'id' => $post['postId'],
                    'title' => $post['title'],
                    'timestamp' => $post['timestamp'],
                    'content' => $post['content']))));
            $res->send();
        }

        public function apiPostsNumber($req, $res) {
            $username = $req->params['username'];
            $blogName = $req->params['blogName'];
            $blogsDB = new BlogsHelper();
            $usersDB = new UsersHelper();
            $userId = $this->checkUsernameAndBlogname($username, $blogName, $usersDB, $blogsDB);
            if($userId == -1) {
                $res->add(json_encode(array('valid' => false, 'posts' => NULL)));
                $res->send();
            }
            $posts = $blogsDB->getBlogPostsNumber($userId, $blogName);
            $res->add(json_encode(array('valid' => true, 'posts' => $posts)));
            $res->send();
        }

        public function searchPosts($req, $res) {
            $username = $req->params['username'];
            $blogName = $req->params['blogName'];
            $page = $req->params['page'];
            $blogsDB = new BlogsHelper();
            $usersDB = new UsersHelper();
            $userId = $this->checkUsernameAndBlogname($username, $blogName, $usersDB, $blogsDB);
            if($userId == -1) {
                $res->add(json_encode(array('valid' => false, 'posts' => NULL)));
                $res->send();
            }
            $text = trim($req->data['text']);
            if($userId === $_SESSION['id']) {
                $currentUser = true;
            } else {
                $currentUser = false;
            }
            $posts = $blogsDB->searchBlogPosts($userId, $blogName, $page, $text);
            if(sizeof($posts) < 0) {
                $res->add(json_encode(array('valid' => false, 'currentUser' => $currentUser, 
                    'posts' => NULL)));
                $res->send();
            }
            $jsonPosts = array();
            foreach ($posts as $post) {
                $jsonPosts[] = array(
                    'id' => $post['postId'],
                    'title' => $post['title'],
                    'timestamp' => $post['timestamp'],
                    'content' => $post['content']);
            }
            $res->add(json_encode(array('valid' => true, 'currentUser' => $currentUser,
                'posts' => $jsonPosts)));
            $res->send();
        }
    }
?>
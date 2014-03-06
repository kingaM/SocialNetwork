<?php

    include_once('helpers/database/UsersHelper.php');
    include_once('helpers/database/PhotosHelper.php');

    class Photos {

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

        public function getAlbums($req, $res) {
            $usersDB = new UsersHelper();
            $username = $req->params['username'];
            if(!$usersDB->checkUsernameExists($username)) {
                $res->add($this->show404());
                $res->send();
            }
            require_once('mustache_conf.php');
            $content = $m->render('userphotos', NULL);
            $content = $m->render('user', array('content' => $content, 'username' => $username,
                'photos' => 'active'));
            $res->add($m->render('main', array('title' => 'Photos', 'content' => $content)));
            $res->send();
        }

        public function getPhotoAlbums($req, $res) {
            $username = $req->params['username'];
            $photosDB = new PhotosHelper();
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
            $albums = $photosDB->getPhotoAlbums($userId);
            $jsonAlbums = array();
            foreach ($albums as $album) {
                $jsonAlbums[] = array(
                    'id' => $album['albumId'],
                    'name' => $album['name'],
                    'about' => $album['about']);
            }
            $res->add(json_encode(array('valid' => true, 'currentUser' => $currentUser,
                'albums' => $jsonAlbums)));
            $res->send();
        }

        public function addNewAlbum($req, $res) {
            $username = $req->params['username'];
            $photosDB = new PhotosHelper();
            $usersDB = new UsersHelper();
            $userId = $usersDB->getIdFromUsername($username);
            $json = array('valid' => false);
            if($userId !== $_SESSION['id']) {
                $res->add(json_encode($json));
                $res->send();
            }
            $text = $req->data['text'];
            $name = $req->data['name'];
            
            if($photosDB->addAlbum($userId, $name, $text)) {
                $json['valid'] = true;
            } else {
                $json['valid'] = false;
            }
            $res->add(json_encode($json));
            $res->send();
        }
    }
?>
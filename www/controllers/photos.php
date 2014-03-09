<?php

    include_once('helpers/database/UsersHelper.php');
    include_once('helpers/database/PhotosHelper.php');
    require_once('libs/ImageManipulator.php');
    require_once('libs/FirePHPCore/FirePHP.class.php');  

    class Photos {

        private function show404() {
            header("Content-Type: text/html;", TRUE, 404);
            $uri = $_SERVER['REQUEST_URI'];
            require_once('mustache_conf.php');
            $content = $m->render('404', array('page' => $uri));
            return $m->render('main', array('title' => '404', 'content' => $content));
        }

        private function checkUsernameAndPhotoAlbum($username, $blogName, $usersDB, $photosDB) {
            $id = $usersDB->getIdFromUsername($username);
            if($id == -1 || $photosDB->getBlogId($id, $blogName) == -1) {
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

        public function getPhotos($req, $res) {
            $username = $req->params['username'];
            $id = $req->params['id'];
            $usersDB = new UsersHelper();
            $userId = $usersDB->getIdFromUsername($username);
            if($userId == -1) {
                $res->add(json_encode(array('valid' => false, 'currentUser' => false, 
                    'photos' => NULL)));
                $res->send();
            }
            if($userId === $_SESSION['id']) {
                $currentUser = true;
            } else {
                $currentUser = false;
            }
            $photosDB = new PhotosHelper();
            $posts = $photosDB->getPhotos($userId, $id);
            if(sizeof($posts) < 0) {
                $res->add(json_encode(array('valid' => false, 'currentUser' => $currentUser, 
                    'photos' => NULL)));
                $res->send();
            }
            $jsonPosts = array();
            foreach ($posts as $post) {
                $jsonPosts[] = array(
                    'id' => $post['photoId'],
                    'description' => $post['description'],
                    'timestamp' => $post['timestamp'],
                    'url' => $post['url'], 
                    'thumbnailUrl' => $post['thumbnailUrl']);
            }
            $res->add(json_encode(array('valid' => true, 'currentUser' => $currentUser,
                'photos' => $jsonPosts)));
            $res->send();
        }

        public function addPhoto($req, $res) {
            if(sizeof($_FILES) != 1) {
                $res->add(json_encode(array('valid' => false)));
                $res->send();
            }
            $username = $req->params['username'];
            $albumId = $req->params['id'];
            $firephp = FirePHP::getInstance(true);
            $firephp->log($req->data);
            $data = $req->data;
            $data["description"] = trim($data["description"]);
            $data["description"] = strip_tags($data["description"]);
            if($data["description"] == "") {
                $data["description"] = null;
            } 
            $usersDB = new UsersHelper();
            $photosDB = new PhotosHelper();
            $userId = $usersDB->getIdFromUsername($username);
            if ($userId == -1) {
                $res->add(json_encode(array('valid' => false, 'image' => NULL)));
                $res->send();
            }
            $timestamp = time();
            try {
                $url = $this->uploadImage($_FILES[0], $timestamp);
                $thumbnailUrl = $this->uploadThumbnail($_FILES[0], $timestamp);
            } catch (Exception $e) {
                $res->add(json_encode(array('valid' => true, 'image_error' => true)));
                $res->send();
            }
            if ($url === false || $thumbnailUrl === false) {
                $res->add(json_encode(array('valid' => true, 'image_error' => true)));
                $res->send();
            } 
            if ($photosDB->addPhoto($albumId, $timestamp, $data["description"], $url, 
                $thumbnailUrl)) {
                $res->add(json_encode(array('valid' => true, 'image_error' => false, 
                    'image' => $url, 'thumbnailUrl' => $thumbnailUrl)));
                $res->send();
            }
            $res->add(json_encode(array('valid' => false)));
            $res->send();
        }

        private function uploadImage($file, $timestamp) {
            if ($file['error'] > 0) {
                return false;
            } else {
                $validExtensions = array('.jpg', '.jpeg', '.gif', '.png');
                $fileExtension = strrchr($file['name'], ".");
                if (in_array($fileExtension, $validExtensions)) {
                    $manipulator = new ImageManipulator($file['tmp_name']);
                    $pictureName = 'uploads/album_pics/full/' . $_SESSION['id'] . $timestamp .
                       $fileExtension;
                    $manipulator->save($pictureName);
                    return "/" . $pictureName;
                } else {
                    return false;
                }
            }
        }

        private function uploadThumbnail($file, $timestamp) {
            if ($file['error'] > 0) {
                return false;
            } else {
                $validExtensions = array('.jpg', '.jpeg', '.gif', '.png');
                $fileExtension = strrchr($file['name'], ".");
                if (in_array($fileExtension, $validExtensions)) {
                    $manipulator = new ImageManipulator($file['tmp_name']);
                    $pictureName = 'uploads/album_pics/thumbnail/' . $_SESSION['id'] . $timestamp .
                       $fileExtension;
                    $firephp = FirePHP::getInstance(true);
                    $height = $manipulator->getHeight();
                    $width = $manipulator->getWidth();
                    $firephp->log($width . " " . $height);
                    if ($height <= $width) {
                        $width  = round(200 / $height * $width);
                        $height = 200;
                    } else {
                        $height = round(200 / $width * $height);
                        $width = 200;
                    }
                    $firephp->log($width . " " . $height);
                    $manipulator = $manipulator->resample($width, $height, false);
                    $manipulator = $manipulator->crop(0, 0, 200, 200);
                    $manipulator->save($pictureName);
                    return "/" . $pictureName;
                } else {
                    return false;
                }
            }
        }

        public function deleteAlbum($req, $res) {
            $username = $req->params['username'];
            $albumId = $req->params['id'];
            $usersDB = new UsersHelper();
            $userId = $usersDB->getIdFromUsername($username);
            if($userId !== $_SESSION['id']) {
                $res->add(json_encode(array('valid' => false)));
                $res->send();
            }
            $photosDB = new PhotosHelper();
            $photosToDelete = $photosDB->getPhotos($userId, $albumId);
            foreach ($photosToDelete as $photo) {
                unlink(substr($photo['thumbnailUrl'], 1));
                unlink(substr($photo['url'], 1));
            }
            $photosDB->deleteAlbum($_SESSION['id'], $albumId);
            $res->add(json_encode(array('valid' => true)));
            $res->send();
        }

        public function deletePhoto($req, $res) {
            $username = $req->params['username'];
            $albumId = $req->params['albumId'];
            $photoId = $req->params['photoId'];
            $usersDB = new UsersHelper();
            $userId = $usersDB->getIdFromUsername($username);
            if($userId !== $_SESSION['id']) {
                $res->add(json_encode(array('valid' => false)));
                $res->send();
            }
            $photosDB = new PhotosHelper();
            $photo = $photosDB->getPhoto($userId, $albumId, $photoId);
            unlink(substr($photo['thumbnailUrl'], 1));
            unlink(substr($photo['url'], 1));
            $photosDB->deletePhoto($_SESSION['id'], $albumId, $photoId);
            $res->add(json_encode(array('valid' => true)));
            $res->send();
        }

        public function getComments($req, $res) {
            $username = $req->params['username'];
            $albumId = $req->params['albumId'];
            $photoId = $req->params['photoId'];
            $usersDB = new UsersHelper();
            $userId = $usersDB->getIdFromUsername($username);
            if($userId == -1) {
                $res->add(json_encode(array('valid' => false, 'comments' => null)));
                $res->send();
            }
            $photosDB = new PhotosHelper();
            $comments = $photosDB->getComments($albumId, $photoId);
            $json = array();
            $firephp = FirePHP::getInstance(true);
            $firephp->log($comments);
            foreach ($comments as $comment) {
                $json[] = array('content' => $comment['content'], 
                    'timestamp' => $comment['timestamp'],
                    'firstName' => $comment['first_name'], 
                    'middleName' => $comment['middle_name'], 
                    'lastName' => $comment['last_name'], 
                    'username' => $comment['login'], 
                    'profilePicture' => $comment['profilePicture']);
            }
            $res->add(json_encode(array('valid' => true, 'comments' => $json)));
            $res->send();
        }
    }
?>
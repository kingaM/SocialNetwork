<?php

    include_once('helpers/database/UsersHelper.php');
    include_once('helpers/database/PhotosHelper.php');
    include_once('helpers/database/FriendsHelper.php');
    require_once('libs/ImageManipulator.php');           


    class Photos {

        private function show404() {
            header("Content-Type: text/html;", TRUE, 404);
            $uri = $_SERVER['REQUEST_URI'];
            require_once('mustache_conf.php');
            $content = $m->render('404', array('page' => $uri));
            return $m->render('main', array('title' => '404', 'content' => $content));
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
            $friendsDB = new FriendsHelper();
            $relationship = $friendsDB->getRelationship($_SESSION['id'], $userId);
            if($userId === $_SESSION['id']) {
                $currentUser = true;
            } else {
                $currentUser = false;
            }
            $albums = $photosDB->getPhotoAlbums($userId);
            $isAdmin = $usersDB->isAdmin($_SESSION['username']);
            $jsonAlbums = array();
            foreach ($albums as $album) {
                if($relationship <= $album['privacy'] || $isAdmin) {
                    $jsonAlbums[] = array(
                        'id' => $album['albumId'],
                        'name' => $album['name'],
                        'about' => $album['about']);
                }
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

            $data = $req->data;
            foreach ($data as $key => $value) {
                $data[$key] = trim($data[$key]);
                $data[$key] = strip_tags($data[$key]);
            }

            $text = $req->data['text'];
            $name = $req->data['name'];
            $privacy = $req->data['privacy'];
            $valid = (filter_var($privacy, FILTER_VALIDATE_INT) !== false)
                && intval($privacy) > 0 && intval($privacy) < 7;
            if(empty($text) || empty($name) || empty($privacy) || !$valid) {
                $res->add(json_encode($json));
                $res->send();
            }
            
            if($photosDB->addAlbum($userId, $name, $text, $privacy)) {
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
            $photosDB = new PhotosHelper();
            $userId = $usersDB->getIdFromUsername($username);
            if($userId == -1 || 
                !$this->isVisibleAlbum($_SESSION['id'], $userId, $id, $photosDB, $usersDB)) {
                $res->add(json_encode(array('valid' => false, 'currentUser' => false, 
                    'photos' => NULL)));
                $res->send();
            }
            if($userId === $_SESSION['id']) {
                $currentUser = true;
            } else {
                $currentUser = false;
            }
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

        public function getPhoto($req, $res) {
            $username = $req->params['username'];
            $id = $req->params['albumId'];
            $photoId = $req->params['photoId'];
            $usersDB = new UsersHelper();
            $photosDB = new PhotosHelper();
            $userId = $usersDB->getIdFromUsername($username);
            if($userId == -1 || 
                !$this->isVisibleAlbum($_SESSION['id'], $userId, $id, $photosDB, $usersDB)) {
                $res->add(json_encode(array('valid' => false, 'currentUser' => false, 
                    'photo' => NULL)));
                $res->send();
            }
            if($userId === $_SESSION['id']) {
                $currentUser = true;
            } else {
                $currentUser = false;
            }
            $photo = $photosDB->getPhoto($userId, $id, $photoId);
            if($photo == -1) {
                $res->add(json_encode(array('valid' => false, 'currentUser' => $currentUser, 
                    'photo' => NULL)));
                $res->send();
            }
            $jsonPhotos = array(
                    'id' => $photo['photoId'],
                    'description' => $photo['description'],
                    'timestamp' => $photo['timestamp'],
                    'url' => $photo['url'], 
                    'thumbnailUrl' => $photo['thumbnailUrl']);
            $res->add(json_encode(array('valid' => true, 'currentUser' => $currentUser,
                'photo' => $jsonPhotos)));
            $res->send();
        }

        public function addPhoto($req, $res) {
            if(sizeof($_FILES) != 1) {
                $res->add(json_encode(array('valid' => false)));
                $res->send();
            }
            $username = $req->params['username'];
            $albumId = $req->params['id'];
            $data = $req->data;
            $data["description"] = trim($data["description"]);
            $data["description"] = strip_tags($data["description"]);
            $usersDB = new UsersHelper();
            $photosDB = new PhotosHelper();
            $userId = $usersDB->getIdFromUsername($username);
            if ($userId !== $_SESSION['id'] || 
                !$photosDB->isValidUsernameAlbumPair($userId, $albumId)) {
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
                    $height = $manipulator->getHeight();
                    $width = $manipulator->getWidth();
                    if ($height <= $width) {
                        $width  = round(200 / $height * $width);
                        $height = 200;
                    } else {
                        $height = round(200 / $width * $height);
                        $width = 200;
                    }
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
            $photosDB = new PhotosHelper();
            if($userId !== $_SESSION['id'] ||
                !$photosDB->isValidUsernameAlbumPair($userId, $albumId)) {
                $res->add(json_encode(array('valid' => false)));
                $res->send();
            }
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
            $photosDB = new PhotosHelper();
            $userId = $usersDB->getIdFromUsername($username);
            if($userId !== $_SESSION['id'] || 
                !$photosDB->isValidUsernameAlbumPair($userId, $albumId)) {
                $res->add(json_encode(array('valid' => false)));
                $res->send();
            }
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
            $photosDB = new PhotosHelper();
            $userId = $usersDB->getIdFromUsername($username);
            if($userId == -1 || !$photosDB->isValidUsernameAlbumPair($userId, $albumId) ||
                !$this->isVisibleAlbum($_SESSION['id'], $userId, $albumId, $photosDB, $usersDB)) {
                $res->add(json_encode(array('valid' => false, 'comments' => null)));
                $res->send();
            }
            $comments = $photosDB->getComments($albumId, $photoId);
            $json = array();
            foreach ($comments as $comment) {
                $json[] = array('content' => $comment['content'], 
                    'timestamp' => $comment['timestamp'],
                    'firstName' => $comment['first_name'], 
                    'middleName' => $comment['middle_name'], 
                    'lastName' => $comment['last_name'], 
                    'username' => $comment['login'], 
                    'profilePicture' => $comment['profilePicture'],
                    'reported' => $comment['reported'],
                    'id' => $comment['id']);
            }
            $res->add(json_encode(array('valid' => true, 'comments' => $json)));
            $res->send();
        }

        public function addComment($req, $res) {
            $username = $req->params['username'];
            $albumId = $req->params['albumId'];
            $photoId = $req->params['photoId'];
            $comment = $req->data['comment'];
            $comment = trim($comment);
            $comment = strip_tags($comment);
            if($comment == "") {
                $comment = null;
            } 
            $usersDB = new UsersHelper();
            $photosDB = new PhotosHelper();
            $friendsDB = new FriendsHelper();
            $userId = $usersDB->getIdFromUsername($username);
            if($userId == -1 || !$photosDB->isValidUsernameAlbumPair($userId, $albumId) ||
                $comment == null || 
                !$this->isVisibleAlbum($_SESSION['id'], $userId, $albumId, $photosDB, $usersDB) ||
                (!$userId == $_SESSION['id'] && !$friendsDB->isFriend($_SESSION['id'], $userId))) {
                $res->add(json_encode(array('valid' => false, 'emptyComment' => 
                    ($comment == null))));
                $res->send();
            }
            if($photosDB->addComment($_SESSION['id'], $photoId, $comment, time())) {
                $res->add(json_encode(array('valid' => true)));
                $res->send();
            } else {
                $res->add(json_encode(array('valid' => false)));
                $res->send();
            }
        }

        private function isVisibleAlbum($currentUser, $userAlbum, $albumId, $photosDB, $usersDB) {
            $friendsDB = new FriendsHelper();
            $album = $photosDB->getPhotoAlbum($userAlbum, $albumId);
            if($album == NULL) {
                return false;
            }    
            if ($currentUser == $userAlbum) {
                return true;
            }        
            $isAdmin = $usersDB->isAdmin($_SESSION['username']);
            $relationship = $friendsDB->getRelationship($currentUser, $userAlbum);
            if($relationship <= $album['privacy'] || $isAdmin) {
                return true;
            }
            return false;
        }
    }
?>
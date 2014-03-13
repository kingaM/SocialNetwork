<?php

    include_once('helpers/database/UsersHelper.php');
    include_once('helpers/database/FriendsHelper.php');
    require_once('libs/ImageManipulator.php');
    require_once('libs/FirePHPCore/FirePHP.class.php'); 

    class Profile {
        public function getProfile($req, $res) {
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
            $content = $m->render('editprofile', NULL);
            $content = $m->render('user', array('content' => $content, 'username' => $username,
                'profile' => 'active'));
            $res->add($m->render('main', array('title' => 'Profile', 'content' => $content)));
            $res->send();
        }

        public function getProfileInfo($req, $res) {
            $username = $req->params['username'];
            if ($username == "-1") {
                $username = $_SESSION['username'];
            }
            $usersDB = new UsersHelper();
            if(!$usersDB->checkUsernameExists($username)) {
                $res->add(json_encode(array('valid' => false, 'currentUser' => false, 
                    'user' => NULL)));
                $res->send();
            }
            $userId = $usersDB->getIdFromUsername($username);
            $userInfo = $usersDB->getUser($userId);
            if($userId === $_SESSION['id']) {
                $currentUser = true;
            } else {
                $currentUser = false;
            }
            $friendsDB = new FriendsHelper();
            $firephp = FirePHP::getInstance(true);
            $firephp->log($friendsDB->getRelationship($_SESSION['id'], $userId));
            if($friendsDB->getRelationship($_SESSION['id'], $userId) <= 
                $userInfo['profilePrivacy'] || $userDb->isAdmin($_SESSION['username'])) {
                $user = array('firstName' => $userInfo['first_name'],
                    'middleName' => ($userInfo['middle_name'] ? $userInfo['middle_name'] : ''),
                    'lastName' => $userInfo['last_name'],
                    'gender' => $userInfo['gender'],
                    'dob' => $userInfo['dob'],
                    'locations' => $userInfo['locations'],
                    'languages' => $userInfo['languages'],
                    'about' => $userInfo['about'],
                    'email' => $userInfo['email'],
                    'username' => $userInfo['login'],
                    'banned' => $userInfo['banned'],
                    'admin' => $userInfo['admin'],
                    'profilePicture' => $userInfo['profilePicture'],
                    'profilePrivacy' => $userInfo['profilePrivacy'],
                    'privacyOption' => $userInfo['privacyOption']);
            } else {
                $user = array('firstName' => $userInfo['first_name'],
                    'middleName' => ($userInfo['middle_name'] ? $userInfo['middle_name'] : ''),
                    'lastName' => $userInfo['last_name'],
                    'gender' => null,
                    'dob' => null,
                    'locations' => null,
                    'languages' => null,
                    'about' => null,
                    'email' => null,
                    'username' => $userInfo['login'],
                    'banned' => $userInfo['banned'],
                    'admin' => $userInfo['admin'],
                    'profilePicture' => null,
                    'profilePrivacy' => null,
                    'privacyOption' => null);
            }
            $res->add(json_encode(array('valid' => true, 'currentUser' => $currentUser,
                'user' => $user)));
                $res->send();
        }

        public function editProfileInfo($req, $res) {
            $username = $req->params['username'];
            $usersDB = new UsersHelper();
            $userId = $usersDB->getIdFromUsername($username);
            if(!$usersDB->checkUsernameExists($username) || $userId !== $_SESSION['id']) {
                $res->add(json_encode(array('valid' => false)));
                $res->send();
            }
            $data = $req->data;
            foreach ($data as $key => $value) {
                $data[$key] = trim($data[$key]);
                $data[$key] = strip_tags($data[$key]);
                if($data[$key] == "") {
                    $data[$key] = null;
                } 
            }
            $usersDB->updateProfileInfo($_SESSION['id'], $data["firstName"], 
                $data["middleName"], $data["lastName"], $data["gender"], 
                $data["dob"], $data["about"], $data["locations"], 
                $data["languages"]);
            $res->add(json_encode(array('valid' => true)));
            $res->send();
        }

        public function savePhoto($req, $res) {
            if(sizeof($_FILES) != 1) {
                $res->add(json_encode(array('valid' => false)));
                $res->send();
            }
            $username = $req->params['username'];
            $usersDB = new UsersHelper();
            $userId = $usersDB->getIdFromUsername($username);
            if ($userId == -1) {
                $res->add(json_encode(array('valid' => false, 'image' => NULL)));
                $res->send();
            }
            $result = $this->uploadImage($_FILES[0]);
            if ($result === false) {
                $res->add(json_encode(array('valid' => true, 'image_error' => true)));
                $res->send();
            } 
            if ($usersDB->updatePictureUrl($userId, $result)) {
                $res->add(json_encode(array('valid' => true, 'image_error' => false, 
                    'image' => $result)));
                $res->send();
            }
            $res->add(json_encode(array('valid' => false)));
            $res->send();
        }

        public function getPhoto($req, $res) {
            $username = $req->params['username'];
            $usersDB = new UsersHelper();
            $userId = $usersDB->getIdFromUsername($username);
            if ($userId == -1) {
                $res->add(json_encode(array('valid' => false, 'image' => NULL)));
                $res->send();
            }
            $url = $usersDB->getPictureUrl($userId);
            $res->add(json_encode(array('valid' => false, 'image' => $url)));
            $res->send();
        }

        private function uploadImage($file) {
            if ($file['error'] > 0) {
                return false;
            } else {
                $validExtensions = array('.jpg', '.jpeg', '.gif', '.png');
                $fileExtension = strrchr($file['name'], ".");
                if (in_array($fileExtension, $validExtensions)) {
                    $manipulator = new ImageManipulator($file['tmp_name']);
                    $newImage = $manipulator->resample(400, 400, false);
                    $pictureName = 'uploads/profile_pics/' . $_SESSION['id'] . 
                       $fileExtension;
                    $manipulator->save($pictureName);
                    return "/" . $pictureName;
                } else {
                    return false;
                }
            }
        }
        
    }

?>
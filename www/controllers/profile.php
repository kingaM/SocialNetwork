<?php

    include_once('helpers/database/UsersHelper.php');
    require_once('libs/FirePHPCore/FirePHP.class.php'); 
    require_once('libs/ImageManipulator.php');

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
            $res->add(json_encode(array('valid' => true, 'currentUser' => $currentUser,
                'user' => array('firstName' => $userInfo['first_name'],
                    'middleName' => ($userInfo['middle_name'] ? $userInfo['middle_name'] : ''),
                    'lastName' => $userInfo['last_name'],
                    'gender' => $userInfo['gender'],
                    'dob' => $userInfo['dob'],
                    'locations' => $userInfo['locations'],
                    'languages' => $userInfo['languages'],
                    'about' => $userInfo['about'],
                    'email' => $userInfo['email'],
                    'username' => $userInfo['login']
                    )
                )));
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
            $usersDB->updateProfileInfo($_SESSION['id'], $req->data["firstName"], 
                $req->data["middleName"], $req->data["lastName"], $req->data["gender"], 
                $req->data["dob"], $req->data["about"], $req->data["locations"], 
                $req->data["languages"]);
            $res->add(json_encode(array('valid' => true)));
            $res->send();
        }

        public function savePhoto($req, $res) {
            $firephp = FirePHP::getInstance(true);
            $firephp->log(var_dump($req));
            $firephp->log($_FILES);
            if(sizeof($_FILES) != 1) {
                $res->add(json_encode(array('valid' => false)));
                $res->send();
            }
            else {
                $result = $this->uploadImage($_FILES[0]);
                if ($result == -1) {
                    $res->add(json_encode(array('valid' => false)));
                    $res->send();
                } else if ($result == 0) {
                    $res->add(json_encode(array('valid' => true, 'succeeded' => false)));
                    $res->send();
                }
            }
            $res->add(json_encode(array('valid' => true, 'succeeded' => true)));
            $res->send();
        }

        private function uploadImage($file) {
            $firephp = FirePHP::getInstance(true);
            if ($file['error'] > 0) {
                return -1;
            } else {
                // array of valid extensions
                $validExtensions = array('.jpg', '.jpeg', '.gif', '.png');
                // get extension of the uploaded file
                $fileExtension = strrchr($file['name'], ".");
                // check if file Extension is on the list of allowed ones
                if (in_array($fileExtension, $validExtensions)) {
                    $manipulator = new ImageManipulator($file['tmp_name']);
                    // resizing to 200x200
                    $newImage = $manipulator->resample(200, 200);
                    // saving file to uploads folder
                    $firephp->log($fileExtension);
                    $manipulator->save('uploads/profile_pics/' . $_SESSION['id'] . "." . 
                       $fileExtension);
                    return 1;
                } else {
                    return 0;
                }
            }
        }
        
    }

?>
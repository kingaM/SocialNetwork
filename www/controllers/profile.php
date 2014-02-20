<?php

    include_once('helpers/database/UsersHelper.php');

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
            $res->add($m->render('main', array('title' => 'Profile', 'content' => $content)));
            $res->send();
        }

        public function getProfileInfo($req, $res) {
            $username = $req->params['username'];
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
                    'email' => $userInfo['email']
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
    }

?>
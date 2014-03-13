<?php

    include_once('helpers/database/UsersHelper.php');

    class Settings {
        public function getSettings($req, $res) {
            require_once('mustache_conf.php');
            $content = $m->render('settings', NULL);
            $res->add($m->render('main', array('title' => 'Settings', 'content' => $content)));
            $res->send();
        }

        public function updateUsername($req, $res) {
            $username = $req->data['username'];
            $password = $req->data['password'];
            $userDB = new UsersHelper();
            $valid = $userDB->verifyUser($_SESSION['username'], $password) >= 0;
            $alphaNum = ctype_alnum($username);
            if ($username == $_SESSION['username']) {
                $unique = true;
            } else {
                $unique = !$userDB->checkUsernameExists($username); 
            }
            if(!$valid || !$alphaNum || !$unique) {
                $res->add(json_encode(array('valid' => false, 'password' => $valid, 
                    'alphaNum' => $alphaNum, 'unique' => $unique, 'succeded' => false)));
                $res->send();
            }
            $valid = $userDB->updateUsername($_SESSION['id'], $username);
            if(!$valid) {
                $res->add(json_encode(array('valid' => true, 'succeded' => false)));
                $res->send();
            } else {
                $_SESSION['username'] = $username;
                $res->add(json_encode(array('valid' => true, 'succeded' => false)));
                $res->send();
            }
        }

        public function updatePassword($req, $res) {
            $newPassword = $req->data['newPassword'];
            $password = $req->data['password'];
            $userDB = new UsersHelper();
            $valid = $userDB->verifyUser($_SESSION['username'], $password) >= 0;
            if(!$valid) {
                $res->add(json_encode(array('valid' => false, 'password' => false)));
                $res->send();
            }
            $valid = $userDB->updatePassword($_SESSION['id'], $newPassword);
            if(!$valid) {
                $res->add(json_encode(array('valid' => true, 'succeded' => false)));
                $res->send();
            } else {
                $res->add(json_encode(array('valid' => true, 'succeded' => true)));
                $res->send();
            }
        }

        public function updateEmail($req, $res) {
            $email = $req->data['email'];
            $password = $req->data['password'];
            $userDB = new UsersHelper();
            $validEmail = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
            $unique = !$userDB->checkEmailExists($email); 
            $valid = $userDB->verifyUser($_SESSION['username'], $password) >= 0;
            if(!$valid || !$validEmail || !$unique) {
                $res->add(json_encode(array('valid' => false, 'password' => $valid, 
                    'validEmail' => $validEmail, 'unique' => $unique, 'succeded' => false)));
                $res->send();
            }
            $valid = $userDB->updateEmail($_SESSION['id'], $email);
            if(!$valid) {
                $res->add(json_encode(array('valid' => false, 'succeded' => false)));
                $res->send();
            } else {
                $res->add(json_encode(array('valid' => true, 'succeded' => true)));
                $res->send();
            }
        }

        public function updateProfilePrivacy($req, $res) {
            $privacy = $req->data['privacy'];
            $userDB = new UsersHelper();
            // TODO: 7 is hard coded value and assumes that there are only 6 different privacy
            // settings. This should be checked dynamically with the database.
            $valid = (filter_var($privacy, FILTER_VALIDATE_INT) !== false)
                && intval($privacy) > 0 && intval($privacy) < 7;
            if(!$valid) {
                $res->add(json_encode(array('valid' => false)));
                $res->send();
            }
            $valid = $userDB->updateProfilePrivacy($_SESSION['id'], $privacy);
            if(!$valid) {
                $res->add(json_encode(array('valid' => false)));
                $res->send();
            } else {
                $res->add(json_encode(array('valid' => true)));
                $res->send();
            }
        }
    }

?>
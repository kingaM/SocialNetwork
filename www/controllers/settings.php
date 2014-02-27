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
            $valid = $userDB->verifyUser($_SESSION['username'], $password);
            if(!$valid) {
                $res->add(json_encode(array('valid' => true, 'password' => false)));
                $res->send();
            }
            $valid = $userDB->updateUsername($_SESSION['id'], $username);
            if(!$valid) {
                $res->add(json_encode(array('valid' => false, 'password' => true)));
                $res->send();
            } else {
                $_SESSION['username'] = $username;
                $res->add(json_encode(array('valid' => true, 'password' => true)));
                $res->send();
            }
        }

        public function updatePassword($req, $res) {
            $newPassword = $req->data['newPassword'];
            $password = $req->data['password'];
            $userDB = new UsersHelper();
            $valid = $userDB->verifyUser($_SESSION['username'], $password);
            if(!$valid) {
                $res->add(json_encode(array('valid' => true, 'password' => false)));
                $res->send();
            }
            $valid = $userDB->updatePassword($_SESSION['id'], $newPassword);
            if(!$valid) {
                $res->add(json_encode(array('valid' => false, 'password' => true)));
                $res->send();
            } else {
                $res->add(json_encode(array('valid' => true, 'password' => true)));
                $res->send();
            }
        }

        public function updateEmail($req, $res) {
            $email = $req->data['email'];
            $password = $req->data['password'];
            $userDB = new UsersHelper();
            $valid = $userDB->verifyUser($_SESSION['username'], $password);
            if(!$valid) {
                $res->add(json_encode(array('valid' => true, 'password' => false)));
                $res->send();
            }
            $valid = $userDB->updateEmail($_SESSION['id'], $email);
            if(!$valid) {
                $res->add(json_encode(array('valid' => false, 'password' => true)));
                $res->send();
            } else {
                $res->add(json_encode(array('valid' => true, 'password' => true)));
                $res->send();
            }
        }
    }

?>
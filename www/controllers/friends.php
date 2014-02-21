<?php

    require_once('helpers/database/FriendsHelper.php');
    require_once('helpers/database/UsersHelper.php');

    class Friends {

        public function getPage($req, $res) {
            require_once('mustache_conf.php');
            $usersDB = new UsersHelper();
            $username = $req->params['username'];

            if(!$usersDB->checkUsernameExists($username))
                return404($res);

            if($username === $_SESSION['username']) {
                $content = $m->render('friends', array());
                $content = $m->render('user', array('content' => $content, 'username' => $username,
                    'timeline' => '', 'profile' => '', 'friends' => 'active'));
                $res->add($m->render('main', array('title' => 'Friends', 'content' => $content)));
                $res->send();
            }else {
                // $content = '<script src="/js/friends_friendsTable.js"></script>' . 
                // '<div id="current_friends"></div><script>createFriendsTable();</script>';
                $content = $m->render('friends_small', array());
                $content = $m->render('user', array('content' => $content, 'username' => $username,
                    'timeline' => '', 'profile' => '', 'friends' => 'active'));
                $res->add($m->render('main', array('title' => 'Friends', 'content' => $content)));
                $res->send();
            }
        }

        public function getFriends($req, $res) {
            $db = new FriendsHelper();

            $username = $req->params['username'];
            if($username === $_SESSION['username']) {
                $friends = $db->getFriends($username);
                $friendRequests = $db->getFriendRequests($_SESSION['id']);
                $friendsOfFriends = $db->getFriendsOfFriends($_SESSION['id']);
                $circles = $db->getCircles($_SESSION['id']);
                $friendsInfo = array(
                    'friends' => $friends, 
                    'friendRequests' => $friendRequests,
                    'friendsOfFriends' => $friendsOfFriends,
                    'circles' => $circles);
            } else {
                $friends = $db->getFriends($username);
                $friendsInfo = array('friends' => $friends);
            }

            $res->add(json_encode($friendsInfo));
            $res->send();
        }

        public function addFriend($req, $res) {
            $addUsername = $req->data['username'];
            $username = $req->params['username'];

            if($username !== $_SESSION['username'])
                return404($res);

            try {
                $db = new FriendsHelper();
                $accepted = $db->addFriend($_SESSION['id'], $addUsername);

                if($accepted) {
                    $res->add(json_encode(array('result' => 'accepted')));
                    $res->send();
                }
                $res->add(json_encode(array('result' => 'requested')));
                $res->send();
            }
            catch (Exception $e) {
                $res->add(json_encode(array('error' => $e->getMessage())));
                $res->send();
            }
        }

        public function removeFriend($req, $res) {

            $username = $req->params['username'];
            if($username !== $_SESSION['username'])
                return404($res);

            $delUsername = $req->params['login'];
            $db = new FriendsHelper();
            $db->deleteFriend($_SESSION['username'], $delUsername);
            $res->send();
        }

        public function addCircle($req, $res) {
            $circleName = $req->data['circleName'];
            try {
                $db = new FriendsHelper();
                $db->addCircle($_SESSION['id'], $circleName);
                $res->add(json_encode(array('result' => 'added')));
                $res->send();
            }
            catch (Exception $e) {
                $res->add(json_encode(array('error' => $e->getMessage())));
                $res->send();
            }
        }

        public function deleteCircle($req, $res) {
             $circleName = $req->params['circleName'];
             $db = new FriendsHelper();
             $db->deleteCircle($_SESSION['id'], $circleName);
             $res->send();
        }

        public function addToCircle($req, $res) {
            $circleName = $req->params['circleName'];
            $username = $req->data['username'];
            try {
                $db = new FriendsHelper();
                $db->addToCircle($_SESSION['id'], $circleName, $username);
                $res->add(json_encode(array('result' => 'added')));
                $res->send();
            }
            catch (Exception $e) {
                $res->add(json_encode(array('error' => $e->getMessage())));
                $res->send();
            }
        }

        private function return404($res) {
            require_once('mustache_conf.php');
            header("Content-Type: text/html;", TRUE, 404);
            $uri = $_SERVER['REQUEST_URI'];
            $content = $m->render('404', array('page' => $uri));
            $res->add($m->render('main', array('title' => '404', 'content' => $content)));
            $res->send();
            die();
        }
    }
?>

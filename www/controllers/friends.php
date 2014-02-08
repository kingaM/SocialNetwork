<?php

    require_once('helpers/database/FriendsHelper.php');

    class Friends {

        public function getPage($req, $res) {
            require_once('mustache_conf.php');
            $content = $m->render('friends', array());
            $res->add($m->render('main', array('title' => 'Friends', 'content' => $content)));
            $res->send();
        }

        public function getFriends($req, $res) {
            $db = new FriendsHelper();
            $friends = $db->getFriends($_SESSION['id']);
            $friendRequests = $db->getFriendRequests($_SESSION['id']);
            $friendsInfo = array('friends' => $friends, 'friendRequests' => $friendRequests);
            $res->add(json_encode($friendsInfo));
            $res->send();
        }

        public function addFriend($req, $res) {

            require_once('mustache_conf.php');
            $addUsername = $req->data['username'];

            try {
                $db = new FriendsHelper();
                $accepted = $db->addFriend($_SESSION['id'], $addUsername);

                if($accepted) {
                    header('Location: /friends');
                    return;
                }
                $res->send();
            }
            catch (Exception $e) {
                $res->add(json_encode(array('error' => $e->getMessage())));
                $res->send();
            }
        }

        public function removeFriend($req, $res) {
            require_once('mustache_conf.php');
            $delUsername = $req->params['login'];
            $db = new FriendsHelper();
            $db->deleteFriend($_SESSION['username'], $delUsername);
            header('Location: /friends');
        }
    }
?>

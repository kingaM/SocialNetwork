<?php

    require_once('helpers/database.php');

    class Friends {

        public function getPage($req, $res) {
            require_once('mustache_conf.php');
            $db = new FriendsTable();
            $friends = $db->getFriends($_SESSION['id']);
            $friendRequests = $db->getFriendRequests($_SESSION['id']);
            $content = $m->render('friends', array('friendsList' => $friends, 'pendingFriendsList' => $friendRequests));
            $res->add($m->render('main', array('title' => 'Friends', 'content' => $content)));
            $res->send();
        }

        public function addFriend($req, $res) {

            require_once('mustache_conf.php');
            $addUsername = $req->data['username'];

            try {
                $db = new FriendsTable();
                $accepted = $db->addFriend($_SESSION['id'], $addUsername);

                if($accepted) {
                    header('Location: /friends');
                    return;
                }

                $res->add($m->render('main', array('title' => 'Friends', 'content' => 'Friend request sent')));
                $res->send();
            }
            catch (DatabaseException $e) {
                $res->add($m->render('main', array('title' => 'Friends', 'content' => $e->getMessage())));
                $res->send();
            }
        }

        public function removeFriend($req, $res) {
            require_once('mustache_conf.php');
            $delUsername = $req->params['login'];
            $db = new FriendsTable();
            $db->deleteFriend($_SESSION['username'], $delUsername);
            header('Location: /friends');
        }
    }
?>

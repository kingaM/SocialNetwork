<?php

    require_once('helpers/database.php');

    class Friends {

        public function getPage($req, $res) {
            require_once('mustache_conf.php');
            $db = new FriendsTable();
            $friends = $db->getFriends($_SESSION['id']);
            $content = $m->render('friends', array('friendsList' => $friends));
            $res->add($m->render('main', array('title' => 'Friends', 'content' => $content)));
            $res->send();
        }

        public function addFriend($req, $res) {
            
        }
    }
?>

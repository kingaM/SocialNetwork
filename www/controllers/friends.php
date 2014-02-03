<?php

    require_once('/helpers/database.php');

    class Friends {

        private $current_user;

        public function __construct() {
            if(isset($_SESSION['id']))
                $this->current_user = $_SESSION['id'];
            else
               $this->current_user = -1;
        }

        public function getPage($req, $res) {
            require_once('mustache_conf.php');
            $db = new FriendsTable();
            $friends = $db->getFriends($this->current_user);
            $content = $m->render('friends', array('friendsList' => $friends));
            $res->add($m->render('main', array('title' => 'Friends', 'content' => $content)));
            $res->send();
        }

        public function addFriend($req, $res) {
            
        }
    }
?>

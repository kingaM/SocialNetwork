<?php
    class Friends {
        public function getPage($req, $res) {
            require_once('mustache_conf.php');
            $content = $m->render('friends', array('content' => 'content'));
            echo $m->render('main', array('title' => 'Friends', 'content' => $content));
        }
    }
?>

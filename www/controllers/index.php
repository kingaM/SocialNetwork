<?php
    class Index {
        public function getPage($req, $res) {
			require_once('mustache_conf.php');
            $content = $m->render('home', array());
            $res->add($m->render('main', array('title' => 'Home', 'content' => $content)));
            $res->send();
        }
    }
?>

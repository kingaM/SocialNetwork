<?php
    class Index {
        public function getPage($req, $res) {
			require_once('mustache_conf.php');
            $res->add($m->render('main', array('title' => 'Test')));
            $res->send();
        }
    }
?>

<?php
    class Index {
        public function getPage($req, $res) {
			require_once('mustache_conf.php');
            echo $m->render('main', array('title' => 'Test'));
        }
    }
?>

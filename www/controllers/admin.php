<?php
    
    require_once('helpers/database/UsersHelper.php');

    class Admin {

        function __construct() {
            $db = new UsersHelper();
            if(!$db->isAdmin($_SESSION['username'])) {
                header("Content-Type: text/html;", TRUE, 404);
                $uri = $_SERVER['REQUEST_URI'];
                require_once('mustache_conf.php');
                $content = $m->render('404', array('page' => $uri));
                $out = $m->render('main', array('title' => '404', 'content' => $content));
                die($out);
            }
        }

        public function getPage($req, $res) {
            require_once('mustache_conf.php');
            $content = $m->render('admin', array());
            $res->add($m->render('main', array('title' => 'Admin', 'content' => $content)));
            $res->send();
        }
    }
?>

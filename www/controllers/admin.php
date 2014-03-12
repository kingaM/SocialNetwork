<?php
    
    require_once('helpers/database/UsersHelper.php');
    require_once('helpers/database/AdminHelper.php');

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

        public function deleteUser($req, $res) {
            $username = $req->params['username'];
            $db = new UsersHelper();
            $db->deleteUser($username);
            $res->send();
        }

        public function banUser($req, $res) {
            $username = $req->params['username'];
            $db = new UsersHelper();
            $db->banUser($username);
            $res->send();
        }

        public function unbanUser($req, $res) {
            $username = $req->params['username'];
            $db = new UsersHelper();
            $db->unbanUser($username);
            $res->send();
        }

        public function getReportedComments($req, $res) {
            $db = new AdminHelper();
            $comments = $db->getReportedComments();
            $res->add(json_encode($comments));
            $res->send();
        }

        public function ignoreReport($req, $res) {
            $id = $req->params['id'];
            $db = new AdminHelper();
            $db->ignoreReport($id);
            $res->send();
        }

        public function deleteComment($req, $res) {
            $id = $req->params['id'];
            $db = new AdminHelper();
            $db->deleteComment($id);
            $res->send();
        }
    }

    class Report {

        public function reportComment($req, $res) {
            $id = $req->params['id'];
            $db = new AdminHelper();
            $db->reportComment($id);
            $res->send();
        }

    }
?>

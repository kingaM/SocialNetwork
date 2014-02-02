<?php

	include_once('helpers/database.php');

    class Login {
        public function getPage($req, $res) {
        	require_once('mustache_conf.php');
            $content = $m->render('login', NULL);
            $res->add($m->render('main', array('title' => 'Login', 'content' => $content)));
            $res->send();
        }

        public function verifyUser($req, $res) {
        	$username = $req->data['username'];
        	$password = $req->data['password'];

        	$id = UsersTable::verifyUser($username, $password);

        	if($id != -1) {
        		$_SESSION['username'] = $username;
        		$_SESSION['id'] = $id;
        		header('Location: /');
        	} else {
        		header('Location: /login');
        	}
        } 

        public function logout($req, $res) {
        	unset($_SESSION['username']);
        	unset($_SESSION['id']);
        	header('Location: /login');
        }
    }
?>
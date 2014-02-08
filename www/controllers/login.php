<?php

	include_once('helpers/database/UsersHelper.php');

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
            $db = new UsersHelper();
        	$id = $db->verifyUser($username, $password);

        	if($id != -1) {
        		$_SESSION['username'] = $username;
        		$_SESSION['id'] = $id;
                $res->add(json_encode(array('valid' => true)));
        	} else {
                $res->add(json_encode(array('valid' => false)));
        	}
            $res->send();  
        } 

        public function logout($req, $res) {
        	unset($_SESSION['username']);
        	unset($_SESSION['id']);
        	header('Location: /login');
        }
    }
?>
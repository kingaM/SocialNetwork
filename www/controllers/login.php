<?php

	include_once('helpers/database/UsersHelper.php');
    require_once('libs/FirePHPCore/FirePHP.class.php');

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

                if($db->isBanned($username)) {
                    $message = "Your account has been banned.";
                    $res->add(json_encode(array('valid' => false, 'match' => true, 'ban' => true)));
                    $res->send(); 
                }

        		$_SESSION['username'] = $username;
        		$_SESSION['id'] = $id;
                $res->add(json_encode(array('valid' => true)));
        	} else {
                $res->add(json_encode(array('valid' => false, 'match' => false, 'ban' => false)));
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

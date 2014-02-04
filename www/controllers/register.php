<?php

	include_once('helpers/database.php');

    class Register {
        public function getPage($req, $res) {
        	$res->add($this->renderTemplate());
            $res->send();
        }

        private function renderTemplate($info = '') {
            require_once('mustache_conf.php');
            $content = $m->render('register', array('info' => $info));
            return $m->render('main', array('title' => 'Register', 'content' => $content));        
        }

        public function addUser($req, $res) {

            $errorMessage = '';

        	$username = $req->data['username'];
        	$password = $req->data['password'];
            $firstname = $req->data['firstname'];
            $middlename = $req->data['middlename'];
            $lastname = $req->data['lastname'];
            $passwordRetype = $req->data['password_retype'];
            $emailRetype = $req->data['email_retype'];
            $email = $req->data['email'];

            if (empty($username) || empty($password) || empty($firstname) || empty($lastname) ||
                empty($email) || empty($passwordRetype) || empty($emailRetype)) {
                $errorMessage .= "Complete the missing required fields. <br>";
            }
            if ($password != $passwordRetype) {
                $errorMessage .= "The passwords do not match <br>";
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errorMessage .= "The e-mail is not valid. <br>";
            }
            if ($email != $emailRetype) {
                $errorMessage .= "The e-mails do not match <br>";
            }
            if (UsersTable::checkUsernameExists($username)) {
                $errorMessage .= "The username already exists. <br>";
            }

            if(!empty($errorMessage)) {
                // TODO: Once we can, dynamically change the content of the page to display errors
                // For now re-loading mustache, which clears all the data from the form
                $res->add($this->renderTemplate($errorMessage));
                $res->send();
            } else {
                if(empty($middlename)) {
                    $middlename = NULL;
                }
                $result = UsersTable::addUser($username, $password, $firstname, $lastname, $email, 
                    $middlename);
                if ($result < 0) {
                    $errorMessage = "Something went wrong. <br>";
                    // TODO: Once we can, dynamically change the content of the page to display errors
                    // For now re-loading mustache, which clears all the data from the form
                    $res->add($this->renderTemplate($errorMessage));
                    $res->send();
                } else {
                    header('Location: /');
                }
            }
        } 
    }
?>
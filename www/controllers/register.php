<?php

	include_once('helpers/database/UsersHelper.php');
    include_once('helpers/mail.php');

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

            $data = $req->data;
            foreach ($data as $key => $value) {
                $data[$key] = trim($data[$key]);
                $data[$key] = strip_tags($data[$key]);
                if($data[$key] == "") {
                    $data[$key] = null;
                } 
            }

        	$username = $req->data['username'];
        	$password = $req->data['password'];
            $firstname = $data['firstname'];
            $middlename = $data['middlename'];
            $lastname = $data['lastname'];
            $email = $req->data['email'];
            $json = array();

            if (empty($username) || empty($password) || empty($firstname) || empty($lastname) ||
                empty($email)) {
                $json["empty"] = True;
            }
            $userDB = new UsersHelper();
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $json["email-valid"] = False;
            } else if ($userDB->checkEmailExists($email)) {
                $json["email-unique"] = False;
            }
            if (!ctype_alnum($username)) {
                $json["username-valid"] = True;
            } else if($userDB->checkUsernameExists($username)) {
                $json["username-unique"] = False;
            }
            if(!empty($json)) {
                $res->add(json_encode(array('valid' => false, 'errors' => $json)));
                $res->send();
            } else {
                if(empty($middlename)) {
                    $middlename = NULL;
                }
                $hash = $email.time();
                $result = $userDB->addUser($username, $password, $firstname, $lastname, $email, 
                    $hash, 0, $middlename);
                if ($result < 0) {
                    $res->add(json_encode(array('valid' => true, 'suceeded' => false)));
                    $res->send();
                } else {
                    if($this->sendVerificationEmail($firstname, $email, $hash)) {
                        $res->add(json_encode(array('valid' => true, 'suceeded' => true)));
                        $res->send();
                    } else {
                        $res->add(json_encode(array('valid' => true, 'suceeded' => false)));
                        $res->send();
                    }
                        
                }
            }
        }

        public function activate($req, $res) {
            $hash = $req->params['hash'];
            $db = new UsersHelper();
            $authenticated = $db->checkIfAuthenticated($hash);
            if ($authenticated == -1) {
                echo 'The user does not exist';
            } else if ($authenticated == 1) {
                header('Location: /login');
            } else if ($authenticated == 0) {
                $db->updateAuthenticated($hash);
                header('Location: /login');
            } else {
                echo 'Something went wrong. Try again later.';
            }
        }

        private function sendVerificationEmail($firstname, $email, $hash) {
            $activationURL = $_SERVER['SERVER_NAME'] . '/activate/' . sha1($hash) . '/';  
            $subject = 'SocialNetwork Activation';
            $body = 'Hi ' . $firstname . ",<br>" .
                'Thank you for registering with us. To complete the registration please go to ' .
                "<a href=\"". $activationURL . "\">" . $activationURL . "</a> <br>" .
                "Thanks, <br> The SocialNetwork Team";
            return Mail::sendMail($email, $subject, $body);
        } 

    }
?>
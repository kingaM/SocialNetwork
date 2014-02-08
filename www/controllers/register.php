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

            if(!empty($errorMessage)) {
                // TODO: Once we can, dynamically change the content of the page to display errors
                // For now re-loading mustache, which clears all the data from the form
                $res->add($this->renderTemplate($errorMessage));
                $res->send();
            } else {
                if(empty($middlename)) {
                    $middlename = NULL;
                }
                $hash = $email.time();
                $db = new UsersHelper();
                $result = $db->addUser($username, $password, $firstname, $lastname, $email, 
                    $hash, 0, $middlename);
                if ($result < 0) {
                    // TODO: Add a more fine-grained error message
                    $errorMessage = "Something is not unique. <br>";
                    // TODO: Once we can, dynamically change the content of the page to display errors
                    // For now re-loading mustache, which clears all the data from the form
                    $res->add($this->renderTemplate($errorMessage));
                    $res->send();
                } else {
                    if($this->sendVerificationEmail($firstname, $email, $hash))
                        echo "An e-mail has been sent to the e-mail address given." .
                            " Please activate it with the URL provided in the e-mail.";
                    else
                        echo "Something went wrong. Please try again.";
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
                echo 'You have already authenticated, now you can login.';
            } else if ($authenticated == 0) {
                $db->updateAuthenticated($hash);
                header('Location: /login');
            } else {
                echo 'Something went massively wrong';
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
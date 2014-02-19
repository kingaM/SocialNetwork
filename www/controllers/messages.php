<?php

    include_once('helpers/database/MessagesHelper.php');
    include_once('helpers/database/UsersHelper.php');
    include_once('helpers/database/FriendsHelper.php');

    class Messages {
        public function getPage($req, $res) {
            require_once('mustache_conf.php');
            $content = $m->render('messages', NULL);
            $res->add($m->render('main', array('title' => 'Messages', 'content' => $content)));
            $res->send();
        }

        public function getReciepients($req, $res) {
            $db = new MessagesHelper();
            $result = $db->getReciepients($_SESSION['id']);
            $json = array("reciepients" => array());
            foreach ($result as $r) {
                $json["reciepients"][] = array(
                    'username' => $r['login'], 
                    'name' => $r['name'],
                    'message' => $r['content'], 
                    'timestamp' => $r['timestamp']);
            }
            $res->add(json_encode($json));
            $res->send();
        }

        public function getMessages($req, $res) {
            $messagesDB = new MessagesHelper();
            $usersDB = new UsersHelper();
            $friendsDB = new FriendsHelper();
            $username = $req->params['username'];
            $pos = strpos($username,'_');
            if( $pos === false) {
                $id = $usersDB->getIdFromUsername($username);
                $result = $messagesDB->getMessagesUser($_SESSION['id'], $id);
            } else {
                $circle = explode('_', $username);
                $owner = $circle[0];
                $name = $circle[1];
                $id = $friendsDB->getCircleId($owner, $name);
                $result = $messagesDB->getMessagesCircle($_SESSION['id'], $id);
            }
            
            $json = array("messages" => array());
            foreach ($result as $r) {
                $json["messages"][] = array('message' => $r['content'], 'from' => $r['from'], 
                    'timestamp' => $r['timestamp'],
                    'firstName' => $r['first_name'], 
                    'middleName' => ($r['middle_name'] ? $r['middle_name'] : ''),
                    'lastName' => $r['last_name']);
            }
            $res->add(json_encode($json));
            $res->send();
        }

        private function addMessageUser($username, $messageText, $res) {
            $usersDB = new UsersHelper();
            if(!$usersDB->checkUsernameExists($username)) {
                $res->add(json_encode(array('valid' => 0, 'friend' => 0)));
                $res->send();
            }
            $messagesDB = new MessagesHelper();
            $friendsDB = new FriendsHelper();
            $reciepientId = $usersDB->getIdFromUsername($username);
            if(!$friendsDB->isFriend($reciepientId, $_SESSION['id'])) {
                $res->add(json_encode(array('valid' => 1, 'friend' => 0)));
                $res->send();
            }
            
            $result = $messagesDB->addMessage($_SESSION['id'], 
                $reciepientId, NULL, $messageText, time());
            $res->add(json_encode(array('valid' => 1, 'friend' => 1)));
            $res->send();
        }

        private function addMessageCircle($circleName, $ownerId, $messageText, $res) {
            $messagesDB = new MessagesHelper();
            $friendsDB = new FriendsHelper();
            $circleId = $friendsDB->getCircleId($ownerId, $circleName);
            if($circleId == -1) {
                $res->add(json_encode(array('valid' => 0)));
                $res->send();
            }
            
            $result = $messagesDB->addMessage($ownerId, 
                NULL, $circleId, $messageText, time());
            $res->add(json_encode(array('valid' => 1)));
            $res->send();
        }

        public function addMessage($req, $res) {
            $username = $req->params['username'];
            $pos = strpos($username,'_');
            if( $pos === false) {
                $this->addMessageUser($username, $req->data["messageText"], $res);
            } else {
                $circle = explode('_', $username);
                $owner = $circle[0];
                $name = $circle[1];
                $this->addMessageCircle($name, $owner, $req->data["messageText"], $res);
            }
            
        }

        public function addCircleMessage($req, $res) {
            $circleName = $req->params['circleName'];
            $this->addMessageCircle($circleName, $_SESSION['id'], $req->data["messageText"], $res);
        }
    }
?>
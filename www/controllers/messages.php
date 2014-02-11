<?php

    include_once('helpers/database/MessagesHelper.php');
    include_once('helpers/database/UsersHelper.php');

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
                $json["reciepients"][] = array('username' => $r['login'], 
                    'firstName' => $r['first_name'], 
                    'middleName' => ($r['middle_name'] ? $r['middle_name'] : ''),
                    'lastName' => $r['last_name'], 'message' => $r['content'], 
                    'timestamp' => $r['timestamp']);
            }
            $res->add(json_encode($json));
            $res->send();
        }

        public function getMessages($req, $res) {
            $messagesDB = new MessagesHelper();
            $usersDB = new UsersHelper();
            $username = $req->params['username'];
            $id = $usersDB->getIdFromUsername($username);
            $result = $messagesDB->getMessages($_SESSION['id'], $id);
            $json = array("messages" => array());
            foreach ($result as $r) {
                $json["messages"][] = array('message' => $r['content'], 'from' => $r['from'], 
                    'to' => $r['to'], 'timestamp' => $r['timestamp'],
                    'firstName' => $r['first_name'], 
                    'middleName' => ($r['middle_name'] ? $r['middle_name'] : ''),
                    'lastName' => $r['last_name']);
            }
            $res->add(json_encode($json));
            $res->send();
        }

        public function addMessage($req, $res) {
            $username = $req->params['username'];
            $messagesDB = new MessagesHelper();
            $usersDB = new UsersHelper();
            $result = $messagesDB->addMessage($_SESSION['id'], 
                $usersDB->getIdFromUsername($username), $req->data["messageText"], time());
            $res->send();
        }
    }
?>
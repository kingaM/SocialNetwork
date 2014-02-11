<?php

    include_once('helpers/database/MessagesHelper.php');
    include_once('helpers/database/UsersHelper.php');
    require_once('libs/FirePHPCore/FirePHP.class.php');

    class Messages {
        public function getPage($req, $res) {
            require_once('mustache_conf.php');
            $content = $m->render('messages', NULL);
            $res->add($m->render('main', array('title' => 'Messages', 'content' => $content)));
            $res->send();
        }

        public function getReciepients($req, $res) {
            $firephp = FirePHP::getInstance(true); 
            $firephp->log("In getReciepients PHP", 'PHP');
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
            $firephp->log("test", 'PHP');
            $firephp->log($result, 'PHP');
            $res->add(json_encode($json));
            $res->send();
        }

        public function getMessages($req, $res) {
            $firephp = FirePHP::getInstance(true); 
            $firephp->log("in get messages", 'Messages');
            $messagesDB = new MessagesHelper();
            $usersDB = new UsersHelper();
            $username = $req->params['username'];
            $id = $usersDB->getIdFromUsername($username);
            $firephp->log($id, 'Messages');
            $result = $messagesDB->getMessages($_SESSION['id'], $id);
            $json = array("messages" => array());
            foreach ($result as $r) {
                $json["messages"][] = array('message' => $r['content'], 'from' => $r['from'], 
                    'to' => $r['to'], 'timestamp' => $r['timestamp']);
            }
            $res->add(json_encode($json));
            $res->send();
        }
    }
?>
<?php
    
    require_once('helpers/database/TimelineHelper.php');

    class Timeline {
        public function getPage($req, $res) {
            $username = $req->params['username'];
            require_once('mustache_conf.php');
            $content = $m->render('timeline', array());
            $content = $m->render('user', array('content' => $content, 'username' => $username,
                    'timeline' => 'active', 'profile' => '', 'friends' => ''));
            $res->add($m->render('main', array('title' => $username, 'content' => $content)));
            $res->send();
        }

        public function addPost($req, $res) {
            $to = $req->params['username'];
            $from = $_SESSION['id']; // Quicker to use the id
            $content = $req->data['content'];
            $db = new TimelineHelper();
            try {
                $db->addPost($to, $from, $content);
                $res->add(json_encode(array('result' => 'added')));
                $res->send();
            } catch (Exception $e) {
                $res->add(json_encode(array('error' => $e->getMessage())));
                $res->send();
            }
        }

        public function getPosts($req, $res) {
            $username = $req->params['username'];
            $db = new TimelineHelper();
            $posts = $db->getPosts($username);
            $posts = array('posts' => $posts);
            $res->add(json_encode($posts));
            $res->send();
        }

        public function addComment($req, $res) {
            $from = $_SESSION['id'];
            $postID = $req->params['postID'];
            $content = $req->data['content'];
            $db = new TimelineHelper();
            try {
                $db->addComment($postID, $from, $content);
                $res->add(json_encode(array('result' => 'added')));
                $res->send();
            } catch (Exception $e) {
                $res->add(json_encode(array('error' => $e->getMessage())));
                $res->send();
            }
        }
    }
?>

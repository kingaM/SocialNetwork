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
            $from = $_SESSION['username'];
            $content = htmlspecialchars($req->data['content']);
            $db = new TimelineHelper();
            try {
                $db->addPost($to, $from, $content, "post");
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
            $content = htmlspecialchars($req->data['content']);
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

        public function getNewsFeed($req, $res) {
            $user = $_SESSION['username'];
            $db = new TimelineHelper();
            $posts = $db->getNewsFeed($user);
            $posts = array('posts' => $posts);
            $res->add(json_encode($posts));
            $res->send();
        }

        public function changePrivacy($req, $res) {
            $postID = $req->params['postID'];
            $privacy = $req->data['privacyLevel'];
            if($privacy > 5 || $privacy < 0)
                return;
            $db = new TimelineHelper();
            // Note, check for ability to change this is done in the DB method
            $db->changePrivacy($postID, $privacy);
        }
    }
?>

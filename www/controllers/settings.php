<?php

    include_once('helpers/database/UsersHelper.php');
    include_once('helpers/database/MessagesHelper.php');
    include_once('helpers/database/FriendsHelper.php');
    include_once('helpers/database/TimelineHelper.php');
    include_once('helpers/database/BlogsHelper.php');

    class Settings {
        public function getSettings($req, $res) {
            require_once('mustache_conf.php');
            $content = $m->render('settings', NULL);
            $res->add($m->render('main', array('title' => 'Settings', 'content' => $content)));
            $res->send();
        }

        public function updateUsername($req, $res) {
            $username = $req->data['username'];
            $password = $req->data['password'];
            $userDB = new UsersHelper();
            $valid = $userDB->verifyUser($_SESSION['username'], $password) >= 0;
            $alphaNum = ctype_alnum($username);
            if ($username == $_SESSION['username']) {
                $unique = true;
            } else {
                $unique = !$userDB->checkUsernameExists($username); 
            }
            if(!$valid || !$alphaNum || !$unique) {
                $res->add(json_encode(array('valid' => false, 'password' => $valid, 
                    'alphaNum' => $alphaNum, 'unique' => $unique, 'succeded' => false)));
                $res->send();
            }
            $valid = $userDB->updateUsername($_SESSION['id'], $username);
            if(!$valid) {
                $res->add(json_encode(array('valid' => true, 'succeded' => false)));
                $res->send();
            } else {
                $_SESSION['username'] = $username;
                $res->add(json_encode(array('valid' => true, 'succeded' => true)));
                $res->send();
            }
        }

        public function updatePassword($req, $res) {
            $newPassword = $req->data['newPassword'];
            $password = $req->data['password'];
            $userDB = new UsersHelper();
            $valid = $userDB->verifyUser($_SESSION['username'], $password) >= 0;
            if(!$valid) {
                $res->add(json_encode(array('valid' => false, 'password' => false)));
                $res->send();
            }
            $valid = $userDB->updatePassword($_SESSION['id'], $newPassword);
            if(!$valid) {
                $res->add(json_encode(array('valid' => true, 'succeded' => false)));
                $res->send();
            } else {
                $res->add(json_encode(array('valid' => true, 'succeded' => true)));
                $res->send();
            }
        }

        public function updateEmail($req, $res) {
            $email = $req->data['email'];
            $password = $req->data['password'];
            $userDB = new UsersHelper();
            $validEmail = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
            $unique = !$userDB->checkEmailExists($email); 
            $valid = $userDB->verifyUser($_SESSION['username'], $password) >= 0;
            if(!$valid || !$validEmail || !$unique) {
                $res->add(json_encode(array('valid' => false, 'password' => $valid, 
                    'validEmail' => $validEmail, 'unique' => $unique, 'succeded' => false)));
                $res->send();
            }
            $valid = $userDB->updateEmail($_SESSION['id'], $email);
            if(!$valid) {
                $res->add(json_encode(array('valid' => false, 'succeded' => false)));
                $res->send();
            } else {
                $res->add(json_encode(array('valid' => true, 'succeded' => true)));
                $res->send();
            }
        }

        public function exportData($req, $res) {
            $info = array(
                'profile' => $this->exportProfile(),
                'friends' => $this->exportFriends(),
                'messages' => $this->exportMessages(),
                'timeline' => $this->exportTimeline(),
                'blogs' => $this->exportBlogs(),
                );

            $xml = new SimpleXMLElement('<data/>');
            $node = $xml->addChild($_SESSION['username']);
            $this->array_to_xml($info, $node);
            header('Content-type: text/xml');
            echo $xml->asXML();
        }

        // source: http://stackoverflow.com/a/17430859/1276327
        private function array_to_xml($array, &$xml) {
            foreach($array as $key => $value) {
                if(is_array($value)) {
                    if(!is_numeric($key)){
                        $subnode = $xml->addChild("$key");
                        $this->array_to_xml($value, $subnode);
                    } else {
                        $this->array_to_xml($value, $xml);
                    }
                } else {
                    $xml->addChild("$key","$value");
                }
            }
        }

        private function exportProfile() {
            $id = $_SESSION['id'];
            $db = new UsersHelper();
            $profile = $db->getUser($id);
            $profile = array('firstName' => $profile['first_name'],
                        'middleName' => ($profile['middle_name'] ? $profile['middle_name'] : ''),
                        'lastName' => $profile['last_name'],
                        'gender' => $profile['gender'],
                        'dob' => $profile['dob'],
                        'locations' => $profile['locations'],
                        'languages' => $profile['languages'],
                        'about' => $profile['about'],
                        'email' => $profile['email'],
                        'username' => $profile['login'],
                    );
            return $profile;
        }

        private function exportFriends() {
            $username = $_SESSION['username'];
            $db = new FriendsHelper();
            $xmlFriends = array();
            $friends = $db->getFriends($username);
            foreach ($friends as $friend) {
               $xmlFriends[] = array('friend' => $friend);
            }
            return $xmlFriends;
        }

        private function exportMessages() {
            $id = $_SESSION['id'];
            $db = new MessagesHelper();
            $xmlMessages = array();
            $conversations = $db->getReciepients($id);
            foreach ($conversations as $conversation) {

                $conversation = array(
                    'name' => $conversation['name'],
                    'login' => $conversation['login'],
                    'id' => $conversation['id'],
                    'messages' => array(),
                    );

                if(strpos($conversation['name'],'Circle:') !== false)
                    $messages = $db->getMessagesCircle($id, $conversation['id']);
                else
                    $messages = $db->getMessagesUser($id, $conversation['id']);

                foreach ($messages as $message) {
                    $message = array(
                        'message' => $message['content'], 
                        'from' => $message['from'], 
                        'timestamp' => $message['timestamp'],
                        );
                    $conversation['messages'][] = $message;
                }

                $xmlMessages[] = array('conversation' => $conversation);
            }
            return $xmlMessages;
        }

        private function exportTimeLine() {
            $username = $_SESSION['username'];
            $db = new TimelineHelper();
            $posts = $db->getPosts($username);
            $xmlPosts = array();
            foreach ($posts as $post) {
                $xmlComments = array();
                $comments = $post['comments'];
                foreach ($comments as $comment) {
                    $xmlComments[] = array('comment' => $comment);
                }
                $post['comments'] = $xmlComments;
                $xmlPosts[] = array('post' => $post);
            }
            return $xmlPosts;
        }

        private function exportBlogs() {
            $xmlBlogs = array();
            $id = $_SESSION['id'];
            $db = new BlogsHelper();
            $blogs = $db->getBlogs($id);

            foreach ($blogs as $blog) {
                $blog = array(
                    'name' => $blog['name'],
                    'about' => $blog['about'],
                    'url' => $blog['url'],
                    );

                $blogPosts = array();
                $posts = $db->getAllBlogPosts($id, $blog['url']);
                foreach ($posts as $post) {
                    $post = array(
                        'postId' => $post['postId'],
                        'title' => $post['title'],
                        'timestamp' => $post['timestamp'],
                        'content' => $post['content'],
                        );
                    $blogPosts[] = array('blogPost' => $post);
                }

                $blog['blogPosts'] = $blogPosts;
                $xmlBlogs[] = array('blog' => $blog);
            }
            return $xmlBlogs;
        }

        public function updateProfilePrivacy($req, $res) {
            $privacy = $req->data['privacy'];
            $userDB = new UsersHelper();
            $valid = (filter_var($privacy, FILTER_VALIDATE_INT) !== false)
                && intval($privacy) > 0 && intval($privacy) < 7;
            if(!$valid) {
                $res->add(json_encode(array('valid' => false)));
                $res->send();
            }
            $valid = $userDB->updateProfilePrivacy($_SESSION['id'], $privacy);
            if(!$valid) {
                $res->add(json_encode(array('valid' => false)));
                $res->send();
            } else {
                $res->add(json_encode(array('valid' => true)));
                $res->send();
            }
        }
    }

?>
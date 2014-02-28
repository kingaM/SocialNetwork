<?php

    require_once('helpers/database/database.php');

    class TimelineHelper {

        private $db;

        public function __construct() {
            $this->db = new DatabaseHelper();
        }

        /**
         * Adds a wall post
         *
         * @param String $to The username of the wall owner
         * @param int $from The id of the sender
         * @param String $content The content of the post
         *
         */
        public function addPost($to, $from, $content) {

            if(strlen($content) < 1)
                throw new Exception("Can't make an empty post");
            if(strlen($content) > 10000)
                throw new Exception("Content too long - 10,000 character limit");

            $result = $this->db->execute("INSERT INTO wall_posts(`to`, `from`, content, `timestamp`)
                SELECT ID as `to`, :fromUser, :content, :time 
                FROM users WHERE login=:toUser",
                Array(':toUser' => $to, ':fromUser' => $from, ':content' => $content, 
                    ':time' => time()));
        }

        /**
         * Gets wall posts
         *
         * @param String $username The username of the wall owner
         *
         */
        public function getPosts($username) {

            // TODO: Find a nice way to do all of this in 1 statement
            $posts = array();
            $postIDs = array();

            $result = $this->db->fetch("SELECT wp.id, wp.`content`, wp.`timestamp`,
                (CASE WHEN toUser.middle_name IS NULL THEN 
                        CONCAT(toUser.first_name, ' ', toUser.last_name) 
                    ELSE 
                        CONCAT(toUser.first_name, ' ', toUser.middle_name, ' ', toUser.last_name) 
                END) as toName,
                (CASE WHEN fromUser.middle_name IS NULL THEN 
                        CONCAT(fromUser.first_name, ' ', fromUser.last_name) 
                    ELSE 
                        CONCAT(fromUser.first_name, ' ', fromUser.middle_name, ' ', fromUser.last_name) 
                END) as fromName,
                toUser.login as toLogin, fromUser.login as fromLogin
                FROM wall_posts as wp, users as toUser, users as fromUser
                WHERE wp.to=toUser.id AND wp.from=fromUser.id
                AND toUser.login=:username
                ORDER BY `timestamp` DESC",
                Array(':username' => $username));

            foreach ($result as $r) {
                $post = array(  'id' => $r['id'],
                                'to' => $r['toLogin'],
                                'from' => $r['fromLogin'],
                                'toName' => $r['toName'],
                                'fromName' => $r['fromName'],
                                'content' => $r['content'],
                                'timestamp' => $r['timestamp'],
                                'comments' => array());
                $posts[] = $post;
                $postIDs[] = $r['id'];
            }

            if(count($postIDs) === 0)
                return $posts;

            $inSet = implode(",", $postIDs);

            $result = $this->db->fetch("SELECT c.id, c.wall_post, c.content, c.timestamp, u.login,
                (CASE WHEN u.middle_name IS NULL THEN 
                        CONCAT(u.first_name, ' ', u.last_name) 
                    ELSE 
                        CONCAT(u.first_name, ' ', u.middle_name, ' ', u.last_name) 
                END) as fromName
                FROM comments as c, users as u
                WHERE u.ID=c.from AND wall_post IN ($inSet)",
                Array());

            foreach ($result as $r) {
                $wallPostID = $r['wall_post'];
                $comment = array(   'id' => $r['id'],
                                    'content' => $r['content'],
                                    'timestamp' => $r['timestamp'],
                                    'login' => $r['login'],
                                    'fromName' => $r['fromName']);
                foreach ($posts as &$post) {
                    if($post['id'] === $wallPostID) {
                        $comments = &$post['comments'];
                        $comments[] = $comment;
                        break;
                    }
                }
            }

            return $posts;
        }

        /**
         * Adds a comment to a wall post
         *
         * @param int $postID The ID of the wall post to comment on
         * @param int $from The id of the sender
         * @param String $content The content of the comment
         *
         */
        public function addComment($postID, $from, $content) {

            if(strlen($content) < 1)
                throw new Exception("Can't make an empty comment");
            if(strlen($content) > 10000)
                throw new Exception("Content too long - 10,000 character limit");

            $result = $this->db->execute("INSERT INTO comments(`from`, wall_post, content, 
                `timestamp`)
                VALUES(:fromUser, :postID, :content, :time)",
                Array(':fromUser' => $from, ':postID' => $postID, ':content' => $content, 
                    ':time' => time()));
        }

        /**
         * Gets wall posts of a user's friends
         *
         * @param String $user The user to get the newsfeed for
         *
         */
        public function getNewsFeed($user) {
            // TODO: Fix this terrible efficiency!
            require_once('helpers/database/FriendsHelper.php');
            $fh = new FriendsHelper();
            $friends = $fh->getFriends($user);
            
            $friendLogins = array();
            $posts = array();
            $postIDs = array();

            foreach ($friends as $friend) {
                $friendLogins[] = "'" . $friend['login'] . "'";
            }

            $inSet = implode(",", $friendLogins);

            $result = $this->db->fetch("SELECT wp.id, wp.`content`, wp.`timestamp`,
                (CASE WHEN toUser.middle_name IS NULL THEN 
                        CONCAT(toUser.first_name, ' ', toUser.last_name) 
                    ELSE 
                        CONCAT(toUser.first_name, ' ', toUser.middle_name, ' ', toUser.last_name) 
                END) as toName,
                (CASE WHEN fromUser.middle_name IS NULL THEN 
                        CONCAT(fromUser.first_name, ' ', fromUser.last_name) 
                    ELSE 
                        CONCAT(fromUser.first_name, ' ', fromUser.middle_name, ' ', fromUser.last_name) 
                END) as fromName,
                toUser.login as toLogin, fromUser.login as fromLogin
                FROM wall_posts as wp, users as toUser, users as fromUser
                WHERE wp.to=toUser.id AND wp.from=fromUser.id
                AND toUser.login IN ($inSet)
                ORDER BY `timestamp` DESC",
                Array());

            foreach ($result as $r) {
                $post = array(  'id' => $r['id'],
                                'to' => $r['toLogin'],
                                'from' => $r['fromLogin'],
                                'toName' => $r['toName'],
                                'fromName' => $r['fromName'],
                                'content' => $r['content'],
                                'timestamp' => $r['timestamp'],
                                'comments' => array());
                $posts[] = $post;
                $postIDs[] = $r['id'];
            }

            if(count($postIDs) === 0)
                return $posts;

            $inSet = implode(",", $postIDs);

            $result = $this->db->fetch("SELECT c.id, c.wall_post, c.content, c.timestamp, u.login,
                (CASE WHEN u.middle_name IS NULL THEN 
                        CONCAT(u.first_name, ' ', u.last_name) 
                    ELSE 
                        CONCAT(u.first_name, ' ', u.middle_name, ' ', u.last_name) 
                END) as fromName
                FROM comments as c, users as u
                WHERE u.ID=c.from AND wall_post IN ($inSet)",
                Array());

            foreach ($result as $r) {
                $wallPostID = $r['wall_post'];
                $comment = array(   'id' => $r['id'],
                                    'content' => $r['content'],
                                    'timestamp' => $r['timestamp'],
                                    'login' => $r['login'],
                                    'fromName' => $r['fromName']);
                foreach ($posts as &$post) {
                    if($post['id'] === $wallPostID) {
                        $comments = &$post['comments'];
                        $comments[] = $comment;
                        break;
                    }
                }
            }

            return $posts;
        }

    }

?>

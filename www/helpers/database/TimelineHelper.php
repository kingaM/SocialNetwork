<?php

    require_once('helpers/database/database.php');

    /**
     * A helper that has database functions related to the Timeline and News Feed feature of the 
     * site. 
     */
    class TimelineHelper {

        private $db;

        public function __construct() {
            $this->db = new DatabaseHelper();
        }

        /**
         * Adds a wall post
         *
         * @param String $to The username of the wall owner
         * @param String $from The id of the sender
         * @param String $content The content of the post
         * @param String $type The type of activity: post/friend/birthday/blog
         *
         */
        public function addPost($to, $from, $content, $type) {

            if(strlen($content) < 1)
                throw new Exception("Can't make an empty post");
            if(strlen($content) > 10000)
                throw new Exception("Content too long - 10,000 character limit");

            $result = $this->db->execute("INSERT INTO wall_posts(`to`, `from`, content, `timestamp`, 
                `type`, `lastTouched`, `privacy`)
                SELECT toUser.ID as `to`, fromUser.ID as `from`, :content, :time, :type, :time, 
                toUser.wallPrivacy 
                FROM users as toUser, users as fromUser 
                WHERE toUser.login=:toUser AND fromUser.login=:fromUser",
                Array(':toUser' => $to, ':fromUser' => $from, ':content' => $content, 
                    ':time' => time(), ':type' => $type));
        }

        /**
         * Changes privacy of a wall post
         *
         * @param String $postID
         * @param String $privacy The enum of the new privacy level
         *
         */
        public function changePrivacy($postID, $privacy) {

            $result = $this->db->fetch("SELECT `to` FROM wall_posts WHERE id=:postID", 
                Array(':postID' => $postID));

            if(sizeof($result) == 0 || $result[0]['to'] !== $_SESSION['id']) {
                return;
            }

            $this->db->execute("UPDATE wall_posts SET privacy=:privacy WHERE id=:postID",
                Array(':postID' => $postID, ':privacy' => $privacy));
        }

        /**
         * Gets wall posts
         *
         * @param String $username The username of the wall owner
         *
         */
        public function getPosts($username) {

            require_once('helpers/database/FriendsHelper.php');
            require_once('helpers/database/UsersHelper.php');

            // TODO: Find a nice way to do all of this in 1 statement
            $posts = array();
            $postIDs = array();

            $result = $this->db->fetch("SELECT wp.id, wp.`content`, wp.`timestamp`, wp.`type`,
                (CASE WHEN toUser.middle_name IS NULL THEN 
                        CONCAT(toUser.first_name, ' ', toUser.last_name) 
                    ELSE 
                        CONCAT(toUser.first_name, ' ', toUser.middle_name, ' ', toUser.last_name) 
                END) as toName,
                (CASE WHEN fromUser.middle_name IS NULL THEN 
                        CONCAT(fromUser.first_name, ' ', fromUser.last_name) 
                    ELSE 
                        CONCAT(fromUser.first_name, ' ', fromUser.middle_name, ' ', 
                            fromUser.last_name) 
                END) as fromName,
                toUser.login as toLogin, fromUser.login as fromLogin, wp.privacy
                FROM wall_posts as wp, users as toUser, users as fromUser
                WHERE ((wp.to=toUser.id AND wp.from=fromUser.id) 
                    OR (wp.from=toUser.id AND wp.to=fromUser.id AND wp.type='friend'))
                AND toUser.login=:username
                ORDER BY `lastTouched` DESC",
                Array(':username' => $username));

            $fh = new FriendsHelper();
            $uh = new UsersHelper();
            $relation = $fh->getRelationship($_SESSION['id'], $uh->getIdFromUsername($username));

            foreach ($result as $r) {
                if($relation <= $r['privacy'] || $uh->isAdmin($_SESSION['username'])) {
                    $post = array(  'id' => $r['id'],
                                    'to' => $r['toLogin'],
                                    'from' => $r['fromLogin'],
                                    'toName' => $r['toName'],
                                    'fromName' => $r['fromName'],
                                    'content' => $r['content'],
                                    'timestamp' => $r['timestamp'],
                                    'type' => $r['type'],
                                    'comments' => array());
                    if($_SESSION['username'] === $username)
                        $post['privacy'] = $r['privacy'];
                    $posts[] = $post;
                    $postIDs[] = $r['id'];
                }
            }

            if(count($postIDs) === 0)
                return $posts;

            return $this->getComments($postIDs, $posts);
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

            $result = $this->db->execute("UPDATE wall_posts
                SET lastTouched=:time WHERE id=:postID",
                Array(':time' => time(), ':postID' => $postID));
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
            require_once('helpers/database/UsersHelper.php');
            $fh = new FriendsHelper();
            $friends = $fh->getFriends($user);
            
            $friendLogins = array();
            $posts = array();
            $postIDs = array();

            foreach ($friends as $friend) {
                $friendLogins[] = "'" . $friend['login'] . "'";
            }
            $friendLogins[] = "'" .$_SESSION['username'] . "'";

            $inSet = implode(",", $friendLogins);

            $result = $this->db->fetch("SELECT wp.id, wp.`content`, wp.`timestamp`, wp.`type`,
                (CASE WHEN toUser.middle_name IS NULL THEN 
                        CONCAT(toUser.first_name, ' ', toUser.last_name) 
                    ELSE 
                        CONCAT(toUser.first_name, ' ', toUser.middle_name, ' ', toUser.last_name) 
                END) as toName,
                (CASE WHEN fromUser.middle_name IS NULL THEN 
                        CONCAT(fromUser.first_name, ' ', fromUser.last_name) 
                    ELSE 
                        CONCAT(fromUser.first_name, ' ', fromUser.middle_name, ' ', 
                            fromUser.last_name) 
                END) as fromName,
                toUser.login as toLogin, fromUser.login as fromLogin, wp.privacy
                FROM wall_posts as wp, users as toUser, users as fromUser
                WHERE wp.to=toUser.id AND wp.from=fromUser.id
                AND toUser.login IN ($inSet)
                ORDER BY `lastTouched` DESC",
                Array());

            $uh = new UsersHelper();

            foreach ($result as $r) {
                $relation = $fh->getRelationship($_SESSION['id'], 
                    $uh->getIdFromUsername($r['toLogin']));
                if($relation <= $r['privacy'] || $uh->isAdmin($_SESSION['username'])) {
                    $post = array(  'id' => $r['id'],
                                    'to' => $r['toLogin'],
                                    'from' => $r['fromLogin'],
                                    'toName' => $r['toName'],
                                    'fromName' => $r['fromName'],
                                    'content' => $r['content'],
                                    'timestamp' => $r['timestamp'],
                                    'type' => $r['type'],
                                    'comments' => array());
                    if($_SESSION['username'] === $r['toLogin'])
                        $post['privacy'] = $r['privacy'];
                    $posts[] = $post;
                    $postIDs[] = $r['id'];
                }
            }

            if(count($postIDs) === 0)
                return $posts;

            return $this->getComments($postIDs, $posts);
        }

        /**
         * Gets comments for an array of wall posts
         *
         * @param String $user The user to get the newsfeed for
         *
         */
        private function getComments($postIDs, $posts) {
            $inSet = implode(",", $postIDs);

            $result = $this->db->fetch("SELECT c.id, c.wall_post, c.content, c.timestamp, u.login,
                c.reported,
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
                                    'reported' => $r['reported'],
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

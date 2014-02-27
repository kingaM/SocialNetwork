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
         * Adds a wall post
         *
         * @param String $username The username of the wall owner
         *
         */
        public function getPosts($username) {

            $posts = array();

            $result = $this->db->fetch("SELECT wp.`to`, wp.`from`, wp.`content`, wp.`timestamp`,
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
                $post = array(  'to' => $r['toLogin'],
                                'from' => $r['fromLogin'],
                                'toName' => $r['toName'],
                                'fromName' => $r['fromName'],
                                'content' => $r['content'],
                                'timestamp' => $r['timestamp']);
                $posts[] = $post;
            }
            return $posts;
        }

    }

?>

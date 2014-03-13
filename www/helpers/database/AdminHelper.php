<?php

    require_once('helpers/database/database.php');

    /**
     * A helper that has database functions related to Admin tasks not fitting elsewhere 
     */
    class AdminHelper {

        private $db;

        public function __construct() {
            $this->db = new DatabaseHelper();
        }

        /**
         * Reports a comment
         *
         * @param int $id The id of the comment
         *
         */
        public function reportComment($id) {
            $this->db->execute("UPDATE comments SET reported=1 WHERE id=:id", Array(':id' => $id));
        }

        /**
         * Resets a report
         *
         * @param int $id The id of the reported comment
         *
         */
        public function ignoreReport($id) {
            $this->db->execute("UPDATE comments SET reported=0 WHERE id=:id", Array(':id' => $id));
        }

        /**
         * Deletes a comment
         *
         * @param int $id The id of the reported comment
         *
         */
        public function deleteComment($id) {
            $this->db->execute("DELETE FROM comments WHERE reported=1 AND id=:id", 
                Array(':id' => $id));
        }

        /**
         * Gets reported comments
         *
         */
        public function getReportedComments() {
            $result = $this->db->fetch("SELECT c.id, c.wall_post, c.content, c.timestamp, u.login,
                c.reported,
                (CASE WHEN u.middle_name IS NULL THEN 
                        CONCAT(u.first_name, ' ', u.last_name) 
                    ELSE 
                        CONCAT(u.first_name, ' ', u.middle_name, ' ', u.last_name) 
                END) as fromName
                FROM comments as c, users as u
                WHERE u.ID=c.from AND reported=1", Array());
            $comments = array();
            foreach ($result as $r) {
                $comment = array(   'id' => $r['id'],
                                    'content' => $r['content'],
                                    'timestamp' => $r['timestamp'],
                                    'login' => $r['login'],
                                    'reported' => $r['reported'],
                                    'fromName' => $r['fromName']);
                $comments[] = $comment;
            }
            return $comments;
        }
    }

?>

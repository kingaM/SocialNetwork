<?php
    
    require_once('helpers/database/database.php');
    require_once('libs/FirePHPCore/FirePHP.class.php');

    /**
     * A helper class consiting of methods used to get information needed for messaging 
     * functionality of the site.
     */
    class MessagesHelper {

        private $db = NULL;

        public function __construct() {
            $this->db = new DatabaseHelper();
        }

        /**
         * Adds a message into the database.
         * @param integer $from      The creator of the message.
         * @param integer $to        The reciepient of the message.
         * @param string  $content   The content of the message.
         * @param integer $timestamp The time the message was created.
         */
        public function addMessage($from, $to, $content, $timestamp) {
            $sql = "INSERT INTO messages(`from`, `to`, `content`, `timestamp`)
                    VALUES
                        (:from, :to, :content, :timestamp)";
            $array = array(':from' => $from, ':to' => $to, ':content' => $content, 
                ':timestamp' => $timestamp);
            $this->db->execute($sql, $array);
        }

        /**
         * Gets all messages between two users.
         * 
         * @param  integer $from The id of one user.
         * @param  integer $to   The id of the second user.
         * 
         * @return Array         The array of the results, in a row by row and column by column
         *                       format.  
         */
        public function getMessages($from, $to) {
            $firephp = FirePHP::getInstance(true); 
            try{
            $sql = "SELECT `from`, `to`, content, `timestamp`,
                        first_name, middle_name, last_name
                    FROM messages, users
                    WHERE (`from` = :from AND `to` = :to AND `from` = users.id) OR 
                        (`from` = :to AND `to` = :from AND `from` = users.id)
                    ORDER BY timestamp ASC";
            $array = array(':from' => $from, ':to' => $to);
            $result = $this->db->fetch($sql, $array);
            return $result;
            } catch(Exception $e) {
                $firephp->log($e, 'PHP');
            }
        }

        /**
         * Gets all users that a given user was talking to.
         *     
         * @param  integer $from The id of the user.
         * @return Array         The array of the results, in a row by row and column by column
         *                       format.  
         */
        public function getReciepients($from) {
            $sql = "SELECT 
                    DISTINCT CASE  `to` WHEN  :from THEN  `from` ELSE  `to` END AS id, 
                    login, first_name, middle_name, last_name, content, timestamp
                    FROM messages, users
                    WHERE ((`from` = :from AND  `to` = users.id)
                        OR (`to` = :from AND  `from` = users.id))
                    AND timestamp = (SELECT timestamp 
                                    FROM messages 
                                    WHERE ((`from` = :from AND  `to` = users.id)
                                        OR (`to` = :from AND  `from` = users.id))
                                    ORDER BY timestamp DESC
                                    LIMIT 1)
                    ORDER BY timestamp ASC";
            $array = array(':from' => $from);
            return $this->db->fetch($sql, $array);
        }

    }

?>
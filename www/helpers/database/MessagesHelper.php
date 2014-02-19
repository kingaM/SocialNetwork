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
         * @param integer $to_user   The reciepient of the message.
         * @param string  $content   The content of the message.
         * @param integer $timestamp The time the message was created.
         */
        public function addMessage($from, $to_user, $to_circle, $content, $timestamp) {
            if ($to_user == NULL) {
                $type = 'C';
            } else {
                $type = 'P';
            }
            $sql = "INSERT INTO messages(`from`, `to_user`, `to_circle`, `type`, `content`, 
                        `timestamp`)
                    VALUES
                        (:from, :to_user, :to_circle, :type, :content, :timestamp)";
            $array = array(':from' => $from, ':to_user' => $to_user, ':content' => $content, 
                ':timestamp' => $timestamp, ':to_circle' => $to_circle, ':type' => $type);
            $this->db->execute($sql, $array);
        }

        /**
         * Gets all messages between two users.
         * 
         * @param  integer $from The id of one user.
         * @param  integer $to_user   The id of the second user.
         * 
         * @return Array         The array of the results, in a row by row and column by column
         *                       format.  
         */
        public function getMessagesUser($from, $to_user) {
            $firephp = FirePHP::getInstance(true); 
            try{
                $sql = "SELECT `from`, `to_user`, content, `timestamp`,
                            first_name, middle_name, last_name
                        FROM messages, users
                        WHERE (`from` = :from AND `to_user` = :to_user AND `from` = users.id) OR 
                            (`from` = :to_user AND `to_user` = :from AND `from` = users.id)
                        ORDER BY timestamp ASC";
                $array = array(':from' => $from, ':to_user' => $to_user);
                $result = $this->db->fetch($sql, $array);
                return $result;
            } catch(Exception $e) {
                $firephp->log($e, 'PHP');
            }
        }

        /**
         * Gets all messages in a circle.
         * 
         * @param  integer $from The id of one user.
         * @param  integer $to_circle   The id of the second user.
         * 
         * @return Array         The array of the results, in a row by row and column by column
         *                       format.  
         */
        public function getMessagesCircle($from, $to_circle) {
            $firephp = FirePHP::getInstance(true); 
            try{
                $sql = "SELECT `from`, `to_circle`, content, `timestamp`,
                            first_name, middle_name, last_name
                        FROM messages, users
                        WHERE ((`from` = :from AND `to_circle` = :to_circle AND `from` = users.id) 
                            OR (`from` IN (
                                SELECT user
                                FROM circle_memberships 
                                WHERE circle = :to_circle) 
                            AND `to_circle` = :to_circle AND `from` = users.id)) 
                        ORDER BY timestamp ASC";
                $array = array(':from' => $from, ':to_circle' => $to_circle);
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
            $sql = "SELECT DISTINCT * 
                    FROM (
                        SELECT
                            CASE `type` 
                                WHEN 'C' THEN CONCAT('Circle: ', name, ' owned by ', (
                                    SELECT CASE 
                                        WHEN id = :from THEN 'You'
                                        WHEN middle_name IS NULL 
                                            THEN CONCAT(first_name, ' ', last_name) 
                                        ELSE CONCAT(first_name, ' ', middle_name, ' ', last_name)
                                        END
                                    FROM users 
                                    WHERE users.id = owner)
                                ) 
                                ELSE (CASE 
                                        WHEN middle_name IS NULL 
                                            THEN CONCAT(first_name, ' ', last_name) 
                                        ELSE CONCAT(first_name, ' ', middle_name, ' ', last_name) 
                                    END)
                                END AS name,
                            CASE `type`
                                WHEN 'C' THEN CONCAT(owner, '_', name)
                                ELSE login 
                                END AS login,
                            content, timestamp
                        FROM messages, users, circles
                        WHERE ((`from` = :from AND `to_user` IS NOT NULL
                                    AND `to_user` = users.id AND `to_circle` IS NULL)
                                OR (`to_user` IS NOT NULL AND `to_user` = :from 
                                    AND  `from` = users.id AND `to_circle` IS NULL) 
                                OR (`from` = :from AND  `to_circle` IS NOT NULL 
                                    AND`to_circle` = circles.id AND `to_user` IS NULL))
                            AND timestamp = (SELECT timestamp 
                                            FROM messages 
                                            WHERE ((`from` = :from AND `to_user` IS NOT NULL 
                                                    AND `to_user` = users.id 
                                                    AND `to_circle` IS NULL)
                                                OR (`to_user` IS NOT NULL AND `to_user` = :from 
                                                    AND  `from` = users.id AND `to_circle` IS NULL) 
                                                OR (`from` = :from AND  `to_circle` IS NOT NULL 
                                                    AND`to_circle` = circles.id 
                                                    AND `to_user` IS NULL))
                                            ORDER BY timestamp DESC
                                            LIMIT 1)
                        ORDER BY timestamp ASC ) AS reciepients
                    ORDER BY timestamp, login ASC ";
            $array = array(':from' => $from);
            return $this->db->fetch($sql, $array);
        }

    }

?>
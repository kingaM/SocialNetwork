<?php
    
    require_once('helpers/database/database.php');
    require_once('libs/FirePHPCore/FirePHP.class.php');

    class MessagesHelper {

        private $db = NULL;

        public function __construct() {
            $this->db = new DatabaseHelper();
        }

        public function addMessage($from, $to, $content, $timestamp) {
            $sql = "INSERT INTO messages(`from`, `to`, `content`, `timestamp`)
                    VALUES
                        (:from, :to, :content, :timestamp)";
            $array = array(':from' => $from, ':to' => $to, ':content' => $content, 
                ':timestamp' => $timestamp);
            $this->db->execute($sql, $array);
        }

        public function getMessages($from, $to) {
            $firephp = FirePHP::getInstance(true); 
            try{
            $sql = "SELECT `from`, `to`, content, `timestamp` FROM messages
                    WHERE (`from` = :from AND `to` = :to) OR 
                    (`from` = :to AND `to` = :from)";
            $array = array(':from' => $from, ':to' => $to);
            $result = $this->db->fetch($sql, $array);
            $firephp->log($result, 'PHP');
            return $result;
            } catch(Exception $e) {
                $firephp->log($e, 'PHP');
            }
        }

        public function getReciepients($from) {
            try{
                $sql = "SELECT DISTINCT 
                        CASE  `to` WHEN  :from THEN  `from` ELSE  `to` END AS id, 
                        login, first_name, middle_name, last_name, content, timestamp
                        FROM messages, users
                        WHERE ((`from` = :from AND  `to` = users.id)
                            OR (`to` = :from AND  `from` = users.id))
                        AND timestamp = (SELECT timestamp 
                                        FROM messages 
                                        ORDER BY timestamp DESC
                                        LIMIT 1)";
                $array = array(':from' => $from);
                return $this->db->fetch($sql, $array);
            } catch(Exception $e) {
                $firephp = FirePHP::getInstance(true); 
                $firephp->log($e, 'PHP');
            }

        }

    }

?>
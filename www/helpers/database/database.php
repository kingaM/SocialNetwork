<?php

require_once('libs/FirePHPCore/FirePHP.class.php');
    /**
     * Exception thrown if there is a problem with the conncection or sql.
     *
     * @package database
     */
    class DatabaseException extends Exception {

        public function __construct($message, $code = 0, Exception $previous = null) {
            parent::__construct($message, $code, $previous);
        }

        public function __toString() {
           return __CLASS__ . "[{$this->code}]: {$this->message}\n";
        }
    }

    /**
     * A class that wraps the basic functionality of the database. 
     *
     * @package database
     */
    class DatabaseHelper {

        private $username = 'root';
        private $password = 'root';
        private $pdo = NULL;

        public function __construct() {
            $this->connect();
        }

        /**
         * Function that connects to the database, using preset credentials. 
         *
         * @throws DatabaseException when there is a PDO error
         */
        protected function connect() {
            try {
                $this->pdo = new PDO('mysql:host=localhost;dbname=SocialNetwork', 
                    $this->username, $this->password, array(
                    PDO::ATTR_PERSISTENT => true,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ));
            } catch (PDOException $e) {
                throw new DatabaseException($e->getMessage(), $e->getCode());
            }
        }

        /**
         * Executes the givien sql using parameters in the array. 
         *
         * @param string $sql The sql query to execture
         *                    The sql should be of form: "SELECT name, colour, calories FROM fruit
         *                    WHERE calories < :calories AND colour = :colour"
         * @param array $array The array with the values to execute
         *                     The array should be of form:
         *                     Array (
         *                         ':calories' => 150, ':colour' => 'red'
         *                     )
         *
         * @return boolean True if the execution succeeded, false otherwise. 
         *
         * @throws DatabaseException when there is a PDO error
         */
        public function execute($sql, $array) {
            try {
                $q = $this->pdo->prepare($sql);
                return $q->execute($array);
            } catch (PDOException $e) {
                throw new DatabaseException($e->getMessage(), $e->getCode());
            }
        }

        /**
         * Executes the givien sql using parameters in the array. 
         *
         * @param string $sql The sql query to execture
         * @param array $array The array with the values to execute
         *
         * @return array of all the rows that are returned by the query. The array is in format
         *                  e.g. "SELECT name, colour FROM fruit" would return
         *                      Array (
         *                          [0] => Array (
         *                                  [NAME] => pear
         *                                  [0] => pear
         *                                  [COLOUR] => green
         *                                  [1] => green
         *                                 )
         * 
         *                         [1] => Array (
         *                                 [NAME] => watermelon
         *                                 [0] => watermelon
         *                                 [COLOUR] => pink
         *                                 [1] => pink
         *                                )
         * )
         *
         * @throws DatabaseException when there is a PDO error
         */
        public function fetch($sql, $array = NULL) {
            try {
                $firephp = FirePHP::getInstance(true);
                $q = $this->pdo->prepare($sql);
                // Otherwise ints are treated as strings, should be done for other types too.
                foreach ($array as $key => $value) {
                    if(is_int($value)) {
                        $q->bindValue($key, $value, PDO::PARAM_INT);
                    } else {
                        $q->bindValue($key, $value, PDO::PARAM_STR);
                    } 
                }
                $q->execute();
                return $q->fetchAll();
            } catch (PDOException $e) {
                throw new DatabaseException($e->getMessage(), $e->getCode());
            }
        }

        /**
         * @return integer The ID of the last inserted row or sequence value.
         *
         * @throws DatabaseException when there is a PDO error
         */
        public function getLastId() {
            try {
                return $this->pdo->lastInsertId();
            } catch (PDOException $e) {
                throw new DatabaseException($e->getMessage(), $e->getCode());
            }
        }

        /**
         * Disconnects from the database.
         */
        protected function disconnect() {
            $this->pdo = NULL;
        }

        public function __destruct() {
            $this->disconnect();
        }
    }

?>

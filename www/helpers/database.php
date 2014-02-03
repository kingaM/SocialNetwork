<?php
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
                $this->pdo = new PDO('mysql:host=localhost;dbname=SocialNetwork', $this->username, $this->password, array(
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
                $q = $this->pdo->prepare($sql);
                $q->execute($array);
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
                return $this->pdo->lasInsertId();
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

    class UsersTable {

        /**
         * Function to check if the user credentials are correct
         * 
         * @param  string $username User's username
         * @param  string $password User's password
         * @return integer Username ID if the credentials are correct, -1 otherwise
         */
        public static function verifyUser($username, $password) {
            $db = new DatabaseHelper();
            $result = $db->fetch("SELECT login, password, ID FROM users WHERE login = :username", Array(':username' => $username));

            if(sizeof($result) != 1 || !($result[0]["login"] == $username && $result[0]["password"] == sha1($password))) {
                return -1;
            }

            return $result[0]["ID"];

        }

    }

    class FriendsTable extends DatabaseHelper {

        /**
         * Creates a friend request
         *
         * @param int $requester The user ID of the person making the request
         * @param int $addFriend The user ID of the person to be added as a friend
         *
         * @return void 
         **/
        public function createFriendshipReq($requester, $addFriend) {
            
        }

        /**
         * Gets the names of a user's friends
         *
         * @param int $userID The user to show friends from
         *
         * @return String[] Array of friend names
         **/
        public function getFriends($userID) {
            $friends = array();
            $result = $this->fetch("SELECT first_name, middle_name, last_name 
                FROM friendships as f, users as u
                WHERE ((f.user1=:user AND NOT u.ID=:user) OR (user2=:user AND NOT u.ID=:user)) 
                AND status = 1",
                Array(':user' => $userID));

            foreach ($result as $r) {
                if($r['middle_name'])
                    $friends[] = $r['first_name'] . ' ' . $r['middle_name'] . ' ' . $r['last_name'];
                else
                    $friends[] = $r['first_name'] . ' ' . $r['last_name'];
            }
            return $friends;
        }
    }

?>

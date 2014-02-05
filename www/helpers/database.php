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

    class UsersTable {

        /**
         * Function to check if the user credentials are correct
         * 
         * @param  string $username User's username
         * @param  string $password User's password
         * 
         * @return integer Username ID if the credentials are correct, -1 otherwise
         */
        public static function verifyUser($username, $password) {
            $db = new DatabaseHelper();
            $result = $db->fetch("SELECT login, password, ID FROM users " .
                "WHERE login = :username AND activated = 1", 
                Array(':username' => $username));

            if(sizeof($result) != 1 || !($result[0]["login"] == $username && $result[0]["password"] 
                == sha1($password))) {
                return -1;
            }

            return $result[0]["ID"];

        }

        /**
         * Checks if the username is in the database already.
         * 
         * @param  string $username User's usernam
         * @param  DatabaseHelper $db The databaseHelper object used to connect to database. Since
         *         this is a helper class there is no need to create a new connection.
         * 
         * @return boolean True if the username is already present in the database, 
         *         False otherwise
         */
        private static function checkUsernameExists($username, $db) {
            $array = $db->fetch("SELECT ID FROM users WHERE login = :username", 
                Array(':username' => $username));

            if(sizeof($array) != 0) {
                return true;
            }

            return false;
        }

        /**
         * Checks if the email is in the database already.
         * 
         * @param  string $email User's usernam
         * @param  DatabaseHelper $db The databaseHelper object used to connect to database. Since
         *         this is a helper class there is no need to create a new connection.
         * 
         * @return boolean True if the email is already present in the database, 
         *         False otherwise
         */
        private static function checkEmailExists($email, $db) {
            $array = $db->fetch("SELECT ID FROM users WHERE email = :email", 
                Array(':email' => $email));

            if(sizeof($array) != 0) {
                return true;
            }

            return false;
        }

        /**
         * Adds a user to the database. 
         * 
         * @param string $username   User's username, has to be unique
         * @param string $password   User's password, not encrypted
         * @param string $firstName  User's first name
         * @param string $lastName   User's last name
         * @param string $email      User's email address
         * @param string $middleName User's middle name, defaults to NULL
         *
         * @return integer The ID of the newly added user or -1 if both username and email are not 
         *         unique, -2 if the username exists, -3 if the email exists. 
         */
        public static function addUser($username, $password, $firstName, $lastName, $email, $hash, 
            $activated = 0, $middleName = NULL) {
            $db = new DatabaseHelper();
            $uniqueUsername = UsersTable::checkUsernameExists($username, $db);
            $uniqueEmail = UsersTable::checkEmailExists($email, $db);
            if($uniqueUsername && $uniqueEmail) {
                return -1;
            } else if ($uniqueUsername) {
                return -2;
            } else if ($uniqueEmail) {
                return -3;
            }
            $db->execute("INSERT INTO " .
                "users(first_name, middle_name, last_name, email, login, password, hash, activated)" .
                "VALUES (:firstName, :middleName, :lastName, :email, :username, SHA1(:password)," .
                    "SHA1(:hash), :activated);",
                Array(':firstName' => $firstName, ':middleName' => $middleName,
                    'lastName' => $lastName, ':email' => $email, ':username' => $username, 
                    ':password' => $password, ':activated' => $activated, ':hash' => $hash));
            return $db->getLastId();
        }

        public static function checkIfAuthenticated($hash) {
            $db = new DatabaseHelper();
            $result = $db->fetch("SELECT activated FROM users WHERE hash = :hash",
                array(':hash' => $hash));
            if(sizeof($result) != 1) {
                return -1;
            } 
            if ($result[0]['activated'] == true) {
                return 1;
            } else {
                return 0;
            }
        }

        public static function updateAuthenticated($hash) {
            $db = new DatabaseHelper();
            $db->execute("UPDATE users SET activated = 1 WHERE hash = :hash",
                array(':hash' => $hash));
        }

    }

    class FriendsTable extends DatabaseHelper {

        /**
         * Gets the names of a user's friends
         *
         * @param int $userID The user to show friends from
         *
         * @return String[] Array of friend names
         */
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

        /**
         * Creates a friend request
         *
         * @param int $requester The user ID of the person making the request
         * @param int $addFriend The user ID of the person to be added as a friend
         *
         * @return void 
         */
        public function createFriendshipReq($requester, $addFriend) {
            
        }
    }

?>

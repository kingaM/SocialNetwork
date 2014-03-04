<?php
    
    require_once('helpers/database/database.php');

    /**
     * A helper that has bassic database functions acting on basic User infomation. 
     */
    class UsersHelper {

        private $db = NULL;

        public function __construct() {
            $this->db = new DatabaseHelper();
        }

        /**
         * Function to check if the user credentials are correct
         * 
         * @param  string $username User's username
         * @param  string $password User's password
         * 
         * @return integer Username ID if the credentials are correct, -1 otherwise
         */
        public function verifyUser($username, $password) {
            $result = $this->db->fetch("SELECT login, password, ID FROM users " .
                "WHERE login = :username AND activated = 1", 
                array(':username' => $username));

            if(sizeof($result) != 1 || !($result[0]["login"] == $username && $result[0]["password"] 
                == sha1($password))) {
                return -1;
            }

            return $result[0]["ID"];
        }

        /**
         * Checks if the username is in the database already.
         * 
         * @param  string $username User's username
         * @param  DatabaseHelper $db The databaseHelper object used to connect to database. Since
         *         this is a helper class there is no need to create a new connection.
         * 
         * @return boolean True if the username is already present in the database, 
         *         False otherwise
         */
        public function checkUsernameExists($username) {
            $array = $this->db->fetch("SELECT ID FROM users WHERE login = :username", 
                array(':username' => $username));

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
        public function checkEmailExists($email) {
            $array = $this->db->fetch("SELECT ID FROM users WHERE email = :email", 
                array(':email' => $email));
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
        public function addUser($username, $password, $firstName, $lastName, $email, $hash, 
            $activated = 0, $middleName = NULL) {
            $uniqueUsername = $this->checkUsernameExists($username);
            $uniqueEmail = $this->checkEmailExists($email);
            if($uniqueUsername && $uniqueEmail) {
                return -1;
            } else if ($uniqueUsername) {
                return -2;
            } else if ($uniqueEmail) {
                return -3;
            }
            $this->db->execute("INSERT INTO 
                users(first_name, middle_name, last_name, email, login, password, hash, activated)
                VALUES (:firstName, :middleName, :lastName, :email, :username, SHA1(:password),
                    SHA1(:hash), :activated);",
                array(':firstName' => $firstName, ':middleName' => $middleName,
                    ':lastName' => $lastName, ':email' => $email, ':username' => $username, 
                    ':password' => $password, ':activated' => $activated, ':hash' => $hash));
            $id = $this->db->getLastId();
            $this->db->execute('INSERT INTO profile(userId) VALUES (:id)', 
                array(':id' => $id));
            return $id;
        }

        /**
         * Checks if the e-mail has been activated.
         * @param  string $hash The e-mail activation code
         * @return integer      -1 if an error occurs, 1 if it is activated, 0 if it isn't
         */
        public function checkIfAuthenticated($hash) {
            $result = $this->db->fetch("SELECT activated FROM users WHERE hash = :hash",
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

        /**
         * Set the activation of the e-mail to true.
         * @param  string $hash The activation code for the e-mail
         */
        public function updateAuthenticated($hash) {
            $this->db->execute("UPDATE users SET activated = 1 WHERE hash = :hash",
                array(':hash' => $hash));
        }

        /**
         * Get user and profile information
         * 
         * @param  integer $id The id of the user
         * 
         * @return array     The array of all the data about the user, array fields:
         *         id, first_name, middle_name, last_name, gender, dob, about, locations, languages,
         *         email or null if the id is not valid. 
         */
        public function getUser($id) {
            $sql = "SELECT id, first_name, middle_name, last_name, gender, dob, about, locations, 
                languages, email, login, profilePicture
                FROM users, profile
                WHERE id = :id AND activated = 1
                AND id = userId";
            $result = $this->db->fetch($sql, array(':id' => $id));
            if(sizeof($result) != 1) {
                return NULL;
            } else {
                return $result[0];
            }
        }

        /**
         * Updates user information.
         *     
         * @param  integer $id        The unique id of the user. Cannot be null.
         * @param  string $firstName  The first name of the user
         * @param  string $middleName The middle name of the user
         * @param  string $lastName   The last name of the user
         * @param  string $gender     The gender of the user
         * @param  integer $dob       The date of birth of the user, as unix timestamp
         * @param  string $about      The user's about
         * @param  string $locations  The locations of the user
         * @param  string $languages  The languages of the user
         */
        public function updateProfileInfo($id, $firstName, $middleName, $lastName, $gender, 
            $dob, $about, $locations, $languages) {
            $sql = "UPDATE users, profile
                SET first_name = :firstName, middle_name = :middleName, last_name = :lastName,
                gender = :gender, dob = :dob, about = :about, locations = :locations, 
                languages = :languages
                WHERE id = :id AND id = userId";
            $array = array(':firstName' => $firstName, ':middleName' => $middleName, 
                ':lastName' => $lastName, ':gender' => $gender, ':dob' => $dob, ':about' => $about,
                ':locations' => $locations, ':languages' => $languages, ':id' => $id);
            $this->db->execute($sql, $array);
        }

        /**
         * Updates user's username.
         *     
         * @param  integer $id        The unique id of the user. Cannot be null.
         * @param  string  $username  The new username of the user.
         *
         * @return boolean True if the update succeeded, false otherwise.
         */
        public function updateUsername($id, $username) {
            $sql = "UPDATE users
                SET login = :username
                WHERE id = :id";
            $array = array(':id' => $id, ':username' => $username);
            return $this->db->execute($sql, $array);
        }

        /**
         * Updates user's password.
         *     
         * @param  integer $id        The unique id of the user. Cannot be null.
         * @param  string  $password  The new password of the user.
         *
         * @return boolean True if the update succeeded, false otherwise.
         */
        public function updatePassword($id, $password) {
            $sql = "UPDATE users
                SET password = sha1(:password)
                WHERE id = :id";
            $array = array(':id' => $id, ':password' => $password);
            return $this->db->execute($sql, $array);
        }

        /**
         * Updates user's email.
         *     
         * @param  integer $id     The unique id of the user. Cannot be null.
         * @param  string  $email  The new email of the user.
         *
         * @return boolean True if the update succeeded, false otherwise.
         */
        public function updateEmail($id, $email) {
            $sql = "UPDATE users
                SET email = :email
                WHERE id = :id";
            $array = array(':id' => $id, ':email' => $email);
            return $this->db->execute($sql, $array);
        }

        /**
         * Get ID of the user with the given username
         * @param  string $username The unique username of a user. 
         * @return integer          The unique id of the user.
         */
        public function getIdFromUsername($username) {
            $result = $this->db->fetch("SELECT ID FROM users WHERE login = :username",
                array(':username' => $username));
            if(sizeof($result) != 1) {
                return -1;
            } 
            return $result[0]['ID'];
        }

        /**
         * Get the username from the given id.
         * @param  integer $id The id of the user.
         * @return string      The username of the user.
         */
        public function getUsernameFromId($id) {
            $result = $this->db->fetch("SELECT login FROM users WHERE ID = :id",
                array(':id' => $id));
            if(sizeof($result) != 1) {
                return -1;
            }
            return $result[0]['login'];
        }

        /**
         * Suggests users starting with a given string
         * @param  string $name The name to complete
         */
        public function autoCompleteUsers($name) {
            $result = $this->db->fetch("SELECT login 
                FROM users 
                WHERE login LIKE :name",
                array(':name' => $name . '%'));

            $suggestions = array();
            foreach ($result as $r) {
                $suggestions[] = $r['login'];
            }
            return $suggestions;
        }

        /**
         * Gets URL of the picture of the user. 
         * 
         * @param  string  $id The id of the user. 
         * @return string      The url of the photo or NULL if there is an error.  
         */
        public function getPictureUrl($id) {
            $sql = "SELECT profilePicture FROM profile WHERE
                userId = :id";
            $array = array(':id' => $id);
            $result = $this->db->fetch($sql, $array);
            if(sizeof($result) != 1) {
                return NULL;
            } else {
                return $result[0]["profilePicture"];
            }
        }

    }

?>

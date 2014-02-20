<?php

    require_once('helpers/database/database.php');

    class FriendsHelper {

        private $db;

        public function __construct() {
            $this->db = new DatabaseHelper();
        }

        /**
         * Gets the names of a user's friends
         *
         * @param int $userID The user to show friends from
         *
         * @return String[] Array of friend names
         */
        public function getFriends($userID) {
            $friends = array();
            $result = $this->db->fetch("SELECT login 
                FROM friendships as f, users as u
                WHERE ((f.user1=:user AND NOT u.ID=:user AND f.user2=u.ID) 
                    OR (f.user2=:user AND NOT u.ID=:user AND f.user1=u.ID)) 
                AND status = 1",
                Array(':user' => $userID));

            foreach ($result as $r) {
                $friends[] = $r['login'];
            }
            return $friends;
        }

        /**
         * Checks if the users are friends.
         * 
         * @param  integer  $user1 Id of one of the users.
         * @param  integer  $user2 Id of the second user.
         * 
         * @return boolean         True if the users are friends, false otherwise. 
         */
        public function isFriend($user1, $user2) {
            $sql = "SELECT * FROM friendships as f
                    WHERE ((f.user1=:user1 AND f.user2=:user2) 
                        OR (f.user2=:user1 AND f.user1=:user2)) 
                        AND status = 1";
            $array = array(':user1' => $user1, ':user2' => $user2);
            $result = $this->db->fetch($sql, $array);
            if(sizeof($result) != 1) {
                return false;
            } 
            return true;
        }

        /**
         * Gets the names of a user's friends of friends
         *
         * @param int $userID The user to show friends from
         *
         * @return String[] Array of friend names
         */
        public function getFriendsOfFriends($userID) {
            $friends = array();
            // MySQL doesn't support the WITH syntax ... this is the best we can do
            // ID's of all friends
            $subq = "(SELECT u.ID 
                FROM friendships as f, users as u
                WHERE ((f.user1=:user AND NOT u.ID=:user AND f.user2=u.ID) 
                    OR (f.user2=:user AND NOT u.ID=:user AND f.user1=u.ID)) 
                AND status = 1)";
            $result = $this->db->fetch("SELECT login
                FROM friendships as f, users as u
                WHERE ((f.user1 IN " . $subq . " AND NOT u.ID IN " . $subq . " AND f.user2=u.ID) 
                    OR (f.user2 IN " . $subq . " AND NOT u.ID IN " . $subq . " AND f.user1=u.ID))
                AND status = 1 
                AND NOT u.ID=:user 
                GROUP BY login",
                Array(':user' => $userID));

            foreach ($result as $r) {
                $friends[] = $r['login'];
            }
            return $friends;
        }

        /**
         * Creates a friend request or accepts an existing request
         *
         * @param int $requester The user ID of the person making the request
         * @param int $addFriend The username of the person to be added as a friend
         *
         * @return boolean True if request has been accepted
         */
        public function addFriend($requester, $addFriend) {
            
            // Check is user exists
            require_once('helpers/database/UsersHelper.php');
            $users = new UsersHelper();
            if(!$users->checkUsernameExists($addFriend))
                throw new Exception('Invalid username');

            // Check if friend already exists
            $result = $this->db->fetch("SELECT * 
                FROM friendships as f, users as u
                WHERE ((f.user1=:requester AND f.user2=u.ID) 
                    OR (f.user2=:requester AND f.user1=u.ID)) 
                AND u.login=:addFriend AND status=1",
                Array(':requester' => $requester, ':addFriend' => $addFriend));

            if(sizeof($result) != 0)
                throw new Exception("Already friends with $addFriend");

            // Check if friend request is pending or blocked
            $result = $this->db->fetch("SELECT * 
                FROM friendships as f, users as u
                WHERE (f.user1=:requester AND f.user2=u.ID) 
                AND u.login=:addFriend",
                Array(':requester' => $requester, ':addFriend' => $addFriend));

            if(sizeof($result) != 0)
                throw new Exception("Friend request already sent to $addFriend");

            // Check for an existing request from the other user
            $result = $this->db->fetch("SELECT * 
                FROM friendships as f, users as u
                WHERE (f.user2=:requester AND f.user1=u.ID) 
                AND u.login=:addFriend",
                Array(':requester' => $requester, ':addFriend' => $addFriend));

            if(sizeof($result) != 0) {
                // Accept the friend request if there is an existing one
                $result = $this->db->execute("UPDATE friendships
                    SET status=1
                    WHERE user2=:requester AND user1 IN
                    (SELECT ID FROM users WHERE login=:addFriend)",
                    Array(':requester' => $requester, ':addFriend' => $addFriend));
                return True;
            }

            // TODO: What if the user deletes their account in this gap?
            //       Doesn't matter too much and nothing will be added but it would be nice to
            //       find a way to let the user know an error instead

            // Add request
            $result = $this->db->execute("INSERT INTO friendships(user1, user2, startTimestamp)
                SELECT :requester, ID as user2, :time 
                FROM users WHERE login=:addFriend",
                Array(':requester' => $requester, ':addFriend' => $addFriend, ':time' => time()));
            return False;
        }

        /**
         * Deletes a friend and declines a friend request
         *
         * @param int $user1 The first username
         * @param int $user2 The second username
         *
         * @return void
         */
        public function deleteFriend($user1, $user2) {
            $this->db->execute("DELETE FROM friendships 
                WHERE (user1 IN (SELECT ID FROM users WHERE login=:user1) 
                    AND user2 IN (SELECT ID FROM users WHERE login=:user2))
                OR (user1 IN (SELECT ID FROM users WHERE login=:user2) 
                    AND user2 IN (SELECT ID FROM users WHERE login=:user1))",
                Array(':user1' => $user1, 'user2' => $user2));

            $this->db->execute("DELETE FROM circle_memberships 
                WHERE (user IN (SELECT ID FROM users WHERE login=:user1) 
                    AND circle IN (SELECT c.ID FROM circles as c, users as u 
                                    WHERE owner=u.id AND login=:user2))
                OR (user IN (SELECT ID FROM users WHERE login=:user2) 
                    AND circle IN (SELECT c.ID FROM circles as c, users as u 
                                    WHERE owner=u.id AND login=:user1))",
                Array(':user1' => $user1, 'user2' => $user2));
        }

        /**
         * Gets a user's pending friend requests
         *
         * @param int $userID The user to show requests for
         *
         * @return String[] Array of friend logins
         */
        public function getFriendRequests($userID) {

            $friends = array();
            $result = $this->db->fetch("SELECT login 
                FROM friendships as f, users as u
                WHERE f.user2=:user AND NOT u.ID=:user AND f.user1=u.ID AND status = 0",
                Array(':user' => $userID));

            foreach ($result as $r) {
                $friends[] = $r['login'];
            }
            return $friends;
        }

        /**
         * Gets a users circles
         *
         * @param int $userID The user to show friends from
         *
         * @return String[][] Array of circles and their friends
         */
        public function getCircles($userID) {
            $result = $this->db->fetch("SELECT *
                FROM circles
                WHERE owner=:user",
                Array(':user' => $userID));

            $circles = array();
            foreach ($result as $r) {
                $circle = array();
                $circle['name'] = $r['name'];
                $circle['users'] = array();
                array_push($circles, $circle);
            }

            $result = $this->db->fetch("SELECT u.login, c.name as circleName
                FROM users as u, circles as c, circle_memberships as cm
                WHERE cm.circle=c.id AND cm.user=u.id
                AND c.owner=:user
                ORDER BY c.name",
                Array(':user' => $userID));

            foreach ($result as $r) {
                $login = $r['login'];
                $circleName = $r['circleName'];
                foreach ($circles as &$circle) {
                    if($circle['name'] == $circleName) {
                        $users = &$circle['users'];
                        $users[] = $login;
                        break;
                    }
                }
            }

            return $circles;
        }

        /**
         * Get the id of the circle.
         * 
         * @param  integer $owner The id of the user that owns the circle.
         * @param  string  $name  The name of the circle.
         * 
         * @return integer        The id of the circle. 
         */
        public function getCircleId($owner, $name) {
            $sql = "SELECT id FROM circles  
                WHERE owner = :owner AND name = :name";
            $array = array(':owner' => $owner, ':name' => $name);

            $result = $this->db->fetch($sql, $array);
            if (sizeof($result) != 1) {
                return -1;
            } else {
                return $result[0]['id'];
            }
        }

        /**
         * Adds a circle for a user
         *
         * @param int $userID The user adding the circle
         * @param String $circleName The name of the circle
         *
         */
        public function addCircle($userID, $circleName) {

            // Check circle doesn't already exist
            $result = $this->db->fetch("SELECT *
                FROM circles 
                WHERE owner=:user AND name=:name",
                Array(':user' => $userID, ':name' => $circleName));
            if(sizeof($result) != 0)
                throw new Exception("You already have a circle called '$circleName'");

            $result = $this->db->execute("INSERT INTO circles(owner, name) VALUES(:user, :name)",
                Array(':user' => $userID, ':name' => $circleName));
        }

        /**
         * Deletes a circle for a user
         *
         * @param int $userID The user deleting the circle
         * @param String $circleName The name of the circle
         *
         */
        public function deleteCircle($userID, $circleName) {
            $result = $this->db->execute("DELETE FROM circles WHERE owner=:user AND name=:name",
                Array(':user' => $userID, ':name' => $circleName));
        }

        /**
         * Adds a user to a circle
         *
         * @param int $userID The user adding the circle
         * @param String $circleName The name of the circle
         *
         */
        public function addToCircle($owner, $circleName, $username) {

            // Check is user exists and is a friend
            $result = $this->db->fetch("SELECT *
                FROM users as u, friendships as f 
                WHERE ((f.user1=:u1 AND f.user2=u.ID) OR (f.user2=:u1 AND f.user1=u.ID)) 
                AND u.login=:u2 AND status = 1",
                Array(':u1' => $owner, ':u2' => $username));
            if(sizeof($result) == 0)
                throw new Exception("You are not friends with '$username'");

            // Check circle exists
            // Note: Should only not exist with concurrency conflicts or abusing the form
            $result = $this->db->fetch("SELECT *
                FROM circles 
                WHERE owner=:user AND name=:name",
                Array(':user' => $owner, ':name' => $circleName));
            if(sizeof($result) == 0)
                throw new Exception("'$circleName' doesn't exist");

            // Check user isn't already in that circle
            $result = $this->db->fetch("SELECT *
                FROM circles as c, circle_memberships as cm, users as u
                WHERE cm.circle=c.id AND c.name=:circleName AND cm.user=u.id
                AND c.owner=:owner AND u.login=:username",
                Array(':owner' => $owner, ':circleName' => $circleName, ':username' => $username));
            if(sizeof($result) != 0)
                throw new Exception("'$username' is already in '$circleName'");

            // Checks passed ... add user to circle
            $result = $this->db->execute("INSERT INTO circle_memberships(user, circle)
                SELECT u.id as user, c.id as circle
                FROM users as u, circles as c
                WHERE c.owner=:owner AND c.name=:circleName AND u.login=:username",
                Array(':owner' => $owner, ':circleName' => $circleName, ':username' => $username));
        }

    }
?>

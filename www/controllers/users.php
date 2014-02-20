<?php

    require_once('helpers/database/FriendsHelper.php');
    require_once('helpers/database/UsersHelper.php');

    class Users {
        
        public function autoComplete($req, $res) {
            $current = $req->params['name'];

            if(strlen($current) < 5) {
                $db = new FriendsHelper();

                $friends = $db->getFriends($_SESSION['id']);
                $friendsOfFriends = $db->getFriendsOfFriends($_SESSION['id']);

                $related = array_merge($friends, $friendsOfFriends);
                $suggestions = array();

                foreach ($related as $r) {
                    if(substr($r, 0, strlen($current)) === $current)
                        $suggestions[] = $r;
                }
            }
            else {
                $db = new UsersHelper();
                $suggestions = $db->autoCompleteUsers($current);
            }

            $res->add(json_encode(array("suggestions" => $suggestions)));
            $res->send();
        }

    }
?>

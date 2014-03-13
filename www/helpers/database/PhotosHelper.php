<?php
    
    require_once('helpers/database/database.php');
    require_once('helpers/database/TimelineHelper.php');

    /**
     * A helper that has bassic database functions acting on basic User infomation. 
     */
    class PhotosHelper {

        private $db = NULL;

        public function __construct() {
            $this->db = new DatabaseHelper();
        }

        /**
         * Gets photo albums of the user.
         * @param  integer $id The id of the user
         * @return Array       An array containing all photo albums of the user. The nested array 
         *                     contains name and about of each albums.
         */
        public function getPhotoAlbums($id) {
            $sql = "SELECT albumId, name, about FROM photo_albums WHERE user = :id";
            $array = array(':id' => $id);
            $result = $this->db->fetch($sql, $array);
            return $result;
        }

        /**
         * Adds a album.
         * 
         * @param  integer $userId The id of the user.
         * @param  string  $name   The name of the album.
         * @param  string  $about  A short description of the album.
         * 
         * @return boolean         Indicates if the insert succeeded or not.
         */
        public function addAlbum($userId, $name, $about) {
            $sql = "INSERT INTO photo_albums(`user`, `name`, `about`)
                VALUES (:userId, :name, :about)";
            $array = array(':userId' => $userId, ':name' => $name, 
                ':about' => $about);
            return $this->db->execute($sql, $array);
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

        /**
         * Updates URL of the picture of the user. 
         * 
         * @param  string  $id  The id of the user.
         * @param  string  $url The new url of the picture.
         * @return boolean      True if succeeded, false otherwise.   
         */
        public function updatePictureUrl($id, $url) {
            $sql = "UPDATE profile
                SET profilePicture = :url 
                WHERE userId = :id";
            $array = array(':id' => $id, ':url' => $url);
            return $this->db->execute($sql, $array);
        }

        /**
         * Gets all photos of a particular user in a particular photo album.
         * 
         * @param  integer $userId  The id of the user.
         * @param  integer $albumId The id of the album.
         * 
         * @return Array           An array with all the photos and information about them.
         */
        public function getPhotos($userId, $albumId) {
            $sql = "SELECT photoId, `timestamp`, `description`, `url`, `thumbnailUrl` 
                    FROM photos, photo_albums
                    WHERE photos.albumId = :albumId AND photos.albumId = photo_albums.albumId AND
                        photo_albums.user = :userId
                    ORDER BY timestamp DESC";
            $array = array(':userId' => $userId, ':albumId' => $albumId);
            $result = $this->db->fetch($sql, $array);
            return $result;
        }

        /**
         * Get one photo of a particular user in a particular photo album.
         * @param  integer $userId  The id of the user.
         * @param  integer $albumId The id of the album.
         * @param  integer $photoId The id of the photo.
         * @return boolean          True if succeeded, false otherwise.
         */
        public function getPhoto($userId, $albumId, $photoId) {
            $sql = "SELECT photoId, `timestamp`, `description`, `url`, `thumbnailUrl` 
                    FROM photos, photo_albums
                    WHERE photos.albumId = :albumId AND photos.albumId = photo_albums.albumId AND
                        photo_albums.user = :userId AND photoId = :photoId";
            $array = array(':userId' => $userId, ':albumId' => $albumId, ':photoId' => $photoId);
            $result = $this->db->fetch($sql, $array);
            if(sizeof($result) != 1) {
                return -1;
            }
            return $result[0];
        }

        /**
         * Get one photo of a particular user in a particular photo album.
         * @param  integer $albumId The id of the album.
         * @param  integer $photoId The id of the photo.
         * @return array            An array with each row that matches. Data returned: 
         *                          content, timestamp, first_name, middle_name, last_name, login, 
         *                          profilePicture, id
         */
        public function getComments($albumId, $photoId) {
            $sql = "SELECT content, comments.timestamp, first_name, middle_name, last_name, login, 
                        profilePicture, comments.id, reported
                    FROM photos, photo_albums, users, profile, comments
                    WHERE photos.albumId = :albumId AND photo_albums.albumId = photos.albumId AND
                        photoId = :photoId AND users.id = comments.from AND 
                        profile.userId = users.id AND comments.photo = :photoId
                    ORDER BY comments.timestamp DESC";
            $array = array(':albumId' => $albumId, ':photoId' => $photoId);
            $result = $this->db->fetch($sql, $array);
            return $result;
        }

        /**
         * Adds new photo to a particular album.
         * @param integer $albumId      The id of the album.
         * @param integer $timestamp    The timestamp the picture was uploaded.
         * @param string  $description  The description about the picture.
         * @param string  $url          The absolute url of the picture.
         * @param string  $thumbnailUrl The absolute url of the thumbnail of the picture.
         */
        public function addPhoto($albumId, $timestamp, $description, $url, $thumbnailUrl) {
            $sql = "INSERT INTO photos(`albumId`, `timestamp`, `description`, `url`, 
                `thumbnailUrl`) 
                    VALUES (:albumId, :timestamp, :description, :url, :thumbnailUrl)";
            $array = array(':albumId' => $albumId, ':timestamp' => $timestamp, 
                ':description' => $description, ':url' => $url, ':thumbnailUrl' => $thumbnailUrl); 
            $result = $this->db->execute($sql, $array);    
            if($result) {
                $tlh = new TimelineHelper();
                $content = $url;
                $tlh->addPost($_SESSION['username'], $_SESSION['username'], $content, "image"); 
            }
            return $result;
        }

        /**
         * Deletes a particular album. 
         * @param  integer $userId  The id of the user.
         * @param  integer $albumId The id of the album.
         * @return boolean          True if succeeded, false otherwise.
         */
        public function deleteAlbum($userId, $albumId) {
            $sql = "DELETE FROM `photo_albums` WHERE `albumId` = :albumId AND `user` = :userId";
            $array = array(':albumId' => $albumId, ':userId' => $userId); 
            return $this->db->execute($sql, $array); 
        }

        /**
         * Deletes one photo of a particular user in a particular photo album.
         * @param  integer $userId  The id of the user.
         * @param  integer $albumId The id of the album.
         * @param  integer $photoId The id of the photo.
         * @return boolean          True if succeeded, false otherwise.
         */
        public function deletePhoto($userId, $albumId, $photoId) {
            $tlh = new TimelineHelper();
            $url = $this->getPhoto($userId, $albumId, $photoId)['url'];
            $tlh->deletePost($url); 
            $sql = "DELETE FROM `photos` WHERE `albumId` = :albumId AND `photoId` = :photoId";
            $array = array(':albumId' => $albumId, ':photoId' => $photoId); 
            return $this->db->execute($sql, $array); 
        }

        /**
         * Adds comment to a particular photo.
         * @param  integer $userId    The id of the user.
         * @param  integer $photoId   The id of the photo.
         * @param  string  $comment   The content of the comment.
         * @param  integer $timestemp The time when the comment was created.
         * @return boolean            True if succeeded, false otherwise.
         */
        public function addComment($userId, $photoId, $comment, $timestamp) {
            $sql = "INSERT INTO `comments` (`from`, `wall_post`, `photo`, `content`, `timestamp`) 
                VALUES (:userId, NULL, :photoId, :comment, :timestamp)";
            $array = array(':userId' => $userId, ':photoId' => $photoId, ':comment' => $comment, 
                ':timestamp' => $timestamp); 
            return $this->db->execute($sql, $array); 
        }

        /**
         * Checks if the album belongs to the user. 
         * @param  integer $userId  The id of the user.
         * @param  integer $albumId The id of the album.
         * @return boolean          True if it is a valid pair, false otherwise.
         */
        public function isValidUsernameAlbumPair($userId, $albumId) {
            $sql = "SELECT * FROM photo_albums WHERE albumId = :albumId AND user = :userId";
            $array = array(':albumId' => $albumId, ':userId' => $userId); 
            if(sizeof($this->db->fetch($sql, $array)) != 1) {
                return false;
            } 
            return true;
        }

    }

?>

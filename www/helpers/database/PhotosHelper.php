<?php
    
    require_once('helpers/database/database.php');

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
            // $tlh = new TimelineHelper();
            // $content = "Created a new blog, <a href='/user/".$_SESSION['username'].
            //             "/blogs/$url/pages/1'>$name</a>";
            // $tlh->addPost($_SESSION['username'], $_SESSION['username'], $content, "blog");

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

    }

?>

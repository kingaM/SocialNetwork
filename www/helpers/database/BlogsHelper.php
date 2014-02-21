<?php
    
    require_once('helpers/database/database.php');

    class BlogsHelper {

        private $db = NULL;

        public function __construct() {
            $this->db = new DatabaseHelper();
        }

        public function getBlogs($id) {
            $sql = "SELECT name, about, url FROM blogs WHERE user = :id";
            $array = array(':id' => $id);
            $result = $this->db->fetch($sql, $array);
            return $result;
        }

    }

?>

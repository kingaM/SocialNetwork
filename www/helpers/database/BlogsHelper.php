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

        public function getBlogInfo($id, $url) {
            $sql = "SELECT name, about, url FROM blogs WHERE user = :id AND blogs.name = :url";
            $array = array(':id' => $id, ':url' => $url);
            $result = $this->db->fetch($sql, $array);
            return $result;
        }

        public function getBlogPosts($userId, $url, $page) {
            $sql = "SELECT posts.title, timestamp, content 
                    FROM posts, posts_details, blogs
                    WHERE blogs.name = :url AND blogs.blogId = posts.blogId AND 
                        posts.postId = posts_details.postId AND blogs.user = :userId
                    ORDER BY timestamp DESC
                    LIMIT :page, 2";
            $array = array(':url' => $url, ':page' => (int) (($page - 1) * 2), 
                ':userId' => $userId);
            $result = $this->db->fetch($sql, $array);
            return $result;
        }

        public function getBlogPostsNumber($userId, $url) {
            $sql = "SELECT COUNT(*) AS count
                    FROM posts, posts_details, blogs
                    WHERE blogs.name = :url AND blogs.blogId = posts.blogId AND 
                        posts.postId = posts_details.postId AND blogs.user = :userId";
            $array = array(':url' => $url, ':userId' => $userId);
            $result = $this->db->fetch($sql, $array);
            return $result[0]['count'];
        }

    }

?>

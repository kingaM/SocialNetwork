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
            $sql = "SELECT name, about, url FROM blogs WHERE user = :id AND blogs.url = :url";
            $array = array(':id' => $id, ':url' => $url);
            $result = $this->db->fetch($sql, $array);
            return $result;
        }

        public function getBlogPosts($userId, $url, $page) {
            $sql = "SELECT posts.title, timestamp, content 
                    FROM posts, posts_details, blogs
                    WHERE blogs.url = :url AND blogs.blogId = posts.blogId AND 
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
                    WHERE blogs.url = :url AND blogs.blogId = posts.blogId AND 
                        posts.postId = posts_details.postId AND blogs.user = :userId";
            $array = array(':url' => $url, ':userId' => $userId);
            $result = $this->db->fetch($sql, $array);
            return $result[0]['count'];
        }

        public function getBlogId($userId, $url) {
            $sql = "SELECT blogId FROM blogs WHERE url = :url AND user = :userId";
            $array = array(':url' => $url, ':userId' => $userId);
            $result = $this->db->fetch($sql, $array);
            if(sizeof($result) != 1) {
                return -1;
            } else {
                return $result[0]['blogId'];
            }
        }

        // TODO: Wrap it into transaction
        public function addBlogPost($userId, $url, $title, $content, $timestamp) {
            $sql = "INSERT INTO posts(`blogId`, `title`, `timestamp`)
                VALUES (:blogId, :title, :timestamp)";
            $blogId = $this->getBlogId($userId, $url);
            if($blogId < 0) {
                return -1;
            }
            $array = array(':blogId' => $blogId, ':title' => $title, 
                ':timestamp' => $timestamp); 
            if(!$this->db->execute($sql, $array)) {
                return -1;
            } 
            $id = $this->db->getLastId();
            $sql = "INSERT INTO posts_details(`postId`, `content`)
                VALUES (:postId, :content)";
            $array = array(':postId' => $id, ':content' => $content);
            $this->db->execute($sql, $array);
        }

        public function addBlog($userId, $name, $url, $about) {
            $sql = "INSERT INTO blogs(`user`, `name`, `url`, `about`)
                VALUES (:userId, :name, :url, :about)";
            $array = array(':userId' => $userId, ':name' => $name, ':url' => $url, 
                ':about' => $about);
            return $this->db->execute($sql, $array);
        }

        public function checkBlogUrlUnique($id, $url) {
            $sql = "SELECT url FROM blogs WHERE user = :id AND blogs.url = :url";
            $array = array(':id' => $id, ':url' => $url);
            $result = $this->db->fetch($sql, $array);
            if(sizeof($result) == 0) {
                return true;
            }
            return false;
        }

    }

?>

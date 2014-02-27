<?php
    
    require_once('helpers/database/database.php');

    class BlogsHelper {

        private $db = NULL;

        public function __construct() {
            $this->db = new DatabaseHelper();
        }

        /**
         * Gets blogs of the user.
         * @param  integer $id The id of the user
         * @return Array       An array containing all blogs of the user. The nested array contains
         *                     name, about and url of each blog.
         */
        public function getBlogs($id) {
            $sql = "SELECT name, about, url FROM blogs WHERE user = :id";
            $array = array(':id' => $id);
            $result = $this->db->fetch($sql, $array);
            return $result;
        }

        /**
         * Gets the name, about and url of a given blog.
         * @param  integer $id  The id of the user
         * @param  string  $url The url of the blog
         * @return Array        Containing the name, about and url of the blog. NULL if there is an
         *                      error.
         */
        public function getBlogInfo($id, $url) {
            $sql = "SELECT name, about, url FROM blogs WHERE user = :id AND blogs.url = :url";
            $array = array(':id' => $id, ':url' => $url);
            $result = $this->db->fetch($sql, $array);
            if(sizeof($result) != 1) {
                return NULL;
            }
            return $result[0];
        }

        /**
         * Gets 2 blog posts from a blog, depening on which page you are on.
         * @param  integer $userId The id of the user.
         * @param  string  $url    The url of the blog.
         * @param  integer $page   The page you would want to get.
         * @return Array           An array with all the posts and information about them.
         */
        public function getBlogPosts($userId, $url, $page) {
            $sql = "SELECT posts.postId, posts.title, timestamp, content 
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

        /**
         * Searches through blog posts and returns 2 blog posts from a blog, 
         * depening on which page you are on.
         * 
         * @param  integer $userId The id of the user.
         * @param  string  $url    The url of the blog.
         * @param  string  $url    The url of the blog.
         * @return Array           An array with all the posts and information about them.
         */
        public function searchBlogPosts($userId, $url, $searchText) {
            $sql = "SELECT posts.postId, posts.title, timestamp, content 
                    FROM posts, posts_details, blogs
                    WHERE blogs.url = :url AND blogs.blogId = posts.blogId AND 
                        posts.postId = posts_details.postId AND blogs.user = :userId
                        AND (content LIKE :searchText)
                    ORDER BY timestamp DESC";
            $searchText = '%' . $searchText . '%';
            $array = array(':url' => $url, 
                ':userId' => $userId, ':searchText' => $searchText);
            $result = $this->db->fetch($sql, $array);
            return $result;
        }

        /**
         * Gets blog posts from a blog depending on the post id.
         * @param  integer $userId The id of the user.
         * @param  string  $url    The url of the blog.
         * @param  integer $postId The id of the post.
         * @return Array           An array with the information about the post. 
         */
        public function getBlogPost($userId, $url, $postId) {
            $sql = "SELECT posts.postId, posts.title, timestamp, content 
                    FROM posts, posts_details, blogs
                    WHERE blogs.url = :url AND blogs.blogId = posts.blogId AND 
                        posts.postId = posts_details.postId AND blogs.user = :userId
                        AND posts.postId = :postId
                    ORDER BY timestamp DESC";
            $array = array(':url' => $url, ':postId' => $postId, 
                ':userId' => $userId);
            $result = $this->db->fetch($sql, $array);

            if(sizeof($result) != 1) {
                return -1;
            } else {
                return $result[0];
            }
        }

        /**
         * Gets the total number of posts of a given blog. 
         * @param  integer $userId The id of the user.
         * @param  string  $url    The url of the blog.
         * @return integer         The number of posts.
         */
        public function getBlogPostsNumber($userId, $url) {
            $sql = "SELECT COUNT(*) AS count
                    FROM posts, posts_details, blogs
                    WHERE blogs.url = :url AND blogs.blogId = posts.blogId AND 
                        posts.postId = posts_details.postId AND blogs.user = :userId";
            $array = array(':url' => $url, ':userId' => $userId);
            $result = $this->db->fetch($sql, $array);
            return $result[0]['count'];
        }

        /**
         * Gets the Id of the blog given the url and userId. 
         * @param  integer $userId The id of the user.
         * @param  string  $url    The url of the blog.
         * @return integer         The id of the blog or -1 id there is an error.
         */
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
        
        /**
         * Adds a post to a given blog.
         * @param  integer $userId    The id of the user.
         * @param  string  $url       The url of the blog.
         * @param  string  $title     The title of the post.
         * @param  string  $content   The content of the post.
         * @param  integer $timestamp The unix timestamp of the post.
         * @return boolean            Indicates if the insert succeeded or not.
         */
        public function addBlogPost($userId, $url, $title, $content, $timestamp) {
            try {
                $this->db->beginTransaction();
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
                return $this->db->commit();
            } catch (Exception $e) {
                return $this->db->rollBack();
            }
        }

        /**
         * Adds a blog.
         * @param  integer $userId The id of the user.
         * @param  string  $name   The name of the blog.
         * @param  string  $url    A unique identifier of the blog.
         * @param  string  $about  A short description of the blog.
         * @return boolean         Indicates if the insert succeeded or not.
         */
        public function addBlog($userId, $name, $url, $about) {
            $sql = "INSERT INTO blogs(`user`, `name`, `url`, `about`)
                VALUES (:userId, :name, :url, :about)";
            $array = array(':userId' => $userId, ':name' => $name, ':url' => $url, 
                ':about' => $about);
            return $this->db->execute($sql, $array);
        }

        /**
         * Checks if the url is already in the database.
         * @param  integer $userId The id of the user.
         * @param  string  $url    The url of the blog.
         * @return boolean      True if it doesn't exist, false otherwise.
         */
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

<?php

namespace App\Model;

use mysql_xdevapi\DatabaseObject;
use Nette;

class PostManager
{
    /**
     * @var Nette\Database\Context
     */
    private $database;

    /**
     * PostManager constructor.
     * @param Nette\Database\Context $database
     */
    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

//--------------------------------------------------------------------------------------------------------------------->

    /**
     * @return Nette\Database\IRow
     */
    public function getPublicPosts()
    {
        $posts = $this->database->query('
            SELECT p.wall_post_content,
                   p.wall_post_id,
                   p.wall_post_title,
                   p.wall_post_created_at,
                   p.hidden,
                   u.user_profile_img_path AS post_user_profile_img_path,
                   u.firstname AS post_firstname,
                   u.lastname AS post_lastname,
                   u.user_id AS post_user_id,
                   u.username AS post_username
            FROM wall_posts p
            LEFT JOIN users u ON u.user_id = p.user_id
            WHERE 
                p.deleted = false
                AND  
                p.hidden = false
            ORDER BY p.wall_post_created_at DESC
        ')->fetchAll();


        if(!$posts){
            return null;
        }

        foreach($posts as $post){
            $post->comments = $this->getPostComments($post->wall_post_id);

        }
        return $posts;
    }


//--------------------------------------------------------------------------------------------------------------------->


    /**
     * @param int $wall_post_id
     * @return Nette\Database\IRow[]
     */
    public function getPostComments(int $wall_post_id)
    {
        return $this->database->query('
            SELECT c.comment_content,
                   c.comment_created_at,
                   u.user_profile_img_path AS comment_user_profile_img_path,
                   u.firstname AS comment_firstname,
                   u.lastname AS comment_lastname,
                   u.username AS comment_username
            FROM comments c
            LEFT JOIN users u ON u.user_id = c.user_id
            WHERE c.wall_post_id = ?
            ORDER BY c.comment_created_at DESC
            LIMIT 5
            ', $wall_post_id
        )->fetchAll();
    }

//--------------------------------------------------------------------------------------------------------------------->


    /**
     * @param int $wall_post_id
     * @param bool $comments
     * @return Nette\Database\IRow[]
     */
    public function getPublicPostById(int $wall_post_id, bool $comments = true)
    {

        $post = $this->database->query("
            SELECT p.wall_post_content,
                   p.wall_post_id,
                   p.wall_post_title,
                   p.wall_post_created_at,
                   p.hidden,
                   u.user_profile_img_path AS post_user_profile_img_path,
                   u.firstname AS post_firstname,
                   u.lastname AS post_lastname,
                   u.user_id AS post_user_id,
                   u.username AS post_username
            FROM wall_posts p
            LEFT JOIN users u ON u.user_id = p.user_id
            WHERE 
                p.deleted = false
                AND
                p.wall_post_id = '$wall_post_id'
            ORDER BY p.wall_post_created_at DESC
        ")->fetchAll();

        if(!$post){
            return null;
        }

        if($comments){
            $post['comments'] = $this->getPostComments($wall_post_id);
        }
        return $post;

    }


//--------------------------------------------------------------------------------------------------------------------->
    /**
     * @param int $user_id
     * @param bool $comments
     * @return Nette\Database\IRow[]
     */
    public function getPublicPostsByUser(int $user_id, bool $comments = true)
    {
        $posts = $this->database->query("
            SELECT p.wall_post_content,
                   p.wall_post_id,
                   p.wall_post_title,
                   p.wall_post_created_at,
                   p.hidden,
                   u.user_profile_img_path AS post_user_profile_img_path,
                   u.firstname AS post_firstname,
                   u.lastname AS post_lastname,
                   u.user_id AS post_user_id,
                   u.username AS post_username
            FROM wall_posts p
            LEFT JOIN users u ON u.user_id = p.user_id
            WHERE 
                p.deleted = false
                AND
                p.hidden = false
                AND
                p.user_id = '$user_id'
            ORDER BY p.wall_post_created_at DESC
        ")->fetchAll();

        if(!$posts){
            return null;
        }

        if($comments){
            foreach($posts as $post){
                $post->comments = $this->getPostComments($post->wall_post_id);

            }
        }

        return $posts;
    }


    //--------------------------------------------------------------------------------------------------------------------->
    /**
     * @param int $user_id
     * @param bool $comments
     * @return Nette\Database\IRow[]
     */
    public function getPostsByUser(int $user_id, bool $comments = true)
    {
        $posts = $this->database->query("
            SELECT p.wall_post_content,
                   p.wall_post_id,
                   p.wall_post_title,
                   p.wall_post_created_at,
                   p.hidden,
                   u.user_profile_img_path AS post_user_profile_img_path,
                   u.firstname AS post_firstname,
                   u.lastname AS post_lastname,
                   u.user_id AS post_user_id,
                   u.username AS post_username
            FROM wall_posts p
            LEFT JOIN users u ON u.user_id = p.user_id
            WHERE 
                p.deleted = false
                AND
                p.user_id = '$user_id'
            ORDER BY p.wall_post_created_at DESC
        ")->fetchAll();

        if(!$posts){
            return null;
        }

        if($comments){
            foreach($posts as $post){
                $post->comments = $this->getPostComments($post->wall_post_id);

            }
        }

        return $posts;
    }

    //--------------------------------------------------------------------------------------------------------------------->
    /**
     * @param int $user_id
     * @param bool $comments
     * @return Nette\Database\IRow[]
     */
    public function getPublicPostsByUsername(string $username, bool $comments = true)
    {
        $posts = $this->database->query("
            SELECT p.wall_post_content,
                   p.wall_post_id,
                   p.wall_post_title,
                   p.wall_post_created_at,
                   p.hidden,
                   u.user_profile_img_path AS post_user_profile_img_path,
                   u.firstname AS post_firstname,
                   u.lastname AS post_lastname,
                   u.user_id AS post_user_id,
                   u.username AS post_username
            FROM wall_posts p
            LEFT JOIN users u ON u.user_id = p.user_id
            WHERE 
                p.deleted = false
                AND
                p.hidden = false
                AND
                u.username = '$username'
            ORDER BY p.wall_post_created_at DESC
        ")->fetchAll();

        if(!$posts){
            return null;
        }

        if($comments){
            foreach($posts as $post){
                $post->comments = $this->getPostComments($post->wall_post_id);

            }
        }

        return $posts;
    }




}
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

    /**
     * @return Nette\Database\IRow[]
     */
    public function getPublicPosts()
    {
        $posts = $this->database->query('
            SELECT p.wall_post_content,
                   p.wall_post_id,
                   p.wall_post_title,
                   p.wall_post_created_at,
                   u.firstname AS post_firstname,
                   u.lastname AS post_lastname
            FROM wall_posts p
            LEFT JOIN users u ON u.user_id = p.user_id
            ORDER BY p.wall_post_created_at DESC
        ')->fetchAll();

        foreach($posts as $post){
            $post->comments = $this->getPostComments($post->wall_post_id);

        }
        return $posts;
    }

    /**
     * @param int $wall_post_id
     * @return Nette\Database\IRow[]
     */
    public function getPostComments(int $wall_post_id)
    {
        return $this->database->query('
            SELECT c.comment_content,
                   c.comment_created_at,
                   u.firstname AS comment_firstname,
                   u.lastname AS comment_lastname
            FROM comments c
            LEFT JOIN users u ON u.user_id = c.user_id
            WHERE c.wall_post_id = ?
            ORDER BY c.comment_created_at DESC
            LIMIT 5
            ', $wall_post_id
        )->fetchAll();
    }

    /**
     * @param int $wall_post_id
     * @return Nette\Database\IRow[]
     */
    public function getPublicPostById(int $wall_post_id)
    {
        return $this->database->query("
        SELECT  wall_posts.wall_post_id,
                wall_posts.wall_post_title,
                wall_posts.wall_post_content,
                wall_posts.wall_post_created_at,
                wall_posts.user_id,
                users.firstname,
                users.lastname
        FROM    wall_posts
        JOIN users ON wall_posts.user_id = users.user_id
        WHERE wall_post_id = '$wall_post_id'
        ")->fetchAll();
    }




}
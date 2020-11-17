<?php

namespace App\Model;

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
        return $this->database->query('
        SELECT  wall_posts.wall_post_id,
                wall_posts.wall_post_title,
                wall_posts.wall_post_content,
                wall_posts.wall_post_created_at,
                wall_posts.user_id,
                users.firstname,
                users.lastname
        FROM    wall_posts
        JOIN users ON wall_posts.user_id = users.user_id
        WHERE wall_post_created_at < NOW() 
        ORDER BY wall_post_created_at DESC
        ')->fetchAll();
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
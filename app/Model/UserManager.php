<?php

namespace App\Model;

use Nette\Database\Context;
use Nette\Database\Table\ ActiveRow;

class UserManager{

    /**
     * @var Context
     */
    private $database;

    /**
     * PostManager constructor.
     * @param Context $database
     */
    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * @param int $user_id
     * @return ActiveRow|null
     */
    public function getLoggedUserData(int $user_id)
    {
        return $this->database->table('users')->get($user_id);
    }

    /**
     * @param string $username
     * @return mixed
     */
    public function getUserDataByUsername(string $username)
    {
        return $this->database->table('users')
            ->select('*')
            ->where('username = ?', $username)->fetch();
    }

    /**
     * @param int $limit
     * @return array
     */
    public function getRandomUsers(int $limit = 10):array
    {
        return $this->database->table('users')
            ->select('lastname, firstname, username, user_profile_img_path, about')
            ->where('username != ?', 'NULL')
            ->order('RAND()')
            ->limit($limit)
            ->fetchAll();
    }


}
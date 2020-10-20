<?php

namespace App\Model;

use Nette;

class RegisterUserManager
{
    use Nette\SmartObject;

    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function insertUserIntoDatabase(\stdClass $values): string
    {

        try{
            $this->database->table('users')->insert([
                'password'  => $values->password,
                'email'     => $values->email,
                'lastname'  => $values->lastname,
                'firstname' => $values->firstname,
                'username'  => $values->username
            ]);
            return ('User vas successfully added to database');
        }catch (Nette\Database\ConnectionException $e){
            return ('Database connection failed');
        }

    }

}
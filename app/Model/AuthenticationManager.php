<?php

namespace App\Model;

use Nette;
use Nette\Security\IIdentity;

class AuthenticationManager implements Nette\Security\IAuthenticator
{
    private $database;
    private $passwords;

    public function __construct(Nette\Database\Context $database, Nette\Security\Passwords $passwords)
    {
        $this->database = $database;
        $this->passwords= $passwords;
    }

    public function authenticate(array $credentials): IIdentity
    {
        [$username, $password] = $credentials;

        $row = $this->database->table('users')
            ->where('username', $username)
            ->fetch();

        if(!$row){
            throw new Nette\Security\AuthenticationException('User not found');
        }

//        if($row->password != $password){
//            throw new Nette\Security\AuthenticationException('Invalid password.');
//        }

        if(!$this->passwords->verify($password, $row->password)){
            throw new Nette\Security\AuthenticationException('Invalid password');
        }

        return new Nette\Security\Identity(
          $row->user_id,
          $row->role,
          [
              'username'    => $row->username,
              'lastname'    => $row->lastname,
              'firstname'   => $row->firstname,
              'email'       => $row->email
          ]
        );
    }

}

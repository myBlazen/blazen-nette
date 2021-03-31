<?php

namespace App\Model;

use Nette;

class GameManager
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

    public function getGames()
    {
        $games= $this->database->table('blazen_games')->fetchAll();
        return $games;
    }

    public function getGameById($game_id)
    {
        return $this->database->table('blazen_games')->get($game_id);
    }

}
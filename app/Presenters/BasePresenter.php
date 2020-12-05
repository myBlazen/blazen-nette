<?php

namespace App\Presenters;

use Nette\Application\UI\Presenter;
use Nette\Database\Context;

abstract class BasePresenter extends Presenter
{
    /**
     * @var Context
     */
    private $database;

    /**
     * BasePresenter constructor.
     * @param Context $database
     */
    public function __construct(Context $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    public function beforeRender()
    {
        if ($this->getUser()->isLoggedIn()) {
            $this->template->userData = $this->getLoggedUserData();
        }
    }


    /**
     * @return \Nette\Http\UrlScript
     */
    public function getUrl()
    {
        return $this->getHttpRequest()->getUrl();
    }
    /**
     * @return string
     */
    public function getHost():string
    {
        return $this->getHttpRequest()->getUrl()->getHost();
    }

    /**
     * @return string
     */
    public function getPath():string
    {
        return $this->getHttpRequest()->getUrl()->getPath();
    }

    /**
     * @return string
     */
    public function getScheme():string
    {
        return $this->getHttpRequest()->getUrl()->getScheme();
    }

    /**
     * @return string|null
     */
    public function getUserEmailFromUrl():?string
    {
        return $this->getHttpRequest()->getUrl()->getQueryParameter('email');
    }

    /**
     * @return string|null
     */
    public function getUserHashFromUrl():?string
    {
        return $this->getHttpRequest()->getUrl()->getQueryParameter('hash');
    }

    /**
     * @param $post_user_id
     * @return bool
     */
    public function isPostOwner($post_user_id):bool
    {
        if($post_user_id == $this->getUser()->getId()){
            return true;
        }
        return false;
    }

    /**
     * @return \Nette\Database\Table\ActiveRow|null
     */
    public function getLoggedUserData()
    {
        return $this->database->table('users')->get($this->getUser()->getId());
    }

}

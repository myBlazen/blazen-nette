<?php

namespace App\Presenters;

use App\Model\UserManager;
use Nette\Application\UI\Presenter;
use Nette\Database\Context;
use Nette\Http\UrlScript;

abstract class BasePresenter extends Presenter
{
    /**
     * @var Context
     */
    private $database;

    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * BasePresenter constructor.
     * @param Context $database
     * @param UserManager $userManager
     */
    public function __construct(Context $database, UserManager $userManager)
    {
        parent::__construct();
        $this->userManager = $userManager;
        $this->database = $database;
    }

    public function beforeRender()
    {
        if ($this->getUser()->isLoggedIn()) {
            $this->template->loggedUserData = $this->database->table('users')->get($this->getUser()->getId());
            $this->template->isAdmin = $this->isAdmin();
        }
    }

    /**
     * @return bool
     */
    public function isAdmin():bool
    {
        return $this->getUser()->roles[0] == "admin" && $this->getUser()->isLoggedIn() && $this->getUser()->roles;
    }

    /**
     * @return UrlScript
     */
    public function getUrl():UrlScript
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
        return $post_user_id == $this->getUser()->getId();
    }
}

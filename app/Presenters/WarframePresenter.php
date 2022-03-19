<?php


namespace App\Presenters;

use Nette;

use App\Model\UserManager;
use App\Model\PostManager;
use Nette\Application\UI\Form;
use Nette\Security\Passwords;
use Nette\Database\Context;

final class WarframePresenter extends BasePresenter
{
    /**
     * @var Context
     */
    private $database;

    /**
     * @var PostManager
     */
    private $postManager;

    /**
     * @var Passwords
     */
    private $passwords;

    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * UserPresenter constructor.
     * @param Context $database
     * @param PostManager $postManager
     * @param Passwords $passwords
     * @param UserManager $userManager
     */
    public function __construct(
        Context     $database,
        PostManager $postManager,
        Passwords   $passwords,
        UserManager $userManager
    )
    {
        parent::__construct($database, $userManager);
        $this->database = $database;
        $this->postManager = $postManager;
        $this->passwords = $passwords;
        $this->userManager = $userManager;
    }


    /**
     * @throws Nette\Application\AbortException
     */
    protected function startup()
    {
        parent::startup();


    }

    public function handleRefreshCetusCycle(){
        if($this->isAjax()){
            $this->redrawControl('cetusCycle');
        }
    }
}
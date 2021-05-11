<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

use Nette\Database\Context;
use App\Model\UserManager;
use \Nette\Application\AbortException;

final class FindPresenter extends BasePresenter
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
     * FindPresenter constructor.
     * @param Context $database
     * @param UserManager $userManager
     */
    public function __construct(Context $database, UserManager $userManager)
    {
        parent::__construct($database, $userManager);
        $this->database = $database;
        $this->userManager = $userManager;
    }

    /**
     * @throws AbortException
     */
    protected function startup()
    {
        parent::startup();

        if (!$this->user->isLoggedIn()) {
            if ($this->user->logoutReason === Nette\Http\UserStorage::INACTIVITY) {
                $this->flashMessage('You have been signed out due to inactivity. Please sign in again.', 'alert-info');
            }
            $this->redirect('Sign:in', ['backlink' => $this->storeRequest()]);
        }
    }

    public function RenderDefault(): void
    {
        $this->template->randomUsers = $this->userManager->getRandomUsers(10, $this->getUser()->getId());
    }

    /**
     * @param string $userToReceive
     * @throws AbortException
     */
    public function ActionSendFriendRequest(string $userToReceive)
    {
        try{
            $this->userManager->insertFriendRequest($userToReceive, $this->getUser()->getId());
        }
        catch(\Exception $e){
            $this->flashMessage($e->getMessage(), 'alert-danger');
            $this->redirect('Find:');
        }
        $this->flashMessage('Friend request was sent!','alert-success');
        $this->redirect('Find:');
    }

    /**
     * @param $request_id
     * @throws AbortException
     */
    public function ActionCancelFriendRequest(int $request_id)
    {
        try{
            $this->userManager->deleteFriendRequest($request_id);
        }
        catch (\Exception $e){
            $this->flashMessage($e->getMessage(), 'alert-danger');
            $this->redirect('Find:');
        }
        $this->flashMessage('Friend request was canceled!','alert-success');
        $this->redirect('Find:');
    }

    /**
     * @param int $request_id
     * @throws AbortException
     */
    public function ActionAcceptFriendRequest(int $request_id)
    {
        try {
            $this->userManager->updateFriendRequestStatus($request_id, 'accepted');
        }catch (\Exception $e){
            $this->flashMessage($e->getMessage(), 'alert-danger');
            $this->redirect('Find:');
        }
        $this->flashMessage('Friend request was accepted!','alert-success');
        $this->redirect('Find:');
    }
}
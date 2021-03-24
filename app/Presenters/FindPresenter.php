<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

final class FindPresenter extends BasePresenter
{
    /**
     * @var Nette\Database\Context
     */
    private $database;

    /**
     * FindPresenter constructor.
     * @param Nette\Database\Context $database
     */
    public function __construct(Nette\Database\Context $database)
    {
        parent::__construct($database);
        $this->database = $database;
    }

    /**
     * @throws \Nette\Application\AbortException
     */
    protected function startup()
    {
        parent::startup();

        if (!$this->user->isLoggedIn()) {
            if ($this->user->logoutReason === Nette\Http\UserStorage::INACTIVITY) {
                $this->flashMessage('You have been signed out due to inactivity. Please sign in again.');
            }
            $this->redirect('Sign:in', ['backlink' => $this->storeRequest()]);
        }
    }

    public function RenderDefault(): void
    {
        $this->template->randomUsers = $this->getRandomUsers(10);
        bdump($this->getRandomUsers(10));
        bdump($this->isAdmin());
    }




}
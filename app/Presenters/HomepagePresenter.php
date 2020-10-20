<?php

declare(strict_types=1);

namespace App\Presenters;

final class HomepagePresenter extends BasePresenter
{
    protected function beforeRender()
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
    }
}

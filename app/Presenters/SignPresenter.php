<?php

namespace App\Presenters;

use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;

class SignPresenter extends BasePresenter
{
    protected function beforeRender()
    {
        if ($this->getUser()->isLoggedIn()) {
            $this->redirect('Homepage:');
        }
    }

    protected function createComponentSignInForm(): Form
    {
        $form = new Form;

        $form->addText('username')
            ->setRequired('Username is required!');

        $form->addPassword('password')
            ->setRequired('Password is required!');

        $form->addSubmit('signIn', 'Login');

        $form->onSuccess[] = [$this, 'signInFormSucceeded'];
        return $form;
    }

    public function signInFormSucceeded(Form $form, \stdClass $values): void
    {
        try{
            $this->getUser()->login($values->username, $values->password);
            $this->getUser()->setExpiration('15 minutes');
            $this->redirect('Homepage:');
        }catch (AuthenticationException $e){
            $form->addError('Incorrect username or password');
        }
    }

    public function actionOut(): void
    {
        $this->getUser()->logout();
        $this->flashMessage('Succesfully logged out');
        $this->redirect('Sign:in');
    }
}
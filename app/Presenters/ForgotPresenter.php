<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette\Database\Context;
use Nette\Http\Url;
use Latte\Engine;
use Nette\Application\UI\Form;
use Nette\Mail\Message;
use Nette\Mail\SendException;
use Nette\Mail\SendmailMailer;
use Nette\Security\Passwords;
use App\Model\UserManager;

final class ForgotPresenter extends BasePresenter
{
    /**
     * @var Context
     */
    private $database;
    /**
     * @var Passwords
     */
    private $passwords;

    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * ForgotPresenter constructor.
     * @param Context $database
     * @param Passwords $passwords
     * @param UserManager $userManager
     */
    public function __construct(Context $database, Passwords $passwords, UserManager $userManager)
    {
        parent::__construct($database, $userManager);
        $this->database = $database;
        $this->passwords = $passwords;
        $this->userManager = $userManager;
    }

    public function beforeRender()
    {
        parent::beforeRender();
        if ($this->getUser()->isLoggedIn()) {
            $this->redirect('Homepage:');
        }
    }

    /**
     * @return bool
     */
    public function showResetPasswordForm(): bool
    {
        $url = $this->getUrl();
        $email = $url->getQueryParameter('email');
        $hash = $url->getQueryParameter('hash');

        if(isset($hash) && $hash != '' && isset($email) && $email != ''){
            return true;
        }
        return false;
    }

    /**
     *
     */
    public function renderPassword(): void
    {
        $this->template->showResetPasswordForm = $this->showResetPasswordForm();
    }

    public function getUserHashUsingEmail($email): ?string
    {
        try{
            $query = $this->database->table('users')
                ->select('hash')
                ->where('email = ?', $email)->fetchAll();

            return $query[0]['hash'];
        }catch (\Exception $e){
            $this->flashMessage('Account with this email doesnt exist', 'alert-info');
        }
        return null;
    }

    /**
     * @return Form
     */
    public function createComponentResetPasswordFormEmail(): Form
    {
        $form = new Form;

        $form->addText('email')
            ->setRequired('Enter your email');

        $form->addSubmit('resetPasswordEmail', 'Send email');

        $form->onSuccess[] = [$this, 'resetPasswordEmailSucceeded'];

        return $form;
    }

    /**
     * @param Form $form
     * @param \stdClass $values
     */
    public function resetPasswordEmailSucceeded(Form $form, \stdClass $values):void
    {
        $latte = new Engine();
        $mail = new Message();

        $url = new Url;

        $url->setScheme($this->getScheme())
            ->setHost($this->getHost())
            ->setPath($this->getPath())
            ->setQueryParameter('email', $values->email)
            ->setQueryParameter('hash', $this->getUserHashUsingEmail($values->email));

        $params = [
            'url' => $url
        ];

        $mail->setFrom('my.blazen@gmail.com', 'BLAZEN')
            ->addTo($values->email)
            ->setSubject('BLAZEN - Password recovery')
            ->setHtmlBody(
                $latte->renderToString(__DIR__.'/templates/Email/passwordRecoveryEmail.latte', $params), 'images'
            );

        $mailer = new SendmailMailer();

        try{
            $mailer->send($mail);
        }catch (SendException $e){
            $this->flashMessage('Failed to send mail', 'alert-danger');
        }

    }

    /**
     * @return Form
     */
    public function createComponentResetPasswordForm(): Form
    {
        $form = new Form;

        $form->addHidden('email', $this->getUserEmailFromUrl());

        $form->addHidden('hash', $this->getUserHashFromUrl());

        $passwordInput = $form->addPassword('password', 'Password')
            ->setRequired('Please enter password');

        $form->addPassword('repeat_password', 'Password (verify)')
            ->setRequired('Please enter password for verification')
            ->addRule($form::EQUAL, 'Password verification failed. Passwords do not match', $passwordInput);

        $form->addSubmit('resetPassword', 'Reset password');

        $form->onSuccess[] = [$this, 'resetPasswordSucceeded'];
        return $form;

    }

    /**
     * @param Form $form
     * @param \stdClass $values
     */
    public function resetPasswordSucceeded(Form $form, \stdClass $values): void
    {
        $data = array(
            'password'  => $this->passwords->hash($values->password)
        );

        $this->database->table('users')
            ->where('email = ? AND hash = ?', $values->email, $values->hash)
            ->update($data);

        $this->redirect('Sign:in');
    }



}
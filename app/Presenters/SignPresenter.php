<?php

namespace App\Presenters;

use Latte\Engine;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Database\UniqueConstraintViolationException;
use Nette\Http\Url;
use Nette\Mail\Message;
use Nette\Mail\SendException;
use Nette\Mail\SendmailMailer;
use Nette\Security\AuthenticationException;
use Nette\Security\Passwords;

class SignPresenter extends BasePresenter
{
    /**
     * @var Context
     */
    private $database;

    /**
     * @var Passwords
     */
    private $passwords;

    public function __construct(Context $database, Passwords $passwords)
    {
        parent::__construct($database);
        $this->database = $database;
        $this->passwords = $passwords;
    }

    public function beforeRender()
    {
        parent::beforeRender();
        if ($this->getUser()->isLoggedIn()) {
            $this->redirect('Homepage:');
        }
    }

/**---------------------------------------------------SIGNIN----------------------------------------------------------->

    /**
     * @return Form
     */
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

    /**
     * @param Form $form
     * @param \stdClass $values
     * @throws \Nette\Application\AbortException
     */
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

/**--------------------------------------------------SIGNOUT----------------------------------------------------------->

    /**
     * @throws \Nette\Application\AbortException
     */
    public function actionOut(): void
    {
        $this->getUser()->logout();
        $this->flashMessage('logged out');
        $this->redirect('Sign:in');
    }


/**---------------------------------------------------SIGNUP----------------------------------------------------------->


    /**
     * @return bool
     */
    public function showRegisterForm(): bool
    {
        $url = $this->getUrl();
        $email = $url->getQueryParameter('email');
        $hash = $url->getQueryParameter('hash');

        if(isset($hash) && $hash != '' && isset($email) && $email != ''){
            return false;
        }
        return true;
    }

    /**
     *
     */
    public function renderUp(): void
    {
        $this->template->showRegisterForm = $this->showRegisterForm();
    }

    /**
     * @return Form
     */
    protected function createComponentRegisterForm(): Form
    {
        $form = new Form;

        $form->addText('firstname')
            ->setRequired('Firstrname is required!');

        $form->addText('lastname')
            ->setRequired('Lastname is required!');

        $form->addEmail('email')
            ->setRequired('Email is required!');


        $form->addSubmit('register', 'Register Account');

        $form->onSuccess[] = [$this, 'RegisterFormSucceeded'];
        return $form;
    }

    /**
     * @param Form $form
     * @param \stdClass $values
     */
    public function RegisterFormSucceeded(Form $form, \stdClass $values): void
    {
        $latte = new Engine();
        $mail = new Message();

        $url = new Url;

        $sendMail = true;

        $hash = bin2hex(random_bytes(32));

        $url->setScheme($this->getScheme())
            ->setHost($this->getHost())
            ->setPath($this->getPath())
            ->setQueryParameter('email', $values->email)
            ->setQueryParameter('hash', $hash);

        try{
            $this->database->table('users')->insert([
                'email'     => $values->email,
                'lastname'  => $values->lastname,
                'firstname' => $values->firstname,
                'hash'  => $hash,
            ]);
        }catch (UniqueConstraintViolationException $e){
            $this->flashMessage('This email is already reqistered');
            $sendMail = false;
        }catch (\Exception $e){
            $this->flashMessage('db insert failed');
            $sendMail = false;
        }

        $params = [
            'name' => $values->firstname . ' ' . $values->lastname,
            'emailUrl' => $url
        ];

        $mail ->setFrom('my.blazen@gmail.com', 'BLAZEN')
            ->addTo($values->email)
            ->setSubject('BLAZEN - Registration')
            ->setHtmlBody(
                $latte->renderToString(__DIR__.'/templates/Email/registerEmail.latte', $params), 'images'
            );

        $mailer = new SendmailMailer();

        if($sendMail){
            try{
                $mailer->send($mail);
                $this->flashMessage('Email send.. it can take a while. U need to open this email to continue registration');
            }catch (SendException $e) {
                $this->flashMessage('Failed to send mail');
            }
        }
    }

    /**
     * @return Form
     */
    protected function createComponentRegisterPasswordForm(): Form
    {
        $form = new Form;

        $form->addHidden('email', $this->getUserEmailFromUrl());

        $form->addHidden('hash', $this->getUserHashFromUrl());

        $form->addText('username')
            ->setRequired('Username is required!');

        $passwordInput = $form->addPassword('password', 'Password')
            ->setRequired('Please enter password');

        $form->addPassword('repeat_password', 'Password (verify)')
            ->setRequired('Please enter password for verification')
            ->addRule($form::EQUAL, 'Password verification failed. Passwords do not match', $passwordInput);

        $form->addSubmit('registerPassword', 'Register Account');

        $form->onSuccess[] = [$this, 'RegisterPasswordFormSucceeded'];

        return $form;
    }

    /**
     * @param Form $form
     * @param \stdClass $values
     */
    public function RegisterPasswordFormSucceeded(Form $form, \stdClass $values): void
    {
        $latte = new Engine();
        $mail = new Message();

        $sendMail = true;

        $data = array(
            'username' => $values->username,
            'password'  => $this->passwords->hash($values->password)
        );

        try{
            $this->database->table('users')
                ->where('hash = ? AND email = ?', $values->hash, $values->email)
                ->update($data);

        }catch (\Exception $e) {
            $this->flashMessage('something went wrong');
            $sendMail = false;
        }

        $params = [
            'username' => $values->username,
        ];

        $mail ->setFrom('my.blazen@gmail.com', 'BLAZEN')
            ->addTo($values->email)
            ->setSubject('BLAZEN - Your account is complete!')
            ->setHtmlBody(
                $latte->renderToString(__DIR__.'/templates/Email/registerCompleteEmail.latte', $params), 'images'
            );

        $mailer = new SendmailMailer();

        if($sendMail){
            try{
                $mailer->send($mail);
            }catch (SendException $e){
                $this->flashMessage('Failed to send mail');
            }
            $this->flashMessage('You have successfully registered, now u can sign in');
            $this->redirect('Sign:in');
        }

    }



}
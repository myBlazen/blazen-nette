<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\RegisterUserManager;
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


class RegisterPresenter extends BasePresenter
{
    protected function beforeRender()
    {
        if ($this->getUser()->isLoggedIn()) {
            $this->redirect('Homepage:');
        }
    }

    private $database;
    private $registerUserManager;
    private $passwords;

    public function __construct
    (
        Context $database,
        RegisterUserManager $registerUserManager,
        Passwords $passwords
    )
    {
        $this->database = $database;
        $this->registerUserManager = $registerUserManager;
        $this->passwords = $passwords;
    }

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

    public function getHost():string
    {
        return $this->getHttpRequest()->getUrl()->getHost();
    }

    public function getPath():string
    {
        $path = $this->getHttpRequest()->getUrl()->getPath();
        return str_replace('one', 'two', $path);
    }

    public function RegisterFormSucceeded(Form $form, \stdClass $values): void
    {
        $latte = new Engine();
        $mail = new Message();
        $url = new Url;

        $ERRORS = null;

        try{
            $hash = bin2hex(random_bytes(32));
        }catch(\Exception $e){
            $this->flashMessage('Hash generation failed');
            $ERRORS = 'fail';
        }
        try{
            $this->database->table('users')->insert([
                'email'     => $values->email,
                'lastname'  => $values->lastname,
                'firstname' => $values->firstname,
                'hash'  => $hash,
            ]);
        }catch (UniqueConstraintViolationException $e){
            $this->flashMessage('This email is already reqistered');
            $ERRORS = 'fail';
        }catch (\Exception $e){
            $this->flashMessage('db insert failed');
            $ERRORS = 'fail';
        }
        if($ERRORS === null){
            try{
                $url->setScheme('http')
                    ->setHost($this->getHost())
                    ->setPath($this->getPath())
                    ->setQueryParameter('email', $values->email)
                    ->setQueryParameter('hash', $hash);

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
                $mailer->send($mail);
            }catch (SendException $e) {
                $this->flashMessage('Failed to send mail');
            }
        }
    }

    public function renderPartTwo(string $hash, string $email):void
    {

    }

    protected function createComponentRegisterFormPassword(): Form
    {
        $form = new Form;

        $form->addText('username')
            ->setRequired('Username is required!');

        $passwordInput = $form->addPassword('password', 'Password')
            ->setRequired('Please enter password');

        $form->addPassword('repeat_password', 'Password (verify)')
            ->setRequired('Please enter password for verification')
            ->addRule($form::EQUAL, 'Password verification failed. Passwords do not match', $passwordInput);

        $form->addSubmit('registerPassword', 'Register Account');

        $form->onSuccess[] = [$this, 'RegisterFormPasswordSucceeded'];
        return $form;
    }

    public function RegisterFormPasswordSucceeded(Form $form, \stdClass $values): void
    {
        $latte = new Engine();
        $mail = new Message();

        $ERRORS = null;

        $url = $this->getHttpRequest()->getUrl();
        $email = $url->getQueryParameter('email');
        $hash = $url->getQueryParameter('hash');

        try{

            $data = array(
                'username' => $values->username,
                'password'  => $this->passwords->hash($values->password)
            );

            $this->database->table('users')
                ->where('hash = ? AND email = ?', $hash, $email)
                ->update($data);

        }catch (\Exception $e) {
            $this->flashMessage('něco se pomrdalo');
            $ERRORS = 'fail';
        }
        if($ERRORS === null){
            try{
                $params = [
                    'username' => $values->username,

                ];

                $mail ->setFrom('my.blazen@gmail.com', 'BLAZEN')
                    ->addTo($email)
                    ->setSubject('BLAZEN - Your account is complete!')
                    ->setHtmlBody(
                        $latte->renderToString(__DIR__.'/templates/Email/registerCompleteEmail.latte', $params), 'images'
                    );

                $mailer = new SendmailMailer();
                $mailer->send($mail);
            }catch (SendException $e){
                $this->flashMessage('Failed to send mail');
            }
            $this->redirect('Sign:in');
        }
    }

}

<?php

namespace App\Presenters;

use Nette;

use App\Model\UserManager;
use App\Model\PostManager;
use Nette\Application\UI\Form;
use Nette\Security\Passwords;
use Nette\Database\Context;

final class UserPresenter extends BasePresenter
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
        Context $database,
        PostManager $postManager,
        Passwords $passwords,
        UserManager $userManager
    ){
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

        if (!$this->user->isLoggedIn()) {
            if ($this->user->logoutReason === Nette\Http\UserStorage::INACTIVITY) {
                $this->flashMessage('You have been signed out due to inactivity. Please sign in again.', 'alert-info');
            }
            $this->redirect('Sign:in', ['backlink' => $this->storeRequest()]);
        }
    }


    public function RenderProfile(string $username = null): void
    {
        $isUserProfile = $this->isUserProfile($this->userManager->getUserIdByUsername($username));
        if($this->userManager->getUserIdByUsername($username) === null){
            $this->template->userNotFound = true;
            return;
        }
        $this->template->userNotFound = false;
        $this->template->isUserProfile = $isUserProfile;
        if($isUserProfile){
            $this->template->wall_posts = $this->postManager->getPostsByUser($this->getUser()->getId(),true);
            $this->template->userData = $this->userManager->getLoggedUserData($this->getUser()->getId());
            $this->template->userFriendRequests = $this->userManager->getFriendRequestsForUser($this->getUser()->getId());
            $this->template->friends = $this->userManager->getUserFriends($this->getUser()->getId());
        }
        else{
            $this->template->wall_posts = $this->postManager->getPublicPostsByUsername($username);
            $this->template->userData = $this->userManager->getUserDataByUsername($username);
            $this->template->friends = $this->userManager->getUserFriends($this->userManager->getUserIdByUsername($username));
        }

    }


    /**
     * @return Form
     */
    protected function createComponentUploadImageForm(): Form
    {
        $form = new Form();

        $form->addHidden('user_id', $this->getUser()->getId());

        $form->addUpload('image','images')
            ->setRequired()
            ->addRule($form::IMAGE, 'Please select file format JPEG, PNG or GIF.')
            ->addRule($form::MAX_FILE_SIZE, 'Maximum size is 5 MB.', 1024 * 1024 * 5);

        $form->addSubmit('uploadImage', 'Upload Image');

        $form->onSuccess[] = [$this, 'uploadImageSucceeded'];

        return $form;
    }

    /**
     * @param Form $form
     * @param \stdClass $values
     * @throws Nette\Application\AbortException
     */
    public function uploadImageSucceeded(Form $form, \stdClass $values): void
    {
        $path = "/users_images/" . $values->user_id . "/profile_image/" . $values->image->getName();

        $data = array(
            'user_profile_img_path' => $path
        );

        $user = $this->database->table('users')->get($this->getUser()->getId());
        $user->update($data);

        $values->image->move("../www" . $path);

        $this->flashMessage('Image was uploaded', 'alert-success');

        $this->redirect('User:settings');
    }

    /**
     * @return Form
     */
    protected function createComponentCommentPostForm(): Form
    {
        $form = new Form;

        $form->addHidden('user_id', $this->getUser()->getId())
            ->setRequired();

        $form->addHidden('wall_post_id')
            ->setRequired();

        $form->addTextArea('comment_content', 'Comment')
            ->setRequired();

        $form->addSubmit('commentPost', 'Comment');

        $form->onSuccess[] = [$this, 'commentPostFormSucceeded'];

        return $form;
    }

    /**
     * @param Form $form
     * @param array $values
     */
    public function commentPostFormSucceeded(Form $form, array $values): void
    {
        $this->database->table('comments')->insert($values);

        $values = null;

        $this->flashMessage('Comment was published', 'alert-success');

        $this->redirect('User:profile');

    }

    public function actionSettings()
    {
        $data = $this->userManager->getLoggedUserData($this->getUser()->getId());
        $this['generalSettingsForm']->setDefaults($data);
        $this['informationSettingsForm']->setDefaults($data);
        $this['connectionsSettingsForm']->setDefaults($data);

    }

    /**
     * @return Form
     */
    public function createComponentGeneralSettingsForm():Form
    {
        $form = new Form;

        $form->addText('firstname', 'First Name')
            ->setRequired();

        $form->addText('lastname', 'Last Name')
            ->setRequired();

        $form->addText('username', 'Username')
            ->setRequired();

        $form->addPassword('password','Password')
            ->setRequired();

        $form->addSubmit('saveChanges', 'Save Changes');

        $form->onSuccess[] = [$this, 'GeneralSettingsFormSucceeded'];

        return $form;
    }

    /**
     * @param Form $form
     * @param array $values
     * @throws Nette\Application\AbortException
     */
    public function GeneralSettingsFormSucceeded(Form $form, array $values):void
    {

        $user = $this->database->table('users')->get($this->getUser()->getId());

        if($user){
            if($this->passwords->verify($values['password'], $user['password'])){
                unset($values['password']);
                if($values['birthday'] ===''){
                    unset($values['birthday']);
                }
                $user->update($values);

                $this->flashMessage('Changes saved', 'alert-success');

                $this->redirect('User:settings');
            }
            else{
                $this->flashMessage('Your password is incorect','alert-danger');

                $this->redirect('User:settings');
            }
        }
        else{
            $this->flashMessage('Uups something went wrong', 'alert-danger');

            $this->redirect('User:settings');
        }

    }

    /**
     * @return Form
     */
    public function createComponentInformationSettingsForm():Form
    {
        $form = new Form;

        $form->addTextArea('about', 'About Me');

        $form->addText('birthday', 'Birthday')
            ->setHtmlType('date');

        $form->addText('birthplace', 'Birthplace');

        $form->addText('lives_in', 'Lives In');

        $form->addText('occupation', 'Occupation');

        $form->addSelect('sex', 'Sex')
            ->setItems(array(
                '' => 'select...',
                'Male' => 'Male',
                'Female' => 'Female'
            ));

        $form->addSelect('status', 'Status')
            ->setItems(array(
                '' => 'select...',
                'Single' => 'Single',
                'In Relationship' => 'In Relationship'
                ));

        $form->addPassword('password','Password')
            ->setRequired();


        $form->addSubmit('saveChanges', 'Save Changes');

        $form->onSuccess[] = [$this, 'InformationSettingsFormSucceeded'];

        return $form;
    }

    /**
     * @param Form $form
     * @param array $values
     * @throws Nette\Application\AbortException
     */
    public function InformationSettingsFormSucceeded(Form $form, array $values): void
    {
        $user = $this->database->table('users')->get($this->getUser()->getId());

        if($user){
            if($this->passwords->verify($values['password'], $user['password'])){
                unset($values['password']);
                $user->update($values);

                $this->flashMessage('Changes saved','alert-success');

                $this->redirect('User:settings');
            }
            else{
                $this->flashMessage('Your password is incorect','alert-danger');

                $this->redirect('User:settings');
            }
        }
        else{
            $this->flashMessage('Uups something went wrong','alert-danger');

            $this->redirect('User:settings');
        }
    }

    public function createComponentConnectionsSettingsForm():Form
    {
        $form = new Form;

        $form->addText('facebook_name', 'Facebook');

        $form->addText('instagram_name', 'Instagram');

        $form->addPassword('password','Password')
            ->setRequired();

        $form->addSubmit('saveChanges', 'Save Changes');

        $form->onSuccess[] = [$this, 'ConnectionsSettingsFormSucceeded'];

        return $form;
    }


    /**
     * @param Form $form
     * @param array $values
     * @throws Nette\Application\AbortException
     */
    public function ConnectionsSettingsFormSucceeded(Form $form, array $values): void
    {
        $user = $this->database->table('users')->get($this->getUser()->getId());

        if($user){
            if($this->passwords->verify($values['password'], $user['password'])){
                unset($values['password']);
                $user->update($values);

                $this->flashMessage('Changes saved','alert-success');

                $this->redirect('User:settings');
            }
            else{
                $this->flashMessage('Your password is incorect','alert-danger');

                $this->redirect('User:settings');
            }
        }
        else{
            $this->flashMessage('Uups something went wrong','alert-danger');

            $this->redirect('User:settings');
        }
    }
}

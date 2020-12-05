<?php

namespace App\Presenters;

use Nette;
use App\Model\PostManager;
use Nette\ComponentModel\IComponent;
use Nette\Application\UI\Form;
use Nette\Utils\Image;

class UserPresenter extends BasePresenter
{
    /**
     * @var Nette\Database\Context
     */
    private $database;

    /**
     * @var PostManager
     */
    private $postManager;

    /**
     * HomepagePresenter constructor.
     * @param Nette\Database\Context $database
     */
    public function __construct(Nette\Database\Context $database, PostManager $postManager)
    {
        parent::__construct($database);
        $this->database = $database;
        $this->postManager = $postManager;
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


    public function RenderProfile(): void
    {
        $this->template->wall_posts = $this->postManager->getPostsByUser($this->getUser()->getId(),true);
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
            ->addRule($form::MAX_FILE_SIZE, 'Maximum size is 1 MB.', 1024 * 1024);

        $form->addSubmit('uploadImage', 'Upload Image');

        $form->onSuccess[] = [$this, 'uploadImageSucceeded'];

        return $form;
    }

    /**
     * @param Form $form
     * @param \stdClass $values
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

        $this->flashMessage('Image was uploaded');

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

        $this->flashMessage('Comment was published');

        $this->redirect('User:profile');

    }






}
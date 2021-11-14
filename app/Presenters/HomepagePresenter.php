<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use App\Model\PostManager;
use App\Model\UserManager;
use Nette\Application\UI\Form;
use Nette\Database\Context;

final class HomepagePresenter extends BasePresenter
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
     * @var UserManager
     */
    private $userManager;

    /**
     * HomepagePresenter constructor.
     * @param Context $database
     * @param PostManager $postManager
     * @param UserManager $userManager
     */
    public function __construct(Context $database, PostManager $postManager, UserManager $userManager)
    {
        parent::__construct($database, $userManager);
        $this->database = $database;
        $this->postManager = $postManager;
        $this->userManager = $userManager;
    }

    public function beforeRender()
    {
        parent::beforeRender();
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
    }

    public function handleRefreshWallPosts(){
        if($this->isAjax()){
            $this->redrawControl('wallPosts');
        }
    }


    public function renderDefault():void
    {
        $this->template->wall_posts = $this->postManager->getPublicPosts();
    }


    /**
     * @return Form
     */
    protected function createComponentAddPostForm(): Form
    {
        $form = new Form;

        $form->addHidden('user_id', $this->getUser()->getId());

        $form->addText('wall_post_title', 'Title')
            ->setRequired();

        $form->addTextArea('wall_post_content', 'Write your text here')
            ->setRequired();

        $form->addSubmit('publishPost', 'Publish post');

        $form->onSuccess[] = [$this, 'AddPostFormSucceeded'];

        return $form;
    }

    /**
     * @param Form $form
     * @param array $values
     * @throws Nette\Application\AbortException
     */
    public function AddPostFormSucceeded(Form $form, array $values): void
    {
        $this->database->table('wall_posts')->insert($values);

        $values = null;

        $this->flashMessage('Post was published', 'alert-success');

        $this->redirect('Homepage:');

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
     * @throws Nette\Application\AbortException
     */
    public function commentPostFormSucceeded(Form $form, array $values): void
    {
        $this->database->table('comments')->insert($values);

        $values = null;

        $this->flashMessage('Comment was published', 'alert-success');

        $this->redirect('Homepage:');

    }

}

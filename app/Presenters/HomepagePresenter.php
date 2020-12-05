<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use App\Model\PostManager;
use Nette\ComponentModel\IComponent;
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
     * HomepagePresenter constructor.
     * @param Context $database
     * @param PostManager $postManager
     */
    public function __construct(Context $database, PostManager $postManager)
    {
        parent::__construct($database);
        $this->database = $database;
        $this->postManager = $postManager;
    }

    public function beforeRender()
    {
        parent::beforeRender();
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
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

        $this->flashMessage('Post was published');

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

        $this->flashMessage('Comment was published');

        $this->redirect('Homepage:');

    }

}

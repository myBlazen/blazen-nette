<?php

namespace App\Presenters;

use Nette;
use App\Model\PostManager;
use Nette\Application\UI\Form;

class PostPresenter extends BasePresenter
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
        $this->database = $database;
        $this->postManager = $postManager;
    }


    /**
     * @throws \Nette\Application\AbortException
     */
    protected function beforeRender()
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
    }


    /**
     * @param int $wall_post_id
     */
    public function RenderShow(int $wall_post_id): void
    {
        $this->template->wall_post = $this->postManager->getPublicPostById($wall_post_id);
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

        $redirect = $values['wall_post_id'];

        $values = null;

        $this->flashMessage('Comment was published');

        $this->redirect('Post:show?wall_post_id=' . $redirect);

    }

    public function createComponentEditPostForm(): Form
    {
        $form = new Form;

        $form->addHidden('user_id', $this->getUser()->getId());

        $form->addText('wall_post_title', 'Title')
            ->setRequired();

        $form->addTextArea('wall_post_content', 'Write your text here')
            ->setRequired();

        $form->addSubmit('publishPost', 'Save changes');

        $form->onSuccess[] = [$this, 'editPostFormSucceeded'];

        return $form;
    }

    public function editPostFormSucceeded(Form $form, array $values):void
    {
        $wall_post_id = $this->getParameter('wall_post_id');

        $post = $this->database->table('wall_posts')->get($wall_post_id);
        $post->update($values);

        $this->redirect('Post:show?wall_post_id=' . $wall_post_id);
    }


    /**
     * @param int $wall_post_id
     * @throws Nette\Application\BadRequestException
     */
    public function actionEdit(int $wall_post_id): void
    {
        $post = $this->postManager->getPublicPostById($wall_post_id, false);

        bdump($post);

        if(!$post){
            $this->error('Post not found');
        }
        if($post[0]['post_user_id']!= $this->getUser()->getId()){
            $this->error('authentication failed');
        }

        $this['editPostForm']->setDefaults($post[0]);

    }

    /**
     * @param int $wall_post_id
     * @param int $post_user_id
     * @throws Nette\Application\AbortException
     * @throws Nette\Application\BadRequestException
     */
    public function actionDelete(int $wall_post_id, int $post_user_id): void
    {

        if($post_user_id != $this->getUser()->getId()){
            $this->error('authentication failed');
        }

        $values = array(
            'deleted' => true
        );

        $post = $this->database->table('wall_posts')->get($wall_post_id);
        $post->update($values);

        $this->flashMessage('Your post was deleted');

        $this->redirect('Homepage:');
    }

    /**
     * @param int $wall_post_id
     * @param int $post_user_id
     * @throws Nette\Application\AbortException
     * @throws Nette\Application\BadRequestException
     */
    public function actionHide(int $wall_post_id, int $post_user_id): void
    {

        if($post_user_id != $this->getUser()->getId()){
            $this->error('authentication failed');
        }

        $values = array(
            'hidden' => true
        );

        $post = $this->database->table('wall_posts')->get($wall_post_id);
        $post->update($values);

        $this->flashMessage('Your post was hidden, you can still see your post on your timeline but nobody else');

        $this->redirect('Homepage:#'. $wall_post_id);
    }

    public function actionPublish(int $wall_post_id, int $post_user_id):void
    {

        bdump($this->getUrl());
        if($post_user_id != $this->getUser()->getId()){
            $this->error('authentication failed');
        }

        $values = array(
            'hidden' => false
        );

        $post = $this->database->table('wall_posts')->get($wall_post_id);
        $post->update($values);

        $this->flashMessage('Your post is visible again!');

        $this->redirect('Homepage:#'. $wall_post_id);
    }


}
<?php

namespace Cms\Controller;

use Cms\Form\ArticleForm;
use Cms\Model\Article\Article;
use Cms\Model\Article\ArticleCommandInterface;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Cms\Model\Article\ArticleRepositoryInterface;

class AdminController extends AbstractActionController
{
    /**
     * @var ArticleCommandInterface
     */
    private $command;

    /**
     * @var ArticleForm
     */
    private $form;

    private $repository;

    /**
     * @param ArticleCommandInterface $command
     * @param ArticleForm $form
     */
    public function __construct(
        ArticleCommandInterface $command,
        ArticleForm $form,
        ArticleRepositoryInterface $repository
    )
    {
        $this->command = $command;
        $this->form = $form;
        $this->repository = $repository;
    }

    public function indexAction()
    {
        return new ViewModel([
            'posts' => $this->repository->findAllPosts(),
        ]);
    }

    public function addArticleAction()
    {

        $request   = $this->getRequest();
        $viewModel = new ViewModel(['form' => $this->form]);

        if (! $request->isPost()) {
            return $viewModel;
        }

        $this->form->setData($request->getPost());

        if (! $this->form->isValid()) {
            return $viewModel;
        }

        $post = $this->form->getData();

        try {
            $post = $this->command->insertPost($post);
        } catch (\Exception $ex) {
            // An exception occurred; we may want to log this later and/or
            // report it to the user. For now, we'll just re-throw.
            throw $ex;
        }

        return $this->redirect()->toRoute(
            'blog/detail',
            ['id' => $post->getId()]
        );
    }

    public function editArticleAction()
    {
        $id = $this->params()->fromRoute('id');
        if (! $id) {
            return $this->redirect()->toRoute('blog');
        }

        try {
            $post = $this->repository->findPost($id);
        } catch (InvalidArgumentException $ex) {
            return $this->redirect()->toRoute('blog');
        }

        $this->form->bind($post);
        $viewModel = new ViewModel(['form' => $this->form]);

        $request = $this->getRequest();
        if (! $request->isPost()) {
            return $viewModel;
        }

        $this->form->setData($request->getPost());

        if (! $this->form->isValid()) {
            return $viewModel;
        }

        $post = $this->command->updatePost($post);
        return $this->redirect()->toRoute(
            'cms/article/view',
            ['id' => $post->getId()]
        );
    }


    public function deleteArticleAction()
    {
        $id = $this->params()->fromRoute('id');
        if (! $id) {
            return $this->redirect()->toRoute('blog');
        }

        try {
            $post = $this->repository->findPost($id);
        } catch (InvalidArgumentException $ex) {
            return $this->redirect()->toRoute('blog');
        }

        $request = $this->getRequest();
        if (! $request->isPost()) {
            return new ViewModel(['post' => $post]);
        }

        if ($id != $request->getPost('id')
            || 'Delete' !== $request->getPost('confirm', 'no')
        ) {
            return $this->redirect()->toRoute('blog');
        }

        $post = $this->command->deletePost($post);
        return $this->redirect()->toRoute('blog');
    }
}
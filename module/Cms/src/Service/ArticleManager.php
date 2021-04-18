<?php

namespace Cms\Service;

use Cms\Model\Article\ArticleRepositoryInterface;
use Cms\Model\Article\ArticleCommandInterface;
use Cms\Form\ArticleForm;

class ArticleManager
{

    private $articleRepository;

    private $articleCommand;

    private $form;

    public function __construct(
        ArticleRepositoryInterface $articleRepository,
        ArticleCommandInterface $articleCommand,
        ArticleForm $articleForm
    ){
        $this->articleRepository = $articleRepository;
        $this->articleCommand = $articleCommand;
        $this->form = $articleForm;
    }

    public function getArticles()
    {
        return $this->articleRepository->findAllPosts();
    }

    public function getArticle($id)
    {
        if (is_numeric($id)) {
            $article = $this->articleRepository->findPost($id);
        } else {
            $article = $this->articleRepository->findPostByUrl($id);
        }

        return $article;
    }

    public function getRecentArticles($limit="")
    {
        return $this->articleRepository->findRecentPosts($limit);
    }

    public function getForm()
    {
        return $this->form;
    }


}
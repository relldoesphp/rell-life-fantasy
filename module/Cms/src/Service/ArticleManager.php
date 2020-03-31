<?php

namespace Cms\Service;

use Cms\Model\Article\ArticleRepositoryInterface;

class ArticleManager
{

    private $articleRepository;

    public function __construct(
        ArticleRepositoryInterface $articleRepository
    ){
        $this->articleRepository = $articleRepository;
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


}
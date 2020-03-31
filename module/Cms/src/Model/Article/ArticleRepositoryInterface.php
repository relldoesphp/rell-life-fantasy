<?php

namespace Cms\Model\Article;

interface ArticleRepositoryInterface
{
    public function findAllPosts();

    public function findPost($id);

    public function findPostByUrl($url);

    public function findRecentPosts($limit);
}
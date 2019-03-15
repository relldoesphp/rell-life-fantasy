<?php

namespace Blog\Model;

interface PostRepositoryInterface
{
    public function findAllPosts();

    public function findPost($id);
}
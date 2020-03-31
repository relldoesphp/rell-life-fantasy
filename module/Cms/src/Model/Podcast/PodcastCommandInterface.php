<?php

namespace Cms\Model\Podcast;

use Cms\Model\Podcast\Podcast;

interface PodcastCommandInterface
{
    public function insertPost(Podcast $post);

    public function updatePost(Podcast $post);

    public function deletePost(Podcast $post);
}
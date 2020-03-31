<?php

namespace Cms\Model\Podcast;

interface PodcastRepositoryInterface
{
    public function findAllPodcasts();

    public function findPodcast($id);

    public function findRecentPodcast($limit);
}
<?php


namespace Cms\Service;


use Cms\Model\Podcast\PodcastRepositoryInterface;

class PodcastManager
{
    private $podcastRepository;

    public function __construct(
        PodcastRepositoryInterface $podcastRepository
    ){
        $this->podcastRepository = $podcastRepository;
    }

    public function getAllPodcasts()
    {
        return $this->podcastRepository->findAllPodcasts();
    }

    public function getPodcast($id)
    {
        return $this->podcastRepository->findPodcast($id);
    }

    public function getRecentPodcasts($limit="")
    {
        return $this->podcastRepository->findRecentPodcast($limit);
    }
}
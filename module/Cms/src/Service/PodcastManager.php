<?php


namespace Cms\Service;


use Cms\Form\PodcastForm;
use Cms\Model\Podcast\PodcastRepositoryInterface;
use Cms\Model\Podcast\PodcastCommandInterface;

class PodcastManager
{
    private $podcastRepository;

    private $podcastCommand;

    private $form;

    public function __construct(
        PodcastRepositoryInterface $podcastRepository,
        PodcastCommandInterface $podcastCommand,
        PodcastForm $podcastForm
    ){
        $this->podcastRepository = $podcastRepository;
        $this->podcastCommand = $podcastCommand;
        $this->form = $podcastForm;
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

    public function getForm()
    {
        $this->form->init();
        return $this->form;
    }

    public function addPodcast($podcast)
    {
        return $this->podcastCommand->insertPost($podcast);
    }

    public function updatePodcast($podcast)
    {
        return $this->podcastCommand->updatePost($podcast);
    }

    public function deletePodcast($podcast)
    {
        return $this->podcastCommand->deletePost($podcast);
    }
}
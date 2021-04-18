<?php


namespace Cms\Controller;

use Cms\Service\PodcastManager;
use Laminas\View\Model\ViewModel;

class PodcastController
{
    private $podcastManager;

    public function __construct(
        PodcastManager $podcastManager
    )
    {
        $this->podcastManager = $podcastManager;
    }

    public function indexAction()
    {
        return new ViewModel([
            'posts' => $this->podcastManager->getAllPodcasts(),
        ]);
    }

    public function viewAction()
    {
        $id = $this->params()->fromRoute('id');

        try {
            $podcast = $this->podcastManager->findPodcast($id);

            $recents = $this->podcastManager->findRecentPosts(4);

        } catch (\InvalidArgumentException $ex) {
            return $this->redirect()->toRoute('podcast');
        }

        return new ViewModel([
            'podcast' => $podcast,
            'recents' => $recents
        ]);
    }
}
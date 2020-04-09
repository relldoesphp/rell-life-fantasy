<?php


namespace Cms\Service\Factory;

use Cms\Model\Podcast\PodcastRepositoryInterface;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Cms\Service\PodcastManager;
use Cms\Model\Podcast\PodcastCommandInterface;
use Cms\Form\PodcastForm;

class PodcastManagerFactory
{
    /**p
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new PodcastManager(
            $container->get(PodcastRepositoryInterface::class),
            $container->get(PodcastCommandInterface::class),
            new PodcastForm()
        );
    }
}
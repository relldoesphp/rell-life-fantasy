<?php


namespace Cms\Service\Factory;

use Cms\Model\Podcast\PodcastRepositoryInterface;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Cms\Service\PodcastManager;

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
            $container->get(PodcastRepositoryInterface::class)
        );
    }
}
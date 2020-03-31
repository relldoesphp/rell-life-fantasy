<?php


namespace Cms\Service\Factory;


use Cms\Model\Article\ArticleRepositoryInterface;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Cms\Service\ArticleManager;

class ArticleManagerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ArticleManager(
            $container->get(ArticleRepositoryInterface::class)
        );
    }
}
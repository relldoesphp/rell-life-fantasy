<?php


namespace Cms\Service\Factory;


use Cms\Model\Article\ArticleRepositoryInterface;
use Cms\Model\Article\ArticleCommandInterface;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Cms\Service\ArticleManager;
use Cms\Form\ArticleForm;

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
            $container->get(ArticleRepositoryInterface::class),
            $container->get(ArticleCommandInterface::class),
            new ArticleForm()
        );
    }
}
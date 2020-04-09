<?php

namespace Cms\Controller\Factory;

use Cms\Controller\AdminController;
use Cms\Form\ArticleForm;
use Cms\Model\Article\ArticleCommandInterface;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Cms\Model\Article\ArticleRepositoryInterface;
use Cms\Service\PodcastManager;
use Cms\Service\ArticleManager;

class AdminControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return AdminController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $formManager = $container->get('FormElementManager');
        return new AdminController(
            $container->get(ArticleCommandInterface::class),
            $formManager->get(ArticleForm::class),
            $container->get(ArticleRepositoryInterface::class),
            $container->get(PodcastManager::class),
            $container->get(ArticleManager::class)
        );
    }
}
<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 3/13/19
 * Time: 10:30 PM
 */

namespace Cms\Controller\Factory;

use Cms\Controller\ArticleController;
use Cms\Model\Article\ArticleRepositoryInterface;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ArticleControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return ArticleController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ArticleController($container->get(ArticleRepositoryInterface::class));
    }
}
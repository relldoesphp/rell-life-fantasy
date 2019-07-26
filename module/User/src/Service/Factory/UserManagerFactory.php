<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 7/23/19
 * Time: 12:27 AM
 */

namespace User\Service\Factory;

use User\Model\User\RepositoryInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use User\Service\UserManager;

class UserManagerFactory implements FactoryInterface
{
    /**
     * This method creates the UserManager service and returns its instance.
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $repository = $container->get(RepositoryInterface::class);
        $command = $container->get(ContainerInterface::class);
        $viewRenderer = $container->get('ViewRenderer');
        $config = $container->get('Config');

        return new UserManager($repository, $command, $viewRenderer, $config);
    }
}
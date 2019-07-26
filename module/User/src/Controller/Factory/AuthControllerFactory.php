<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 7/23/19
 * Time: 12:54 PM
 */

namespace User\Controller\Factory;

use User\Model\User\CommandInterface;
use User\Model\User\RepositoryInterface;
use User\Service\Factory\AuthManagerFactory;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use User\Controller\AuthController;
use User\Service\AuthManager;
use User\Service\UserManager;

class AuthControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new AuthController(
            $container->get(RepositoryInterface::class),
            $container->get(CommandInterface::class),
            $container->get(AuthManager::class),
            $container->get(UserManager::class)
        );
    }
}
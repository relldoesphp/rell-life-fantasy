<?php


namespace User\Controller\Factory;


use User\Controller\AdminController;
use Interop\Container\ContainerInterface;
use User;
use Laminas\ServiceManager\Factory\FactoryInterface;

class AdminControllerFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        // TODO: Implement __invoke() method.
        return new AdminController(
            $container->get(User\Service\UserManager::class)
        );
    }
}
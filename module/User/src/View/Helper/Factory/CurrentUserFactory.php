<?php

namespace User\View\Helper\Factory;

use Interop\Container\ContainerInterface;
use User\View\Helper\CurrentUser;
use User;
use Laminas\ServiceManager\Factory\FactoryInterface;

class CurrentUserFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $userManager = $container->get(User\Service\UserManager::class);
        $authService = $container->get(\Laminas\Authentication\AuthenticationService::class);

        return new CurrentUser($userManager, $authService);
    }
}
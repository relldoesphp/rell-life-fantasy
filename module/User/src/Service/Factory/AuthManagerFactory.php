<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 7/23/19
 * Time: 11:52 AM
 */

namespace User\Service\Factory;

use User\Model\User\RepositoryInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use User\Service\AuthManager;
use Laminas\Session\SessionManager;

class AuthManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $authService = $container->get(\Laminas\Authentication\AuthenticationService::class);
        $sessionManager = $container->get(SessionManager::class);
        $config = $container->get('Config');

        return new AuthManager($authService, $sessionManager, $config);
    }
}
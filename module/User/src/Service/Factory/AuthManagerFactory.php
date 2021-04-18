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
use User\Service\PatreonManager;
use User\Service\RbacManager;
use User\Service\UserManager;

class AuthManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $authService = $container->get(\Laminas\Authentication\AuthenticationService::class);
        $sessionManager = $container->get(SessionManager::class);
        $config = $container->get('Config');
        $patreon = $container->get(PatreonManager::class);
        $user = $container->get(UserManager::class);
        $rbacManager = $container->get(RbacManager::class);

        return new AuthManager($authService, $sessionManager, $config, $patreon, $user, $rbacManager);
    }
}
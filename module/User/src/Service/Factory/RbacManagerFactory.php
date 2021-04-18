<?php
namespace User\Service\Factory;

use Interop\Container\ContainerInterface;
use User\Service\RbacManager;
use Laminas\Authentication\AuthenticationService;
use User\Service\UserManager;

/**
 * This is the factory class for RbacManager service. The purpose of the factory
 * is to instantiate the service and pass it dependencies (inject dependencies).
 */
class RbacManagerFactory
{
    /**
     * This method creates the RbacManager service and returns its instance.
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $userManager = $container->get(UserManager::class);
        $authService = $container->get(\Laminas\Authentication\AuthenticationService::class);
        $cache = $container->get('FilesystemCache');

        $assertionManagers = [];
        $config = $container->get('config');
        if (isset($config['rbac_manager']['assertions'])) {
            foreach ($config['rbac_manager']['assertions'] as $serviceName) {
                $assertionManagers[$serviceName] = $container->get($serviceName);
            }
        }

        return new RbacManager($userManager, $authService, $cache, $assertionManagers);
    }
}
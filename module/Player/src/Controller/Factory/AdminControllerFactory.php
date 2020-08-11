<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 3/22/19
 * Time: 8:55 AM
 */

namespace Player\Controller\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Player\Model\Player\PlayerRepositoryInterface;
use Player\Controller\AdminController;
use Player\Model\Player;
use Player\Service\TeamManager;

class AdminControllerFactory implements FactoryInterface
{
    /**
     * @return AdminController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new AdminController(
            $container->get(PlayerRepositoryInterface::class),
            $container->get(Player\PlayerCommandInterface::class),
            $container->get(TeamManager::class)
        );
    }
}
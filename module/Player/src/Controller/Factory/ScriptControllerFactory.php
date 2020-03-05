<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 3/22/19
 * Time: 9:36 AM
 */

namespace Player\Controller\Factory;

use Player\Model\Player\PlayerRepositoryInterface;
use Player\Service\StatsManager;
use Player\Service\TeamManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Player\Controller\ScriptController;
use Player\Model\Player\PlayerCommandInterface;
use Player\Service\PlayerManager;

class ScriptControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return ScriptController
     */

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ScriptController(
            $container->get(PlayerCommandInterface::class),
            $container->get(PlayerRepositoryInterface::class),
            $container->get(PlayerManager::class),
            $container->get(StatsManager::class),
            $container->get(TeamManager::class)
        );
    }
}
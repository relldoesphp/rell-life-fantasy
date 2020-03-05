<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 9/15/19
 * Time: 1:47 AM
 */

namespace Player\Controller\Factory;


use Player\Controller\TeamController;
use Player\Service\MatchupManager;
use Player\Service\TeamManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;


class TeamControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new TeamController(
            $container->get(TeamManager::class),
            $container->get(MatchupManager::class)
        );
    }
}
<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 10/27/19
 * Time: 6:09 PM
 */

namespace Player\Controller\Factory;

use Player\Controller\MatchupController;
use Player\Service\MatchupManager;
use Player\Service\TeamManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;


class MatchupControllerFactory implements FactoryInterface
{
    /**
     * @return MatchupController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new MatchupController(
            $container->get(TeamManager::class),
            $container->get(MatchupManager::class)
        );
    }
}
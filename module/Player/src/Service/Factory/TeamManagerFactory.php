<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 9/13/19
 * Time: 2:16 PM
 */

namespace Player\Service\Factory;


use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Player\Model\Stats\StatsCommandInterface;
use Player\Model\Stats\StatsRepositoryInterface;
use Player\Model\Team\TeamCommandInterface;
use Player\Model\Team\TeamRepositoryInterface;
use Player\Service\StatsManager;
use Player\Service\TeamManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Player\Model\Player\PlayerCommandInterface;
use Player\Model\Player\PlayerRepositoryInterface;
use Laminas\ProgressBar\Adapter\Console;


class TeamManagerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new TeamManager(
            $container->get(TeamRepositoryInterface::class),
            $container->get(TeamCommandInterface::class),
            $container->get(PlayerRepositoryInterface::class),
            new Console()
        );
    }
}
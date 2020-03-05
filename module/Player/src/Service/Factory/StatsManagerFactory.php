<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 8/11/19
 * Time: 2:18 AM
 */

namespace Player\Service\Factory;


use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Player\Model\Stats\StatsCommandInterface;
use Player\Model\Stats\StatsRepositoryInterface;
use Player\Service\StatsManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Player\Model\Player\PlayerCommandInterface;
use Player\Model\Player\PlayerRepositoryInterface;
use Laminas\ProgressBar\Adapter\Console;

class StatsManagerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new StatsManager(
            $container->get('Dtw\Db\Adapter'),
            new Console(),
            $container->get(PlayerCommandInterface::class),
            $container->get(PlayerRepositoryInterface::class),
            $container->get(StatsCommandInterface::class),
            $container->get(StatsRepositoryInterface::class)
        );
    }
}
<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 8/11/19
 * Time: 2:03 AM
 */

namespace Player\Model\Stats\Sql\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Player\Model\Stats\Sql\SqlStatsRepository;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Hydrator\Reflection as ReflectionHydrator;
use Player\Model\Stats\GameLog;
use Player\Model\Stats\SeasonStats;

class SqlStatRepositoryFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new SqlStatsRepository(
            $container->get('Dtw\Db\Adapter'),
            new ReflectionHydrator(),
            new GameLog(),
            new SeasonStats()
        );
    }

}
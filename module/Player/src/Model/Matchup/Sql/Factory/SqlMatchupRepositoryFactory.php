<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 10/23/19
 * Time: 12:59 PM
 */

namespace Player\Model\Matchup\Sql\Factory;

use Player\Model\Matchup\Sql\SqlMatchupRepository;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Player\Model\Matchup\Matchup;
use Zend\Hydrator\Reflection as ReflectionHydrator;

class SqlMatchupRepositoryFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new SqlMatchupRepository(
            $container->get('Dtw\Db\Adapter'),
            new ReflectionHydrator(),
            new Matchup()
        );
    }
}